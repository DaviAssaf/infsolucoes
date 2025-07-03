<?php
require_once '../verificacao_sessao.php';
include '../conn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_veiculo'])) {
    $id_veiculo = intval($_POST['id_veiculo']); 

    $query = "DELETE FROM veiculos WHERE id_veiculo = ?";
    $stmt = $conn->prepare($query);

    if ($stmt) {
        $stmt->bind_param("i", $id_veiculo);
        if ($stmt->execute()) {
            header("Location: ". dirname($_SERVER['PHP_SELF']));
            exit();
        } else {
            echo "<script>alert('Erro ao excluir o veículo: " . $stmt->error . "'); history.back();</script>";
        }

        $stmt->close();
    } else {
        echo "<script>alert('Erro na preparação da consulta: " . $conn->error . "'); history.back();</script>";
    }
} else {
    echo "<script>alert('Requisição inválida.'); history.back();</script>";
}

$conn->close();
