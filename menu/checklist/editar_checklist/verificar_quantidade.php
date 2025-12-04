<?php
require_once '../../verificacao_sessao.php';
ob_start();

include '../../conn.php';

if (!isset($_GET['id']) || !isset($_GET['quantidade'])) {
    header('Content-Type: application/json');
    echo json_encode(['disponivel' => false, 'error' => 'ID ou quantidade não especificados']);
    ob_end_flush();
    $conn->close();
    exit;
}

$id = $_GET['id'];
$quantidade = floatval($_GET['quantidade']); // Usar floatval para permitir ponto flutuante
$tipo = $_GET['tipo'] ?? 'ferramenta'; // Define 'ferramenta' como padrão se 'tipo' não for especificado

switch ($tipo) {
    case 'ferramenta':
        // Lógica para verificar se a ferramenta está disponível
        $query_ferramenta = "SELECT quantidade_atual FROM ferramentas WHERE id_ferramenta = ?";
        $stmt_ferramenta = $conn->prepare($query_ferramenta);

        if ($stmt_ferramenta === false) {
            header('Content-Type: application/json');
            echo json_encode(['disponivel' => false, 'error' => 'Erro na preparação da consulta: ' . $conn->error]);
            ob_end_flush();
            $conn->close();
            exit;
        }

        $stmt_ferramenta->bind_param("i", $id);
        $stmt_ferramenta->execute();
        $result_ferramenta = $stmt_ferramenta->get_result();

        if ($result_ferramenta->num_rows > 0) {
            $row = $result_ferramenta->fetch_assoc();
            $quantidade_atual = $row['quantidade_atual'] ?? 0; // Garante que seja 0 se não estiver definido
            $disponivel = $quantidade <= $quantidade_atual;
            header('Content-Type: application/json');
            echo json_encode([
                'disponivel' => $disponivel,
                'quantidade_atual' => $quantidade_atual
            ]);
        } else {
            header('Content-Type: application/json');
            echo json_encode([
                'disponivel' => false,
                'error' => 'Ferramenta não encontrada',
                'quantidade_atual' => 0 // Define explicitamente para evitar 'undefined'
            ]);
        }
        $stmt_ferramenta->close();
        break;

    case 'maleta':
        // Lógica para verificar se a maleta está disponível
        $query_maleta = "SELECT situacao FROM maletas WHERE id_maleta = ?";
        $stmt_maleta = $conn->prepare($query_maleta);

        if ($stmt_maleta === false) {
            header('Content-Type: application/json');
            echo json_encode(['disponivel' => false, 'error' => 'Erro na preparação da consulta: ' . $conn->error]);
            ob_end_flush();
            $conn->close();
            exit;
        }

        $stmt_maleta->bind_param("i", $id);
        $stmt_maleta->execute();
        $result_maleta = $stmt_maleta->get_result();

        if ($result_maleta->num_rows > 0) {
            $row = $result_maleta->fetch_assoc();
            $disponivel = $row['situacao'] === 'Disponível';
            header('Content-Type: application/json');
            echo json_encode([
                'disponivel' => $disponivel,
                'quantidade_atual' => 1 // Maletas sempre têm quantidade 1
            ]);
        } else {
            header('Content-Type: application/json');
            echo json_encode([
                'disponivel' => false,
                'error' => 'Maleta não encontrada',
                'quantidade_atual' => 0
            ]);
        }
        $stmt_maleta->close();
        break;

    case 'materia_prima':
        // Lógica para verificar se a matéria-prima está disponível
        $query_materia_prima = "SELECT estoque FROM materia_prima WHERE id_mp = ?";
        $stmt_materia_prima = $conn->prepare($query_materia_prima);

        if ($stmt_materia_prima === false) {
            header('Content-Type: application/json');
            echo json_encode(['disponivel' => false, 'error' => 'Erro na preparação da consulta: ' . $conn->error]);
            ob_end_flush();
            $conn->close();
            exit;
        }

        $stmt_materia_prima->bind_param("i", $id);
        $stmt_materia_prima->execute();
        $result_materia_prima = $stmt_materia_prima->get_result();

        if ($result_materia_prima->num_rows > 0) {
            $row = $result_materia_prima->fetch_assoc();
            $quantidade_atual = $row['estoque'] ?? 0; // Garante que seja 0 se não estiver definido
            $disponivel = $quantidade <= $quantidade_atual;
            header('Content-Type: application/json');
            echo json_encode([
                'disponivel' => $disponivel,
                'quantidade_atual' => $quantidade_atual
            ]);
        } else {
            header('Content-Type: application/json');
            echo json_encode([
                'disponivel' => false,
                'error' => 'Matéria-prima não encontrada',
                'quantidade_atual' => 0
            ]);
        }
        $stmt_materia_prima->close();
        break;

    default:
        // Tipo inválido
        header('Content-Type: application/json');
        echo json_encode([
            'disponivel' => false,
            'error' => 'Tipo inválido',
            'quantidade_atual' => 0
        ]);
        break;
}

$conn->close();
ob_end_flush();
