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
    <title>Gerando PDF do Check-list</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/x-icon" href="../../../images/icon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        window.id_checklist = <?php echo json_encode($id_checklist); ?>;
    </script>
    <script type="module" src="script.js"></script>
</head>

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
    <title>Gerando PDF do Check-list</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/x-icon" href="../../../images/icon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        window.id_checklist = <?php echo json_encode($id_checklist); ?>;
    </script>
    <script type="module" src="script.js"></script>
</head>

<body>
    <div class="hidden">
        <div class="container-print" id="pdf-content">

            <!-- HEADER -->
            <div class="header">
                <img src="../../../images/logo_infinity_menu.jpg" alt="Logo" class="logo">
                <h1 class="title">Check-list # <?php echo htmlspecialchars($checklist['id_checklist']); ?></h1>
            </div>

            <!-- INFO PRINCIPAL -->
            <div class="section info">
                <h3>Ordem de Serviço: <?php echo htmlspecialchars($checklist['nome_num'] ?? 'Não informado'); ?></h3>
                <p><strong>Cliente:</strong> <?php echo htmlspecialchars($checklist['nome_empresa'] ?? 'Não informado'); ?></p>
                <p><strong>Telefone:</strong> <?php echo htmlspecialchars($checklist['telefone'] ?? 'Não informado'); ?></p>
            </div>

            <!-- RESPONSÁVEL -->
            <div class="section info-colab">
                <p><strong>Responsável:</strong> <?php echo htmlspecialchars($checklist['nome_responsavel'] ?? 'Não informado'); ?></p>
                <p><strong>Destino:</strong> <?php echo htmlspecialchars($destino); ?></p>
                <p><strong>Saída:</strong> 
                    <?php echo $checklist['saida'] ? date('d/m/Y H:i', strtotime($checklist['saida'])) : 'Não informado'; ?>
                </p>
                <p><strong>Retorno:</strong>
                    <?php echo $checklist['retorno'] ? date('d/m/Y H:i', strtotime($checklist['retorno'])) : '____/____/________  ____:____'; ?>
                </p>
            </div>

            <!-- VEÍCULO -->
            <?php if ($checklist['veiculo'] != null): ?>
            <div class="section vehicle-section">
                <h3>Veículo</h3>
                <div class="vehicle-details">
                    <p><strong>Nome:</strong> <?php echo htmlspecialchars($checklist['nome_veiculo']); ?></p>
                    <p><strong>Placa:</strong> <?php echo htmlspecialchars($checklist['placa']); ?></p>
                    <p><strong>Motorista:</strong> <?php echo htmlspecialchars($checklist['nome_motorista']); ?></p>
                    <p><strong>KM de Saída:</strong> <?php echo htmlspecialchars($checklist['km_saida'] ?: '0'); ?></p>
                    <p><strong>KM de Retorno:</strong> ____________________</p>
                </div>
            </div>
            <?php endif; ?>

            <!-- FERRAMENTAS -->
            <div class="ferramentas">
                <h3>Ferramentas</h3>

                <!-- TABELA PRINCIPAL -->
                <table class="table-style-print">
                    <thead>
                        <tr>
                            <th>ITEM</th>
                            <th>SAÍDA</th>
                            <th>RETORNO (SOMENTE EXTRAVIO)</th>
                            <th>RETORNADO</th>
                        </tr>
                    </thead>
                    <tbody>

                    <?php while ($item = $result_itens->fetch_assoc()): ?>
                        <tr>
                            <?php if ($item['id_maleta']): ?>
                                <tr class="maleta-row">
                                    <td class="initial maleta"><?php echo htmlspecialchars($item['nome_maleta']); ?></td>
                                    <td class="maleta"></td>
                                    <td class="maleta"></td>
                                    <td class="final maleta"></td>
                                </tr>


                            <!-- MINI TABELA DA MALETA -->
                            <?php if (isset($ferramentas_por_maleta[$item['id_maleta']])): ?>
                            <tr>
                                <td colspan="4" class="mini-table">
                                    <div class="mini-table-wrapper">
                                        <table class="mini-table-content">
                                            <thead>
                                                <tr>
                                                    <th>FERRAMENTAS DA MALETA</th>
                                                    <th>Saída</th>
                                                    <th>Retorno</th>
                                                    <th>OK</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($ferramentas_por_maleta[$item['id_maleta']] as $ferramenta): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($ferramenta['nome_ferramenta']); ?></td>
                                                        <td><?php echo htmlspecialchars($ferramenta['quantidade_levada'] ?: 0); ?></td>
                                                        <td></td>
                                                        <td><input type="checkbox"></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                            <?php endif; ?>

                            <?php else: ?>
                            <!-- FERRAMENTA SOLTA -->
                                <td><?php echo htmlspecialchars($item['nome_ferramenta']); ?></td>
                                <td><?php echo htmlspecialchars($item['quantidade_levada'] ?: 0); ?></td>
                                <td></td>
                                <td><input type="checkbox"></td>
                            <?php endif; ?>
                        </tr>
                    <?php endwhile; ?>

                    </tbody>
                </table>
            </div>

            <!-- OBS -->
            <div class="obs">
                <p><strong>Observações:</strong></p>
                <textarea></textarea>
            </div>

            <!-- ASSINATURAS -->
            <div class="sign">
                <div class="sign-saida">
                    <h4>Saída</h4>
                    <p>_____________________________</p>
                    <p>Assinatura do responsável</p>
                </div>
                <div class="line"></div>
                <div class="sign-retorno">
                    <h4>Retorno</h4>
                    <p>_____________________________</p>
                    <p>Assinatura do responsável</p>
                </div>
            </div>

            <h3 style="text-align: center;">Assinatura</h3>

        </div>
    </div>

    <?php
    $stmt_checklist->close();
    $stmt_itens->close();
    $stmt_ferramentas_maleta->close();
    $conn->close();
    ?>
</body>

</html>

</html>