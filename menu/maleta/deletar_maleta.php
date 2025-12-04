<?php
require_once '../verificacao_sessao.php';
include '../conn.php';

// Iniciar transação
$conn->begin_transaction();

try {
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_maleta'])) {
        $id_maleta = (int)$_POST['id_maleta'];
        $detalhes = "Motivo: " . ($_POST['motivo_exclusao'] ?? 'Sem motivo especificado');

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

        // Buscar o nome da maleta antes de deletá-la
        $query_nome = "SELECT nome FROM maletas WHERE id_maleta = ?"; // Supondo que 'nome' seja um campo em maletas
        $stmt_nome = $conn->prepare($query_nome);
        $stmt_nome->bind_param("i", $id_maleta);
        $stmt_nome->execute();
        $result_nome = $stmt_nome->get_result();
        $row_nome = $result_nome->fetch_assoc();
        $nome_maleta = $row_nome ? $row_nome['nome'] : 'Maleta desconhecida';
        $stmt_nome->close();

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

        $user_id = $_SESSION['user_id'];
        $acao = "EXCLUSÃO";
        $item = "$nome_maleta";
        $secao = 'Maletas';
        $query_historico = "INSERT INTO historico (funcionario_id, secao, item, acao, detalhes) VALUES (?, ?, ?, ?, ?)";
        $stmt_historico = $conn->prepare($query_historico);
        if (!$stmt_historico) {
            throw new Exception("Erro na preparação da consulta de histórico: " . $conn->error);
        }
        $stmt_historico->bind_param("issss", $user_id, $secao, $item, $acao, $detalhes);
        $stmt_historico->execute();
        $stmt_historico->close();

        // Commit da transação
        $conn->commit();

        // Redirecionar com mensagem de sucesso
        header("Location: ../maleta?mensagem=Maleta excluída com sucesso!");
        exit();
    } else {
        throw new Exception("ID da maleta não especificado ou método inválido.");
    }
} catch (Exception $e) {
    // Rollback em caso de erro
    $conn->rollback();
    header("Location: " . dirname($_SERVER['PHP_SELF']) . "?mensagem=Erro: " . urlencode($e->getMessage()));
    exit();
}

$conn->close();
?>