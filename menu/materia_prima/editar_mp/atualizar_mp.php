<?php
require_once '../../verificacao_sessao.php';
include '../../conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obter e sanitizar dados
    $id_mp = filter_input(INPUT_POST, 'id_mp', FILTER_VALIDATE_INT);
    $nome = strtoupper(htmlspecialchars(trim(filter_input(INPUT_POST, 'nome', FILTER_UNSAFE_RAW))));
    $custo = str_replace(',', '.', filter_input(INPUT_POST, 'custo', FILTER_UNSAFE_RAW)); // Substitui vírgula por ponto
    $custo = number_format(floatval($custo), 2, '.', ''); // Converte para float e força 2 casas decimais
    $quantidade = filter_input(INPUT_POST, 'quantidade', FILTER_VALIDATE_FLOAT);
    $medida = filter_input(INPUT_POST, 'medida', FILTER_DEFAULT);
    $descricao = isset($_POST['descricao']) ? htmlspecialchars(trim($_POST['descricao'])) : null;
    $quantidade_min = filter_input(INPUT_POST, 'quantidade_min', FILTER_VALIDATE_FLOAT);

    $conn->begin_transaction();

    // Pegar os valores atuais
    $query_antigo = "SELECT nome, custo, quantidade, medida, descricao, quantidade_min FROM materia_prima WHERE id_mp = ?";
    $stmt_antigo = $conn->prepare($query_antigo);
    $stmt_antigo->bind_param("i", $id_mp);
    $stmt_antigo->execute();
    $result_antigo = $stmt_antigo->get_result()->fetch_assoc() ?: [];
    $stmt_antigo->close();

    // Atualizar os dados no banco de dados
    $query = "UPDATE materia_prima SET nome = ?, custo = ?, quantidade = ?, medida = ?, descricao = ?, quantidade_min = ? WHERE id_mp = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sddsssi", $nome, $custo, $quantidade, $medida, $descricao, $quantidade_min, $id_mp);

    try {
        $stmt->execute();

        // Gera os detalhes das alterações
        $detalhes = '';
        if ($result_antigo) {
            $campos = [
                'nome' => $nome,
                'custo' => $custo,
                'quantidade' => $quantidade,
                'medida' => $medida,
                'descricao' => $descricao,
                'quantidade_min' => $quantidade_min
            ];

            $nomes_humanos = [
                'nome' => 'Nome: ',
                'custo' => 'Custo: ',
                'quantidade' => 'Quantidade: ',
                'medida' => 'Medida: ',
                'descricao' => 'Descrição: ',
                'quantidade_min' => 'Quantidade Mínima: '
            ];

            foreach ($campos as $campo => $novo_valor) {
                $valor_antigo = $result_antigo[$campo] ?? null;
                // Conversão explícita para tipos consistentes
                if ($campo === 'custo' || $campo === 'quantidade' || $campo === 'quantidade_min') {
                    $valor_antigo = $custo = number_format(floatval($valor_antigo), 2, '.', '');
                    $novo_valor = number_format(floatval($novo_valor), 2, '.', '');
                } else {
                    $valor_antigo = strval($valor_antigo);
                    $novo_valor = strval($novo_valor);
                }
                if ($novo_valor !== $valor_antigo && ($novo_valor !== null || $valor_antigo !== null)) {
                    $detalhes .= $nomes_humanos[$campo] . ($valor_antigo ?? 'N/A') . " => " . ($novo_valor ?? 'N/A') . ";\n";
                }
            }
            $detalhes = trim($detalhes, ";\n");
        }

        // Inserir no histórico
        if (!empty($detalhes)) {
            $acao = "ATUALIZAÇÃO";
            $item = "$nome";
            $query_historico = "INSERT INTO historico (funcionario_id, secao, item, acao, detalhes) VALUES (?, ?, ?, ?, ?)";
            $stmt_historico = $conn->prepare($query_historico);
            $user_id = $_SESSION['user_id'];
            $secao = 'Matéria Prima';
            $stmt_historico->bind_param("issss", $user_id, $secao, $item, $acao, $detalhes);
            $stmt_historico->execute();
            $stmt_historico->close();
        }

        $conn->commit();
        header("Location: ..?success=" . urlencode("Matéria-prima atualizada com sucesso."));
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        header("Location: ../editar_mp?Erro=Erro ao atualizar matéria-prima: " . urlencode($e->getMessage()));
        exit;
    }

    $stmt->close();
}

$conn->close();
?>