<?php
require_once '../../verificacao_sessao.php';
include '../../conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id_ferramenta = $_POST['id_ferramenta'];
  $nome = strtoupper($_POST['nome']);
  $valor = str_replace(',', '.', $_POST['valor']); // Substitui vírgula por ponto e converte
  $valor = floatval($valor); // Garante que seja um número decimal
  $quantidade_total = intval($_POST['quantidade_total']);
  $quantidade_atual = intval($_POST['quantidade_atual']);
  $tipo = $_POST['tipo'];
  $situação = $_POST['situacao'];
  $os = $_POST['num_ordem_servico'];

  if (empty($os)) {
    $os = NULL;
  }

  $conn->begin_transaction();

  // Pegar os valores atuais
  $query_antigo = "SELECT nome, valor, quantidade_total, quantidade_atual, tipo, situacao, nome_num FROM ferramentas WHERE id_ferramenta = ?";
  $stmt_antigo = $conn->prepare($query_antigo);
  $stmt_antigo->bind_param("i", $id_ferramenta);
  $stmt_antigo->execute();
  $result_antigo = $stmt_antigo->get_result()->fetch_assoc() ?: [];
  $stmt_antigo->close();

  // Atualizar os valores
  $query_update = "UPDATE ferramentas SET nome = ?, valor = ?, quantidade_total = ?, quantidade_atual = ?, tipo = ?, situacao = ?, nome_num = ? WHERE id_ferramenta = ?";
  $stmt_update = $conn->prepare($query_update);
  $stmt_update->bind_param("sdiissii", $nome, $valor, $quantidade_total, $quantidade_atual, $tipo, $situação, $os, $id_ferramenta); // Usando "d" para valor decimal

  try {
    $stmt_update->execute();

    // Gera os detalhes das alterações
    $detalhes = '';
    if ($result_antigo) {
      $valor_antigo_situacao = $result_antigo['situacao'] ?? null;
      $valor_novo_situacao = $situação;

      if ($valor_novo_situacao !== $valor_antigo_situacao && ($valor_novo_situacao !== null || $valor_antigo_situacao !== null)) {
        $detalhes .= "Situação: " . ($valor_antigo_situacao ?? 'N/A') . " => " . ($valor_novo_situacao ?? 'N/A') . ";\n";
      }

      $campos = [
        'nome' => $nome,
        'valor' => $valor,
        'quantidade_total' => $quantidade_total,
        'quantidade_atual' => $quantidade_atual,
        'tipo' => $tipo,
        'nome_num' => $os
      ];

      $nomes_humanos = [
        'nome' => 'Nome: ',
        'valor' => 'Valor: ',
        'quantidade_total' => 'Quantidade Total: ',
        'quantidade_atual' => 'Quantidade Atual: ',
        'tipo' => 'Tipo: ',
        'nome_num' => 'Ordem de Serviço: '
      ];

      foreach ($campos as $campo => $novo_valor) {
        $valor_antigo = $result_antigo[$campo] ?? null;
        // Conversão explícita para tipos consistentes
        if ($campo === 'valor') {
          $valor_antigo = floatval($valor_antigo);
          $novo_valor = floatval($novo_valor);
        } elseif ($campo === 'quantidade_total' || $campo === 'quantidade_atual' || $campo === 'nome_num') {
          $valor_antigo = $valor_antigo === null ? null : intval($valor_antigo);
          $novo_valor = $novo_valor === null ? null : intval($novo_valor);
        } else {
          $valor_antigo = strval($valor_antigo);
          $novo_valor = strval($novo_valor);
        }
        if ($novo_valor !== $valor_antigo && ($novo_valor !== null || $valor_antigo !== null)) {
          $detalhes .= $nomes_humanos[$campo] . ($valor_antigo ?? 'N/A') . " => " . ($novo_valor ?? 'N/A') . ";\n";
        }
      }
      $detalhes = trim($detalhes, ";\n");
    }

    // Inserir no histórico
    if (!empty($detalhes)) {
      $acao = "ATUALIZAÇÃO";
      $item = "$nome";
      $query_historico = "INSERT INTO historico (funcionario_id, secao, item, acao, detalhes) VALUES (?, ?, ?, ?, ?)";
      $stmt_historico = $conn->prepare($query_historico);
      $user_id = $_SESSION['user_id'];
      $secao = 'Ferramentas';
      $stmt_historico->bind_param("issss", $user_id, $secao, $item, $acao, $detalhes);
      $stmt_historico->execute();
      $stmt_historico->close();
    }

    $conn->commit();
    echo "Ferramenta atualizada com sucesso.";
    header("Location: ..");
    exit;
  } catch (Exception $e) {
    $conn->rollback();
    echo "Erro ao atualizar ferramenta: " . $e->getMessage() . " (Linha: " . $e->getLine() . ")";
  }

  $stmt_update->close();
}

$conn->close();
?>