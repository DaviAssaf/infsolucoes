<?php
include_once '../../verificacao_sessao.php';
include '../../conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_responsavel = $_SESSION['user_id'];
    $os = filter_input(INPUT_POST, 'ordem_servico', FILTER_DEFAULT);
    $materias_primas = $_POST['materias_primas'];
    $quantidades = $_POST['quantidades'];
    $custos_totais = $_POST['custos_totais'];
    $custo_final = filter_input(INPUT_POST, 'custo_final', FILTER_VALIDATE_FLOAT) ?: 0;
    $mp_ids = $_POST['mp_ids'];

    $query = "INSERT INTO registro_estoque (id_responsavel, os, custo_final, tipo) VALUES (?, ?, ?, 0)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("isd", $id_responsavel, $os, $custo_final);
    if ($stmt->execute()) {
        $registro_id = $stmt->insert_id;
        $query = "INSERT INTO saida_estoque (id_registro_estoque, id_mp, quantidade, custo_total) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        foreach ($mp_ids as $index => $mp_id) {
            $quantidade = $quantidades[$index];
            $custo_total = $custos_totais[$index];
            if ($quantidade > 0) {
                $stmt->bind_param("iidd", $registro_id, $mp_id, $quantidade, $custo_total);
                $stmt->execute();
            }
        }
    }

    $query = "UPDATE materia_prima SET quantidade = quantidade - ?, last_commit = ? WHERE id_mp = ?";
    $stmt = $conn->prepare($query);
    foreach ($mp_ids as $index => $mp_id) {
        $quantidade = $quantidades[$index];
        if ($quantidade > 0) {
            $last_commit = htmlspecialchars("-" . $custos_totais[$index]);
            $stmt->bind_param("ddi", $quantidade, $last_commit, $mp_id);
            $stmt->execute();
        }
    }
    $stmt->close();
    $conn->close();
    header("Location: ../?success=Registro de saída realizado com sucesso.");
    exit();
}
