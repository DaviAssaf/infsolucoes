<?php
require_once '../../verificacao_sessao.php';
include '../../conn.php';

if (isset($_GET['tipo'])) {
    $tipo = $_GET['tipo'];
    $ferramentas = [];

    switch ($tipo) {
        case 'Ferramentas elétricas':
            $query = "SELECT id_ferramenta AS id, nome, 'ferramenta' AS tipo, quantidade_atual 
                      FROM ferramentas 
                      WHERE situacao = 'Disponível' AND tipo = 'Ferramentas elétricas' AND quantidade_atual > 0";
            break;
        case 'Ferramentas manuais':
            $query = "SELECT id_ferramenta AS id, nome, 'ferramenta' AS tipo, quantidade_atual 
                      FROM ferramentas 
                      WHERE situacao = 'Disponível' AND tipo = 'Ferramentas manuais' AND quantidade_atual > 0";
            break;
        case 'Maletas':
            $query = "SELECT id_maleta AS id, nome, 'maleta' AS tipo, NULL AS quantidade_atual 
                      FROM maletas 
                      WHERE situacao = 'Disponível'";
            break;
        case 'Todas as Ferramentas':
            $query = "SELECT id_ferramenta AS id, nome, 'ferramenta' AS tipo, quantidade_atual 
                      FROM ferramentas 
                      WHERE situacao = 'Disponível' AND (tipo = 'Ferramentas elétricas' OR tipo = 'Ferramentas manuais') AND quantidade_atual > 0";
            break;
        default:
            echo json_encode(['ferramentas' => []]);
            $conn->close();
            exit;
    }

    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $ferramentas[] = $row;
    }

    header('Content-Type: application/json');
    echo json_encode(['ferramentas' => $ferramentas]);

    $stmt->close();
} else {
    header('Content-Type: application/json');
    echo json_encode(['ferramentas' => []]);
}

$conn->close();
