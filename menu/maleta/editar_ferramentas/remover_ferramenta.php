<?php
require_once '../../verificacao_sessao.php';
include '../../conn.php';

// Obter e validar os parâmetros
$id_maleta = $_GET['maleta'] ?? 0;
$id_ferramenta = $_GET['id'] ?? 0;

if (empty($id_maleta) || empty($id_ferramenta)) {
    header("Location: #?maleta=$id_maleta&mensagem=Erro: Dados inválidos para exclusão.");
    exit();
}

// Converter para inteiros para segurança
$id_maleta = (int)$id_maleta;
$id_ferramenta = (int)$id_ferramenta;

// Iniciar transação
$conn->begin_transaction();

try {
    // Buscar a quantidade associada à ferramenta na maleta
    $query_quantidade = "SELECT quantidade FROM ferramenta_maleta WHERE id_maleta = ? AND id_ferramenta = ?";
    $stmt_quantidade = $conn->prepare($query_quantidade);
    if (!$stmt_quantidade) {
        throw new Exception("Erro ao preparar a consulta de quantidade: " . $conn->error);
    }
    $stmt_quantidade->bind_param("ii", $id_maleta, $id_ferramenta);
    $stmt_quantidade->execute();
    $result_quantidade = $stmt_quantidade->get_result();

    if ($result_quantidade->num_rows === 0) {
        throw new Exception("Associação entre ferramenta e maleta não encontrada.");
    }

    $row_quantidade = $result_quantidade->fetch_assoc();
    $quantidade = (int)$row_quantidade['quantidade'];
    $stmt_quantidade->close();

    // Atualizar a quantidade e a situação da ferramenta na tabela ferramentas
    $query_update = "UPDATE ferramentas 
                     SET quantidade_atual = quantidade_atual + ?, 
                         situacao = 'Disponível' 
                     WHERE id_ferramenta = ?";
    $stmt_update = $conn->prepare($query_update);
    if (!$stmt_update) {
        throw new Exception("Erro ao preparar a consulta de atualização: " . $conn->error);
    }
    $stmt_update->bind_param("ii", $quantidade, $id_ferramenta);
    if (!$stmt_update->execute()) {
        throw new Exception("Erro ao atualizar a ferramenta: " . $stmt_update->error);
    }
    $stmt_update->close();

    // Deletar a associação da tabela ferramenta_maleta
    $query_delete = "DELETE FROM ferramenta_maleta WHERE id_maleta = ? AND id_ferramenta = ?";
    $stmt_delete = $conn->prepare($query_delete);
    if (!$stmt_delete) {
        throw new Exception("Erro ao preparar a consulta de deleção: " . $conn->error);
    }
    $stmt_delete->bind_param("ii", $id_maleta, $id_ferramenta);
    if (!$stmt_delete->execute()) {
        throw new Exception("Erro ao excluir a ferramenta da maleta: " . $stmt_delete->error);
    }
    $stmt_delete->close();

    // Commit da transação
    $conn->commit();

    // Redirecionar com mensagem de sucesso
    header("Location: #?maleta=$id_maleta&mensagem=Ferramenta excluída com sucesso!");
    exit();
} catch (Exception $e) {
    // Rollback em caso de erro
    $conn->rollback();
    header("Location: #?maleta=$id_maleta&mensagem=Erro: " . urlencode($e->getMessage()));
    exit();
}
?>