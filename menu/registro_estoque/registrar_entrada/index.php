<?php
include_once '../../verificacao_sessao.php';
include '../../conn.php';

$query = "SELECT * FROM materia_prima";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();
$materias_primas = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $materias_primas[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../../../images/icon.ico">
    <link rel="stylesheet" href="style.css">
    <title>Registrar Entrada de Estoque</title>
    <script type="module" src="script.js"></script>
</head>

<body>
    <div class="back-button">
        <a href=".." class="back-btn">Voltar</a>
    </div>

    <h1>Registrar Entrada de Estoque</h1>

    <form action="processar_entrada.php" class="form-container" method="POST" id="saidaForm">
        <div class="form-header">

            <div class="input-group">
                <select name="materias_primas" id="materias_primas">
                    <option value='' selected>Selecione uma matéria prima</option>
                    <?php foreach ($materias_primas as $materia_prima) { ?>
                        <option
                            value="<?php echo $materia_prima['id_mp']; ?>"
                            data-quantidade="<?php echo htmlspecialchars($materia_prima['quantidade']); ?>"
                            data-medida="<?php echo htmlspecialchars($materia_prima['medida']); ?>"
                            data-custo="<?php echo htmlspecialchars($materia_prima['custo']); ?>">
                            <?php echo htmlspecialchars($materia_prima['nome']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <button type="button" id="addMp" class="button-inline">+</button>
        </div>
        <div class="form-body">
            <table class="table-style">
                <thead>
                    <tr>
                        <th>Ações</th>
                        <th>Matéria Prima</th>
                        <th>Quantidade</th>
                        <th>Custo Unitário</th>
                        <th>Custo Total</th>
                    </tr>
                </thead>
                <tbody id="mp_lista">
                </tbody>
                <tfoot id="custo-total-geral">
                </tfoot>
            </table>
        </div>

        <div class="form-footer">
            <button type="submit" class="button-submit" id="saidaSubmit">Registrar Entrada</button>
        </div>
    </form>
</body>

</html>