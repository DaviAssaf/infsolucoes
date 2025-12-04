<?php
require_once '../../verificacao_sessao.php';
include '../../conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = isset($_POST['nome']) ? trim(strip_tags($_POST['nome'])) : null;
    $custo = filter_input(INPUT_POST, 'custo', FILTER_UNSAFE_RAW); // Permite entrada crua para substituir vírgula
    $custo = str_replace(',', '.', $custo); // Substitui vírgula por ponto
    $custo = floatval($custo); // Converte para float
    $quantidade = isset($_POST['quantidade']) ? floatval($_POST['quantidade']) : 0;
    $medida = isset($_POST['medida']) ? trim(strip_tags($_POST['medida'])) : 'uni';
    $descricao = isset($_POST['descricao']) ? strip_tags($_POST['descricao']) : null;
    $quantidade_min = filter_input(INPUT_POST, 'quantidade_min', FILTER_VALIDATE_FLOAT) ?: null;

    $conn->begin_transaction();

    $query = "INSERT INTO materia_prima (nome, custo, quantidade, medida, descricao, quantidade_min) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sddssd", $nome, $custo, $quantidade, $medida, $descricao, $quantidade_min);

    try {
        if ($stmt->execute()) {
            $id_mp = $conn->insert_id; // Obtém o ID da matéria-prima inserida

            // Gera os detalhes da inserção
            $detalhes = "Matéria-prima $nome cadastrada com sucesso";
            $detalhes .= " | Custo: $custo";
            $detalhes .= " | Quantidade: $quantidade";
            $detalhes .= " | Medida: $medida";
            $detalhes .= $descricao ? " | Descrição: $descricao" : "";
            $detalhes .= $quantidade_min !== null ? " | Quantidade Mínima: $quantidade_min" : "";

            // Inserir no histórico
            $acao = "CADASTRO";
            $item = "$nome";
            $query_historico = "INSERT INTO historico (funcionario_id, secao, item, acao, detalhes) VALUES (?, ?, ?, ?, ?)";
            $stmt_historico = $conn->prepare($query_historico);
            $user_id = $_SESSION['user_id'];
            $secao = 'Matéria Prima';
            $stmt_historico->bind_param("issss", $user_id, $secao, $item, $acao, $detalhes);
            $stmt_historico->execute();
            $stmt_historico->close();

            $conn->commit();
            header("Location: ..");
            exit;
        } else {
            throw new Exception("Erro ao inserir dados: " . $stmt->error);
        }
    } catch (Exception $e) {
        $conn->rollback();
        die("Erro ao cadastrar matéria-prima: " . $e->getMessage());
    }

    $stmt->close();
}

$conn->close();
?>