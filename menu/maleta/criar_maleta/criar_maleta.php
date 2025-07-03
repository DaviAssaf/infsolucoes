<?php
require_once '../../verificacao_sessao.php';
include '../../conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $custo = trim($_POST['custo']);

    $query = "INSERT INTO maletas (nome, custo) VALUES (?, ?)";
    $stmt = $conn->prepare($query);

    if ($stmt) {
        $stmt->bind_param("sd", $nome, $custo);

        if ($stmt->execute()) {
            $id_maleta = $stmt->insert_id;
            header("Location: ../editar_ferramentas?id_maleta=" . $id_maleta);
            exit();
        } else {
            die("Erro ao inserir dados na tabela maletas: " . $stmt->error);
        }

        $stmt->close();
    } else {
        die("Erro na preparação da query: " . $conn->error);
    }
}
