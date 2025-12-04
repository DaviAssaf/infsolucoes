<?php
include_once '../../verificacao_sessao.php';
include '../../conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_responsavel = $_SESSION['user_id'];

    // Dados do formulário
    $mp_ids = $_POST['mp_ids'] ?? [];
    $quantidades = $_POST['quantidades'] ?? [];
    $custos_totais = $_POST['custos_totais'] ?? [];

    // ✅ Calcula o custo_final no backend (independente do JavaScript)
    $custo_final = 0;
    foreach ($custos_totais as $valor) {
        $custo_final += floatval($valor);
    }

    // Insere o registro principal
    $query = "INSERT INTO registro_estoque (id_responsavel, custo_final, tipo) VALUES (?, ?, 1)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("id", $id_responsavel, $custo_final);

    if ($stmt->execute()) {
        $registro_id = $stmt->insert_id;

        // Insere os itens de entrada na tabela de saída (itens movimentados)
        $query = "INSERT INTO saida_estoque (id_registro_estoque, id_mp, quantidade, custo_total) VALUES (?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($query);

        foreach ($mp_ids as $index => $mp_id) {
            $quantidade = isset($quantidades[$index]) ? floatval($quantidades[$index]) : 0;
            $custo_total = isset($custos_totais[$index]) ? floatval($custos_totais[$index]) : 0;

            if ($quantidade > 0) {
                $stmt_insert->bind_param("iidd", $registro_id, $mp_id, $quantidade, $custo_total);
                $stmt_insert->execute();
            }
        }
        $stmt_insert->close();
    }

    $query = "UPDATE materia_prima SET quantidade = quantidade + ?, last_commit = ? WHERE id_mp = ?";
    $stmt_update = $conn->prepare($query);

    foreach ($mp_ids as $index => $mp_id) {
        $quantidade = isset($quantidades[$index]) ? floatval($quantidades[$index]) : 0;
        if ($quantidade > 0) {
            $last_commit = htmlspecialchars('+' . number_format(floatval($custos_totais[$index] ?? 0), 2, '.', ''));
            $stmt_update->bind_param("dsi", $quantidade, $last_commit, $mp_id);
            $stmt_update->execute();
        }
    }

    $stmt_update->close();
    $stmt->close();
    $conn->close();

    header("Location: ../?success=Registro de entrada realizado com sucesso.");
    exit();
}
?>
