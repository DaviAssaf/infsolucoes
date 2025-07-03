<?php
require_once '../../verificacao_sessao.php';
include '../../conn.php';

date_default_timezone_set('America/Sao_Paulo');

// Validar os parâmetros recebidos
$id_checklist = filter_input(INPUT_GET, 'id_checklist', FILTER_VALIDATE_INT);
$id_ferramenta = filter_input(INPUT_GET, 'id_ferramenta', FILTER_VALIDATE_INT);
$id_maleta = filter_input(INPUT_GET, 'id_maleta', FILTER_VALIDATE_INT);
$descricao = htmlspecialchars($_GET['descricao'] ?? 'Ocorrência criada manualmente.', ENT_QUOTES, 'UTF-8');
$nome_num = htmlspecialchars($_GET['nome_num'] ?? '', ENT_QUOTES, 'UTF-8');

// Verificar se o ID da checklist foi fornecido
if (!$id_checklist) {
    die("Erro: É necessário informar o ID da checklist.");
}

// Verificar se pelo menos um dos parâmetros (ferramenta ou maleta) foi fornecido
if (!$id_ferramenta && !$id_maleta) {
    die("Erro: É necessário informar um ID de ferramenta ou maleta.");
}

// Se nome_num não foi fornecido, buscar diretamente da tabela checklist
if (empty($nome_num)) {
    $query_checklist = "SELECT nome_num FROM checklist WHERE id_checklist = ?";
    $stmt_checklist = $conn->prepare($query_checklist);
    $stmt_checklist->bind_param("i", $id_checklist);
    $stmt_checklist->execute();
    $result_checklist = $stmt_checklist->get_result();
    if ($result_checklist->num_rows > 0) {
        $row = $result_checklist->fetch_assoc();
        $nome_num = htmlspecialchars($row['nome_num'] ?? 'Não informado', ENT_QUOTES, 'UTF-8');
    } else {
        $nome_num = 'Não informado';
    }
    $stmt_checklist->close();
}

// Obter informações adicionais para a ocorrência
$responsavel = htmlspecialchars($_GET['responsavel'] ?? 'Não informado', ENT_QUOTES, 'UTF-8');

// Inserir a ocorrência no banco de dados
$query_ocorrencia = "INSERT INTO ocorrencias (nome_num, responsavel, id_ferramenta, id_maleta, descricao, situacao) 
                     VALUES (?, ?, ?, ?, ?, 'A resolver')";
$stmt_ocorrencia = $conn->prepare($query_ocorrencia);
$stmt_ocorrencia->bind_param("ssiss", $nome_num, $responsavel, $id_ferramenta, $id_maleta, $descricao);

if ($stmt_ocorrencia->execute()) {
    // Redirecionar para a checklist correspondente com mensagem de sucesso
    header("Location: ..?id_checklist=" . urlencode($id_checklist) . "&message=" . urlencode("Ocorrência criada com sucesso."));
} else {
    // Exibir mensagem de erro em caso de falha
    die("Erro ao criar ocorrência: " . $stmt_ocorrencia->error);
}

// Fechar a conexão
$stmt_ocorrencia->close();
$conn->close();
?>