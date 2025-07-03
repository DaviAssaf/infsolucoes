<?php
require_once '../../verificacao_sessao.php';
include '../../conn.php';

if (isset($_GET['id_mp'])) {
    $id_mp = filter_input(INPUT_GET, 'id_mp', FILTER_VALIDATE_INT);
    if (!$id_mp || $id_mp <= 0) {
        echo "ID da matéria-prima inválido.";
        exit;
    }

    $query = "SELECT * FROM materia_prima WHERE id_mp = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_mp);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
    } else {
        echo "Matéria-prima não encontrada.";
        exit;
    }

    $stmt->close();
} else {
    echo "ID da matéria-prima não especificado.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Matéria-Prima</title>
    <link rel="icon" type="image/x-icon" href="../../../images/icon.ico">
    <link rel="stylesheet" href="style.css">
    <script type="module" src="../../mask_cost.js"></script>
</head>

<body>
    <div class="back-button">
        <a href=".." class="back-btn">Voltar</a>
    </div>

    <h1>Editar Matéria-Prima</h1>

    <div class="container-box">
        <form action="atualizar_mp.php" method="POST">
            <input type="hidden" name="id_mp" value="<?php echo htmlspecialchars($row['id_mp']); ?>">

            <label for="nome">Nome:</label>
            <input type="text" name="nome" id="nome" value="<?php echo htmlspecialchars($row['nome']); ?>" required>

            <label for="custo">Custo:</label>
            <div class="input-container">
                <span class="currency-symbol">R$</span>
                <input type="text" id="inputCurrency" name="custo" placeholder="0.00" value="<?php echo isset($row['custo']) ? number_format(floatval($row['custo']), 2, '.', '') : '0.00'; ?>" step="0.01" min="0.000" required>
            </div>

            <label for="quantidade">Quantidade:</label>
            <input type="number" name="quantidade" id="quantidade" placeholder="0.000" value="<?php echo htmlspecialchars($row['quantidade']) ?>">

            <label for="medida">Unidade de Medida:</label>
            <select name="medida" id="medida" required>
                <option value="">Selecione uma unidade</option>
                <option value="uni" <?php echo $row['medida'] == 'uni' ? 'selected' : ''; ?>>Unidade</option>
                <option value="m²" <?php echo $row['medida'] == 'm²' ? 'selected' : ''; ?>>Metro²</option>
                <option value="m³" <?php echo $row['medida'] == 'm³' ? 'selected' : ''; ?>>Metro cúbico</option>
                <option value="m" <?php echo $row['medida'] == 'm' ? 'selected' : ''; ?>>Metro</option>
                <option value="cm" <?php echo $row['medida'] == 'cm' ? 'selected' : ''; ?>>Centímetro</option>
                <option value="ft" <?php echo $row['medida'] == 'ft' ? 'selected' : ''; ?>>Pé</option>
                <option value="L" <?php echo $row['medida'] == 'L' ? 'selected' : ''; ?>>Litro</option>
                <option value="mL" <?php echo $row['medida'] == 'mL' ? 'selected' : ''; ?>>Mililitro</option>
                <option value="g" <?php echo $row['medida'] == 'g' ? 'selected' : ''; ?>>Grama</option>
                <option value="lb" <?php echo $row['medida'] == 'lb' ? 'selected' : ''; ?>>Libra</option>
            </select>

            <label for="descrição">Descrição:</label>
            <input type="text" name="descricao" id="descricao" value="<?php echo htmlspecialchars($row['descricao'] ?? ''); ?>">

            <label for="quantidade_min">Quantidade Recomendada:</label>
            <input type="number" name="quantidade_min" id="quantidade_min" step="0.001" min="0" value="<?php echo htmlspecialchars($row['quantidade_min'] ?? ''); ?>">


            <button type="submit">Salvar Alterações</button>
        </form>
    </div>
</body>

</html>