<?php
require_once '../../../verificacao_sessao.php';
include '../../../conn.php';

// Validar ID da ferramenta ou maleta via GET
$id_ferramenta = filter_input(INPUT_GET, 'id_ferramenta', FILTER_VALIDATE_INT);
$id_maleta = filter_input(INPUT_GET, 'id_maleta', FILTER_VALIDATE_INT);
$id_ocorrencia = $_GET['id_ocorrencia'];

if (!$id_ferramenta && !$id_maleta) {
    die("Erro: ID da ferramenta ou maleta inválido ou não especificado.");
}

// Buscar ID da ocorrência com base em id_ferramenta ou id_maleta
$query_ocorrencia = "SELECT num_ocorrencia 
    FROM ocorrencias 
    WHERE (id_ferramenta = ? AND ? IS NOT NULL) 
    OR (id_maleta = ? AND ? IS NOT NULL)";
$stmt_ocorrencia = $conn->prepare($query_ocorrencia);
$stmt_ocorrencia->bind_param("iiii", $id_ferramenta, $id_ferramenta, $id_maleta, $id_maleta);
$stmt_ocorrencia->execute();
$result_ocorrencia = $stmt_ocorrencia->get_result();

if ($result_ocorrencia->num_rows === 0) {
    die("Erro: Nenhuma ocorrência encontrada para a ferramenta ou maleta especificada.");
}

$stmt_ocorrencia->close();

// Buscar dados completos da ocorrência
$query_dados = "SELECT 
    o.num_ocorrencia, o.nome_num, o.responsavel, o.id_ferramenta, o.id_maleta, o.id_mp, 
    o.data_ocorrencia, o.descricao, o.situacao, 
    c.retorno AS data_checklist, c.observacoes AS observacoes_checklist
FROM ocorrencias o 
LEFT JOIN checklist c ON o.nome_num = c.nome_num
WHERE o.num_ocorrencia = ?";
$stmt_dados = $conn->prepare($query_dados);
$stmt_dados->bind_param("i", $id_ocorrencia);
$stmt_dados->execute();
$result_dados = $stmt_dados->get_result();
$row = $result_dados->fetch_assoc();
$stmt_dados->close();

// Buscar nome do responsável da checklist
$query_responsavel = "SELECT nome FROM funcionarios WHERE id_funcionario = ?";
$stmt_responsavel = $conn->prepare($query_responsavel);
$stmt_responsavel->bind_param("i", $row['responsavel']);
$stmt_responsavel->execute();
$result_responsavel = $stmt_responsavel->get_result();
$nome_responsavel = $result_responsavel->fetch_assoc()['nome'] ?? 'Não informado';
$stmt_responsavel->close();

// Buscar nomes de ferramenta e maleta
$nome_ferramenta = '';
$nome_maleta = '';
if ($row['id_ferramenta']) {
    $query_ferramenta = "SELECT nome FROM ferramentas WHERE id_ferramenta = ?";
    $stmt_ferramenta = $conn->prepare($query_ferramenta);
    $stmt_ferramenta->bind_param("i", $row['id_ferramenta']);
    $stmt_ferramenta->execute();
    $result_ferramenta = $stmt_ferramenta->get_result();
    $nome_ferramenta = $result_ferramenta->fetch_assoc()['nome'] ?? 'Não informado';
    $stmt_ferramenta->close();
}
if ($row['id_maleta']) {
    $query_maleta = "SELECT nome FROM maletas WHERE id_maleta = ?";
    $stmt_maleta = $conn->prepare($query_maleta);
    $stmt_maleta->bind_param("i", $row['id_maleta']);
    $stmt_maleta->execute();
    $result_maleta = $stmt_maleta->get_result();
    $nome_maleta = $result_maleta->fetch_assoc()['nome'] ?? 'Não informado';
    $stmt_maleta->close();
}

// Buscar todos os funcionários para o select de responsável
$query_responsaveis = "SELECT id_funcionario, nome FROM funcionarios ORDER BY nome";
$result_responsaveis = $conn->query($query_responsaveis);

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Ocorrência</title>
    <link rel="icon" type="image/x-icon" href="../../../../images/icon.ico">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="back-button">
        <a href="..">Voltar</a>
    </div>
    <div class="container-box">
        <form action="processar_ocorrencia.php" method="POST">
            <input type="hidden" name="id_ocorrencia" value="<?php echo htmlspecialchars($id_ocorrencia); ?>">
            <input type="hidden" name="num_ordem_servico" value="<?php echo htmlspecialchars($row['nome_num']); ?>">
            <input type="hidden" name="id_ferramenta" value="<?php echo htmlspecialchars($row['id_ferramenta'] ?? ''); ?>">
            <input type="hidden" name="id_maleta" value="<?php echo htmlspecialchars($row['id_maleta'] ?? ''); ?>">
            <input type="hidden" name="id_mp" value="<?php echo htmlspecialchars($row['id_mp'] ?? ''); ?>"> <!-- Adicionado id_mp -->
            <input type="hidden" name="data_ocorrencia" value="<?php echo htmlspecialchars($row['data_ocorrencia']); ?>">

            <div class="section">
                <h2>Ocorrência #<?php echo htmlspecialchars($row['num_ocorrencia']); ?></h2>
            </div>

            <div class="section">
                <label for="num_ordem_servico">Nome/Número de Serviço</label>
                <input type="text" id="num_ordem_servico" name="num_ordem_servico_display" value="<?php echo htmlspecialchars($row['nome_num']); ?>" readonly>

                <label for="responsavel">Responsável:</label>
                <select id="responsavel" name="responsavel" required>
                    <?php while ($responsavel = $result_responsaveis->fetch_assoc()): ?>
                        <option value="<?php echo htmlspecialchars($responsavel['id_funcionario']); ?>"
                            <?php echo $row['responsavel'] == $responsavel['id_funcionario'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($responsavel['nome']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <?php if ($row['id_ferramenta']): ?>
                    <label for="id_ferramenta">Ferramenta:</label>
                    <input type="text" id="id_ferramenta" name="id_ferramenta_display" value="<?php echo htmlspecialchars($nome_ferramenta); ?>" readonly>
                <?php endif; ?>

                <?php if ($row['id_maleta']): ?>
                    <label for="id_maleta">Maleta:</label>
                    <input type="text" id="id_maleta" name="id_maleta_display" value="<?php echo htmlspecialchars($nome_maleta); ?>" readonly>
                <?php endif; ?>

                <label for="data_ocorrencia">Data da Ocorrência:</label>
                <input type="text" id="data_ocorrencia" name="data_ocorrencia_display" value="<?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($row['data_ocorrencia']))); ?>" readonly>

                <label for="descricao">Descrição:</label>
                <textarea id="descricao" name="descricao_display" readonly><?php echo htmlspecialchars($row['descricao'] ?? ''); ?></textarea>

                <label for="situacao">Situação:</label>
                <select id="situacao" name="situacao" required>
                    <option value="A resolver" <?php echo $row['situacao'] === 'A resolver' ? 'selected' : ''; ?>>A resolver</option>
                    <option value="Resolvido" <?php echo $row['situacao'] === 'Resolvido' ? 'selected' : ''; ?>>Resolvido</option>
                    <option value="Em andamento" <?php echo $row['situacao'] === 'Em andamento' ? 'selected' : ''; ?>>Em andamento</option>
                </select>
            </div>

            <div class="section">
                <button type="submit">Salvar Ocorrência</button>
            </div>
        </form>
    </div>
</body>

</html>