<?php
include_once '../verificacao_sessao.php';
include '../conn.php';

$id_registro = $_GET['id'] ?? null;
if (!$id_registro || !is_numeric($id_registro)) {
    die('ID do registro não fornecido ou inválido.');
}

// Obter o tipo do registro
$query = "SELECT tipo FROM registro_estoque WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_registro);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die('Registro de estoque não encontrado.');
}
$row = $result->fetch_assoc();
$tipo = $row['tipo'] ?? null;
if ($tipo === null) {
    die('Tipo de registro inválido.');
}
$stmt->close();

// Obter todos os itens de saida_estoque
$query = "SELECT id_mp, quantidade FROM saida_estoque WHERE id_registro_estoque = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_registro);
$stmt->execute();
$result = $stmt->get_result();

$conn->begin_transaction(); // Iniciar transação
try {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $id_mp = $row['id_mp'];
            $quantidade = floatval($row['quantidade']);

            // Validar e atualizar o estoque com base no tipo
            $check_stock_query = "SELECT quantidade FROM materia_prima WHERE id_mp = ? FOR UPDATE";
            $stock_check_stmt = $conn->prepare($check_stock_query);
            $stock_check_stmt->bind_param("i", $id_mp);
            $stock_check_stmt->execute();
            $stock_result = $stock_check_stmt->get_result();
            $current_stock = $stock_result->fetch_assoc()['quantidade'] ?? 0;
            $stock_check_stmt->close();

            $novo_estoque = $tipo == 1 ? ($current_stock - $quantidade) : ($current_stock + $quantidade);
            if ($novo_estoque < 0) {
                throw new Exception("Estoque insuficiente para id_mp $id_mp. Disponível: $current_stock");
            }

            $update_stock_query = $tipo == 1 ? "UPDATE materia_prima SET quantidade = quantidade - ? WHERE id_mp = ?" : "UPDATE materia_prima SET quantidade = quantidade + ? WHERE id_mp = ?";
            $stock_stmt = $conn->prepare($update_stock_query);
            $stock_stmt->bind_param("di", $quantidade, $id_mp);
            if (!$stock_stmt->execute()) {
                throw new Exception("Erro ao atualizar estoque para id_mp $id_mp: " . $stock_stmt->error);
            }
            $stock_stmt->close();
        }
    }

    // Excluir o registro de registro_estoque
    $delete_registro_query = "DELETE FROM registro_estoque WHERE id = ?";
    $stmt = $conn->prepare($delete_registro_query);
    $stmt->bind_param("i", $id_registro);
    if (!$stmt->execute()) {
        throw new Exception("Erro ao excluir registro_estoque: " . $stmt->error);
    }
    $stmt->close();

    // Excluir os itens de saida_estoque
    $delete_saida_query = "DELETE FROM saida_estoque WHERE id_registro_estoque = ?";
    $stmt = $conn->prepare($delete_saida_query);
    $stmt->bind_param("i", $id_registro);
    if (!$stmt->execute()) {
        throw new Exception("Erro ao excluir saida_estoque: " . $stmt->error);
    }
    $stmt->close();

    // Confirmar transação
    $conn->commit();
    header('Location: ../registro_estoque?message=Registro excluído com sucesso.');
    exit();
} catch (Exception $e) {
    // Reverter transação em caso de erro
    $conn->rollback();
    die('Erro ao excluir registro: ' . htmlspecialchars($e->getMessage()));
}
