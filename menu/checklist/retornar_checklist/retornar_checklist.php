<?php
require_once '../../verificacao_sessao.php';
include '../../conn.php';

date_default_timezone_set('America/Sao_Paulo');

// Validar ID do checklist
$id_checklist = filter_input(INPUT_POST, 'id_checklist', FILTER_VALIDATE_INT);
if (!$id_checklist || $id_checklist <= 0) {
    header("Location: ..?message=" . urlencode("Erro: ID do checklist inválido ou não especificado."));
    exit;
}

// Obter dados do formulário
$retorno = htmlspecialchars($_POST['retorno'] ?? '', ENT_QUOTES, 'UTF-8');
$km_saida = filter_var($_POST['km_saida'] ?? 0, FILTER_VALIDATE_INT);
$km_retorno = filter_var($_POST['km_retorno'] ?? 0, FILTER_VALIDATE_INT);
$observacoes = htmlspecialchars($_POST['observacoes'] ?? '', ENT_QUOTES, 'UTF-8');
$quantidades_devolvidas = $_POST['quantidade_devolvida'] ?? [];
$retornados = $_POST['retornado'] ?? [];
$situacao = 'Concluida';

// Iniciar transação
$conn->begin_transaction();

try {
    // Atualizar checklist
    $query_update_checklist = "UPDATE checklist 
                               SET retorno = ?, km_saida = ?, km_retorno = ?, observacoes = ?, situacao = ?
                               WHERE id_checklist = ?";
    $stmt_update_checklist = $conn->prepare($query_update_checklist);
    $stmt_update_checklist->bind_param("siissi", $retorno, $km_saida, $km_retorno, $observacoes, $situacao, $id_checklist);
    $stmt_update_checklist->execute();
    $stmt_update_checklist->close();

    // Obter veículo
    $stmt = $conn->prepare("SELECT veiculo FROM checklist WHERE id_checklist = ?");
    $stmt->bind_param("i", $id_checklist);
    $stmt->execute();
    $veiculo_result = $stmt->get_result();
    $veiculo = $veiculo_result->fetch_assoc()['veiculo'] ?? null;
    $stmt->close();

    if ($veiculo) {
        $stmt = $conn->prepare("UPDATE veiculos SET motorista = NULL, km = ? WHERE id_veiculo = ?");
        $stmt->bind_param("ii", $km_retorno, $veiculo);
        $stmt->execute();
        $stmt->close();
    }

    // Atualizar ferramentas/maletas
    $stmt = $conn->prepare("SELECT id, id_ferramenta, id_maleta, quantidade_levada, quantidade_devolvida, retornado 
                            FROM caixa_ferramentas WHERE id_checklist = ?");
    $stmt->bind_param("i", $id_checklist);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($item = $result->fetch_assoc()) {
        $id_caixa = $item['id'];
        $id_ferramenta = $item['id_ferramenta'];
        $id_maleta = $item['id_maleta'];
        $qtd_levada = $item['quantidade_levada'];
        $retornado = $item['retornado'] ?? 'NOK';

        $checkbox_marcado = isset($retornados[$id_caixa]) && $retornados[$id_caixa] == '1';

        if ($checkbox_marcado && $retornado != 'OK') {
            $qtd_devolvida = isset($quantidades_devolvidas[$id_caixa])
                ? min((int)$quantidades_devolvidas[$id_caixa], $qtd_levada)
                : $qtd_levada;

            // Atualizar caixa_ferramentas
            $stmt_upd1 = $conn->prepare("UPDATE caixa_ferramentas SET retornado = 'OK', quantidade_devolvida = ? WHERE id = ?");
            $stmt_upd1->bind_param("ii", $qtd_devolvida, $id_caixa);
            $stmt_upd1->execute();
            $stmt_upd1->close();

            // Se for ferramenta (sem maleta)
            if ($id_ferramenta && empty($id_maleta)) {
                $stmt_upd2 = $conn->prepare("UPDATE ferramentas SET quantidade_atual = quantidade_atual + ?, situacao = 'Disponível', nome_num = NULL WHERE id_ferramenta = ?");
                $stmt_upd2->bind_param("ii", $qtd_devolvida, $id_ferramenta);
                $stmt_upd2->execute();
                $stmt_upd2->close();
            }

            // Se for maleta
            if ($id_maleta) {
                $stmt_upd3 = $conn->prepare("UPDATE maletas SET situacao = 'Disponível', nome_num = NULL WHERE id_maleta = ?");
                $stmt_upd3->bind_param("i", $id_maleta);
                $stmt_upd3->execute();
                $stmt_upd3->close();

                $stmt_upd4 = $conn->prepare("UPDATE ferramentas SET nome_num = NULL WHERE situacao = ?");
                $stmt_upd4->bind_param("i", $id_maleta);
                $stmt_upd4->execute();
                $stmt_upd4->close();
            }
        } elseif (!$checkbox_marcado && $retornado == 'OK') {
            // Se foi desmarcado, voltar para NOK
            $stmt_upd5 = $conn->prepare("UPDATE caixa_ferramentas SET retornado = 'NOK', quantidade_devolvida = 0 WHERE id = ?");
            $stmt_upd5->bind_param("i", $id_caixa);
            $stmt_upd5->execute();
            $stmt_upd5->close();
        }
    }
    $stmt->close();

    $conn->commit();
} catch (Exception $e) {
    $conn->rollback();
    error_log("Erro ao salvar checklist: " . $e->getMessage());
    header("Location: ..?message=" . urlencode("Erro ao salvar checklist: " . $e->getMessage()));
    exit;
}

$conn->close();
header("Location: ..?id_checklist=" . urlencode($id_checklist));
exit;
