<?php
require_once '../../verificacao_sessao.php';
include '../../conn.php';

// Obter e validar os parâmetros
$id_maleta = $_GET['id_maleta'] ?? 0;
$id_ferramenta = $_GET['id'] ?? 0;
$quantidade_retirada = $_POST['quantidade_retirada'] ?? 0;

if (empty($id_maleta) || empty($id_ferramenta) || empty($quantidade_retirada)) {
    header("Location: ../editar_ferramentas?id_maleta=$id_maleta&mensagem=Erro: Dados inválidos para exclusão.");
    exit();
}

// Converter para inteiros para segurança
$id_maleta = (int)$id_maleta;
$id_ferramenta = (int)$id_ferramenta;
$quantidade_retirada = (int)$quantidade_retirada;

$query_nome_maleta = ""$query_nome_maleta = "SELECT nome FROM maletas WHERE id_maleta = ?";
$stmt_nome_maleta = $conn->prepare($query_nome_maleta);
$stmt_nome_maleta->bind_param("i", $id_maleta);
$stmt_nome_maleta->execute();
$result_nome_maleta = $stmt_nome_maleta->get_result();

$row_nome_maleta = $result_nome_maleta->fetch_assoc();
$nome_maleta = $row_nome_maleta['nome'];
$stmt_nome_maleta->close();

// Iniciar transação
$conn->begin_transaction();

try {
    // Buscar a quantidade associada à ferramenta na maleta
    $query_quantidade = "SELECT quantidade FROM ferramenta_maleta WHERE id_maleta = ? AND id_ferramenta = ?";
    $stmt_quantidade = $conn->prepare($query_quantidade);
    if (!$stmt_quantidade) {
        throw new Exception("Erro ao preparar a consulta de quantidade: " . $conn->error);
    }
    $stmt_quantidade->bind_param("ii", $id_maleta, $id_ferramenta);
    $stmt_quantidade->execute();
    $result_quantidade = $stmt_quantidade->get_result();

    if ($result_quantidade->num_rows === 0) {
        throw new Exception("Associação entre ferramenta e maleta não encontrada.");
    }

    $row_quantidade = $result_quantidade->fetch_assoc();
    $quantidade_atual = (int)$row_quantidade['quantidade'];
    $stmt_quantidade->close();

    // Validar a quantidade a ser retirada
    if ($quantidade_retirada > $quantidade_atual || $quantidade_retirada <= 0) {
        throw new Exception("Quantidade a retirar inválida.");
    }

    // Atualizar a quantidade na tabela ferramenta_maleta
    $quantidade_restante = $quantidade_atual - $quantidade_retirada;
    if ($quantidade_restante > 0) {
        $query_atualizar = "UPDATE ferramenta_maleta SET quantidade = ? WHERE id_maleta = ? AND id_ferramenta = ?";
        $stmt_atualizar = $conn->prepare($query_atualizar);
        $stmt_atualizar->bind_param("iii", $quantidade_restante, $id_maleta, $id_ferramenta);
        if (!$stmt_atualizar->execute()) {
            throw new Exception("Erro ao atualizar a quantidade: " . $stmt_atualizar->error);
        }
        $stmt_atualizar->close();
    } else {
        // Se a quantidade restante for 0, deletar a associação
        $query_delete = "DELETE FROM ferramenta_maleta WHERE id_maleta = ? AND id_ferramenta = ?";
        $stmt_delete = $conn->prepare($query_delete);
        if (!$stmt_delete) {
            throw new Exception("Erro ao preparar a consulta de deleção: " . $conn->error);
        }
        $stmt_delete->bind_param("ii", $id_maleta, $id_ferramenta);
        if (!$stmt_delete->execute()) {
            throw new Exception("Erro ao excluir a ferramenta da maleta: " . $stmt_delete->error);
        }
        $stmt_delete->close();
    }

    // Atualizar a quantidade na tabela ferramentas
    $query_update_ferramentas = "UPDATE ferramentas SET quantidade_atual = quantidade_atual + ? WHERE id_ferramenta = ?";
    $stmt_update_ferramentas = $conn->prepare($query_update_ferramentas);
    if (!$stmt_update_ferramentas) {
        throw new Exception("Erro ao preparar a consulta de atualização de ferramentas: " . $conn->error);
    }
    $stmt_update_ferramentas->bind_param("ii", $quantidade_retirada, $id_ferramenta);
    if (!$stmt_update_ferramentas->execute()) {
        throw new Exception("Erro ao atualizar a ferramenta: " . $stmt_update_ferramentas->error);
    }
    $stmt_update_ferramentas->close();

    // Buscar o nome da ferramenta para o histórico
    $query_nome = "SELECT nome FROM ferramentas WHERE id_ferramenta = ?";
    $stmt_nome = $conn->prepare($query_nome);
    $stmt_nome->bind_param("i", $id_ferramenta);
    $stmt_nome->execute();
    $result_nome = $stmt_nome->get_result();
    $nome_ferramenta = $result_nome->num_rows > 0 ? htmlspecialchars($result_nome->fetch_assoc()['nome'], ENT_QUOTES, 'UTF-8') : "Ferramenta #$id_ferramenta";
    $stmt_nome->close();

    // Verificar se ainda há associações após a atualização/exclusão
    $query_verificar_associacao = "SELECT COUNT(*) as total FROM ferramenta_maleta WHERE id_ferramenta = ?";
    $stmt_verificar = $conn->prepare($query_verificar_associacao);
    $stmt_verificar->bind_param("i", $id_ferramenta);
    $stmt_verificar->execute();
    $result_verificar = $stmt_verificar->get_result();
    $row_verificar = $result_verificar->fetch_assoc();
    $tem_associacao = $row_verificar['total'] > 0;
    $stmt_verificar->close();

    // Atualizar a situação para 'Disponível' somente se não houver mais associação
    if (!$tem_associacao && $quantidade_restante <= 0) {
        $query_atualizar_situacao = "UPDATE ferramentas SET situacao = ? WHERE id_ferramenta = ?";
        $stmt_situacao = $conn->prepare($query_atualizar_situacao);
        $situacao_nova = 'Disponível';
        $stmt_situacao->bind_param("si", $situacao_nova, $id_ferramenta);
        if (!$stmt_situacao->execute()) {
            throw new Exception("Erro ao atualizar situação: " . $stmt_situacao->error);
        }
        $stmt_situacao->close();
    }

    // Gera os detalhes da exclusão
    $data_hora = date('Y-m-d H:i:s');
    $detalhes = "Ferramenta $nome_ferramenta removida da $nome_maleta | Quantidade removida: $quantidade_retirada";

    // Inserir no histórico
    if (!isset($_SESSION['user_id'])) {
        throw new Exception("Usuário não autenticado.");
    }

    $query_historico = "INSERT INTO historico (funcionario_id, secao, item, acao, detalhes) VALUES (?, ?, ?, ?, ?)";
    $stmt_historico = $conn->prepare($query_historico);
    $user_id = $_SESSION['user_id'];
    $secao = 'Maletas';
    $acao = 'REMOVER FERRAMENTA';
    $item = "$nome_maleta";

    $stmt_historico->bind_param("issss", $user_id, $secao, $item, $acao, $detalhes);
    if (!$stmt_historico->execute()) {
        throw new Exception("Erro ao inserir no histórico: " . $stmt_historico->error);
    }
    $stmt_historico->close();

    // Commit da transação
    $conn->commit();

    // Redirecionar com mensagem de sucesso
    header("Location: ../editar_ferramentas?id_maleta=$id_maleta;&mensagem=Ferramenta removida com sucesso!");
    exit();
} catch (Exception $e) {
    // Rollback em caso de erro
    $conn->rollback();
    header("Location: ../editar_ferramentas?id_maleta=$id_maleta&mensagem=Erro: " . urlencode($e->getMessage()));
    exit();
}
?>