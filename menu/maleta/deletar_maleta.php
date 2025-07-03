<?php
require_once '../verificacao_sessao.php';
include '../conn.php';

if (isset($_GET['id_maleta'])) {
    $id_maleta = (int)$_GET['id_maleta'];

    // Iniciar transação
    $conn->begin_transaction();

    try {
        // Primeiro, atualizar as quantidades das ferramentas
        $query_ferramentas_update = "UPDATE ferramentas f 
                                     INNER JOIN ferramenta_maleta fm ON f.id_ferramenta = fm.id_ferramenta 
                                     SET f.quantidade_atual = f.quantidade_atual + fm.quantidade, f.situacao = 'Disponível' 
                                     WHERE fm.id_maleta = ?";
        $stmt_ferramentas_update = $conn->prepare($query_ferramentas_update);
        if (!$stmt_ferramentas_update) {
            throw new Exception("Erro ao preparar a query de atualização de ferramentas: " . $conn->error);
        }
        $stmt_ferramentas_update->bind_param("i", $id_maleta);
        if (!$stmt_ferramentas_update->execute()) {
            throw new Exception("Erro ao atualizar ferramentas: " . $stmt_ferramentas_update->error);
        }
        $stmt_ferramentas_update->close();

        // Depois, deletar as associações da tabela ferramenta_maleta
        $query_ferramentas = "DELETE FROM ferramenta_maleta WHERE id_maleta = ?";
        $stmt_ferramentas = $conn->prepare($query_ferramentas);
        if (!$stmt_ferramentas) {
            throw new Exception("Erro ao preparar a query de deleção de ferramenta_maleta: " . $conn->error);
        }
        $stmt_ferramentas->bind_param("i", $id_maleta);
        if (!$stmt_ferramentas->execute()) {
            throw new Exception("Erro ao excluir ferramentas da maleta: " . $stmt_ferramentas->error);
        }
        $stmt_ferramentas->close();

        // Por fim, deletar a maleta
        $query_maleta = "DELETE FROM maletas WHERE id_maleta = ?";
        $stmt_maleta = $conn->prepare($query_maleta);
        if (!$stmt_maleta) {
            throw new Exception("Erro ao preparar a query de deleção da maleta: " . $conn->error);
        }
        $stmt_maleta->bind_param("i", $id_maleta);
        if (!$stmt_maleta->execute()) {
            throw new Exception("Erro ao excluir maleta: " . $stmt_maleta->error);
        }
        $stmt_maleta->close();

        // Commit da transação
        $conn->commit();

        // Redirecionar com mensagem de sucesso
        header("Location: ". dirname($_SERVER['PHP_SELF']) . "?mensagem=Maleta excluída com sucesso!");
        exit();
    } catch (Exception $e) {
        // Rollback em caso de erro
        $conn->rollback();
        $conn->close();
        die("Erro: " . $e->getMessage());
    }
} else {
    die("ID da maleta não especificado.");
}

$conn->close();
?>