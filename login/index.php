<?php
$error = '';
if (isset($_GET['error'])) {
    $error = $_GET['error'];
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/x-icon" href="../images/icon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="/menu/mostrar_senha.js"></script>
</head>

<body>
    <div class="logo">
        <img src="../images/logo_infinity_menu.png" alt="Logo">
    </div>

    <?php if ($error): ?>
        <p style="color: red; text-align: center;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <div class="login-wrapper">
        <div class="login-container">
            <div class="login-logo">
                <i class="fas fa-user-circle"></i>
            </div>
            <h2>Entre na Sua Conta</h2>
            <form action="login.php" method="POST">
                <div class="input-group">
                    <i class="fas fa-user"></i>
                    <input type="text" id="username" name="username" placeholder="Usuário" required>
                </div>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="password" name="password" placeholder="Senha" required>
                    <i class="fas fa-eye" id="togglePassword" onclick="mostrarSenha()"></i>
                </div>
                <button type="submit" class="login-btn">Entrar</button>
            </form>
        </div>
    </div>
</body>

</html>