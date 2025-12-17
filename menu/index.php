<?php
include 'conn.php';
require_once 'verificacao_sessao.php';

$query = "SELECT * FROM materia_prima WHERE quantidade < quantidade_min";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();

$num_rows = $result->num_rows;
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Principal</title>
    <link rel="icon" type="image/x-icon" href="../images/icon.ico">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <div class="sidebar">
        <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 1): ?>
            <div class="section">
                <div class="section-title">Área de Cadastro</div>
                <a href="funcionarios"><i class="fas fa-users"></i> Funcionários</a>
                <a href="veiculos"><i class="fas fa-car"></i> Veículos</a>
                <a href="clientes"><i class="fas fa-building"></i> Clientes</a>
            </div>
        <?php endif; ?>
        <div class="section">
            <div class="section-title">Gestão de Estoque</div>
            <a href="ferramentas"><i class="fas fa-tools"></i> Ferramentas</a>
            <a href="materia_prima"><i class="fas fa-boxes"></i> Matérias-Primas
                <?php if ($num_rows > 0): ?>
                    <div class="mp_warning"><i class="fa fa-exclamation-triangle"></i><?php echo htmlspecialchars($num_rows); ?></div>
                <?php endif; ?>
            </a>
            <a href="maleta"><i class="fas fa-briefcase"></i> Maletas</a>
        </div>
        <div class="section">
            <div class="section-title">Registros de Saída e Retorno</div>
            <a href="checklist"><i class="fas fa-clipboard-list"></i> Checklists</a>
            <a href="registro_estoque"><i class="fas fa-clipboard-list"></i> Registro de Estoque</a>
            <a href="historico"><i class="fas fa-clock"></i>Histórico de Alterações</a>
        </div>
        <div class="help-button">
            <a href="ajuda"><i class="fas fa-book"></i> Manual de Uso</a>
        </div>
    </div>

    <div class="main-content">
        <h1>Bem-vindo(a) <?php echo htmlspecialchars($_SESSION['user_name']); ?></h1>
        <p>Selecione uma opção no menu à esquerda para começar</p>
        <div class="logo-wrapper">
            <img src="../images/logo_infinity_menu_cortada.png" alt="logo_infinity" class="logo">
        </div>

        <div class="backup-container">
            <a href="backup/exportar_sql.php" class="backup-btn download">
                <i class="fas fa-download"></i> Baixar Backup
            </a>
        </div>
    </div>


</body>

</html>