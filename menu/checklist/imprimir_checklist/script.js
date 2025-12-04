const id_checklist = window.id_checklist;

function mostrarMiniTable() {
    document.querySelectorAll('.mini-table-content').forEach(miniTable => {
        miniTable.style.display = 'table';
    });
}

//Função para gerar e baixar o PDF
function downloadPDF(id_checklist) {
    const element = document.querySelector('.container-print');
    if (!element) {
        alert('Erro: Conteúdo do checklist não encontrado.');
        return;
    }

    // Configurações do html2pdf
    const opt = {
        margin: 0, // Aumentar a margem para evitar cortes nas bordas
        filename: `checklist_#${id_checklist}.pdf`,
        image: { type: 'jpg', quality: 0.98 },
        html2canvas: {
            scale: 1, // Aumentar a escala para maior resolução
            useCORS: true
        },
        jsPDF: {
            unit: 'mm',
            format: 'a4',
            orientation: 'portrait',
            putOnlyUsedFonts: true, // Otimiza o tamanho do PDF
            floatPrecision: 16 // Precisão para evitar artefatos
        },
        avoidPageBreak: true // Impede que elementos sejam divididos entre páginas
    };

    //     // Gera o PDF e inicia o download
    html2pdf().set(opt).from(element).save().then(() => {
        window.location.href = '..';
    }).catch(err => {
        console.error('Erro ao gerar PDF:', err);
        alert('Erro ao gerar o PDF. Tente novamente.');
    });
}

// Executa ao carregar a página
document.addEventListener('DOMContentLoaded', function () {
    mostrarMiniTable(window.id_checklist)
    if (typeof downloadPDF === 'function') {
        downloadPDF(window.id_checklist);
    } else {
        alert('Função downloadPDF não encontrada.');
    }
});