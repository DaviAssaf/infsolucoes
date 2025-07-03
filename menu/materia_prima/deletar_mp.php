<?php
require_once '../verificacao_sessao.php';
include '../conn.php';

// Iniciar transação
$conn->begin_transaction();

try {
    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = $_POST['id_mp'] ?? 0;
        $detalhes = "Motivo: " . ($_POST['motivo_exclusao'] ?? 'Sem motivo especificado');

        if ($id) {
            $query = "SELECT nome FROM materia_prima WHERE id_mp = ?";
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Erro na preparação da consulta: " . $conn->error);
            }
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $nome_materia_prima = $row['nome']; // Usando 'nome' como identificador no histórico

                $deleteQuery = "DELETE FROM materia_prima WHERE id_mp = ?";
                $deleteStmt = $conn->prepare($deleteQuery);
                if (!$deleteStmt) {
                    throw new Exception("Erro na preparação da consulta de exclusão: " . $conn->error);
                }
                $deleteStmt->bind_param("i", $id);

                if ($deleteStmt->execute()) {
                    $user_id = $_SESSION['user_id'];
                    $acao = "EXCLUSÃO";
                    $item = "$nome_materia_prima";
                    $secao = 'Matéria Prima';
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

                    header("Location: ../materia_prima?mensagem=Matéria-prima excluída com sucesso!");
                    exit;
                } else {
                    throw new Exception("Erro ao excluir a matéria-prima: " . $deleteStmt->error);
                }
                $deleteStmt->close();
            } else {
                throw new Exception("Matéria-prima não encontrada.");
            }
            $stmt->close();
        }
    } else {
        throw new Exception("Acesso inválido.");
    }
} catch (Exception $e) {
    // Rollback em caso de erro
    $conn->rollback();
    header("Location: ../materia_prima?mensagem=Erro: " . urlencode($e->getMessage()));
    exit();
}

$conn->close();
?>