<?php
require_once '../../verificacao_sessao.php';
include '../../conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = strtoupper($_POST['nome']);
    $valor = str_replace(',', '.', $_POST['valor']); // Substitui vírgula por ponto e converte
    $valor = floatval($valor); // Garante que seja um número decimal
    $qtd = intval($_POST['qtd']); // Converte para inteiro
    $tipo = $_POST['tipo'];

    $conn->begin_transaction();

    $query = "INSERT INTO ferramentas (nome, valor, quantidade_total, quantidade_atual, tipo) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);

    if ($stmt) {
        $stmt->bind_param('sdiis', $nome, $valor, $qtd, $qtd, $tipo);

        try {
            if ($stmt->execute()) {
                $id_ferramenta = $conn->insert_id; // Obtém o ID da ferramenta inserida

                // Gera os detalhes da inserção
                $detalhes = "$nome cadastrado(a) ao estoque de ferramentas";

                // Inserir no histórico
                $acao = "CADASTRO";
                $item = "$nome";
                $query_historico = "INSERT INTO historico (funcionario_id, secao, item, acao, detalhes) VALUES (?, ?, ?, ?, ?)";
                $stmt_historico = $conn->prepare($query_historico);
                $user_id = $_SESSION['user_id'];
                $secao = 'Ferramentas';
                $stmt_historico->bind_param("issss", $user_id, $secao, $item, $acao, $detalhes);
                $stmt_historico->execute();
                $stmt_historico->close();

                $conn->commit();
                header("Location: ..?Sucesso no cadastro da ferramenta");
            } else {
                throw new Exception("Erro ao enviar dados: " . $stmt->error);
            }
        } catch (Exception $e) {
            $conn->rollback();
            die("Erro ao processar dados: " . $e->getMessage());
        }

        $stmt->close();
    } else {
        die("Erro na preparação da query: " . $conn->error);
    }

    $conn->close();
}
?>