<?php
session_start();
include_once "../menu/conn.php";

$error = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login = trim($_POST['username']);
    $senha = $_POST['password'];

    if (empty($login) || empty($senha)) {
        $error = "Todos os campos são obrigatórios.";
    } else {
        $query = "SELECT id_funcionario, nome, email, senha, administrador FROM funcionarios WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $login);
        $stmt->execute();
        $result = $stmt->get_result();

        $row = $result->fetch_assoc();

        if ($row && $result->num_rows > 0 && isset($row['senha']) && $row['senha']) {
            // Use password_verify if the password is hashed, otherwise use direct comparison
            if (password_verify($senha, $row['senha']) || $senha === $row['senha']) {
                $_SESSION['user_id'] = $row['id_funcionario'];
                $_SESSION['user_name'] = $row['nome'];
                $_SESSION['user_type'] = $row['administrador'];
                if ($stmt) $stmt->close();
                if ($conn) $conn->close();
                header("Location: ../menu");
                exit();
            } else {
                $error = "Credenciais inválidas.";
                header("Location: ../login?error=$error");
                exit;
            }
        } else {
            $error = "Credenciais inválidas.";
            header("Location: ../login?error=$error");
            exit;
        }

        if ($stmt) $stmt->close();
        if ($conn) $conn->close();
    }
}
