<?php
require_once '../../verificacao_sessao.php';
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Veículo</title>
    <link rel="icon" type="image/x-icon" href="../../../images/icon.ico">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="back-button">
        <a href="..">Voltar</a>
    </div>

    <div class="container">
        <h1>Cadastrar Veículo</h1>

        <form action="cadastrar_veiculo.php" method="POST" enctype="multipart/form-data">
            <label for="nome">Nome do Veículo:</label>
            <input type="text" id="nome" name="nome" required><br><br>

            <label for="placa">Placa:</label>
            <input type="text" id="placa" name="placa" required><br><br>

            <label for="km">KM:</label>
            <input type="number" id="km" name="km" required><br><br>

            <label for="marca">Marca:</label>
            <input type="text" id="marca" name="marca" required><br><br>

            <button type="submit">Cadastrar Veículo</button>
        </form>
    </div>

</body>

</html>