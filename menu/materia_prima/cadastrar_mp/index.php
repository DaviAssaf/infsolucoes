<?php
require_once '../../verificacao_sessao.php';
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Matéria-Prima</title>
    <link rel="icon" type="image/x-icon" href="../../../images/icon.ico">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="back-button">
        <a href="..">Voltar</a>
    </div>

    <form action="cadastrar_mp.php" method="POST">
        <label for="nome">Nome:</label>
        <input type="text" name="nome" id="nome" required>

        <label for="custo">Custo:</label>
        <div class="input-container">
            <span class="currency-symbol">R$</span>
            <input type="number" id="custo" name="custo" placeholder="0.00" value="0.00" step="0.01" min="0.00" required>
        </div>

        <label for="quantidade">Quantidade</label>
        <input type="number" name="quantidade" id="quantidade" placeholder="0.000" value="0.000" step="0.001" min="0.000" required>

        <label for="medida">Unidade de Medida:</label>
        <select name="medida" id="medida" required>
            <option value="">Selecione uma unidade de medida</option>
            <option value="uni" selected>Unidade</option>
            <option value="m²">Metro²</option>
            <option value="m³">Metro cúbico</option>
            <option value="m">Metro</option>
            <option value="cm">Centímetro</option>
            <option value="ft">Pé</option>
            <option value="L">Litro</option>
            <option value="mL">Mililitro</option>
            <option value="g">Grama</option>
            <option value="lb">Libra</option>
        </select>


        <label for="descricao">Descrição:</label>
        <input type="text" name="descricao" id="descricao">

        <label for="quantidade_min">Quantidade Recomendada:</label>
        <input type="number" name="quantidade_min" id="quantidade_min" step="0.001" min="0">

        <input type="submit" value="Cadastrar">
    </form>
</body>

</html>