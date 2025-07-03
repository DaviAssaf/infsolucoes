<?php
require_once '../../verificacao_sessao.php';
include '../../conn.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = trim($_POST['nome']);
    $telefone = trim($_POST['telefone']);
    $email = trim($_POST['email']);
    $senha = $_POST['password'];
    $administrador = intval($_POST['administrador']);

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

    $conn->begin_transaction();

    $query = "INSERT INTO funcionarios (nome, telefone, email, senha, administrador) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);

    if ($stmt) {
        $stmt->bind_param("ssssi", $nome, $telefone, $email, $senhaHash, $administrador);

        try {
            if ($stmt->execute()) {
                $id_funcionario = $conn->insert_id; // Obtém o ID do funcionário inserido

                // Gera os detalhes da inserção
                $detalhes = "$nome cadastrado com sucesso";
                if ($telefone) $detalhes .= " | Telefone: $telefone";
                if ($email) $detalhes .= " | Email: $email";
                if ($senhaHash) $detalhes .= " | Senha: [Definida]";
                $detalhes .= " | Administrador: " . ($administrador ? 'Sim' : 'Não');

                // Inserir no histórico
                $acao = "CADASTRO";
                $item = "$nome";
                $query_historico = "INSERT INTO historico (funcionario_id, secao, item, acao, detalhes) VALUES (?, ?, ?, ?, ?)";
                $stmt_historico = $conn->prepare($query_historico);
                $user_id = $_SESSION['user_id'];
                $secao = 'Funcionários';
                $stmt_historico->bind_param("issss", $user_id, $secao, $item, $acao, $detalhes);
                $stmt_historico->execute();
                $stmt_historico->close();

                $conn->commit();
                header("Location: ..?Funcionário cadastrado com sucesso");
                exit();
            } else {
                throw new Exception("Erro ao inserir dados: " . $stmt->error);
            }
        } catch (Exception $e) {
            $conn->rollback();
            error_log("Erro ao inserir dados: " . $e->getMessage());
            die("Erro ao inserir dados: " . $e->getMessage());
        }

        $stmt->close();
    } else {
        error_log("Erro na preparação da query: " . $conn->error);
        die("Erro ao preparar a query.");
    }
}

$conn->close();
?>