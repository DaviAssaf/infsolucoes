<?php
require_once '../verificacao_sessao.php';
include '../conn.php';

$search = isset($_GET['search']) ? $_GET['search'] : '';
$organizar_coluna = isset($_GET['sort_column']) ? $_GET['sort_column'] : 'nome';
$organizar_direcao = isset($_GET['sort_direction']) ? $_GET['sort_direction'] : 'ASC';

$nova_organizar_direcao = ($organizar_direcao === 'ASC') ? 'DESC' : 'ASC';

$validar_colunas = ['nome', 'valor', 'quantidade_total', 'quantidade_atual', 'tipo', 'situacao', 'nome_num'];
if (!in_array($organizar_coluna, $validar_colunas)) {
    $organizar_coluna = 'nome';
}

$query = "SELECT f.*, m.nome AS nome_maleta
          FROM ferramentas f
          LEFT JOIN ferramenta_maleta fm ON f.id_ferramenta = fm.id_ferramenta
          LEFT JOIN maletas m ON fm.id_maleta = m.id_maleta
          WHERE f.nome LIKE ?
          ORDER BY $organizar_coluna $organizar_direcao";

$stmt = $conn->prepare($query);
$search_param = '%' . $search . '%';
$stmt->bind_param('s', $search_param);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ferramentas</title>
    <link rel="icon" type="image/x-icon" href="../../images/icon.ico">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class='back-button'>
        <a href='..'>Voltar</a>
    </div>

    <h2>Visualizar Ferramentas</h2>
    <form method='GET' action='#'>
        <input type='text' name='search' placeholder='Pesquisar por nome' value='<?php echo htmlspecialchars($search); ?>'>
        <input type='hidden' name='sort_column' value='<?php echo htmlspecialchars($organizar_coluna); ?>'>
        <input type='hidden' name='sort_direction' value='<?php echo htmlspecialchars($organizar_direcao); ?>'>
        <button type='submit'>Pesquisar</button>
    </form>

    <a href='cadastrar_ferramenta'><button>Cadastrar Nova Ferramenta</button></a>

    <table border='1' class="table-container">
        <thead>
            <tr>
                <th class="table-header">
                    <a href='?sort_column=nome&amp;sort_direction=<?php echo $nova_organizar_direcao; ?>&amp;search=<?php echo urlencode($search); ?>'>
                        Nome <?php echo ($organizar_coluna === 'nome' ? ($organizar_direcao === 'ASC' ? '↑' : '↓') : ''); ?>
                    </a>
                </th>
                <th class="table-header">
                    <a href='../ferramentas?sort_column=valor&amp;sort_direction=<?php echo $nova_organizar_direcao; ?>&amp;search=<?php echo urlencode($search); ?>'>
                        Valor <?php echo ($organizar_coluna === 'valor' ? ($organizar_direcao === 'ASC' ? '↑' : '↓') : ''); ?>
                    </a>
                </th>
                <th class="table-header">
                    <a href='../ferramentas?sort_column=quantidade_total&amp;sort_direction=<?php echo $nova_organizar_direcao; ?>&amp;search=<?php echo urlencode($search); ?>'>
                        Quantidade Total <?php echo ($organizar_coluna === 'quantidade_total' ? ($organizar_direcao === 'ASC' ? '↑' : '↓') : ''); ?>
                    </a>
                </th>
                <th class="table-header">
                    <a href='../ferramentas?sort_column=quantidade_atual&amp;sort_direction=<?php echo $nova_organizar_direcao; ?>&amp;search=<?php echo urlencode($search); ?>'>
                        Quantidade Atual <?php echo ($organizar_coluna === 'quantidade_atual' ? ($organizar_direcao === 'ASC' ? '↑' : '↓') : ''); ?>
                    </a>
                </th>
                <th class="table-header">
                    <a href='../ferramentas?sort_column=tipo&amp;sort_direction=<?php echo $nova_organizar_direcao; ?>&amp;search=<?php echo urlencode($search); ?>'>
                        Tipo <?php echo ($organizar_coluna === 'tipo' ? ($organizar_direcao === 'ASC' ? '↑' : '↓') : ''); ?>
                    </a>
                </th>
                <th class="table-header">
                    <a href='../ferramentas?sort_column=situacao&amp;sort_direction=<?php echo $nova_organizar_direcao; ?>&amp;search=<?php echo urlencode($search); ?>'>
                        Situação <?php echo ($organizar_coluna === 'situacao' ? ($organizar_direcao === 'ASC' ? '↑' : '↓') : ''); ?>
                    </a>
                </th>
                <th class="table-header">
                    <a href='../ferramentas?sort_column=nome_num&amp;sort_direction=<?php echo $nova_organizar_direcao; ?>&amp;search=<?php echo urlencode($search); ?>'>
                        Serviço <?php echo ($organizar_coluna === 'nome_num' ? ($organizar_direcao === 'ASC' ? '↑' : '↓') : ''); ?>
                    </a>
                </th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): $situacao = !is_numeric($row['situacao']) ? $row['situacao'] : $row['nome_maleta']; ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['nome']); ?></td>
                        <td><?php echo "R$ " . number_format($row['valor'], 2, ',', '.'); ?></td>
                        <td><?php echo htmlspecialchars($row['quantidade_total']); ?></td>
                        <td><?php echo htmlspecialchars($row['quantidade_atual']); ?></td>
                        <td><?php echo htmlspecialchars($row['tipo']); ?></td>
                        <td><?php echo htmlspecialchars($situacao); ?></td>
                        <td><?php echo htmlspecialchars((string)($row['nome_num'] ?? '')); ?></td>
                        <td class="actions">
                            <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 1): ?>
                                <form action='deletar_ferramenta.php' method='POST' style='display:inline;' onsubmit="return confirm('Tem certeza que deseja excluir esta ferramenta?');">
                                    <input type='hidden' name='id_ferramenta' value='<?php echo htmlspecialchars($row['id_ferramenta']); ?>'>
                                    <button type='submit' class="delete-button">Excluir</button>
                                </form>
                            <?php endif; ?>
                            <button class="edit-button"><a href=' editar_ferramenta?id_ferramenta=<?php echo urlencode($row['id_ferramenta']); ?>'>Editar</a></button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan='8'>Nenhuma ferramenta cadastrada.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

</body>

</html>