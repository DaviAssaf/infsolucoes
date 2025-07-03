<?php
require_once '../../verificacao_sessao.php';
include '../../conn.php';

if (isset($_GET['id_cliente'])) {
    $id_cliente = $_GET['id_cliente'];

    $query_details = "SELECT endereco, numero, bairro, cidade 
                      FROM os_endereco 
                      WHERE id_cliente = ?";
    
    $stmt_details = $conn->prepare($query_details);
    $stmt_details->bind_param("i", $id_cliente);
    $stmt_details->execute();
    $result_details = $stmt_details->get_result();

    if ($result_details->num_rows > 0) {
        $row_details = $result_details->fetch_assoc();
        header('Content-Type: application/json');
        echo json_encode([
            'endereco' => $row_details['endereco'],
            'numero' => $row_details['numero'],
            'bairro' => $row_details['bairro'],
            'cidade' => $row_details['cidade']
        ]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Nenhum endereço encontrado para este cliente.']);
    }

    $stmt_details->close();
} else {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'ID do cliente não especificado']);
}

$conn->close();
?>