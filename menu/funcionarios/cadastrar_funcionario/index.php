<?php
require_once '../../verificacao_sessao.php';
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Funcionários</title>
    <link rel="icon" type="image/x-icon" href="../../../images/icon.ico">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script src="../../mostrar_senha.js"></script>
    <script>
        $(document).ready(function() {
            $('#telefone').mask('(00) 00000-0000');
        });
    </script>
</head>

<body>
    <div class="back-button">
        <a href="..">Voltar</a>
    </div>
    <div class="container-box">
        <form action="cadastrar_funcionario.php" method="POST">
            <label for="nome">Nome</label>
            <input type="text" name="nome" id="nome" required>

            <label for="telefone">Telefone</label>
            <input type="tel" name="telefone" id="telefone">

            <label for="email">Email</label>
            <input type="email" name="email">

            <label for="senha">Senha</label>
            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" id="password" name="password" placeholder="Senha">
                <i class="fas fa-eye" id="togglePassword" onclick="mostrarSenha()"></i>
            </div>

            <label for="administrador">Administrador</label>
            <select name="administrador">
                <option value="1">Sim</option>
                <option value="0" selected>Não</option>
            </select>

            <input type="submit" value="Enviar">
        </form>
    </div>
</body>

</html>