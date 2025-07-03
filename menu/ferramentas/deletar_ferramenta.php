<?php
require_once '../verificacao_sessao.php';
include '../conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id_ferramenta'] ?? 0;

    if ($id) {
        $query = "SELECT * FROM ferramentas WHERE id_ferramenta = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            
            $deleteQuery = "DELETE FROM ferramentas WHERE id_ferramenta = ?";
            $deleteStmt = $conn->prepare($deleteQuery);
            $deleteStmt->bind_param("i", $id);

            if ($deleteStmt->execute()) {
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