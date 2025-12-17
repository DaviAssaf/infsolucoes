<?php
include_once '../verificacao_sessao.php';
include '../conn.php';

// Define os períodos disponíveis e suas condições SQL correspondentes
$periods = [
    'today' => "DATE(r.data_hora) = CURDATE()",
    'yesterday' => "DATE(r.data_hora) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)",
    'last_7_days' => "r.data_hora >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)",
    'this_month' => "YEAR(r.data_hora) = YEAR(CURDATE()) AND MONTH(r.data_hora) = MONTH(CURDATE())",
    'last_month' => "YEAR(r.data_hora) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND MONTH(r.data_hora) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))",
    'last_3_months' => "r.data_hora >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)",
    'last_6_months' => "r.data_hora >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)",
    'this_year' => "YEAR(r.data_hora) = YEAR(CURDATE())",
    'last_year' => "YEAR(r.data_hora) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 YEAR))"
];

$types = [
    'inout' => '1=1', // No filter, show both entrada and saida
    'in' => 'r.tipo = 1', // Only entrada
    'out' => 'r.tipo = 0' // Only saida
];

// Obtém o período selecionado ou usa o padrão
$selected_period = (isset($_GET['period']) && isset($periods[$_GET['period']])) ? $_GET['period'] : 'this_month';
$where = $periods[$selected_period];

// Obtém o tipo selecionado ou usa o padrão
$selected_type = (isset($_GET['type']) && isset($types[$_GET['type']])) ? $_GET['type'] : 'inout';
$what = $types[$selected_type];

try {
    $query = "SELECT r.id, r.tipo, r.data_hora, r.os, r.custo_final, COALESCE(f.nome, 'Desconhecido') AS responsavel 
              FROM registro_estoque r
              LEFT JOIN funcionarios f ON r.id_responsavel = f.id_funcionario
              WHERE $where AND $what
              ORDER BY r.data_hora DESC";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception('Erro na preparação da consulta: ' . $conn->error);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    // Defina $details como array vazio para evitar erro de variável indefinida
    $details = [];
} catch (Exception $e) {
    die('Erro ao consultar o banco de dados: ' . htmlspecialchars($e->getMessage()));
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/x-icon" href="../../images/icon.ico">
    <title>Histórico de Saídas de Estoque</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="script.js"></script>
    <script>
        const detailsData = <?php echo json_encode($details); ?>;
    </script>
</head>

<body>
    <div class="back-button">
        <a href=".." class="back-btn">Voltar</a>
    </div>

    <div class="menu">
        <a href="registrar_saida"><button>Registrar Saída de Estoque</button></a>
        <a href="registrar_entrada"><button>Registrar Entrada de Estoque</button></a>
    </div>

    <div class="container">
        <div class="header">
            <img src="../../images/logo_infinity_menu.jpg" alt="Logo" class="logo">
            <h1>Histórico de Movimentação de Estoque</h1>
        </div>

        <div class="filter-selection">
            <form action="" method="GET" class="selection-form">
                <select name="period" id="period-select">
                    <option value="today" <?php echo $selected_period === 'today' ? 'selected' : ''; ?>>Hoje</option>
                    <option value="yesterday" <?php echo $selected_period === 'yesterday' ? 'selected' : ''; ?>>Ontem</option>
                    <option value="last_7_days" <?php echo $selected_period === 'last_7_days' ? 'selected' : ''; ?>>Últimos 7 Dias</option>
                    <option value="this_month" <?php echo $selected_period === 'this_month' ? 'selected' : ''; ?>>Este Mês</option>
                    <option value="last_month" <?php echo $selected_period === 'last_month' ? 'selected' : ''; ?>>Mês Passado</option>
                    <option value="last_3_months" <?php echo $selected_period === 'last_3_months' ? 'selected' : ''; ?>>Últimos 3 Meses</option>
                    <option value="last_6_months" <?php echo $selected_period === 'last_6_months' ? 'selected' : ''; ?>>Últimos 6 Meses</option>
                    <option value="this_year" <?php echo $selected_period === 'this_year' ? 'selected' : ''; ?>>Este Ano</option>
                    <option value="last_year" <?php echo $selected_period === 'last_year' ? 'selected' : ''; ?>>Ano Passado</option>
                </select>

                <select name="type" id="type-select">
                    <option value="inout">Entrada e Saída</option>
                    <option value="in">Entrada</option>
                    <option value="out">Saída</option>
                </select>

                <button type="submit">Filtrar</button>
                <button type="button" id="export-excel" onclick="downloadExcel()">Imprimir</button>
            </form>
        </div>
        <table class="table-style" id="historico-saidas">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Data/Hora</th>
                    <th>Tipo de Movimentação</th>
                    <th>OS</th>
                    <th>Custo Final</th>
                    <th>Responsável</th>
                    <th class='actions'>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($row['data_hora']))); ?></td>
                            <td><?php echo htmlspecialchars($row['tipo'] == 1 ? 'Entrada' : 'Saída'); ?></td>
                            <td><?php echo $row['os']; ?></td>
                            <td data-total-gasto="<?php echo htmlspecialchars($row['custo_final']); ?>">R$ <?php echo number_format($row['custo_final'], 2, ',', '.'); ?></td>
                            <td><?php echo htmlspecialchars($row['responsavel']); ?></td>
                            <td class="actions">
                                <a href="detalhes?id=<?php echo urlencode(intval($row['id'])); ?>" id="button-view">Ver Detalhes</a>
                                <a href="imprimir_lista.php?id=<?= $row['id'] ?>&pdf=1" id="button-print-pdf">PDF</a>
                                <a href="imprimir_lista.php?id=<?php echo urlencode(intval($row['id'])); ?>" id="button-print">Excel</a>
                                <a href="editar_registro?id=<?php echo urlencode(intval($row['id'])); ?>" id="button-edit">Editar</a>
                                <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 1): ?>
                                    <a href="excluir_registro.php?id=<?php echo urlencode(intval($row['id'])); ?>" id="button-delete" onclick="return confirm('Tem certeza que deseja excluir este registro?')">Excluir</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center;">Nenhuma saída registrada.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php
    $stmt->close();
    $conn->close();
    ?>
</body>

</html>