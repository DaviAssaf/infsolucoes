<?php
require_once '../verificacao_sessao.php';
include '../conn.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Método inválido. Use POST.");
}

// Receber e sanitizar os dados do formulário
$id_cliente = filter_input(INPUT_POST, 'id_cliente', FILTER_VALIDATE_INT);
$cnpj = htmlspecialchars(trim($_POST['cnpj'] ?? ''), ENT_QUOTES, 'UTF-8');
$nome_empresa = htmlspecialchars(trim($_POST['nome_empresa'] ?? ''), ENT_QUOTES, 'UTF-8');
$contato = htmlspecialchars(trim($_POST['contato'] ?? ''), ENT_QUOTES, 'UTF-8');
$telefone = htmlspecialchars(trim($_POST['telefone'] ?? ''), ENT_QUOTES, 'UTF-8');
$email = isset($_POST['email']) && !empty(trim($_POST['email'])) ? filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL) : null;
$cep = htmlspecialchars(trim($_POST['cep'] ?? ''), ENT_QUOTES, 'UTF-8');
$endereco = htmlspecialchars(trim($_POST['endereco'] ?? ''), ENT_QUOTES, 'UTF-8');
$numero = !empty($_POST['numero_endereco']) ? (int)$_POST['numero_endereco'] : null;
$complemento = !empty($_POST['complemento']) ? htmlspecialchars(trim($_POST['complemento']), ENT_QUOTES, 'UTF-8') : null;
$bairro = htmlspecialchars(trim($_POST['bairro'] ?? ''), ENT_QUOTES, 'UTF-8');
$cidade = htmlspecialchars(trim($_POST['cidade'] ?? ''), ENT_QUOTES, 'UTF-8');
$estado = htmlspecialchars(trim($_POST['estado'] ?? ''), ENT_QUOTES, 'UTF-8');

// Validação dos campos obrigatórios
$required_fields = [
    'id_cliente' => $id_cliente,
    'cnpj' => $cnpj,
    'nome_empresa' => $nome_empresa,
    'contato' => $contato,
    'telefone' => $telefone,
    'cep' => $cep,
    'endereco' => $endereco,
    'bairro' => $bairro,
    'cidade' => $cidade,
    'estado' => $estado,
];

foreach ($required_fields as $field_name => $field_value) {
    if (empty($field_value) && $field_value !== 0) { // Permitir 0 como valor válido para $numero
        die("Erro: O campo '$field_name' é obrigatório.");
    }
}

// Validar o formato do email, se fornecido
if ($email !== null && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Erro: O email fornecido é inválido.");
}

// Iniciar transação
$conn->begin_transaction();

try {
    // Verificar se o CNPJ já existe para outro cliente
    $query_check_cnpj = "SELECT id_cliente FROM cliente WHERE cnpj = ? AND id_cliente != ?";
    $stmt_check_cnpj = $conn->prepare($query_check_cnpj);
    if (!$stmt_check_cnpj) {
        throw new Exception("Erro ao preparar a verificação de CNPJ: " . $conn->error);
    }
    $stmt_check_cnpj->bind_param("si", $cnpj, $id_cliente);
    $stmt_check_cnpj->execute();
    $result_check_cnpj = $stmt_check_cnpj->get_result();
    if ($result_check_cnpj->num_rows > 0) {
        throw new Exception("Erro: O CNPJ '$cnpj' já está associado a outro cliente.");
    }
    $stmt_check_cnpj->close();

    // Atualizar a tabela cliente
    $query = "UPDATE cliente SET cnpj = ?, nome_empresa = ?, contato = ?, telefone = ?, email = ? WHERE id_cliente = ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Erro ao preparar a atualização do cliente: " . $conn->error);
    }
    $stmt->bind_param("sssssi", $cnpj, $nome_empresa, $contato, $telefone, $email, $id_cliente);
    if (!$stmt->execute()) {
        throw new Exception("Erro ao atualizar o cliente: " . $stmt->error);
    }
    $stmt->close();

    // Verificar se já existe um endereço para o cliente
    $query_check_endereco = "SELECT id_endereco FROM os_endereco WHERE id_cliente = ?";
    $stmt_check_endereco = $conn->prepare($query_check_endereco);
    if (!$stmt_check_endereco) {
        throw new Exception("Erro ao preparar a verificação de endereço: " . $conn->error);
    }
    $stmt_check_endereco->bind_param("i", $id_cliente);
    $stmt_check_endereco->execute();
    $result_check_endereco = $stmt_check_endereco->get_result();

    if ($result_check_endereco->num_rows > 0) {
        // Atualizar o endereço existente
        $query_update_endereco = "UPDATE os_endereco SET cep = ?, endereco = ?, numero = ?, complemento = ?, bairro = ?, cidade = ?, estado = ? WHERE id_cliente = ?";
        $stmt_update_endereco = $conn->prepare($query_update_endereco);
        if (!$stmt_update_endereco) {
            throw new Exception("Erro ao preparar a atualização do endereço: " . $conn->error);
        }
        $stmt_update_endereco->bind_param("ssissssi", $cep, $endereco, $numero, $complemento, $bairro, $cidade, $estado, $id_cliente);
        if (!$stmt_update_endereco->execute()) {
            throw new Exception("Erro ao atualizar o endereço: " . $stmt_update_endereco->error);
        }
        $stmt_update_endereco->close();
    } else {
        // Inserir um novo endereço
        $query_insert_endereco = "INSERT INTO os_endereco (id_cliente, cep, endereco, numero, complemento, bairro, cidade, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_insert_endereco = $conn->prepare($query_insert_endereco);
        if (!$stmt_insert_endereco) {
            throw new Exception("Erro ao preparar a inserção do endereço: " . $conn->error);
        }
        $stmt_insert_endereco->bind_param("ississss", $id_cliente, $cep, $endereco, $numero, $complemento, $bairro, $cidade, $estado);
        if (!$stmt_insert_endereco->execute()) {
            throw new Exception("Erro ao inserir o endereço: " . $stmt_insert_endereco->error);
        }
        $stmt_insert_endereco->close();
    }
    $stmt_check_endereco->close();

    // Commit da transação
    $conn->commit();
    header("Location: " . dirname($_SERVER['PHP_SELF']) . "?sucess");
    exit();
} catch (Exception $e) {
    // Rollback em caso de erro
    $conn->rollback();
    error_log("Erro ao atualizar cliente: " . $e->getMessage());
    die("Erro ao atualizar cliente: " . $e->getMessage());
} finally {
    $conn->close();
}
?>