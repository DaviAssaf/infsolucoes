<?php
require_once '../../verificacao_sessao.php';
include '../../conn.php';

if (isset($_GET['id_maleta'])) {
    $id_maleta = $_GET['id_maleta'];
} else {
    echo "ID da maleta não especificado.";
    exit;
}

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['retirar_ferramenta'])) {
    $id_ferramenta = $_POST['id_ferramenta'];
    $quantidade_retirada = $_POST['quantidade_retirada'];

    if ($quantidade_retirada > 0) {
        $query_quantidade = "SELECT quantidade FROM ferramenta_maleta 
                           WHERE id_maleta = ? AND id_ferramenta = ?";
        $stmt_quantidade = $conn->prepare($query_quantidade);
        $stmt_quantidade->bind_param("ii", $id_maleta, $id_ferramenta);
        $stmt_quantidade->execute();
        $result_quantidade = $stmt_quantidade->get_result();

        if ($result_quantidade && $result_quantidade->num_rows > 0) {
            $row_quantidade = $result_quantidade->fetch_assoc();
            $quantidade_atual = $row_quantidade['quantidade'];

            if ($quantidade_retirada <= $quantidade_atual) {
                $query_atualizar = "UPDATE ferramenta_maleta SET quantidade = quantidade - ? 
                                   WHERE id_maleta = ? AND id_ferramenta = ?";
                $stmt_atualizar = $conn->prepare($query_atualizar);
                $stmt_atualizar->bind_param("iii", $quantidade_retirada, $id_maleta, $id_ferramenta);

                if ($stmt_atualizar->execute()) {
                    $query_estoque = "UPDATE ferramentas SET quantidade_atual = quantidade_atual + ? 
                                     WHERE id_ferramenta = ?";
                    $stmt_estoque = $conn->prepare($query_estoque);
                    $stmt_estoque->bind_param("ii", $quantidade_retirada, $id_ferramenta);
                    $stmt_estoque->execute();

                    $query_quantidade_atualizada = "SELECT quantidade FROM ferramenta_maleta 
                                                   WHERE id_maleta = ? AND id_ferramenta = ?";
                    $stmt_quantidade_atualizada = $conn->prepare($query_quantidade_atualizada);
                    $stmt_quantidade_atualizada->bind_param("ii", $id_maleta, $id_ferramenta);
                    $stmt_quantidade_atualizada->execute();
                    $result_quantidade_atualizada = $stmt_quantidade_atualizada->get_result();

                    if ($result_quantidade_atualizada && $result_quantidade_atualizada->num_rows > 0) {
                        $row_quantidade_atualizada = $result_quantidade_atualizada->fetch_assoc();
                        $quantidade_atualizada = $row_quantidade_atualizada['quantidade'];

                        if ($quantidade_atualizada == 0) {
                            $query_excluir = "DELETE FROM ferramenta_maleta 
                                            WHERE id_maleta = ? AND id_ferramenta = ?";
                            $stmt_excluir = $conn->prepare($query_excluir);
                            $stmt_excluir->bind_param("ii", $id_maleta, $id_ferramenta);
                            $stmt_excluir->execute();

                            $stmt_excluir->close();
                        }

                        $stmt_quantidade_atualizada->close();
                    }

                    header("Location: #?id_maleta=" . $id_maleta);
                    exit;
                } else {
                    echo "<div class='mensagem erro'>Erro ao retirar ferramenta: " . $stmt_atualizar->error . "</div>";
                }

                $stmt_atualizar->close();
                $stmt_estoque->close();
            } else {
                echo "<div class='mensagem erro'>Quantidade a retirar é maior que a quantidade disponível.</div>";
            }
        } else {
            echo "<div class='mensagem erro'>Ferramenta não encontrada na maleta.</div>";
        }

        $stmt_quantidade->close();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ferramentas na Maleta</title>
    <link rel="icon" type="image/x-icon" href="../../../images/icon.ico">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="back-button">
        <a href="..">Voltar</a>
    </div>

    <h2>Ferramentas na Maleta</h2>

    <?php
    $query = "SELECT f.nome AS nome_ferramenta, fm.quantidade, f.id_ferramenta 
              FROM ferramenta_maleta fm
              INNER JOIN ferramentas f ON fm.id_ferramenta = f.id_ferramenta
              WHERE fm.id_maleta = ?";

    $stmt = $conn->prepare($query);

    if ($stmt) {
        $stmt->bind_param("i", $id_maleta);

        if ($stmt->execute()) {
            $result = $stmt->get_result();

            if ($result && $result->num_rows > 0) {
    ?>
                <table class="table-style">
                    <thead>
                        <tr>
                            <th>Ferramenta</th>
                            <th>Quantidade</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['nome_ferramenta']); ?></td>
                                <td><?php echo $row['quantidade']; ?></td>
                                <td>
                                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?id_maleta=" . $id_maleta; ?>">
                                        <input type="hidden" name="id_ferramenta" value="<?php echo $row['id_ferramenta']; ?>">
                                        <input type="number" name="quantidade_retirada" min="1" max="<?php echo $row['quantidade']; ?>" required>
                                        <button class="delete-button" type="submit" name="retirar_ferramenta">-</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
    <?php
            } else {
                echo "<table class='empty'><tbody><tr><td colspan='3'>Nenhuma ferramenta cadastrada nesta maleta.</td></tr></tbody></table>";
            }
        } else {
            die("Erro na consulta de ferramentas: " . $stmt->error);
        }
    } else {
        die("Erro ao preparar a consulta: " . $conn->error);
    }
    ?>

    <br>

    <a href="adicionar_ferramenta?id_maleta=<?php echo $id_maleta; ?>">
        <button>Adicionar Ferramenta</button>
    </a>

</body>

</html>

<?php
if (isset($stmt)) {
    $stmt->close();
}
$conn->close();
?>