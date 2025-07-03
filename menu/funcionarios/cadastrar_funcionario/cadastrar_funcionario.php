<?php
require_once '../../verificacao_sessao.php';
include '../../conn.php';


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = trim($_POST['nome']);
    $telefone = trim($_POST['telefone']);
    $email = trim($_POST['email']);
    $senha = $_POST['password'];
    $administrador = $_POST['administrador'];

    if (empty($nome)) {
        die("Nome é obrigatório.");
    }

    if (empty($email)) {
        $email = null;
    }

    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Email inválido.");
    }

    if (empty($telefone)) {
        $telefone = null;
    }

    if (empty($senha)) {
        $senha = null;
    } else {
        $senhaHash = password_hash($senha, PASSWORD_BCRYPT);
    }

    $query = "INSERT INTO funcionarios (nome, telefone, email, senha, administrador) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);

    if ($stmt) {
        $stmt->bind_param("ssssi", $nome, $telefone, $email, $senhaHash, $administrador);

        if ($stmt->execute()) {
            header("Location: ..?Funcionário cadastrado com sucesso");
            exit();
        } else {
            error_log("Erro ao inserir dados: " . $stmt->error);
            die("Erro ao inserir dados.");
        }

        $stmt->close();
    } else {
        error_log("Erro na preparação da query: " . $conn->error);
        die("Erro ao preparar a query.");
    }
}

$conn->close();
