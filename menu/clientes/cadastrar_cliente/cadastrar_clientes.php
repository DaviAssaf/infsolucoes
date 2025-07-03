<?php
require_once '../../verificacao_sessao.php';
include '../../conn.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome_empresa = htmlspecialchars(trim($_POST['nome']), ENT_QUOTES, 'UTF-8');
    $contato = htmlspecialchars(trim($_POST['contato']), ENT_QUOTES, 'UTF-8');
    $telefone = htmlspecialchars(trim($_POST['telefone']), ENT_QUOTES, 'UTF-8');
    $email = isset($_POST['email']) && !empty(trim($_POST['email'])) ? filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL) : null;
    $cnpj = htmlspecialchars(trim($_POST['cnpj']), ENT_QUOTES, 'UTF-8');

    $conn->begin_transaction();

    try {
        // Query para inserir os dados do cliente
        $query1 = "INSERT INTO cliente (cnpj, nome_empresa, contato, telefone, email) VALUES (?, ?, ?, ?, ?)";
        $stmt1 = $conn->prepare($query1);
        $stmt1->bind_param("sssss", $cnpj, $nome_empresa, $contato, $telefone, $email);
        $stmt1->execute();
        $id_cliente = $conn->insert_id;
        $stmt1->close();
        
        // Dados do endereço
        $cep = htmlspecialchars(trim($_POST['cep']), ENT_QUOTES, 'UTF-8');
        $endereco = htmlspecialchars(trim($_POST['endereco']), ENT_QUOTES, 'UTF-8');
        $numero_endereco = !empty($_POST['numero_endereco']) ? (int)$_POST['numero_endereco'] : null;
        $complemento = !empty($_POST['complemento']) ? htmlspecialchars(trim($_POST['complemento']), ENT_QUOTES, 'UTF-8') : null;
        $bairro = htmlspecialchars(trim($_POST['bairro']), ENT_QUOTES, 'UTF-8');
        $cidade = htmlspecialchars(trim($_POST['cidade']), ENT_QUOTES, 'UTF-8');
        $estado = htmlspecialchars(trim($_POST['estado']), ENT_QUOTES, 'UTF-8');

        // Inserir na tabela os_endereco
        $query_endereco = "INSERT INTO os_endereco (id_cliente, cep, endereco, numero, complemento, bairro, cidade, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_endereco = $conn->prepare($query_endereco);
        $stmt_endereco->bind_param("ississss", $id_cliente, $cep, $endereco, $numero_endereco, $complemento, $bairro, $cidade, $estado);
        $stmt_endereco->execute();
        $stmt_endereco->close();

        // Gera os detalhes da inserção
        $detalhes = "Cliente $nome_empresa cadastrado com sucesso";
        $detalhes .= " | CNPJ: $cnpj";
        $detalhes .= " | Contato: $contato";
        $detalhes .= $telefone ? " | Telefone: $telefone" : "";
        $detalhes .= $email ? " | Email: $email" : "";
        $detalhes .= " | Endereço: $endereco";
        $detalhes .= $numero_endereco ? " #$numero_endereco" : "";
        $detalhes .= $complemento ? " ($complemento)" : "";
        $detalhes .= " | Bairro: $bairro";
        $detalhes .= " | Cidade: $cidade";
        $detalhes .= " | Estado: $estado";
        $detalhes .= " | CEP: $cep";

        // Inserir no histórico
        $acao = "CADASTRO";
        $item = "$nome_empresa";
        $query_historico = "INSERT INTO historico (funcionario_id, secao, item, acao, detalhes) VALUES (?, ?, ?, ?, ?)";
        $stmt_historico = $conn->prepare($query_historico);
        $user_id = $_SESSION['user_id'];
        $secao = 'Clientes';
        $stmt_historico->bind_param("issss", $user_id, $secao, $item, $acao, $detalhes);
        $stmt_historico->execute();
        $stmt_historico->close();

        // Commit da transação
        $conn->commit();
        header("Location: ..?success=1");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        die("Erro ao cadastrar cliente: " . $e->getMessage());
    }

    $conn->close();
} else {
    die("Método inválido. Use POST.");
}
?>