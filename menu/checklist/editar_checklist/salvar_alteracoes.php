<?php
require_once '../../verificacao_sessao.php';
include '../../conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_checklist = filter_input(INPUT_POST, 'id_checklist', FILTER_VALIDATE_INT);
    $nome_num = mb_strtoupper($_POST['nome_num'] ?? '');
    $km_saida = isset($_POST['km_saida']) && is_numeric($_POST['km_saida']) ? (int)$_POST['km_saida'] : 0;
    $item_ids = $_POST['item_ids'] ?? [];
    $quantidades = $_POST['quantidades'] ?? [];
    $removed_ids = !empty($_POST['removed_ids']) ? explode(',', $_POST['removed_ids']) : [];

    if (!$id_checklist) {
        die('ID do checklist inválido.');
    }
    if (empty($nome_num)) {
        die('Nome/Número do serviço é obrigatório.');
    }
    if (count($item_ids) !== count($quantidades)) {
        die('Os arrays de itens e quantidades devem ter o mesmo tamanho.');
    }

    $conn->begin_transaction();

    try {
        // Obter itens existentes
        $existing_items = [];
        $query = "SELECT id, id_ferramenta, id_maleta, quantidade_levada 
                  FROM caixa_ferramentas 
                  WHERE id_checklist = ? 
                  AND (id_ferramenta IS NOT NULL OR id_maleta IS NOT NULL)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id_checklist);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $key = $row['id_ferramenta'] ? 'ferramenta_' . $row['id_ferramenta'] : 'maleta_' . $row['id_maleta'];
            $existing_items[$key] = $row;
        }
        $stmt->close();

        // Atualizar checklist
        $update_query = "UPDATE checklist SET nome_num = ?, km_saida = ? WHERE id_checklist = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("sii", $nome_num, $km_saida, $id_checklist);
        if (!$stmt->execute()) {
            throw new Exception("Erro ao atualizar checklist: " . $stmt->error);
        }
        $stmt->close();

        // Deletar itens removidos e reverter estoque
        if (!empty($removed_ids)) {
            $delete_query = "DELETE FROM caixa_ferramentas WHERE id = ?";
            $stmt = $conn->prepare($delete_query);
            foreach ($removed_ids as $id) {
                if (is_numeric($id)) {
                    $check_query = "SELECT id_ferramenta, id_maleta, quantidade_levada 
                                    FROM caixa_ferramentas 
                                    WHERE id = ? AND id_checklist = ?";
                    $check_stmt = $conn->prepare($check_query);
                    $check_stmt->bind_param("ii", $id, $id_checklist);
                    $check_stmt->execute();
                    $check_result = $check_stmt->get_result();
                    if ($check_result->num_rows > 0) {
                        $item = $check_result->fetch_assoc();
                        $stmt->bind_param("i", $id);
                        $stmt->execute();

                        if ($item['id_ferramenta']) {
                            $restore_query = "UPDATE ferramentas 
                                             SET quantidade_atual = quantidade_atual + ?, 
                                                 situacao = IF(quantidade_atual + ? > 0, 'Disponível', situacao),
                                                 nome_num = IF(quantidade_atual + ? > 0, NULL, nome_num)
                                             WHERE id_ferramenta = ?";
                            $restore_stmt = $conn->prepare($restore_query);
                            $restore_stmt->bind_param("iisi", $item['quantidade_levada'], $item['quantidade_levada'], $item['quantidade_levada'], $item['id_ferramenta']);
                            $restore_stmt->execute();
                            $restore_stmt->close();
                        } elseif ($item['id_maleta']) {
                            $restore_query = "UPDATE maletas 
                                             SET situacao = 'Disponível', 
                                                 nome_num = NULL 
                                             WHERE id_maleta = ?";
                            $restore_stmt = $conn->prepare($restore_query);
                            $restore_stmt->bind_param("i", $item['id_maleta']);
                            $restore_stmt->execute();
                            $restore_stmt->close();

                            $ferramentas_maleta_query = "SELECT id_ferramenta, quantidade AS quantidade_levada FROM ferramenta_maleta WHERE id_maleta = ?";
                            $fm_stmt = $conn->prepare($ferramentas_maleta_query);
                            $fm_stmt->bind_param("i", $item['id_maleta']);
                            $fm_stmt->execute();
                            $fm_result = $fm_stmt->get_result();
                            while ($fm_row = $fm_result->fetch_assoc()) {
                                $restore_ferramenta_query = "UPDATE ferramentas 
                                                            SET quantidade_atual = quantidade_atual + ?, 
                                                                situacao = IF(quantidade_atual + ? > 0, 'Disponível', situacao),
                                                                nome_num = IF(quantidade_atual + ? > 0, NULL, nome_num)
                                                            WHERE id_ferramenta = ?";
                                $restore_ferramenta_stmt = $conn->prepare($restore_ferramenta_query);
                                $restore_ferramenta_stmt->bind_param("iisi", $fm_row['quantidade_levada'], $fm_row['quantidade_levada'], $fm_row['quantidade_levada'], $fm_row['id_ferramenta']);
                                $restore_ferramenta_stmt->execute();
                                $restore_ferramenta_stmt->close();
                            }
                            $fm_stmt->close();
                        }
                    }
                    $check_stmt->close();
                }
            }
            $stmt->close();
        }

        // Processar itens (atualizar ou inserir)
        $insert_query = "INSERT INTO caixa_ferramentas (id_checklist, id_ferramenta, id_maleta, quantidade_levada, quantidade_devolvida, retornado) 
                         VALUES (?, ?, ?, ?, ?, ?) 
                         ON DUPLICATE KEY UPDATE 
                         quantidade_levada = VALUES(quantidade_levada),
                         quantidade_devolvida = VALUES(quantidade_devolvida),
                         retornado = VALUES(retornado)";
        $stmt = $conn->prepare($insert_query);
        for ($i = 0; $i < count($item_ids); $i++) {
            if (empty($item_ids[$i]) || !is_numeric($quantidades[$i])) {
                throw new Exception('Dados inválidos na posição ' . $i);
            }

            $tipo = strpos($item_ids[$i], 'ferramenta_') === 0 ? 'ferramenta' : 'maleta';
            $id = (int)str_replace(['ferramenta_', 'maleta_'], '', $item_ids[$i]);
            $nova_quantidade = $tipo === 'maleta' ? 1 : floatval($quantidades[$i]);
            $quantidade_devolvida = 0;
            $retornado = 'NOK';

            $id_ferramenta = $tipo === 'ferramenta' ? $id : null;
            $id_maleta = $tipo === 'maleta' ? $id : null;
            $key = $item_ids[$i];

            if (isset($existing_items[$key])) {
                $quantidade_antiga = floatval($existing_items[$key]['quantidade_levada']);
                $diferenca_quantidade = $nova_quantidade - $quantidade_antiga;
                $update_existing_query = "UPDATE caixa_ferramentas 
                                         SET quantidade_levada = ?, quantidade_devolvida = ?, retornado = ?
                                         WHERE id_checklist = ? AND id_ferramenta = ? AND id_maleta = ?";
                $update_stmt = $conn->prepare($update_existing_query);
                $update_stmt->bind_param("iissii", $nova_quantidade, $quantidade_devolvida, $retornado, $id_checklist, $id_ferramenta, $id_maleta);
                $update_stmt->execute();
                $update_stmt->close();
            } else {
                $diferenca_quantidade = $nova_quantidade;
                $stmt->bind_param("iiiiss", $id_checklist, $id_ferramenta, $id_maleta, $nova_quantidade, $quantidade_devolvida, $retornado);
                $stmt->execute();
            }

            // Atualizar estoque
            if ($diferenca_quantidade != 0) {
                if ($tipo === 'ferramenta') {
                    $update_stock_query = "UPDATE ferramentas 
                                          SET quantidade_atual = quantidade_atual - ?, 
                                              situacao = IF(quantidade_atual - ? <= 0, 'Em uso', 'Disponível'),
                                              nome_num = ?
                                          WHERE id_ferramenta = ?";
                    $stock_stmt = $conn->prepare($update_stock_query);
                    $stock_stmt->bind_param("iisi", $diferenca_quantidade, $diferenca_quantidade, $nome_num, $id);
                    $stock_stmt->execute();
                    $stock_stmt->close();
                } elseif ($tipo === 'maleta') {
                    $update_stock_query = "UPDATE maletas 
                                          SET situacao = 'Em uso', 
                                              nome_num = ? 
                                          WHERE id_maleta = ?";
                    $stock_stmt = $conn->prepare($update_stock_query);
                    $stock_stmt->bind_param("si", $nome_num, $id);
                    $stock_stmt->execute();
                    $stock_stmt->close();

                    $ferramentas_maleta_query = "SELECT fm.id_ferramenta, fm.quantidade AS quantidade_levada FROM ferramenta_maleta fm WHERE fm.id_maleta = ?";
                    $fm_stmt = $conn->prepare($ferramentas_maleta_query);
                    $fm_stmt->bind_param("i", $id);
                    $fm_stmt->execute();
                    $fm_result = $fm_stmt->get_result();
                    while ($fm_row = $fm_result->fetch_assoc()) {
                        $id_ferramenta = $fm_row['id_ferramenta'];
                        $quantidade_levada = $fm_row['quantidade_levada'];
                        $update_ferramenta_query = "UPDATE ferramentas 
                                                   SET quantidade_atual = quantidade_atual - ?, 
                                                       situacao = IF(quantidade_atual - ? <= 0, 'Em uso', 'Disponível'),
                                                       nome_num = ?
                                                   WHERE id_ferramenta = ?";
                        $update_ferramenta_stmt = $conn->prepare($update_ferramenta_query);
                        $update_ferramenta_stmt->bind_param("iisi", $quantidade_levada, $quantidade_levada, $nome_num, $id_ferramenta);
                        $update_ferramenta_stmt->execute();
                        $update_ferramenta_stmt->close();
                    }
                    $fm_stmt->close();
                }
            }
        }
        $stmt->close();

        $conn->commit();
        header('Location: ..?success=1');
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Erro ao salvar checklist: " . $e->getMessage());
        header('Location: ..?error=' . urlencode('Erro ao salvar alterações: ' . $e->getMessage()));
        exit();
    }
}

$conn->close();
