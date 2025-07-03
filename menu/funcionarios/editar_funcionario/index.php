<?php
require_once '../../verificacao_sessao.php';
include '../../conn.php';


if (isset($_GET['id_funcionario'])) {
  $id_funcionario = $_GET['id_funcionario'];

  $query = "SELECT * FROM funcionarios WHERE id_funcionario = ?";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("i", $id_funcionario);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
  } else {
    echo "Funcionário não encontrada.";
    exit;
  }
} else {
  echo "ID do Funcionário não especificado.";
  exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/x-icon" href="../../../images/icon.ico">
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <script src="../../mostrar_senha.js" defer></script>
  <title>Editar Funcionário</title>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
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

  <h1>Editar Funcionário</h1>

  <div class="container-box">
    <form action="atualizar_funcionario.php" method="POST">
      <label for="nome">Nome:</label>
      <input type="text" id="nome" name="nome" value="<?php echo $row['nome']; ?>" required><br>

      <label for="telefone">Telefone:</label>
      <input type="text" id="telefone" name="telefone" value="<?php echo $row['telefone']; ?>"><br>

      <label for="email">E-mail:</label>
      <input type="email" id="email" name="email" value="<?php echo $row['email']; ?>"><br>


      <div class="input-group">
        <i class="fas fa-lock"></i>
        <input type="password" id="password" name="senha" placeholder="Deixe vazio para não alterar a senha">
        <i class="fas fa-eye" id="togglePassword" onclick="mostrarSenha()"></i>
      </div>

      <label for="administrador">Administrador</label>
      <select name="administrador">
        <option value="1">Sim</option>
        <option value="0" <?php if ($row['administrador'] != 1) {
                            echo 'selected';
                          } ?>>Não</option>
      </select>

      <input type="hidden" name="id_funcionario" value="<?php echo $row['id_funcionario']; ?>">
      <button type="submit">Atualizar</button>
    </form>
  </div>
</body>

</html>