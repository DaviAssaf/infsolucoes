<?php
require_once '../verificacao_sessao.php';
include '../conn.php';

setlocale(LC_MONETARY, 'pt_BR');

$query = "SELECT id_maleta, nome, custo, situacao, nome_num FROM maletas";
$result = $conn->query($query);

if (!$result) {
    die("Erro na consulta de maletas: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Maletas</title>
    <link rel="icon" type="image/x-icon" href="../../images/icon.ico">
    <script src="../confirmDelete.js"></script>
</head>

<body>
    <div class="back-button">
        <a href="..">Voltar</a>
    </div>

    <h2>Maletas Cadastradas</h2>

    <table class="table-style">
        <thead>
            <tr>
                <th>Nome da Maleta</th>
                <th>Valor</th>
                <th>Situação</th>
                <th>Serviço</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0) : ?>
                <?php while ($row = $result->fetch_assoc()) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['nome']); ?></td>
                        <td>R$ <?php echo number_format($row['custo'], 2, ',', '.'); ?></td>
                        <td><?php echo htmlspecialchars($row['situacao']); ?></td>
                        <td><?php echo $row['nome_num']; ?></td>
                        <td>
                            <a class="visualizar" href="editar_ferramentas/?id_maleta=<?php echo $row['id_maleta']; ?>" title="Visualizar detalhes da maleta">
                                <button>Visualizar</button>
                            </a>
                            <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 1): ?>
                                    <form action="deletar_maleta.php" method="post">
                                        <input type="hidden" name="id_maleta" value="<?php echo $row['id_maleta']; ?>">
                                        <button type="submit" class="delete-button" onclick="confirmDelete(event)">Excluir</button>
                                    </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else : ?>
                <tr>
                    <td colspan="5">Nenhuma maleta cadastrada.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <br>

    <a class="visualizar" href="criar_maleta/" title="Criar uma nova maleta"><button>Criar Nova Maleta</button></a>
</body>

</html>

<?php
$conn->close();
?>