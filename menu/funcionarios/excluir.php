<?php
require_once '../verificacao_sessao.php';
include '../conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id_funcionario'] ?? 0;

    if ($id) {
        $query = "SELECT * FROM funcionarios WHERE id_funcionario = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->get_result();

        if ($result->num_rows > 0) {
            $deleteQuery = "DELETE FROM funcionarios WHERE id_funcionario = ?";
            $deleteStmt = $conn->prepare($deleteQuery);
            $deleteStmt->bind_param("i", $id);

            if ($deleteStmt->execute()) {
                header("Location: " . dirname($_SERVER['PHP_SELF']) . "?mensagem=Funcionário excluído com sucesso!");
                exit;
            } else {
                echo "Erro ao excluir o funcionário: " . $deleteStmt->error;
            }
            $deleteStmt->close();
        } else {
            echo "Funcionário não encontrado.";
        }
        
        $stmt->close();
    }
}

$conn->close();
?>

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
                header("Location: visualizar_ferramentas.php?mensagem=Ferramenta excluída com sucesso!");
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