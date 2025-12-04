<?php
require_once '../../verificacao_sessao.php';
include '../../conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_funcionario = intval($_POST['id_funcionario']); // Garantir que é inteiro
    $nome = trim($_POST['nome']);
    $telefone = trim($_POST['telefone']);
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];
    $administrador = intval($_POST['administrador']);

    $conn->begin_transaction();

    // Pegar os valores atuais
    $query_antigo = "SELECT nome, telefone, email, senha, administrador FROM funcionarios WHERE id_funcionario = ?";
    $stmt_antigo = $conn->prepare($query_antigo);
    $stmt_antigo->bind_param("i", $id_funcionario);
    $stmt_antigo->execute();
    $result_antigo = $stmt_antigo->get_result()->fetch_assoc() ?: [];
    $stmt_antigo->close();

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

    try {
        if ($stmt->execute()) {
            // Gera os detalhes das alterações
            $detalhes = '';
            if ($result_antigo) {
                $campos = [
                    'nome' => $nome,
                    'telefone' => $telefone,
                    'email' => $email,
                    'administrador' => $administrador
                ];

                $nomes_humanos = [
                    'nome' => 'Nome: ',
                    'telefone' => 'Telefone: ',
                    'email' => 'Email: ',
                    'administrador' => 'Administrador: '
                ];

                foreach ($campos as $campo => $novo_valor) {
                    $valor_antigo = $result_antigo[$campo] ?? null;
                    if ($campo === 'administrador') {
                        $valor_antigo = intval($valor_antigo) === 1 ? 'Sim' : 'Não';
                        $novo_valor = intval($novo_valor) === 1 ? 'Sim' : 'Não';
                    } else {
                        $valor_antigo = strval($valor_antigo);
                        $novo_valor = strval($novo_valor);
                    }
                    if ($novo_valor !== $valor_antigo && ($novo_valor !== null || $valor_antigo !== null)) {
                        $detalhes .= $nomes_humanos[$campo] . ($valor_antigo ?? 'N/A') . " => " . ($novo_valor ?? 'N/A') . ";\n";
                    }
                }
                // Verificar se a senha foi alterada
                if (!empty($senha)) {
                    $detalhes .= "Senha: [Alterada];\n";
                }
                $detalhes = trim($detalhes, ";\n");
            }

            // Inserir no histórico
            if (!empty($detalhes)) {
                $acao = "ATUALIZAÇÃO";
                $item = "$nome";
                $query_historico = "INSERT INTO historico (funcionario_id, secao, item, acao, detalhes) VALUES (?, ?, ?, ?, ?)";
                $stmt_historico = $conn->prepare($query_historico);
                $user_id = $_SESSION['user_id'];
                $secao = 'Funcionários';
                $stmt_historico->bind_param("issss", $user_id, $secao, $item, $acao, $detalhes);
                $stmt_historico->execute();
                $stmt_historico->close();
            }

            $conn->commit();
            header("Location: ..");
            exit();
        } else {
            throw new Exception("Erro ao atualizar dados: " . $stmt->error);
        }
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Erro ao atualizar dados: " . $e->getMessage());
        die("Erro ao atualizar dados: " . $e->getMessage());
    }

    $stmt->close();
}

$conn->close();
?>