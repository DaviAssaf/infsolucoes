<?php
require_once '../../verificacao_sessao.php';
include '../../conn.php';

header('Content-Type: application/json');

$id_maleta = isset($_GET['id_maleta']) ? (int)$_GET['id_maleta'] : null;
$id_checklist = isset($_GET['id_checklist']) ? (int)$_GET['id_checklist'] : null;

if (!$id_maleta) {
    echo json_encode(['error' => 'ID da maleta inválido']);
    exit;
}

try {
    // Consulta inicial para ferramentas associadas à maleta (tabela ferramenta_maleta)
    $query = "SELECT fm.id_ferramenta, f.nome AS nome_ferramenta, fm.quantidade AS quantidade_levada 
              FROM ferramenta_maleta fm 
              LEFT JOIN ferramentas f ON fm.id_ferramenta = f.id_ferramenta 
              WHERE fm.id_maleta = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_maleta);
    $stmt->execute();
    $result = $stmt->get_result();
    $ferramentas = [];
    while ($row = $result->fetch_assoc()) {
        $ferramentas[] = $row;
    }

    // Se id_checklist for fornecido, ajustar para refletir o estado atual no checklist
    if ($id_checklist) {
        $query_checklist = "SELECT id_ferramenta, quantidade_levada 
                           FROM caixa_ferramentas 
                           WHERE id_maleta = ?";
        $stmt_checklist = $conn->prepare($query_checklist);
        $stmt_checklist->bind_param("i", $id_maleta);
        $stmt_checklist->execute();
        $result_checklist = $stmt_checklist->get_result();
        $ferramentas_existentes = [];
        while ($row = $result_checklist->fetch_assoc()) {
            $ferramentas_existentes[$row['id_ferramenta']] = $row['quantidade_levada'];
        }
        foreach ($ferramentas as &$ferramenta) {
            if (isset($ferramentas_existentes[$ferramenta['id_ferramenta']])) {
                $ferramenta['quantidade_levada'] = $ferramentas_existentes[$ferramenta['id_ferramenta']];
            }
        }
    }

    echo json_encode($ferramentas);
} catch (Exception $e) {
    echo json_encode(['error' => 'Erro ao buscar ferramentas: ' . $e->getMessage()]);
}

$conn->close();
