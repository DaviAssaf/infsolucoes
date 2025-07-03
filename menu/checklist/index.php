<?php
require_once '../verificacao_sessao.php';
include '../conn.php';

if (!$conn) {
    die("Erro na conexão com o banco de dados: " . $conn->connect_error);
}

// Buscar todos os checklists com informações de responsável e situação
$query_checklists = "SELECT c.id_checklist, c.nome_num, c.acompanhantes, f.nome AS responsavel, fc.nome AS criador, c.situacao, c.retorno, c.saida
                    FROM checklist c
                    LEFT JOIN funcionarios f ON c.responsavel = f.id_funcionario
                    LEFT JOIN funcionarios fc ON c.criador = fc.id_funcionario
                    ORDER BY c.id_checklist DESC";
$result_checklists = $conn->query($query_checklists);

if (!$result_checklists) {
    die("Erro ao executar a consulta: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualizar Checklists</title>
    <link rel="icon" type="image/x-icon" href="../../images/icon.ico">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="back-button">
        <a href="..">Voltar</a>
    </div>

    <div class="menu">
        <a href="registrar_saida">
            <button>Registrar Nova Saída</button>
        </a>

        <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 1): ?>
            <a href="ocorrencias">
                <button>Ocorrencias</button>
            </a>
        <?php endif; ?>
    </div>

    <div class="container">
        <div class="header">
            <img src="../../images/logo_infinity_menu.jpg" alt="Logo" class="logo">
            <h1 style="flex-grow: 1; text-align: center; margin: 0;">Visualizar Checklists</h1>
            <h2 style="margin: 0;"></h2>
        </div>

        <div class="section">
            <h3>Lista de Checklists</h3>
            <table class="table-style">
                <thead>
                    <tr>
                        <th>Numero</th>
                        <th>Criada por</th>
                        <th>Ordem de Serviço</th>
                        <th>Responsável</th>
                        <th>Acompanhantes</th>
                        <th>Situação</th>
                        <th>Saída</th>
                        <th>Retorno</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result_checklists->num_rows > 0) {
                        while ($checklist = $result_checklists->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($checklist['id_checklist']); ?></td>
                                <td><?php echo htmlspecialchars($checklist['criador'] ?? 'Desconhecido') ?></td>
                                <td><?php echo htmlspecialchars($checklist['nome_num'] ?? 'Não informado'); ?></td>
                                <td><?php echo htmlspecialchars($checklist['responsavel'] ?? 'Não informado'); ?></td>
                                <td><?php echo htmlspecialchars($checklist['acompanhantes'] ?? 'Sem Acompanhantes'); ?> </td>
                                <td class="status <?php echo htmlspecialchars($checklist['situacao'] ?: 'Pendente'); ?>"><?php echo htmlspecialchars(!isset($checklist['situacao']) ? 'Pendente' : $checklist['situacao']) ?></td>
                                <td><?php echo htmlspecialchars($checklist['saida'] ? date('d-m-Y H:i', strtotime($checklist['saida'])) : 'Pendente') ?></td>
                                <td><?php echo htmlspecialchars($checklist['retorno'] ? date('d-m-Y H:i', strtotime($checklist['retorno'])) : 'Pendente'); ?></td>
                                <td class="actions">
                                    <a href="retornar_checklist?id_checklist=<?php echo htmlspecialchars($checklist['id_checklist']); ?>">Retornar</a>
                                    <a href="editar_checklist?id_checklist=<?php echo htmlspecialchars($checklist['id_checklist']); ?>">Editar</a>
                                    <a href="imprimir_checklist?id_checklist=<?php echo htmlspecialchars($checklist['id_checklist']); ?>">Imprimir</a>
                                </td>
                            </tr>
                    <?php endwhile;
                    } else {
                        echo "<tr><td colspan='6'>Nenhum checklist encontrado.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <?php
        $conn->close();
        ?>
    </div>
</body>

</html>