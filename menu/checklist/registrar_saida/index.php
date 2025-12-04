<?php
require_once '../../verificacao_sessao.php';
include '../../conn.php';

date_default_timezone_set('America/Sao_Paulo');

// Query para clientes (listando todos os clientes e seus endereços)
$query_clientes = "SELECT c.id_cliente, c.nome_empresa, o.endereco, o.numero, o.bairro, o.cidade
                   FROM cliente c
                   LEFT JOIN os_endereco o ON c.id_cliente = o.id_cliente
                   ORDER BY c.nome_empresa ASC";

$stmt_clientes = $conn->prepare($query_clientes);
if (!$stmt_clientes) {
    die("Erro ao preparar a query de clientes: " . $conn->error);
}
$stmt_clientes->execute();
$result_clientes = $stmt_clientes->get_result();

// Debug: Logar os registros retornados pela query
$debug_clientes = [];
if ($result_clientes->num_rows > 0) {
    while ($row = $result_clientes->fetch_assoc()) {
        $debug_clientes[] = $row;
    }
    error_log("Registros encontrados em cliente: " . print_r($debug_clientes, true));
} else {
    error_log("Nenhum registro encontrado em cliente.");
}

// Voltar o ponteiro do resultado para o início
$result_clientes->data_seek(0);

// Gerar as opções do dropdown de clientes
$options_clientes = '<option value="" SELECTED>Selecione um cliente</option>
                     <option value="servico_customizavel">Serviço Customizável</option>';
if ($result_clientes->num_rows > 0) {
    while ($row_cliente = $result_clientes->fetch_assoc()) {
        $row_cliente = array_map(function ($value) {
            return $value ?? '';
        }, $row_cliente);
        // Armazenar os dados do endereço como atributos data-* para uso no JavaScript
        $options_clientes .= '<option value="' . htmlspecialchars($row_cliente["id_cliente"]) . '" 
                                    data-endereco="' . htmlspecialchars($row_cliente["endereco"]) . '" 
                                    data-numero="' . htmlspecialchars($row_cliente["numero"]) . '" 
                                    data-bairro="' . htmlspecialchars($row_cliente["bairro"]) . '" 
                                    data-cidade="' . htmlspecialchars($row_cliente["cidade"]) . '">
                                    ' . htmlspecialchars($row_cliente["nome_empresa"]) . '</option>';
    }
} else {
    $options_clientes .= '<option value="">Nenhum cliente disponível</option>';
}

// Query para funcionários (para Responsável e Motorista)
$query_funcionarios = "SELECT id_funcionario, nome FROM funcionarios ORDER BY nome ASC";
$stmt_funcionarios = $conn->prepare($query_funcionarios);
if (!$stmt_funcionarios) {
    die("Erro ao preparar a query de funcionários: " . $conn->error);
}
$stmt_funcionarios->execute();
$result_funcionarios = $stmt_funcionarios->get_result();

$options_funcionarios = '<option value="" SELECTED>Selecione</option>';
if ($result_funcionarios) {
    while ($row_funcionario = $result_funcionarios->fetch_assoc()) {
        $row_funcionario = array_map(function ($value) {
            return $value ?? '';
        }, $row_funcionario);
        $options_funcionarios .= '<option value="' . htmlspecialchars($row_funcionario["id_funcionario"]) . '">' . htmlspecialchars($row_funcionario["nome"]) . '</option>';
    }
} else {
    $options_funcionarios .= '<option value="">Erro na consulta de funcionários: ' . htmlspecialchars($conn->error) . '</option>';
}

// Query para veículos
$query_veiculos = "SELECT id_veiculo, nome, placa, km FROM veiculos ORDER BY placa ASC";
$stmt_veiculos = $conn->prepare($query_veiculos);
if (!$stmt_veiculos) {
    die("Erro ao preparar a query de veículos: " . $conn->error);
}
$stmt_veiculos->execute();
$result_veiculos = $stmt_veiculos->get_result();

$options_veiculos = '<option value="" SELECTED>Selecione</option>';
if ($result_veiculos) {
    while ($row_veiculo = $result_veiculos->fetch_assoc()) {
        $row_veiculo = array_map(function ($value) {
            return $value ?? '';
        }, $row_veiculo);
        $options_veiculos .= '<option value="' . htmlspecialchars($row_veiculo["id_veiculo"]) . '" data-km="' . htmlspecialchars($row_veiculo["km"]) . '">' . htmlspecialchars($row_veiculo['nome']) . " - " . htmlspecialchars($row_veiculo["placa"]) . '</option>';
    }
} else {
    $options_veiculos .= '<option value="">Erro na consulta de veículos: ' . htmlspecialchars($conn->error) . '</option>';
}

$tipos = ['Ferramentas elétricas', 'Ferramentas manuais', 'Maletas', 'Todas as Ferramentas'];

$stmt_clientes->close();
$stmt_funcionarios->close();
$stmt_veiculos->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Controle de Ferramentas/Veículos</title>
    <link rel="icon" type="image/x-icon" href="../../../images/icon.ico">
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script src="script.js"></script>
</head>

<body>
    <div class="back-button">
        <a href="..">Voltar</a>
    </div>
    <div class="container-box">
        <form action="criar_checklist.php" method="POST">
            <div class="section">
                <h2>Ordem de Serviço</h2>
                <label for="cliente">Cliente:</label>
                <select name="cliente" id="cliente">
                    <?php echo $options_clientes; ?>
                </select>

                <!-- Campo hidden para armazenar o id_cliente -->
                <input type="hidden" name="id_cliente" id="id_cliente" value="">

                <label for="nome_num">Nome/Número do Serviço:</label>
                <input type="text" name="nome_num" id="nome_num" required>

                <label for="local">Local:</label>
                <input type="text" name="local" id="local">

                <label for="cidade">Cidade:</label>
                <input type="text" name="cidade" id="cidade">
                <div id="customizavel"></div>

                <label for="saida">Data de Saída:</label>
                <input type="datetime-local" name="saida" id="saida" value="<?php echo date('Y-m-d\TH:i'); ?>" required>
            </div>

            <div class="section">
                <h2>Responsável</h2>
                <label for="responsavel">Responsável:</label>
                <select name="responsavel" id="responsavel" required>
                    <?php echo $options_funcionarios; ?>
                </select>

                <label for="veiculo">Veículo:</label>
                <select name="veiculo" id="veiculo">
                    <?php echo $options_veiculos; ?>
                </select>

                <label for="motorista">Motorista:</label>
                <select name="motorista" id="motorista">
                    <?php echo $options_funcionarios; ?>
                </select>


                <label for="acompanhantes">Acompanhantes:</label>
                <textarea name="acompanhantes" id="acompanhantes"></textarea>

                <label for="km">KM Saída:</label>
                <input type="number" name="km" id="km" required readonly>
            </div>

            <div class="section">
                <h2>Ferramentas</h2>
                <label for="tipo">Tipo:</label>
                <select name="tipo" id="tipo">
                    <option value="">Selecione</option>
                    <?php
                    foreach ($tipos as $tipo): ?>
                        <option value="<?php echo htmlspecialchars($tipo); ?>"><?php echo htmlspecialchars($tipo); ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="ferramentas">Ferramentas:</label>
                <select name="ferramentas" id="ferramentas">
                    <option value="">Selecione</option>
                </select>

                <label for="quantidade">Quantidade:</label>
                <input type="number" name="quantidade" id="quantidade" step="0.01" min="0">

                <button type="button" id="addItem" class="btn">+</button>

                <div id="caixa_ferramentas">
                    <table class="table-style">
                        <thead>
                            <tr>
                                <th>Ferramentas/Maletas</th>
                                <th>Quantidade</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <input type="hidden" name="itensAdicionados" id="itensAdicionados">

            <div class="section">
                <button type="submit" class="btn">Enviar Checklist</button>
            </div>
        </form>
    </div>
</body>

</html>