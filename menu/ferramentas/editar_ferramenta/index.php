<?php
require_once '../../verificacao_sessao.php';
include '../../conn.php';


if (isset($_GET['id_ferramenta'])) {
  $id_ferramenta = $_GET['id_ferramenta'];

  $query = "SELECT * FROM ferramentas WHERE id_ferramenta = ?";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("i", $id_ferramenta);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
  } else {
    echo "Ferramenta não encontrada.";
    exit;
  }
} else {
  echo "ID da ferramenta não especificado.";
  exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Editar Ferramenta</title>
  <link rel="icon" type="image/x-icon" href="../../../images/icon.ico">
  <link rel="stylesheet" href="style.css">
  <script type="module" src="../../mask_cost.js"></script>
</head>

<body>
  <div class="back-button">
    <a href=".." class="back-btn">Voltar</a>
  </div>

  <h1>Editar Ferramenta</h1>

  <form action="atualizar_ferramenta.php?id_ferramenta=<?php echo htmlspecialchars($row['id_ferramenta']) ?>" method="POST">
    <input type="hidden" name="id_ferramenta" value="<?php echo htmlspecialchars($row['id_ferramenta']); ?>">

    <label for="nome">Nome:</label>
    <input type="text" name="nome" id="nome" value="<?php echo htmlspecialchars($row['nome']); ?>" required><br><br>

    <label for="valor">Valor:</label>
    <input type="text" name="valor" id="inputCurrency" value="<?php echo htmlspecialchars(number_format($row['valor'], 2, ',', '')); ?>" step="0.01" required><br><br>

    <label for="quantidade_total">Quantidade Total:</label>
    <input type="number" name="quantidade_total" id="quantidade_total" value="<?php echo htmlspecialchars($row['quantidade_total']); ?>" required><br><br>

    <label for="quantidade_atual">Quantidade Atual:</label>
    <input type="number" name="quantidade_atual" id="quantidade_atual" value="<?php echo htmlspecialchars($row['quantidade_atual']); ?>" required><br><br>

    <label for="tipo">Tipo:</label>
    <select name="tipo" id="tipo">
      <option value="Ferramentas manuais" <?php if ($row['tipo'] == 'Ferramentas manuais') echo 'selected'; ?>>Ferramentas manuais</option>
      <option value="Ferramentas elétricas" <?php if ($row['tipo'] == 'Ferramentas elétricas') echo 'selected'; ?>>Ferramentas elétricas</option>
    </select><br><br>

    <?php if ($row['situacao'] == 'Disponível' or $row['situacao'] == 'Em uso' or $row['situacao'] == null): ?>
    <label for="situacao">Situação:</label>
    <select name="situacao" id="situacao">
      <option value="Disponível" <?php if ($row['situacao'] == 'Disponível') echo 'selected'; ?>>Disponível</option>
      <option value="Em uso" <?php if ($row['situacao'] == 'Em uso') echo 'selected'; ?>>Em uso</option>
    </select>
    <?php endif; ?>

    <label for="os">Checklist</label>
    <input type="text" name="nome_num" id="os" value="<?php echo $row['nome_num']; ?>"><br><br>

    <button type="submit">Salvar Alterações</button>
  </form>
</body>

</html>