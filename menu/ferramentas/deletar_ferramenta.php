<?php
require_once '../verificacao_sessao.php';
include '../conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id_ferramenta'] ?? 0;
    $detalhes = "Motivo: " . $_POST['motivo_exclusao'];

    if ($id) {
        $query = "SELECT * FROM ferramentas WHERE id_ferramenta = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        $row = $result->fetch_assoc();

        $nome_ferramenta = $row['nome'];

        if ($result->num_rows > 0) {
            
            $deleteQuery = "DELETE FROM ferramentas WHERE id_ferramenta = ?";
            $deleteStmt = $conn->prepare($deleteQuery);
            $deleteStmt->bind_param("i", $id);

            if ($deleteStmt->execute()) {
                $user_id = $_SESSION['user_id'];
                $acao = "EXCLUSÃO";
                $item = "$nome_ferramenta";
                $secao = 'Ferramentas';
                $query_historico = "INSERT INTO historico (funcionario_id, secao, item, acao, detalhes) VALUES (?, ?, ?, ?, ?)";
                $stmt_historico = $conn->prepare($query_historico);
                
                $stmt_historico->bind_param("issss", $user_id, $secao, $item, $acao, $detalhes);
                $stmt_historico->execute();
                $stmt_historico->close();
                header("Location: ". dirname($_SERVER['PHP_SELF']) ."?mensagem=Ferramenta excluída com sucesso!");
                exit;
            } else {
                echo "Erro ao excluir a ferramenta: " . $deleteStmt->error;
            }
            $deleteStmt->close();

        } else {
            echo "Ferramenta não encontrada.";
        }
        $stmt->close();
    }
}

$conn->close();
?>