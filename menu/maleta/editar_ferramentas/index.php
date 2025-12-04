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
                                    <form method="post" action="remover_ferramenta.php?id_maleta=<?php echo $id_maleta; ?>&id=<?php echo $row['id_ferramenta']; ?>">
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