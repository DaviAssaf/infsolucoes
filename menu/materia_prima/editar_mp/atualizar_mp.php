<?php
require_once '../../verificacao_sessao.php';
include '../../conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obter e sanitizar dados
    $id_mp = filter_input(INPUT_POST, 'id_mp', FILTER_VALIDATE_INT);
    $nome = htmlspecialchars(trim(filter_input(INPUT_POST, 'nome', FILTER_UNSAFE_RAW)));
    $custo = filter_input(INPUT_POST, 'custo', FILTER_VALIDATE_FLOAT);
    $quantidade = filter_input(INPUT_POST, 'quantidade', FILTER_VALIDATE_FLOAT);
    $medida = filter_input(INPUT_POST, 'medida', FILTER_DEFAULT);
    $descricao = isset($_POST['descricao']) ? htmlspecialchars(trim($_POST['descricao'])) : null;
    $quantidade_min = $_POST['quantidade_min'];

    //Atualizar os dados no banco de dados
    $query = "UPDATE materia_prima SET nome = ?, custo = ?, quantidade = ?, medida = ?, descricao = ?, quantidade_min = ? WHERE id_mp = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sddsssi", $nome, $custo, $quantidade, $medida, $descricao, $quantidade_min, $id_mp);

    if ($stmt->execute()) {
        $stmt->close();
        header("Location: ..?success=" . urlencode("Matéria-prima atualizada com sucesso."));
        exit;
    } else {
        $stmt->close();
        header("Location: ../editar_mp?Erro=Erro ao atualizar materia prima");
        exit;
    }
}

$conn->close();
