<?php
include_once '../verificacao_sessao.php';
include '../conn.php';

$id_registro = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id_registro) {
    die('ID do registro inválido.');
}

$query = "SELECT s.id, s.id_mp, s.quantidade, s.custo_total, mp.nome, mp.medida, mp.custo, r.custo_final, r.tipo
          FROM saida_estoque s
          LEFT JOIN materia_prima mp ON s.id_mp = mp.id_mp
          LEFT JOIN registro_estoque r ON s.id_registro_estoque = r.id
          WHERE s.id_registro_estoque = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_registro);
$stmt->execute();
$result = $stmt->get_result();

$details = [];
while ($row = $result->fetch_assoc()) {
    $details[] = $row;
}

$tipo = $details[0]['tipo'] ?? null;
if ($tipo === null) {
    die('Tipo de registro não encontrado.');
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Imprimindo Lista...</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <style>
        #loader {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 20px;
            border-radius: 5px;
            z-index: 1000;
        }
    </style>
    <script>
        window.detailsData = <?php echo json_encode($details); ?>;
        window.id_registro_estoque = <?php echo json_encode($id_registro); ?>;
        window.tipo_registro = <?php echo json_encode($tipo); ?>;

        function downloadList(id_registro_estoque) {
            if (!window.detailsData || !Array.isArray(window.detailsData) || window.detailsData.length === 0) {
                alert('Nenhum dado disponível para exportar.');
                return;
            }
            if (typeof tipo_registro !== 'number' || (tipo_registro !== 0 && tipo_registro !== 1)) {
                alert('Tipo de registro inválido.');
                return;
            }

            const data = [
                ['Matéria Prima', 'Quantidade', 'Custo Unitário', 'Custo Total']
            ];

            window.detailsData.forEach(item => {
                data.push([
                    item.nome || '',
                    item.quantidade || '',
                    item.custo ? Number(item.custo).toLocaleString('pt-BR', {
                        style: 'currency',
                        currency: 'BRL'
                    }) : '',
                    item.custo_total ? Number(item.custo_total).toLocaleString('pt-BR', {
                        style: 'currency',
                        currency: 'BRL'
                    }) : ''
                ]);
            });

            const totalGasto = window.detailsData.reduce((sum, item) => sum + (parseFloat(item.custo_total) || 0), 0);
            const totalRow = ['', '', 'Total:', totalGasto.toLocaleString('pt-BR', {
                style: 'currency',
                currency: 'BRL'
            })];
            data.push(totalRow);

            const worksheet = XLSX.utils.aoa_to_sheet(data);
            worksheet['!cols'] = [{
                wch: 50
            }, {
                wch: 10
            }, {
                wch: 15
            }, {
                wch: 15
            }];

            const workbook = XLSX.utils.book_new();
            if (tipo_registro == 1) {
                XLSX.utils.book_append_sheet(workbook, worksheet, 'Lista de Entrada');
                XLSX.writeFile(workbook, `entrada_registro#${id_registro_estoque}.xlsx`);
            } else {
                XLSX.utils.book_append_sheet(workbook, worksheet, 'Lista de Saídas');
                XLSX.writeFile(workbook, `saida_registro#${id_registro_estoque}.xlsx`);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            if (typeof downloadList === 'function') {
                document.body.innerHTML += '<div id="loader">Gerando arquivo... <span id="countdown">2</span>s</div>';
                downloadList(window.id_registro_estoque);
                let timeLeft = 2;
                const countdown = setInterval(() => {
                    timeLeft--;
                    document.getElementById('countdown').textContent = timeLeft;
                    if (timeLeft <= 0) {
                        clearInterval(countdown);
                        window.location.href = '../registro_estoque';
                    }
                }, 1000);
            } else {
                alert('Função downloadList não encontrada.');
            }
        });
    </script>
</head>

<body>
</body>

</html>