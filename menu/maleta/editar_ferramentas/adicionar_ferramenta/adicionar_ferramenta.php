<?php
require_once '../../../verificacao_sessao.php';
include '../../../conn.php';

if (isset($_GET['id_maleta'])) {
    $id_maleta = $_GET['id_maleta'];
} else {
    echo "ID da maleta não especificado.";
    exit;
}

$mensagem = "";
$mensagem_classe = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST["ferramenta"]) || !isset($_POST["quantidade"]) || empty($_POST["quantidade"])) {
        $mensagem = "Todos os campos são obrigatórios.";
        $mensagem_classe = "erro";
    } else {
        $id_ferramenta = $_POST["ferramenta"];
        $quantidade = $_POST["quantidade"];

        $query_ferramenta = "SELECT quantidade_atual FROM ferramentas WHERE id_ferramenta = ?";
        $stmt_ferramenta = $conn->prepare($query_ferramenta);
        $stmt_ferramenta->bind_param("i", $id_ferramenta);
        $stmt_ferramenta->execute();
        $result_ferramenta = $stmt_ferramenta->get_result();

        if ($result_ferramenta->num_rows > 0) {
            $row_ferramenta = $result_ferramenta->fetch_assoc();
            $quantidade_estoque = $row_ferramenta['quantidade_atual'];

            if ($quantidade_estoque >= $quantidade) {
                $query_inserir = "INSERT INTO ferramenta_maleta (id_maleta, id_ferramenta, quantidade) VALUES (?, ?, ?)";
                $stmt_inserir = $conn->prepare($query_inserir);
                $stmt_inserir->bind_param("iii", $id_maleta, $id_ferramenta, $quantidade);

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

                    $mensagem = "Ferramenta cadastrada com sucesso na maleta!";
                    $mensagem_classe = "sucesso";

                    $stmt_inserir->close();
                    $stmt_atualizar->close();
                    $stmt_situacao->close();
                } else {
                    $mensagem = "Erro ao cadastrar ferramenta na maleta: " . $stmt_inserir->error;
                    $mensagem_classe = "erro";
                    $stmt_inserir->close();
                }
            } else {
                $mensagem = "Quantidade insuficiente em estoque para adicionar à maleta.";
                $mensagem_classe = "erro";
            }
        } else {
            $mensagem = "Ferramenta não encontrada.";
            $mensagem_classe = "erro";
        }

        $stmt_ferramenta->close();
    }
}

header("Location: ..?id_maleta=" . $id_maleta . "&mensagem=" . urlencode($mensagem) . "&mensagem_classe=" . $mensagem_classe);
exit;
