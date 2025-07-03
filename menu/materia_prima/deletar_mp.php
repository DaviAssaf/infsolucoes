<?php
require_once '../verificacao_sessao.php';
include '../conn.php';

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id_mp'] ?? 0;

    if ($id) {
        $query = "SELECT * FROM materia_prima WHERE id_mp = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {

            $deleteQuery = "DELETE FROM materia_prima WHERE id_mp = ?";
            $deleteStmt = $conn->prepare($deleteQuery);
            $deleteStmt->bind_param("i", $id);

            if ($deleteStmt->execute()) {
                header("Location: " . dirname($_SERVER['PHP_SELF']) . "?mensagem=Ferramenta excluida com sucesso");
                exit;
            } else {
                die("Erro ao excluir a ferramenta: " . $deleteStmt->error);
            }
            $deleteStmt->close();
        } else {
            echo "Ferramenta não encontrada.";
        }
        $stmt->close();
    }
}

$conn->close();
