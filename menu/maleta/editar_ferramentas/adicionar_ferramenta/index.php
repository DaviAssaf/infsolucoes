<?php
require_once '../../../verificacao_sessao.php';
include '../../../conn.php';

if (isset($_GET['id_maleta'])) {
    $id_maleta = $_GET['id_maleta'];
} else {
    echo "ID da maleta não especificado.";
    exit;
}

$mensagem = "";
$mensagem_classe = "";

if (isset($_GET['mensagem']) && isset($_GET['mensagem_classe'])) {
    $mensagem = urldecode($_GET['mensagem']);
    $mensagem_classe = $_GET['mensagem_classe'];
}

$query_ferramentas = "SELECT id_ferramenta, nome FROM ferramentas ORDER BY nome ASC";
$result_ferramentas = $conn->query($query_ferramentas);

if (!$result_ferramentas) {
    die("Erro na consulta de ferramentas: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Ferramenta na Maleta</title>
    <link rel="icon" type="image/x-icon" href="../../../../images/icon.ico">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="back-button">
        <a href="..?id_maleta=<?php echo $id_maleta; ?>">Voltar</a>
    </div>

    <h2>Cadastrar Ferramenta na Maleta</h2>

    <div class="formulario-box">
        <form method="post" action="adicionar_ferramenta.php?id_maleta=<?php echo $id_maleta; ?>">
            <label for="ferramenta">Ferramenta:</label>
            <select name="ferramenta" id="ferramenta">
                <option value="">Selecione a ferramenta</option>
                <?php while ($row_ferramentas = $result_ferramentas->fetch_assoc()): ?>
                    <option value="<?php echo $row_ferramentas['id_ferramenta']; ?>">
                        <?php echo htmlspecialchars($row_ferramentas['nome']); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="quantidade">Quantidade:</label>
            <input type="number" name="quantidade" id="quantidade" min="1">

            <button type="submit">Cadastrar</button>
        </form>
        <?php if (!empty($mensagem)): ?>
            <div class="mensagem <?php echo $mensagem_classe; ?>">
                <?php echo $mensagem; ?>
            </div>
        <?php endif; ?>
    </div>

</body>

</html>