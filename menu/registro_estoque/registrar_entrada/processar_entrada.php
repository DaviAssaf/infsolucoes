<?php
include_once '../../verificacao_sessao.php';
include '../../conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_responsavel = $_SESSION['user_id'];
    $materias_primas = $_POST['materias_primas'];
    $quantidades = $_POST['quantidades'];
    $custos_totais = $_POST['custos_totais'];
    $custo_final = filter_input(INPUT_POST, 'custo_final', FILTER_VALIDATE_FLOAT) ?: 0;
    $mp_ids = $_POST['mp_ids'];

    $query = "INSERT INTO registro_estoque (id_responsavel, custo_final, tipo) VALUES (?, ?, 1)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("id", $id_responsavel, $custo_final);
    if ($stmt->execute()) {
        $registro_id = $stmt->insert_id;
        $query = "INSERT INTO saida_estoque (id_registro_estoque, id_mp, quantidade, custo_total) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        foreach ($mp_ids as $index => $mp_id) {
            $quantidade = $quantidades[$index];
            $custo_total = $custos_totais[$index];
            if ($quantidade > 0) {
                $stmt->bind_param("iidi", $registro_id, $mp_id, $quantidade, $custo_total);
                $stmt->execute();
            }
        }
    }

    $query = "UPDATE materia_prima SET quantidade = quantidade + ? WHERE id_mp = ?";
    $stmt = $conn->prepare($query);
    foreach ($mp_ids as $index => $mp_id) {
        $quantidade = $quantidades[$index];
        if ($quantidade > 0) {
            $stmt->bind_param("di", $quantidade, $mp_id);
            $stmt->execute();
        }
    }
    $stmt->close();
    $conn->close();
    header("Location: ../?success=Registro de entrada realizado com sucesso.");
    exit();
}
