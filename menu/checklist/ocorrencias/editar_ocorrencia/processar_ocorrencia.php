<?php
require '../../../verificacao_sessao.php';
include '../../../conn.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Método inválido. Use POST.');
}

if (!isset($_POST['id_ocorrencia']) || !($id_ocorrencia = filter_input(INPUT_POST, 'id_ocorrencia', FILTER_VALIDATE_INT)) || $id_ocorrencia <= 0) {
    die('ID da ocorrência inválido ou não especificado.');
}

// Validação e sanitização
$num_ordem_servico = filter_input(INPUT_POST, 'num_ordem_servico', FILTER_VALIDATE_INT) ?? null;
$responsavel = filter_input(INPUT_POST, 'responsavel', FILTER_VALIDATE_INT) ?? null;
$id_ferramenta = filter_input(INPUT_POST, 'id_ferramenta', FILTER_VALIDATE_INT) ?? null;
$id_maleta = filter_input(INPUT_POST, 'id_maleta', FILTER_VALIDATE_INT) ?? null;
$id_mp = filter_input(INPUT_POST, 'id_mp', FILTER_VALIDATE_INT) ?? null;
$data_ocorrencia = filter_input(INPUT_POST, 'data_ocorrencia', FILTER_UNSAFE_RAW);
if ($data_ocorrencia && !preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $data_ocorrencia)) {
    $data_ocorrencia = date('Y-m-d H:i:s'); // Fallback para data atual se inválida
}
$descricao = filter_input(INPUT_POST, 'descricao', FILTER_UNSAFE_RAW) ?? '';
$situacao = filter_input(INPUT_POST, 'situacao', FILTER_UNSAFE_RAW) ?? 'A resolver';

$conn->begin_transaction();

// Obtém os valores antigos
$query = "SELECT nome_num, responsavel, id_ferramenta, id_maleta, id_mp, data_ocorrencia, descricao, situacao FROM ocorrencias WHERE num_ocorrencia = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_ocorrencia);
$stmt->execute();
$result_antes = $stmt->get_result()->fetch_assoc() ?: [];
$stmt->close();

// Prepara a atualização (apenas campos editáveis)
$query_update = "UPDATE ocorrencias 
    SET responsavel = ?, situacao = ? 
    WHERE num_ocorrencia = ?";
$stmt = $conn->prepare($query_update);
$stmt->bind_param("isi", $responsavel, $situacao, $id_ocorrencia);

try {
    $stmt->execute();

    // Gera os detalhes das alterações
    $detalhes = '';
    if ($result_antes) {
        $campos = [
            'responsavel' => $responsavel,
            'situacao' => $situacao
        ];

        foreach ($campos as $campo => $novo_valor) {
            $valor_antigo = $result_antes[$campo] ?? null;
            if ($novo_valor !== $valor_antigo && ($novo_valor !== null || $valor_antigo !== null)) {
                $detalhes .= "$campo" . "_antigo => " . ($valor_antigo ?? 'NULL') . "; ";
                $detalhes .= "$campo" . "_novo => " . ($novo_valor ?? 'NULL') . "; ";
            }
        }
        $detalhes = trim($detalhes, '; ');
    }

    // Grava no histórico
    if (!empty($detalhes)) {
        $acao = "Ocorrência #$id_ocorrencia editada";
        $item = "Ocorrência #$id_ocorrencia";
        $query_historico = "INSERT INTO historico (funcionario_id, secao, item, acao, detalhes) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query_historico);
        $user_id = $_SESSION['user_id'];
        $secao = 'Ocorrencias';
        $stmt->bind_param("issss", $user_id, $secao, $item, $acao, $detalhes);
        $stmt->execute();
        $stmt->close();
    }

    $conn->commit();
    header('Location: ..?success=1');
    exit;
} catch (Exception $e) {
    $conn->rollback();
    die('Erro ao processar ocorrência: ' . $e->getMessage() . ' (Linha: ' . $e->getLine() . ')');
}

$conn->close();
