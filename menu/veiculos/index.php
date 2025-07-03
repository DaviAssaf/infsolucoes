<?php
require_once '../verificacao_sessao.php';
include '../conn.php';

$query = "SELECT * FROM veiculos";
$result = $conn->query($query);

if (!$result) {
    die("Erro na consulta de veículos: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Veículos Cadastrados</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/x-icon" href="../../images/icon.ico">
    <script>
        function confirmarExclusao() {
            return confirm("Tem certeza que deseja excluir este veículo?");
        }
    </script>
</head>

<body>
    <div class="back-button">
        <a href="..">Voltar</a>
    </div>

    <h1>Veículos Cadastrados</h1>

    <div class="cadastro-button">
        <a href="cadastrar_veiculo">
            <button>Cadastrar novo veículo</button>
        </a>
    </div>

    <table>
        <thead>
            <tr>
                <th>Nome</th>
                <th>Placa</th>
                <th>KM</th>
                <th>Marca</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($veiculo = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($veiculo['nome']); ?></td>
                    <td><?php echo htmlspecialchars($veiculo['placa']); ?></td>
                    <td><?php echo htmlspecialchars($veiculo['km']); ?></td>
                    <td><?php echo htmlspecialchars($veiculo['marca']); ?></td>
                    <td class="actions">
                        <a href="infos-veiculo?id_veiculo=<?php echo $veiculo['id_veiculo']; ?>">
                            <button>Informações</button>
                        </a>
                        <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 1): ?>
                            <form action="excluir_veiculo.php" method="POST">
                                <input type="hidden" name="id_veiculo" value="<?php echo $veiculo['id_veiculo']; ?>">
                                <button type="submit" onclick="confirmarExclusao()">Excluir</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

</body>

</html>

<?php
$conn->close();
?>