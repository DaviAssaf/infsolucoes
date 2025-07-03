<?php
require_once '../../verificacao_sessao.php';
include '../../conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_funcionario = intval($_POST['id_funcionario']); // Garantir que é inteiro
    $nome = trim($_POST['nome']);
    $telefone = trim($_POST['telefone']);
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];
    $administrador = $_POST['administrador'];

    if (empty($senha)) {
        $senhaQuery = "SELECT senha FROM funcionarios WHERE id_funcionario = ?";
        $stmt = $conn->prepare($senhaQuery);
        $stmt->bind_param("i", $id_funcionario);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        if ($row) {
            $senhaHash = $row['senha'];
        } else {
            die("Funcionário não encontrado.");
        }
        $stmt->close();
    } else {
        $senhaHash = password_hash($senha, PASSWORD_BCRYPT);
    }

    $query = "UPDATE funcionarios SET nome = ?, telefone = ?, email = ?, senha = ?, administrador = ? WHERE id_funcionario = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssii", $nome, $telefone, $email, $senhaHash, $administrador, $id_funcionario);

    if ($stmt->execute()) {
        header("Location: ..");
        exit();
    } else {
        error_log("Erro ao atualizar dados: " . $stmt->error);
        die("Erro ao atualizar dados.");
    }
}

$conn->close();
