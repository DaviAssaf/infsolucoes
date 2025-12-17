<?php
include_once '../verificacao_sessao.php';
include '../conn.php';

$id_registro = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id_registro) {
    die('ID do registro inválido.');
}

$query = "SELECT 
    s.id,
    s.id_mp,
    s.quantidade,
    s.custo_total,
    mp.nome,
    mp.medida,
    mp.custo,
    r.custo_final,
    r.tipo,
    r.data_hora,
    r.os
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

$registroInfo = $details[0] ?? null;

if (!$registroInfo) {
    die('Registro não encontrado.');
}

$tipo = $registroInfo['tipo'];
$data_hora = $registroInfo['data_hora'];
$os = $registroInfo['os'];


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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>

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

    window.detailsData = <?php echo json_encode($details); ?>;
    window.id_registro_estoque = <?php echo json_encode($id_registro); ?>;

    window.tipo_registro = <?php echo json_encode($tipo); ?>;
    window.horario_registro = <?php echo json_encode(date('d/m/Y H:i', strtotime($data_hora))); ?>;
    window.os_registro = <?php echo json_encode($os); ?>;


    function downloadExcelList(id_registro_estoque) {
        if (!window.detailsData || !Array.isArray(window.detailsData) || window.detailsData.length === 0) {
            alert('Nenhum dado disponível para exportar.');
            return;
        }

        const data = [
            ['Matéria Prima', 'Quantidade', 'Custo Unitário', 'Custo Total']
        ];

        window.detailsData.forEach(item => {
            data.push([
                item.nome || '',
                item.quantidade || '',
                item.custo ? Number(item.custo).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }) : '',
                item.custo_total ? Number(item.custo_total).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }) : ''
            ]);
        });

        const totalGasto = window.detailsData.reduce((sum, item) => sum + (parseFloat(item.custo_total) || 0), 0);
        const totalRow = ['', '', 'Total:', totalGasto.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })];
        data.push(totalRow);

        const worksheet = XLSX.utils.aoa_to_sheet(data);
        worksheet['!cols'] = [
            { wch: 50 }, { wch: 10 }, { wch: 15 }, { wch: 15 }
        ];

        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(
            workbook,
            worksheet,
            tipo_registro == 1 ? 'Entrada' : 'Saída'
        );

        XLSX.writeFile(workbook,
            `${tipo_registro == 1 ? 'entrada' : 'saida'}_registro#${id_registro_estoque}.xlsx`
        );
    }

    // -----------------------
    // 📌 NOVO: GERAR PDF
    // -----------------------
    function downloadPDFList(id_registro_estoque) {
    if (!window.detailsData || window.detailsData.length === 0) {
        alert('Nenhum dado disponível para exportar.');
        return;
    }

    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    let y = 10;

    // 🔹 TÍTULO
    doc.setFontSize(16);
    doc.text(
        `Registro de ${tipo_registro == 1 ? 'Entrada' : 'Saída'}`,
        105,
        y,
        { align: 'center' }
    );

    y += 10;

    // 🔹 CABEÇALHO (INFO DO REGISTRO)
    doc.setFontSize(11);

    doc.text(`Tipo: ${tipo_registro == 1 ? 'Entrada' : 'Saída'}`, 14, y);
    y += 6;

    doc.text(`Horário de Registro: ${window.horario_registro ?? '-'}`, 14, y);
    y += 6;

    doc.text(`OS: ${window.os_registro ?? '-'}`, 14, y);
    y += 8;

    // 🔹 LINHA SEPARADORA
    doc.line(14, y, 196, y);
    y += 5;

    // 🔹 TABELA
    const headers = ['Matéria Prima', 'Quantidade', 'Custo Unitário', 'Custo Total'];
    const body = [];
    let total = 0;

    window.detailsData.forEach(item => {
        const custo = parseFloat(item.custo_total) || 0;
        total += custo;

        body.push([
            item.nome || '',
            item.quantidade || '',
            item.custo ? `R$ ${Number(item.custo).toFixed(2)}` : '',
            item.custo_total ? `R$ ${Number(item.custo_total).toFixed(2)}` : ''
        ]);
    });

    body.push(['', '', 'TOTAL:', `R$ ${total.toFixed(2)}`]);

    doc.autoTable({
        head: [headers],
        body: body,
        startY: y,
        styles: { fontSize: 10 },
        headStyles: {
            fillColor: [22, 160, 133],
            textColor: 255
        }
    });

    doc.save(
        `${tipo_registro == 1 ? 'entrada' : 'saida'}_registro#${id_registro_estoque}.pdf`
    );
}


    document.addEventListener('DOMContentLoaded', function() {
        // Escolher se vai baixar Excel ou PDF
        const isPDF = new URLSearchParams(window.location.search).get("pdf") === "1";

        document.body.innerHTML +=
            '<div id="loader">Gerando arquivo... <span id="countdown">2</span>s</div>';

        if (isPDF) {
            downloadPDFList(window.id_registro_estoque);
        } else {
            downloadExcelList(window.id_registro_estoque);
        }

        let timeLeft = 2;
        const countdown = setInterval(() => {
            timeLeft--;
            document.getElementById('countdown').textContent = timeLeft;
            if (timeLeft <= 0) {
                clearInterval(countdown);
                window.location.href = '../registro_estoque';
            }
        }, 1000);
    });
</script>
</head>

<body>
</body>

</html>