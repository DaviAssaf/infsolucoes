<?php
require_once '../verificacao_sessao.php';
include '../conn.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$organizar_coluna = isset($_GET['sort_column']) ? $_GET['sort_column'] : 'nome';
$organizar_direcao = isset($_GET['sort_direction']) ? $_GET['sort_direction'] : 'ASC';
$nova_organizar_direcao = ($organizar_direcao === 'ASC') ? 'DESC' : 'ASC';

// Validar colunas para ordenação
$validar_colunas = ['nome', 'custo', 'descricao', 'quantidade', 'medida', 'quantidade_min'];
if (!in_array($organizar_coluna, $validar_colunas)) {
    $organizar_coluna = 'nome';
}

// Escapar coluna para evitar SQL injection (embora já validada)
$organizar_coluna = $conn->real_escape_string($organizar_coluna);
$organizar_direcao = $organizar_direcao === 'DESC' ? 'DESC' : 'ASC';

$query = "SELECT id_mp, nome, custo, quantidade, medida, descricao, quantidade_min 
          FROM materia_prima 
          WHERE nome LIKE ? 
          ORDER BY $organizar_coluna $organizar_direcao";

$stmt = $conn->prepare($query);
$search_param = '%' . $search . '%';
$stmt->bind_param('s', $search_param);
$stmt->execute();
$result = $stmt->get_result();

if ($result === false) {
    die("Erro ao executar a query: " . $conn->error);
}

function mostrarAviso($estoque)
{
    return "<span style='color: red; font-weight: bold;'>$estoque (Estoque baixo!)</span>";
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Matérias-Primas</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/x-icon" href="../../images/icon.ico">
    <script>
        function confirmDelete() {
            return confirm('Tem certeza que deseja excluir esta matéria-prima?');
        }
    </script>
</head>

<body>
    <div class='back-button'>
        <a href='..' class='back-btn'>Voltar</a>
    </div>

    <h2 class='page-title'>Visualizar Matérias-Primas</h2>

    <a href='cadastrar_mp' class='add-button'>Cadastrar Nova Matéria-Prima</a>

    <form method='GET' action='#'>
        <input type='text' name='search' placeholder='Pesquisar por nome' value='<?php echo htmlspecialchars($search); ?>'>
        <input type='hidden' name='sort_column' value='<?php echo htmlspecialchars($organizar_coluna); ?>'>
        <input type='hidden' name='sort_direction' value='<?php echo htmlspecialchars($organizar_direcao); ?>'>
        <button type='submit'>Pesquisar</button>
    </form>

    <table class='table-container table-style'>
        <thead>
            <tr>
                <?php
                $colunas = [
                    'id_mp' => 'ID',
                    'nome' => 'Nome',
                    'custo' => 'Custo',
                    'quantidade' => 'Quantidade',
                    'descricao' => 'Descrição',
                    'quantidade_min' => 'Estoque Recomendado'
                ];

                foreach ($colunas as $coluna => $titulo) {
                    $seta = ($organizar_coluna === $coluna) ? ($organizar_direcao === 'ASC' ? '↑' : '↓') : '';
                    echo "<th class='table-header'>
                            <a href='?sort_column=" . urlencode($coluna) . "&sort_direction=" . urlencode($nova_organizar_direcao) . "&search=" . urlencode($search) . "'>
                                {$titulo} {$seta}
                            </a>
                          </th>";
                }
                ?>
                <th class='table-header'>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <?php
                    $quantidade = $row['quantidade'] ?? 0;
                    $quantidade_min = $row['quantidade_min'];
                    $quantidade_exibicao = (!is_null($quantidade_min) && $quantidade < $quantidade_min)
                        ? mostrarAviso($quantidade . ' ' . $row['medida'] ?? '')
                        : htmlspecialchars($quantidade . ' ' . $row['medida'] ?? '');
                    ?>
                    <tr class='table-row'>
                        <td><?php echo htmlspecialchars($row['id_mp']); ?></td>
                        <td><?php echo htmlspecialchars($row['nome']); ?></td>
                        <td>R$ <?php echo number_format(floatval($row['custo'] ?? 0), 2, ',', '.'); ?></td>
                        <td><?php echo $quantidade_exibicao; ?></td>
                        <td><?php echo htmlspecialchars($row['descricao'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($row['quantidade_min'] ?? ''); ?></td>
                        <td>
                            <a href='editar_mp?id_mp=<?php echo $row['id_mp']; ?>' class='edit-link'>Editar</a>
                            <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 1): ?>
                                <form action='deletar_mp.php' method='POST' style='display:inline;' onsubmit='return confirmDelete()'>
                                    <input type='hidden' name='id_mp' value='<?php echo $row['id_mp']; ?>'>
                                    <button type='submit' class='delete-button'>Excluir</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr class='table-row'>
                    <td colspan='9'>Nenhuma matéria-prima cadastrada.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
<?php
$result->free();
$stmt->close();
$conn->close();
?>

</html>