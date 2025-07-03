<?php
require_once '../../verificacao_sessao.php';
include '../../conn.php';

date_default_timezone_set('America/Sao_Paulo');

if (!isset($_GET['id_checklist']) || !($id_checklist = filter_input(INPUT_GET, 'id_checklist', FILTER_VALIDATE_INT)) || $id_checklist <= 0) {
    die("Erro: ID do checklist inválido ou não especificado.");
}

$query_checklist = "SELECT 
    c.id_checklist, c.nome_num, c.responsavel, f.nome AS nome_responsavel,
    c.motorista, f2.nome AS nome_motorista, c.veiculo, v.nome AS nome_veiculo, 
    v.placa, v.marca, v.km AS km_atual, c.acompanhantes, c.cliente, c.telefone AS telefone_custom, cli.nome_empresa, 
    cli.telefone, cli.contato, c.destino, c.cidade,
    c.saida, c.retorno, c.situacao, c.observacoes, c.km_saida, c.km_retorno 
FROM checklist c 
LEFT JOIN funcionarios f ON c.responsavel = f.id_funcionario 
LEFT JOIN funcionarios f2 ON c.motorista = f2.id_funcionario 
LEFT JOIN veiculos v ON c.veiculo = v.id_veiculo 
LEFT JOIN cliente cli ON c.cliente = cli.id_cliente 
LEFT JOIN os_endereco ce ON c.destino = ce.id_endereco 
WHERE c.id_checklist = ?";
$stmt_checklist = $conn->prepare($query_checklist);
$stmt_checklist->bind_param("i", $id_checklist);
$stmt_checklist->execute();
$result_checklist = $stmt_checklist->get_result();
if ($result_checklist->num_rows === 0) {
    die("Erro: Checklist não encontrado para ID $id_checklist.");
}
$checklist = $result_checklist->fetch_assoc();

if ($checklist['nome_empresa'] === null) {
    $checklist['nome_empresa'] = $checklist['cliente'];
    $checklist['telefone'] = $checklist['telefone_custom'];
}

if (!empty($checklist['destino']) && !empty($checklist['cidade'])) {
    $destino = $checklist['destino'] . ', ' . $checklist['cidade'];
} elseif (!empty($checklist['destino'])) {
    $destino = $checklist['destino'];
} elseif (!empty($checklist['cidade'])) {
    $destino = $checklist['cidade'];
} else {
    $destino = 'Não informado';
}

if ($checklist['nome_num'] === null) {
    if (!empty($checklist['observacoes']) && strpos($checklist['observacoes'], 'Local: ') === 0) {
        $endereco = substr($checklist['observacoes'], 7);
    }
}

$query_itens = "SELECT 
    cf.id, cf.id_checklist, cf.id_ferramenta, f.nome AS nome_ferramenta, 
    cf.id_maleta, m.nome AS nome_maleta, cf.quantidade_levada, cf.quantidade_devolvida, cf.retornado
FROM caixa_ferramentas cf 
LEFT JOIN ferramentas f ON cf.id_ferramenta = f.id_ferramenta 
LEFT JOIN maletas m ON cf.id_maleta = m.id_maleta 
WHERE cf.id_checklist = ? 
AND (
    (cf.id_maleta IS NOT NULL AND cf.id_ferramenta IS NULL)
    OR (cf.id_ferramenta IS NOT NULL AND cf.id_maleta IS NULL)
)";
$stmt_itens = $conn->prepare($query_itens);
$stmt_itens->bind_param("i", $id_checklist);
$stmt_itens->execute();
$result_itens = $stmt_itens->get_result();

$query_ferramentas_maleta = "SELECT 
    cf.id, cf.id_ferramenta, f.nome AS nome_ferramenta, 
    cf.id_maleta, cf.quantidade_levada, cf.quantidade_devolvida, cf.retornado 
FROM caixa_ferramentas cf 
LEFT JOIN ferramentas f ON cf.id_ferramenta = f.id_ferramenta 
WHERE cf.id_checklist = ? AND cf.id_maleta IS NOT NULL AND cf.id_ferramenta IS NOT NULL";
$stmt_ferramentas_maleta = $conn->prepare($query_ferramentas_maleta);
$stmt_ferramentas_maleta->bind_param("i", $id_checklist);
$stmt_ferramentas_maleta->execute();
$result_ferramentas_maleta = $stmt_ferramentas_maleta->get_result();
$ferramentas_por_maleta = [];
while ($row = $result_ferramentas_maleta->fetch_assoc()) {
    $ferramentas_por_maleta[$row['id_maleta']][] = $row;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Retornar Check-list</title>
    <link rel="icon" type="image/x-icon" href="../../../images/icon.ico">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="script.js"></script>
</head>

<body>
    <div class="back-button">
        <a href="..">Voltar</a>
    </div>
    <div class="container">
        <div class="header">
            <img src="../../../images/logo_infinity_menu.jpg" alt="Logo" class="logo">
            <h1 style="flex-grow: 1; text-align: center; margin: 0;"><strong><?php echo htmlspecialchars($checklist['id_checklist']); ?></strong></h1>
            <h1 style="margin: 0;">Check-list</h1>
        </div>
        <form action="retornar_checklist.php" method="POST">
            <input type="hidden" name="id_checklist" value="<?php echo htmlspecialchars($id_checklist); ?>">
            <input type="hidden" name="criador" value="">
            <div class="info">
                <h3>Ordem de Serviço: <?php echo htmlspecialchars($checklist['nome_num'] ?? 'Não informado'); ?></h3>
                <p><strong>Cliente:</strong> <?php echo htmlspecialchars($checklist['nome_empresa'] ?? 'Não informado'); ?></p>
                <p><strong>Telefone:</strong> <?php echo htmlspecialchars($checklist['telefone'] ?? 'Não informado'); ?></p>
            </div>
            <div class="info">
                <p><strong>Responsável:</strong> <?php echo htmlspecialchars($checklist['nome_responsavel'] ?? 'Não informado'); ?></p>
                <p><strong>Destino:</strong> <?php echo htmlspecialchars($destino); ?></p>
                <p><strong>Saída:</strong> <?php echo htmlspecialchars($checklist['saida'] ? date('d/m/Y H:i', strtotime($checklist['saida'])) : 'Não informado'); ?></p>
                <p><strong>Retorno:</strong>
                    <input type="datetime-local" name="retorno" value="<?php echo date('Y-m-d\TH:i'); ?>" required>
                </p>
            </div>
            <?php if ($checklist['veiculo'] != null): ?>
                <div class="section vehicle-section">
                    <h3>Veículo</h3>
                    <div class="vehicle-details">
                        <p><strong>Nome:</strong> <span><?php echo htmlspecialchars($checklist['nome_veiculo'] ?? 'Não informado'); ?></span></p>
                        <p><strong>Placa:</strong> <span><?php echo htmlspecialchars($checklist['placa'] ?? 'Não informado'); ?></span></p>
                        <p><strong>Motorista:</strong> <span><?php echo htmlspecialchars($checklist['nome_motorista'] ?? 'Não informado'); ?></span></p>
                        <div class="km-inputs">
                            <p><strong>KM de Saída:</strong>
                                <input type="number" name="km_saida" value="<?php echo htmlspecialchars($checklist['km_saida'] ?? '0'); ?>" min="0" required class="km-field" data-default="<?php echo htmlspecialchars($checklist['km_saida'] ?? '0'); ?>">
                            </p>
                            <p><strong>KM de Retorno:</strong>
                                <input type="number" name="km_retorno" value="<?php echo htmlspecialchars($checklist['km_retorno'] ?? $checklist['km_atual'] ?? '0'); ?>" min="0" required class="km-field" data-default="<?php echo htmlspecialchars($checklist['km_retorno'] ?? $checklist['km_atual'] ?? '0'); ?>">
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif ?>
            <div class="ferramentas">
                <h3>Ferramentas</h3>
                <div id="master-check-container">
                    <label><input type="checkbox" id="master-check">Marcar Todos</label>
                </div>

                <div id="addTool">

                </div>
                <table class="table-style">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Saída</th>
                            <th>Retorno</th>
                            <th>Retornado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($item = $result_itens->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?php if ($item['id_maleta']): ?>
                                        <button type="button" class="toggle-btn"><i class="fas fa-angle-down"></i></button>
                                        <br>
                                        <a href="criar_ocorrencia.php?id_checklist=<?php echo $id_checklist; ?>&id_maleta=<?php echo $item['id_maleta']; ?>&nome_num=<?php echo urlencode($checklist['nome_num'] ?? 'Não informado'); ?>" class="create-occurrence" data-name="<?php echo htmlspecialchars($item['nome_maleta']); ?>">
                                            <?php echo htmlspecialchars($item['nome_maleta']); ?>
                                        </a>
                                    <?php else: ?>
                                        <a href="criar_ocorrencia.php?id_checklist=<?php echo $id_checklist; ?>&id_ferramenta=<?php echo $item['id_ferramenta']; ?>&nome_num=<?php echo urlencode($checklist['nome_num'] ?? 'Não informado'); ?>" class="create-occurrence" data-name="<?php echo htmlspecialchars($item['nome_ferramenta']); ?>">
                                            <?php echo htmlspecialchars($item['nome_ferramenta']); ?>
                                        </a>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($item['quantidade_levada'] ?? 0); ?></td>
                                <td>
                                    <input type="number" name="quantidade_devolvida[<?php echo $item['id']; ?>]" value="<?php echo htmlspecialchars($item['retornado'] === 'OK' ? ($item['quantidade_levada'] ?? 0) : ($item['quantidade_devolvida'] ?? 0)); ?>" min="0" max="<?php echo $item['quantidade_levada'] ?? 0; ?>" <?php echo $item['retornado'] === 'OK' ? 'disabled' : ''; ?>>
                                </td>
                                <td>
                                    <input type="checkbox" name="retornado[<?php echo $item['id']; ?>]" value="1" <?php echo $item['retornado'] === 'OK' ? 'checked' : ''; ?> class="<?php echo $item['id_maleta'] ? 'maleta-checkbox' : 'checkbox'; ?>" class="<?php echo $item['id_maleta'] && !$item['id_ferramenta'] ? 'maleta-check' : 'ferramenta-check'; ?>" data-id-maleta="<?php echo $item['id_maleta'] ?? ''; ?>">
                                </td>
                            </tr>
                            <?php if ($item['id_maleta'] && isset($ferramentas_por_maleta[$item['id_maleta']])): ?>
                                <tr>
                                    <td colspan="4" class="mini-table">
                                        <table class="mini-table-content">
                                            <thead>
                                                <tr>
                                                    <th>Nome</th>
                                                    <th>Saída</th>
                                                    <th>Retorno</th>
                                                    <th>Retornado</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($ferramentas_por_maleta[$item['id_maleta']] as $ferramenta): ?>
                                                    <tr>
                                                        <td>
                                                            <a href="criar_ocorrencia.php?id_checklist=<?php echo $id_checklist; ?>&id_ferramenta=<?php echo $ferramenta['id_ferramenta']; ?>&nome_num=<?php echo urlencode($checklist['nome_num'] ?? 'Não informado'); ?>" class="create-occurrence" data-name="<?php echo htmlspecialchars($ferramenta['nome_ferramenta']); ?>">
                                                                <?php echo htmlspecialchars($ferramenta['nome_ferramenta']); ?>
                                                            </a>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($ferramenta['quantidade_levada'] ?? 0); ?></td>
                                                        <td>
                                                            <input type="number" name="quantidade_devolvida[<?php echo $ferramenta['id']; ?>]" value="<?php echo htmlspecialchars($ferramenta['retornado'] === 'OK' ? ($ferramenta['quantidade_levada'] ?? 0) : ($ferramenta['quantidade_devolvida'] ?? 0)); ?>" min="0" max="<?php echo $ferramenta['quantidade_levada'] ?? 0; ?>" <?php echo $ferramenta['retornado'] === 'OK' ? 'disabled' : ''; ?>>
                                                        </td>
                                                        <td>
                                                            <input type="checkbox" name="retornado[<?php echo $ferramenta['id']; ?>]" value="1" <?php echo $ferramenta['retornado'] === 'OK' ? 'checked' : ''; ?> class="checkbox" data-id-maleta="<?php echo $ferramenta['id_maleta']; ?>">
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <div class="info">
                <label for="observacoes">Observações:</label>
                <textarea name="observacoes" id="observacoes"><?php echo htmlspecialchars($checklist['observacoes'] ?? ''); ?></textarea>
            </div>
            <div class="section" style="text-align: center; margin-top: 40px;">
                <h3>Assinatura</h3>
                <p style="margin-bottom: 5px;">___________________________________________________________________________</p>
                <p style="font-size: 14px; color: #555;">Assinatura do responsável</p>
            </div>
            <div class="button-container">
                <button type="submit" id="saveChecklist" class="btn">Finalizar Retorno</button>
            </div>
        </form>
        <?php
        $stmt_checklist->close();
        $stmt_itens->close();
        $stmt_ferramentas_maleta->close();
        $conn->close();
        ?>
    </div>
</body>

</html>