<?php
require_once '../../verificacao_sessao.php';
include '../../conn.php';

date_default_timezone_set('America/Sao_Paulo');

if (!isset($_GET['id_checklist']) || !($id_checklist = filter_input(INPUT_GET, 'id_checklist', FILTER_VALIDATE_INT)) || $id_checklist <= 0) {
    die("Erro: ID do checklist inválido ou não especificado.");
}

// Consulta do checklist
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
    header('Location: ..?error=Checklist não encontrado');
    exit;
}
$checklist = $result_checklist->fetch_assoc();

if ($checklist['nome_empresa'] === null) {
    $checklist['nome_cliente'] = $checklist['cliente'];
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

// Consulta de ferramentas e maletas disponíveis
$query_ferramentas = "SELECT id_ferramenta, nome, quantidade_atual, tipo FROM ferramentas WHERE quantidade_atual > 0";
$stmt_ferramentas = $conn->prepare($query_ferramentas);
$stmt_ferramentas->execute();
$result_ferramentas = $stmt_ferramentas->get_result();
$ferramentas = [];
while ($row_ferramenta = $result_ferramentas->fetch_assoc()) {
    $ferramentas[$row_ferramenta['id_ferramenta']] = $row_ferramenta;
}

$query_maletas = "SELECT id_maleta, nome, situacao FROM maletas WHERE situacao = 'Disponível'";
$stmt_maletas = $conn->prepare($query_maletas);
$stmt_maletas->execute();
$result_maletas = $stmt_maletas->get_result();
$maletas = [];
while ($row_maleta = $result_maletas->fetch_assoc()) {
    $maletas[$row_maleta['id_maleta']] = $row_maleta;
}

// Consulta de itens do checklist
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
    <title>Editar Checklist #<?php echo htmlspecialchars($id_checklist); ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/x-icon" href="../../../images/icon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="script.js"></script>
</head>

<body>
    <div class="back-button">
        <a href="..">Voltar</a>
    </div>

    <h1>Editar Checklist #<?php echo htmlspecialchars($id_checklist); ?></h1>
    <div class="container">
        <div class="header">
            <img src="/images/logo_infinity_menu.jpg" alt="Logo" class="logo">
            <h1 style="flex-grow: 1; text-align: center; margin: 0;"><strong><?php echo htmlspecialchars($id_checklist); ?></strong></h1>
            <h1 style="margin: 0;">Check-list</h1>
        </div>
        <form action="salvar_alteracoes.php" method="POST" id="checklistForm">
            <input type="hidden" name="id_checklist" value="<?php echo htmlspecialchars($id_checklist); ?>">
            <input type="hidden" name="removed_ids" id="removed_ids" value="">

            <div class="form-header">
                <div class="info">
                    <h3>Ordem de Serviço: <input type="text" name="nome_num" value="<?php echo htmlspecialchars($checklist['nome_num'] ?? 'Não informado'); ?>" required></h3>
                    <p><strong>Cliente:</strong> <?php echo htmlspecialchars($checklist['nome_empresa'] ?? 'Não informado'); ?></p>
                    <p><strong>Telefone:</strong> <?php echo htmlspecialchars($checklist['telefone'] ?? 'Não informado'); ?></p>
                </div>
                <div class="info">
                    <p><strong>Responsável:</strong> <?php echo htmlspecialchars($checklist['nome_responsavel'] ?? 'Não informado'); ?></p>
                    <p><strong>Destino:</strong> <?php echo htmlspecialchars($destino); ?></p>
                    <p><strong>Saída:</strong> <?php echo htmlspecialchars($checklist['saida'] ? date('d/m/Y H:i', strtotime($checklist['saida'])) : 'Não informado'); ?></p>
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
                                    <input type="number" name="km_saida" value="<?php echo htmlspecialchars($checklist['km_saida'] ?? '0'); ?>" min="<?php echo htmlspecialchars($checklist['km_atual'] ?? '0'); ?>" required class="km-field" data-default="<?php echo htmlspecialchars($checklist['km_atual'] ?? '0'); ?>">
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endif ?>

                <div class="input-group">
                    <label for="tipo">Tipo:</label>
                    <select name="tipo" id="tipo">
                        <option value="">Selecione</option>
                        <?php
                        $tipos = ['Ferramentas elétricas', 'Ferramentas manuais', 'Todas as Ferramentas', 'Maletas'];
                        foreach ($tipos as $tipo): ?>
                            <option value="<?php echo htmlspecialchars($tipo); ?>"><?php echo htmlspecialchars($tipo); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label for="itens">Ferramenta/Maleta:</label>
                    <select name="itens" id="itens">
                        <option value="" selected>Selecione uma ferramenta ou maleta</option>
                    </select>
                    <button type="button" id="addItem" class="button-inline" aria-label="Adicionar item">+</button>
                </div>
            </div>

            <div class="ferramentas">
                <h3>Ferramentas</h3>
                <table class="table-style" id="itens_lista">
                    <thead>
                        <tr>
                            <th>Ações</th>
                            <th>Item</th>
                            <th>Saída</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($item = $result_itens->fetch_assoc()): ?>
                            <tr data-id="<?php echo htmlspecialchars($item['id']); ?>">
                                <td>
                                    <button type="button" class="delete-button btn-danger btn-sm remove-item" aria-label="Remover item">Remover</button>
                                    <?php if ($item['id_maleta']): ?>
                                        <button type="button" class="toggle-btn" aria-label="Expandir maleta"><i class="fas fa-angle-down"></i></button>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <input type="hidden" name="item_ids[]" value="<?php echo $item['id_ferramenta'] ? 'ferramenta_' . htmlspecialchars($item['id_ferramenta']) : 'maleta_' . htmlspecialchars($item['id_maleta']); ?>">
                                    <?php echo htmlspecialchars($item['id_maleta'] ? $item['nome_maleta'] : $item['nome_ferramenta']); ?>
                                </td>
                                <td>
                                    <div class="input-table">
                                        <input type="number" name="quantidades[]" min="0" value="<?php echo htmlspecialchars($item['quantidade_levada'] ?? 0); ?>" required
                                            data-max="<?php echo htmlspecialchars($item['id_maleta'] ? ($maletas[$item['id_maleta']]['quantidade'] ?? 1) : ($ferramentas[$item['id_ferramenta']]['quantidade_atual'] ?? 0)); ?>">
                                    </div>
                                </td>
                            </tr>
                            <?php if ($item['id_maleta'] && isset($ferramentas_por_maleta[$item['id_maleta']])): ?>
                                <tr class="mini-table" style="display: none;">
                                    <td colspan="3">
                                        <table class="mini-table-content">
                                            <thead>
                                                <tr>
                                                    <th>Nome</th>
                                                    <th>Saída</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($ferramentas_por_maleta[$item['id_maleta']] as $ferramenta): ?>
                                                    <tr>
                                                        <td>
                                                            <?php echo htmlspecialchars($ferramenta['nome_ferramenta']); ?>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($ferramenta['quantidade_levada']); ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endwhile; ?>
                        <?php if ($result_itens->num_rows === 0): ?>
                            <tr>
                                <td colspan="3" style="text-align: center;">Nenhum item encontrado</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="button-container">
                <button type="submit" id="saveChecklist" class="btn">Salvar Alterações</button>
            </div>
        </form>
        <?php
        $stmt_checklist->close();
        $stmt_itens->close();
        $stmt_ferramentas_maleta->close();
        $stmt_ferramentas->close();
        $stmt_maletas->close();
        $conn->close();
        ?>
    </div>
</body>

</html>