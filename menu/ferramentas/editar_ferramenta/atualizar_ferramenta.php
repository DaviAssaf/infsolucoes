<?php
require_once '../../verificacao_sessao.php';
include '../../conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id_ferramenta = $_POST['id_ferramenta'];
  $nome = $_POST['nome'];
  $valor = $_POST['valor'];
  $quantidade_total = $_POST['quantidade_total'];
  $quantidade_atual = $_POST['quantidade_atual'];
  $tipo = $_POST['tipo'];
  $situação = $_POST['situacao'];
  $os = $_POST['num_ordem_servico'];

  if (empty($os)) {
    $os = NULL;
  }

  $query = "UPDATE ferramentas SET nome = ?, valor = ?, quantidade_total = ?, quantidade_atual = ?, tipo = ?, situacao = ?, nome_num = ? WHERE id_ferramenta = ?";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("siiissii", $nome, $valor, $quantidade_total, $quantidade_atual, $tipo, $situação, $os, $id_ferramenta);

  if ($stmt->execute()) {
    echo "Ferramenta atualizada com sucesso.";
    header("Location: ..");
  } else {
    echo "Erro ao atualizar ferramenta: " . $stmt->error;
  }

  $stmt->close();
}

$conn->close();
?>