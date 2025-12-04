<?php
require_once '../../verificacao_sessao.php';
include '../../conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = strtoupper(trim($_POST['nome']));
    $custo = trim($_POST['custo']);
    $custo = str_replace(',', '.', $custo); // Substitui vírgula por ponto
    $custo = floatval($custo); // Converte para float

    $conn->begin_transaction();

    $query = "INSERT INTO maletas (nome, custo) VALUES (?, ?)";
    $stmt = $conn->prepare($query);

    if ($stmt) {
        $stmt->bind_param("sd", $nome, $custo);

        try {
            if ($stmt->execute()) {
                $id_maleta = $stmt->insert_id;

                // Gera os detalhes da inserção
                $detalhes = "$nome cadastrada com sucesso | Custo: $custo";

                // Inserir no histórico
                $acao = "CADASTRO";
                $item = "$nome";
                $query_historico = "INSERT INTO historico (funcionario_id, secao, item, acao, detalhes) VALUES (?, ?, ?, ?, ?)";
                $stmt_historico = $conn->prepare($query_historico);
                $user_id = $_SESSION['user_id'];
                $secao = 'Maletas';
                $stmt_historico->bind_param("issss", $user_id, $secao, $item, $acao, $detalhes);
                $stmt_historico->execute();
                $stmt_historico->close();

                $conn->commit();
                header("Location: ../editar_ferramentas?id_maleta=" . $id_maleta);
                exit();
            } else {
                throw new Exception("Erro ao inserir dados na tabela maletas: " . $stmt->error);
            }
        } catch (Exception $e) {
            $conn->rollback();
            die("Erro ao cadastrar maleta: " . $e->getMessage());
        }

        $stmt->close();
    } else {
        die("Erro na preparação da query: " . $conn->error);
    }
}

$conn->close();
?>