<?php
require_once '../../verificacao_sessao.php';
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Ferramentas</title>
    <link rel="icon" type="image/x-icon" href="../../../images/icon.ico">
    <link rel="stylesheet" href="style.css">
    <script type="module" src="../../mask_cost.js"></script>
</head>

<body>
    <div class="back-button">
        <a href="..">Voltar</a>
    </div>
    <form action="cadastrar_ferramentas.php" method="POST">
        <label for="nome">Nome:</label>
        <input type="text" name="nome" placeholder="Nome da Ferramenta" required>

        <label for="valor">Valor:</label>
        <input type="text" value="0,00" name="valor" id="inputCurrency">

        <label for="quantidade">Quantidade:</label>
        <input type="number" name="qtd" required>

        <label for="tipo">Tipo:</label>
        <select name="tipo" required>
            <option value="Ferramentas manuais">Ferramentas manuais</option>
            <option value="Ferramentas elétricas">Ferramentas elétricas</option>
        </select>

        <input type="submit">
    </form>
</body>

</html>