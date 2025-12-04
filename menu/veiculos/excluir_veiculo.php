<?php
require_once '../verificacao_sessao.php';
include '../conn.php';

// Iniciar transação
$conn->begin_transaction();

try {
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_veiculo'])) {
        $id_veiculo = intval($_POST['id_veiculo']);
        $detalhes = "Motivo: " . ($_POST['motivo_exclusao'] ?? 'Sem motivo especificado');

        $query = "SELECT nome, placa FROM veiculos WHERE id_veiculo = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Erro na preparação da consulta: " . $conn->error);
        }
        $stmt->bind_param("i", $id_veiculo);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $placa_veiculo = $row['placa']; // Usando 'placa' como identificador único, ajuste se necessário
            $nome_veiculo = $row['nome'];

            $deleteQuery = "DELETE FROM veiculos WHERE id_veiculo = ?";
            $deleteStmt = $conn->prepare($deleteQuery);
            if (!$deleteStmt) {
                throw new Exception("Erro na preparação da consulta de exclusão: " . $conn->error);
            }
            $deleteStmt->bind_param("i", $id_veiculo);

            if ($deleteStmt->execute()) {
                $user_id = $_SESSION['user_id'];
                $acao = "EXCLUSÃO";
                $item = "$nome_veiculo " . "(Placa: $placa_veiculo)";
                $secao = 'Veículos';
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

                header("Location: " . dirname($_SERVER['PHP_SELF']) . "?mensagem=Veículo excluído com sucesso!");
                exit();
            } else {
                throw new Exception("Erro ao excluir o veículo: " . $deleteStmt->error);
            }
            $deleteStmt->close();
        } else {
            throw new Exception("Veículo não encontrado.");
        }
        $stmt->close();
    } else {
        throw new Exception("Requisição inválida.");
    }
} catch (Exception $e) {
    // Rollback em caso de erro
    $conn->rollback();
    echo "<script>alert('Erro: " . addslashes($e->getMessage()) . "'); history.back();</script>";
    exit();
}

$conn->close();
?>