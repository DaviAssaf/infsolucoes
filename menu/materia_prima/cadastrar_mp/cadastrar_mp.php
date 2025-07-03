<?php
require_once '../infsolucoes/menu/verificacao_sessao.php';
include '../infsolucoes/menu/conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = isset($_POST['nome']) ? trim(strip_tags($_POST['nome'])) : null;
    $custo = filter_input(INPUT_POST, 'custo', FILTER_VALIDATE_FLOAT);
    $quantidade = isset($_POST['quantidade']) ? floatval($_POST['quantidade']) : 0;
    $medida = isset($_POST['medida']) ? trim(strip_tags($_POST['medida'])) : 'uni';
    $descricao = isset($_POST['descricao']) ? strip_tags($_POST['descricao']) : null;
    $quantidade_min = filter_input(INPUT_POST, 'quantidade_min', FILTER_VALIDATE_FLOAT) ?: null;

    $query = "INSERT INTO materia_prima(nome, custo, quantidade, medida, descricao, quantidade_min) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sddssd", $nome, $custo, $quantidade, $medida, $descricao, $quantidade_min);

    if ($stmt->execute()) {
        header("Location: ..");
        exit;
    }
}

$conn->close();
