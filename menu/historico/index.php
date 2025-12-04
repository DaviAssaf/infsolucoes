<?php
include '../conn.php';
include '../verificacao_sessao.php';

$query = "SELECT * FROM historico ORDER BY data_hora DESC";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histórico</title>
    <link rel="icon" type="image/x-icon" href="../../images/icon.ico">
    <link rel="stylesheet" type="text/css" href="style.css">
    <script type="module" src="script.js"></script>
</head>

<body>
    <div class='back-button'>
        <a href='..' class='back-btn'>Voltar</a>
    </div>

    <h1>Histórico de Alterações</h1>
    <table class="table-style">
        <thead>
            <tr>
                <th>Data/Hora</th>
                <th>Usuário</th>
                <th>Seção</th>
                <th>Alteração em</th>
                <th>Ação</th>
                <th>Detalhes</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <?php
                    $query_func = "SELECT nome FROM funcionarios WHERE id_funcionario = ?";
                    $stmt_func = $conn->prepare($query_func);
                    $stmt_func->bind_param("i", $row['funcionario_id']);
                    $stmt_func->execute();
                    $result_funcionario = $stmt_func->get_result();
                    $funcionario = $result_funcionario->fetch_assoc();
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['data_hora']); ?></td>
                        <td><?php echo htmlspecialchars($funcionario['nome'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($row['secao']); ?></td>
                        <td><?php echo htmlspecialchars($row['item']); ?></td>
                        <td><?php echo htmlspecialchars($row['acao']); ?></td>
                        <td><button onclick="historicoDetalhes(<?php echo htmlspecialchars($row['id']); ?>)">Detalhes</button></td>
                    </tr>
                    <?php $stmt_func->close(); ?>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">Nenhum registro encontrado.</td>
                </tr>
            <?php endif; ?>
            <?php $stmt->close(); ?>
        </tbody>
    </table>
</body>

</html>