<?php
require_once '../../verificacao_sessao.php';
include '../../conn.php';

// Buscar todas as ocorrências com dados relacionados
$query_ocorrencias = "SELECT 
    o.num_ocorrencia, o.nome_num, o.id_ferramenta, o.id_maleta, 
    o.data_ocorrencia, o.situacao,
    f.nome AS nome_ferramenta, m.nome AS nome_maleta
FROM ocorrencias o 
LEFT JOIN ferramentas f ON o.id_ferramenta = f.id_ferramenta 
LEFT JOIN maletas m ON o.id_maleta = m.id_maleta 
ORDER BY o.num_ocorrencia DESC";
$result_ocorrencias = $conn->query($query_ocorrencias);

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualizar Ocorrências</title>
    <link rel="icon" type="image/x-icon" href="../../images/icon.ico">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="back-button">
        <a href="..">Voltar</a>
    </div>

    <div class="container">
        <h1>Lista de Ocorrências</h1>
        <table class="table-style">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nome/Numero de Serviço</th>
                    <th>Ferramenta/Maleta</th>
                    <th>Data da Ocorrência</th>
                    <th>Situação</th>
                    <th>Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($ocorrencia = $result_ocorrencias->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($ocorrencia['num_ocorrencia']); ?></td>
                        <td><?php echo htmlspecialchars($ocorrencia['nome_num'] ?: 'Desconhecido'); ?></td>
                        <td><?php echo htmlspecialchars($ocorrencia['nome_ferramenta'] ?: $ocorrencia['nome_maleta'] ?: 'Não informado'); ?></td>
                        <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($ocorrencia['data_ocorrencia']))); ?></td>
                        <td><?php echo htmlspecialchars($ocorrencia['situacao']); ?></td>
                        <td>
                            <a href="editar_ocorrencia?id_ocorrencia=<?php echo htmlspecialchars($ocorrencia['num_ocorrencia']); ?>&id_ferramenta=<?php echo $ocorrencia['id_ferramenta'] ?: ''; ?>&id_maleta=<?php echo $ocorrencia['id_maleta'] ?: ''; ?>">Editar</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>

</html>