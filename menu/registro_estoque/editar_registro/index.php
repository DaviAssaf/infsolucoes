<?php
include_once '../../verificacao_sessao.php';
include '../../conn.php';
$id_registro = $_GET['id'] ?? null;

if (!$id_registro) {
    die('ID do registro não fornecido.');
}

$query = "SELECT s.id, s.id_mp, s.quantidade, s.custo_total, r.id AS id_registro, r.os, r.custo_final, r.tipo, mp.nome, mp.medida, mp.custo
          FROM saida_estoque s
          LEFT JOIN registro_estoque r ON s.id_registro_estoque = r.id
          LEFT JOIN materia_prima mp ON s.id_mp = mp.id_mp
          WHERE s.id_registro_estoque = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_registro);
$stmt->execute();
$result = $stmt->get_result();

$rows = [];
$custo_final = 0;
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
        $custo_final += $row['custo_total'];
    }
} else {
    die('Nenhum registro encontrado.');
}

$query_mp = "SELECT id_mp, nome, quantidade, medida, custo FROM materia_prima";
$stmt_mp = $conn->prepare($query_mp);
$stmt_mp->execute();
$result_mp = $stmt_mp->get_result();
$materias_primas = [];
if ($result_mp->num_rows > 0) {
    while ($row_mp = $result_mp->fetch_assoc()) {
        $materias_primas[$row_mp['id_mp']] = $row_mp;
    }
}

$tipo = $rows[0]['tipo'] == 1 ? 'Entrada' : "Saída";
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Registro #<?php echo htmlspecialchars($id_registro); ?></title>
    <link rel="icon" type="image/x-icon" href="../../../images/icon.ico">
    <link rel="stylesheet" href="style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="script.js"></script>
</head>

<body>
    <div class="back-button">
        <a href="../" class="back-btn">Voltar</a>
    </div>

    <h1>Editar Registro de <?php echo htmlspecialchars($tipo); ?> #<?php echo htmlspecialchars($id_registro); ?></h1>

    <form action="editar_registro.php" class="form-container" method="POST" id="saidaForm">
        <input type="hidden" name="id_registro" value="<?php echo htmlspecialchars($id_registro); ?>">
        <input type="hidden" name="removed_ids" id="removed_ids" value="">

        <div class="form-header">
            <?php if ($rows[0]['tipo'] != 1): ?>
                <div class="input-group">
                    <label for="ordem_servico">Ordem de Serviço:</label>
                    <input type="text" name="ordem_servico" value="<?php echo htmlspecialchars($rows[0]['os'] ?? ''); ?>" id="ordem_servico">
                </div>
            <?php endif; ?>

            <div class="input-group">
                <select name="materias_primas" id="materias_primas">
                    <option value='' selected>Selecione uma matéria prima</option>
                    <?php foreach ($materias_primas as $row_mp) { ?>
                        <option
                            value="<?php echo htmlspecialchars($row_mp['id_mp']); ?>"
                            data-quantidade="<?php echo htmlspecialchars($row_mp['quantidade']); ?>"
                            data-medida="<?php echo htmlspecialchars($row_mp['medida']); ?>"
                            data-custo="<?php echo htmlspecialchars($row_mp['custo']); ?>">
                            <?php echo htmlspecialchars($row_mp['nome']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <button type="button" id="addMp" class="button-inline">+</button>
        </div>

        <div class="form-body">
            <table class="table-style" id="mp_lista">
                <thead>
                    <tr>
                        <th>Ações</th>
                        <th>Matéria Prima</th>
                        <th>Quantidade</th>
                        <th>Custo Unitário</th>
                        <th>Custo Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($rows) > 0): ?>
                        <?php foreach ($rows as $row): ?>
                            <tr data-id="<?php echo htmlspecialchars($row['id']); ?>">
                                <td>
                                    <button type="button" class="delete-button btn-danger btn-sm remove-mp">Remover</button>
                                </td>
                                <td>
                                    <input type="hidden" name="mp_ids[]" value="<?php echo htmlspecialchars($row['id_mp']); ?>">
                                    <?php echo htmlspecialchars($row['nome']); ?>
                                </td>
                                <td>
                                    <div class="input-table">
                                        <input type="number" name="quantidades[]" min="0" value="<?php echo htmlspecialchars($row['quantidade']); ?>" required>
                                        <p><?php echo htmlspecialchars($row['medida']); ?></p>
                                    </div>
                                </td>
                                <td>
                                    R$ <?php echo number_format($row['custo'], 2, ',', '.'); ?>
                                </td>
                                <td>
                                    <div class="input-table">
                                        <p>R$</p>
                                        <input type="number" name="custos_totais[]" min="0" step="0.01" value="<?php echo htmlspecialchars($row['custo_total']); ?>" readonly>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center;">Nenhuma Matéria Prima Encontrada</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                <tfoot id="custo-total-geral">
                    <tr>
                        <td colspan="4" style="text-align: right;"><b>Custo Final:</b></td>
                        <td data-custo-final="<?php echo htmlspecialchars($custo_final); ?>"><b>R$ <?php echo number_format($custo_final, 2, ',', '.'); ?></b></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="form-footer">
            <button type="submit" class="button-submit" id="saidaSubmit">Salvar Alterações</button>
        </div>
    </form>
</body>

</html>