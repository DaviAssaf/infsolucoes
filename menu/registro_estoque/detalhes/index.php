<?php
include_once '../../verificacao_sessao.php';
include '../../conn.php';
$id_registro = $_GET['id'];

$query = "SELECT s.id, s.id_mp, s.quantidade, s.custo_total, mp.nome, mp.medida, mp.custo, r.custo_final
          FROM saida_estoque s
          LEFT JOIN materia_prima mp on s.id_mp = mp.id_mp
          LEFT JOIN registro_estoque r on s.id_registro_estoque = r.id
          WHERE s.id_registro_estoque = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_registro);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes da Saída de Estoque</title>
    <link rel="icon" type="image/x-icon" href="../../../images/icon.ico">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="back-button">
        <a href="../" class="back-btn">Voltar</a>
    </div>

    </div>

    <div class="container">
        <img src="../../../images/logo_infinity_menu.jpg" alt="logo" class="logo">
        <h1><strong><span data-register="<?php echo htmlspecialchars($id_registro); ?>">Registro #<?php echo htmlspecialchars($id_registro); ?></span></strong></h1>
        <table class="table-style">
            <thead class="table-header table-list">
                <tr>
                    <th>Matéria Prima</th>
                    <th>Quantidade</th>
                    <th>Custo Unitário</th>
                    <th>Custo Total</th>
                </tr>
            </thead>
            <tbody class="table-body">
                <?php
                $custo_final = null;
                $rows = [];
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $rows[] = $row;
                        if ($custo_final === null && isset($row['custo_final'])) {
                            $custo_final = $row['custo_final'];
                        }
                    }
                }
                ?>
                <?php if (count($rows) > 0): ?>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['nome']); ?></td>
                            <td><?php echo htmlspecialchars($row['quantidade']), ' ', htmlspecialchars($row['medida']); ?></td>
                            <td>R$ <?php echo number_format($row['custo'], 2, ',', '.'); ?></td>
                            <td>R$ <?php echo number_format($row['custo_total'], 2, ',', '.'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan='3' style='text-align: right;'><b>Custo Final:</b></td>
                        <td data-custo-final="<?php echo htmlspecialchars($custo_final); ?>"><b>R$ <?php echo number_format($custo_final, 2, ',', '.'); ?></b></td>
                    </tr>
                <?php else: ?>
                    <tr>
                        <td colspan='4' style='text-align: center;'>Nenhuma Matéria Prima Encontrada</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>

</html>