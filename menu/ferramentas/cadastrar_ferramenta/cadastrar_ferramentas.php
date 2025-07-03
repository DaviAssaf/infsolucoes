<?php
require_once '../../verificacao_sessao.php';
include '../../conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $valor = $_POST['valor'];
    $qtd = $_POST['qtd'];
    $tipo = $_POST['tipo'];

    if (empty($valor)) {
        $valor = 0.00;
    }

    $query = "INSERT INTO ferramentas (nome, valor, quantidade_total, quantidade_atual, tipo) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);

    if ($stmt) {
        $stmt->bind_param('sdiis', $nome, $valor, $qtd, $qtd, $tipo);

        if ($stmt->execute()) {
            header("Location: " . dirname($_SERVER['PHP_SELF']));
            exit(); 
        } else {
            die("Erro ao enviar dados: " . $stmt->error);
        }

        $stmt->close();
    } else {
        die("Erro na preparação da query: " . $conn->error);
    }

    $conn->close();
}
