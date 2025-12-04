<?php
require_once '../../verificacao_sessao.php';
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Maleta</title>
    <link rel="icon" type="image/x-icon" href="../../../images/icon.ico">
    <link rel="stylesheet" href="style.css">
    <script type="module" src="../../mask_cost.js"></script>
</head>

<body>
    <div class="back-button">
        <a href="..">Voltar</a>
    </div>

    <form action="criar_maleta.php" method="POST">
        <label for="nome">Nome da Maleta:</label>
        <input type="text" id="nome" name="nome" required>

        <label for="custo">Custo da Maleta:</label>
        <div class="input-container">
            <span class="currency-symbol">R$</span>
            <input type="text" id="inputCurrency" name="custo" placeholder="0,00" step="0.01" min="0" required>
        </div>

        <input type="submit" value="Criar Maleta">
    </form>
</body>

</html>