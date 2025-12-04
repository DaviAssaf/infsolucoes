<?php
include_once '../../verificacao_sessao.php';
include '../../conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_registro = filter_input(INPUT_POST, 'id_registro', FILTER_VALIDATE_INT);
    $ordem_servico = $_POST['ordem_servico'] ?? null;
    $custo_final = floatval($_POST['custo_final'] ?? 0.0); // Forçar conversão para float
    $mp_ids = $_POST['mp_ids'] ?? [];
    $quantidades = $_POST['quantidades'] ?? [];
    $custos_totais = $_POST['custos_totais'] ?? [];
    $removed_ids = !empty($_POST['removed_ids']) ? explode(',', $_POST['removed_ids']) : [];

    // Depuração
    error_log("custo_final recebido: " . $custo_final);
    error_log("POST data: " . print_r($_POST, true));

    // Validação básica
    if (!$id_registro) {
        die('ID do registro inválido.');
    }
    if (count($mp_ids) !== count($quantidades) || count($mp_ids) !== count($custos_totais)) {
        die('Os arrays de matérias-primas, quantidades e custos totais devem ter o mesmo tamanho.');
    }

    $query = "SELECT tipo FROM registro_estoque WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_registro);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        die('Registro de estoque não encontrado.');
    }
    $row = $result->fetch_assoc();
    $tipo = $row['tipo'] ?? null;
    if ($tipo === null) {
        die('Tipo de registro inválido.');
    }
    $stmt->close();

    // Iniciar transação
    $conn->begin_transaction();

    try {
        // Obter quantidades existentes
        $existing_items = [];
        $query = "SELECT id, id_mp, quantidade FROM saida_estoque WHERE id_registro_estoque = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id_registro);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $existing_items[$row['id_mp']] = $row['quantidade'];
        }
        $stmt->close();

        // Atualizar registro_estoque (permitir null para ordem_servico se não aplicável)
        $update_query = "UPDATE registro_estoque SET os = ?, custo_final = ? WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("sdi", $ordem_servico, $custo_final, $id_registro);
        if (!$stmt->execute()) {
            throw new Exception("Erro ao atualizar registro_estoque: " . $stmt->error);
        }
        $stmt->close();

        // Deletar itens removidos
        if (!empty($removed_ids)) {
            $delete_query = "DELETE FROM saida_estoque WHERE id = ?";
            $stmt = $conn->prepare($delete_query);
            foreach ($removed_ids as $id) {
                if (is_numeric($id)) {
                    $get_info_query = "SELECT s.id_mp, s.quantidade, r.tipo FROM saida_estoque s JOIN registro_estoque r ON s.id_registro_estoque = r.id WHERE s.id = ?";
                    $get_info_stmt = $conn->prepare($get_info_query);
                    $get_info_stmt->bind_param("i", $id);
                    $get_info_stmt->execute();
                    $get_info_result = $get_info_stmt->get_result();
                    if ($info = $get_info_result->fetch_assoc()) {
                        $mp_id_restore = $info['id_mp'];
                        $quantidade_restore = $info['quantidade'];
                        $tipo_restore = $info['tipo'];
                        $restore_query = $tipo_restore == 1 ? "UPDATE materia_prima SET quantidade = quantidade + ? WHERE id_mp = ?" : "UPDATE materia_prima SET quantidade = quantidade - ? WHERE id_mp = ?";
                        $restore_stmt = $conn->prepare($restore_query);
                        if (!$restore_stmt) {
                            throw new Exception("Erro ao preparar query de restauração: " . $conn->error);
                        }
                        $restore_stmt->bind_param("di", $quantidade_restore, $mp_id_restore);
                        if (!$restore_stmt->execute()) {
                            throw new Exception("Erro ao restaurar estoque: " . $restore_stmt->error);
                        }
                        $restore_stmt->close();
                    }
                    $get_info_stmt->close();

                    $stmt->bind_param("i", $id);
                    if (!$stmt->execute()) {
                        throw new Exception("Erro ao deletar item: " . $stmt->error);
                    }
                }
            }
            $stmt->close();
        }

        // Processar itens (atualizar diferença e inserir novos)
        $insert_query = "INSERT INTO saida_estoque (id_registro_estoque, id_mp, quantidade, custo_total) 
                         VALUES (?, ?, ?, ?) 
                         ON DUPLICATE KEY UPDATE quantidade = VALUES(quantidade), custo_total = VALUES(custo_total)";
        $stmt = $conn->prepare($insert_query);
        for ($i = 0; $i < count($mp_ids); $i++) {
            if (!is_numeric($mp_ids[$i]) || !is_numeric($quantidades[$i]) || !is_numeric($custos_totais[$i])) {
                throw new Exception('Dados inválidos na posição ' . $i);
            }

            $mp_id = $mp_ids[$i];
            $nova_quantidade = floatval($quantidades[$i]);
            $novo_custo_total = floatval($custos_totais[$i]);
            $diferenca_quantidade = $nova_quantidade - ($existing_items[$mp_id] ?? 0); // Diferença direta

            if ($diferenca_quantidade != 0) {
                $check_stock_query = "SELECT quantidade FROM materia_prima WHERE id_mp = ? FOR UPDATE";
                $stock_check_stmt = $conn->prepare($check_stock_query);
                $stock_check_stmt->bind_param("i", $mp_id);
                $stock_check_stmt->execute();
                $stock_result = $stock_check_stmt->get_result();
                $current_stock = $stock_result->fetch_assoc()['quantidade'] ?? 0;
                $stock_check_stmt->close();

                $novo_estoque = $current_stock + ($tipo == 0 ? -$diferenca_quantidade : $diferenca_quantidade);
                if ($novo_estoque < 0) {
                    throw new Exception("Estoque insuficiente para id_mp $mp_id. Disponível: $current_stock");
                }

                $update_stock_query = $tipo == 0 ? "UPDATE materia_prima SET quantidade = quantidade - ?, last_commit = ? WHERE id_mp = ?" : "UPDATE materia_prima SET quantidade = quantidade + ?, last_commit = ? WHERE id_mp = ?";
                $stock_stmt = $conn->prepare($update_stock_query);
                $last_commit = htmlspecialchars("-" . $novo_custo_total);
                $stock_stmt->bind_param("dsi", abs($diferenca_quantidade), $last_commit, $mp_id); // Usa o valor absoluto
                if (!$stock_stmt->execute()) {
                    throw new Exception("Erro ao atualizar estoque: " . $stmt->error);
                }
                $stock_stmt->close();
            }

            $stmt->bind_param("iidd", $id_registro, $mp_id, $nova_quantidade, $novo_custo_total);
            if (!$stmt->execute()) {
                throw new Exception("Erro ao inserir/atualizar saida_estoque: " . $stmt->error);
            }
        }
        $stmt->close();

        // Confirmar transação
        $conn->commit();
        header('Location: ..');
        exit();
    } catch (Exception $e) {
        // Reverter transação em caso de erro
        $conn->rollback();
        die('Erro ao salvar alterações: ' . htmlspecialchars($e->getMessage()));
    }
}
