<?php
require_once '../../../verificacao_sessao.php';
include '../../../conn.php';

if (isset($_GET['id_maleta'])) {
    $id_maleta = $_GET['id_maleta'];
} else {
    echo "ID da maleta não especificado.";
    exit;
}

$query_nome_maleta = "SELECT nome FROM maletas WHERE id_maleta = ?";
$stmt_nome_maleta = $conn->prepare($query_nome_maleta);
$stmt_nome_maleta->bind_param("i", $id_maleta);
$stmt_nome_maleta->execute();
$result_nome_maleta = $stmt_nome_maleta->get_result();

$row_nome_maleta = $result_nome_maleta->fetch_assoc();
$nome_maleta = $row_nome_maleta['nome'];
$stmt_nome_maleta->close();

$mensagem = "";
$mensagem_classe = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST["ferramenta"]) || !isset($_POST["quantidade"]) || empty($_POST["quantidade"])) {
        $mensagem = "Todos os campos são obrigatórios.";
        $mensagem_classe = "erro";
    } else {
        $id_ferramenta = $_POST["ferramenta"];
        $quantidade = intval($_POST["quantidade"]); // Converte para inteiro

        $conn->begin_transaction();

        try {
            $query_ferramenta = "SELECT quantidade_atual, nome FROM ferramentas WHERE id_ferramenta = ?";
            $stmt_ferramenta = $conn->prepare($query_ferramenta);
            $stmt_ferramenta->bind_param("i", $id_ferramenta);
            $stmt_ferramenta->execute();
            $result_ferramenta = $stmt_ferramenta->get_result();

            if ($result_ferramenta->num_rows > 0) {
                $row_ferramenta = $result_ferramenta->fetch_assoc();
                $quantidade_estoque = $row_ferramenta['quantidade_atual'];
                $nome_ferramenta = $row_ferramenta['nome'];

                // Verifica se a ferramenta já existe na maleta
                $query_verifica = "SELECT quantidade FROM ferramenta_maleta WHERE id_maleta = ? AND id_ferramenta = ?";
                $stmt_verifica = $conn->prepare($query_verifica);
                $stmt_verifica->bind_param("ii", $id_maleta, $id_ferramenta);
                $stmt_verifica->execute();
                $result_verifica = $stmt_verifica->get_result();

                if ($quantidade_estoque >= $quantidade) {
                    if ($result_verifica->num_rows > 0) {
                        // Já existe, então soma a quantidade
                        $row_existente = $result_verifica->fetch_assoc();
                        $nova_quantidade_maleta = $row_existente['quantidade'] + $quantidade;
                        $query_atualiza_maleta = "UPDATE ferramenta_maleta SET quantidade = ? WHERE id_maleta = ? AND id_ferramenta = ?";
                        $stmt_inserir = $conn->prepare($query_atualiza_maleta);
                        $stmt_inserir->bind_param("iii", $nova_quantidade_maleta, $id_maleta, $id_ferramenta);
                    } else {
                        // Não existe, faz o insert normalmente
                        $query_inserir = "INSERT INTO ferramenta_maleta (id_maleta, id_ferramenta, quantidade) VALUES (?, ?, ?)";
                        $stmt_inserir = $conn->prepare($query_inserir);
                        $stmt_inserir->bind_param("iii", $id_maleta, $id_ferramenta, $quantidade);
                    }
                    $stmt_verifica->close();

                    if ($stmt_inserir->execute()) {
                        $nova_quantidade = $quantidade_estoque - $quantidade;
                        $query_atualizar = "UPDATE ferramentas SET quantidade_atual = ? WHERE id_ferramenta = ?";
                        $stmt_atualizar = $conn->prepare($query_atualizar);
                        $stmt_atualizar->bind_param("ii", $nova_quantidade, $id_ferramenta);
                        $stmt_atualizar->execute();

                        $query_situacao = "UPDATE ferramentas SET situacao = ? WHERE id_ferramenta = ?";
                        $stmt_situacao = $conn->prepare($query_situacao);
                        $stmt_situacao->bind_param("ii", $id_maleta, $id_ferramenta);
                        $stmt_situacao->execute();

                        // Gera os detalhes da operação
                        $detalhes = "Ferramenta $nome_ferramenta adicionada à $nome_maleta | Quantidade: $quantidade";

                        // Inserir no histórico
                        $acao = "ADICIONAR FERRAMENTA";
                        $item = "$nome_maleta";
                        $query_historico = "INSERT INTO historico (funcionario_id, secao, item, acao, detalhes) VALUES (?, ?, ?, ?, ?)";
                        $stmt_historico = $conn->prepare($query_historico);
                        $user_id = $_SESSION['user_id'];
                        $secao = 'Maletas';
                        $stmt_historico->bind_param("issss", $user_id, $secao, $item, $acao, $detalhes);
                        $stmt_historico->execute();
                        $stmt_historico->close();

                        $mensagem = "Ferramenta cadastrada com sucesso na maleta!";
                        $mensagem_classe = "sucesso";

                        $stmt_inserir->close();
                        $stmt_atualizar->close();
                        $stmt_situacao->close();
                    } else {
                        throw new Exception("Erro ao cadastrar ferramenta na maleta: " . $stmt_inserir->error);
                    }
                } else {
                    $stmt_verifica->close();
                    $mensagem = "Quantidade insuficiente em estoque para adicionar à maleta.";
                    $mensagem_classe = "erro";
                }
            } else {
                $mensagem = "Ferramenta não encontrada.";
                $mensagem_classe = "erro";
            }

            $stmt_ferramenta->close();

            $conn->commit();
        } catch (Exception $e) {
            $conn->rollback();
            $mensagem = "Erro ao processar operação: " . $e->getMessage();
            $mensagem_classe = "erro";
        }
    }
}

header("Location: ..?id_maleta=" . $id_maleta . "&mensagem=" . urlencode($mensagem) . "&mensagem_classe=" . $mensagem_classe);
exit;
?>