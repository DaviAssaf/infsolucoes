<?php
require_once '../verificacao_sessao.php';
include '../conn.php';


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_cliente'])) {
    $id_cliente = $_POST['id_cliente'];

    $delete_cliente_query = "DELETE FROM cliente WHERE id_cliente = ?";
    $stmt_cliente = $conn->prepare($delete_cliente_query);
    $stmt_cliente->bind_param("i", $id_cliente);

    if ($stmt_cliente->execute()) {
        header("location: " . dirname($_SERVER['PHP_SELF']));
        exit();
    } else {
        echo "Erro ao deletar cliente: " . $stmt_cliente->error;
    }

    $stmt_endereco->close();
    $stmt_cliente->close();
    $conn->close();
} else {
    echo "Acesso inválido.";
}
?>
