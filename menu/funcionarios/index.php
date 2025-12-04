<?php
require_once '../verificacao_sessao.php';
include '../conn.php';


$query = "SELECT * FROM funcionarios ORDER BY nome ASC";
$result = $conn->query($query);

if (!$result) {
    die("Erro na consulta de funcionários: " . $conn->error);
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Funcionários</title>
    <link rel="icon" type="image/x-icon" href="../../images/icon.ico">
    <link rel="stylesheet" href="style.css">
    <script src="../confirmDelete.js"></script>
</head>

<body>
    <div class="back-button">
        <a href="..">Voltar</a>
    </div>

    <h1>Funcionários Cadastrados</h1>

    <div class="cadastro-button">
        <a href="cadastrar_funcionario">
            <button>Cadastrar Funcionário</button>
        </a>
    </div>
    <table border="1">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Telefone</th>
                <th>Administrador</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['nome']; ?></td>
                    <td><?php echo $row['telefone']; ?></td>
                    <td><?php echo $row['administrador'] == 1 ? "Sim" : "Não"; ?></td>
                    <td class="actions">
                        <div class="edit-button">
                            <a href="editar_funcionario?id_funcionario=<?php echo $row['id_funcionario']; ?>">
                                <button>Editar</button>
                            </a>
                        </div>
                        <div>
                            <form style='display:inline;' action="excluir.php" method="POST">
                                    <input type='hidden' name='id_funcionario' value='<?php echo htmlspecialchars($row['id_funcionario']); ?>'>
                                    <button type='button' class="delete-button" onclick="confirmDelete(event)">Excluir</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

</body>

</html>

<?php
$conn->close();
?>