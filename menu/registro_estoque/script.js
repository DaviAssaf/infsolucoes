document.addEventListener('DOMContentLoaded', () => {
    const table = document.getElementById('historico-saidas');
    const detailsData = window.detailsData; // Acessa a variável global passada pelo PHP

    function downloadExcel() {
        if (!table) {
            alert('Table element not found.');
            return;
        }
        const periodElem = document.getElementById('period-select');
        const period = periodElem ? periodElem.value : 'default';

        // Extrair dados da tabela
        let data = [];
        let totalGasto = 0;
        const rows = table.getElementsByTagName('tr');
        let custoFinalColIndex = -1;

        for (let i = 0; i < rows.length; i++) {
            const row = [];
            const headers = rows[i].getElementsByTagName('th');
            const cols = rows[i].getElementsByTagName('td');

            if (headers.length > 0) {
                // Cabeçalho
                for (let j = 0; j < headers.length; j++) {
                    if (!headers[j].classList.contains('actions')) {
                        row.push(headers[j].innerText);
                        if (headers[j].innerText.trim().toLowerCase().includes('custo final')) {
                            custoFinalColIndex = row.length - 1;
                        }
                    }
                }
                data.push(row);
            } else if (cols.length > 0) {
                // Dados
                let colIdx = 0;
                for (let j = 0; j < cols.length; j++) {
                    if (!cols[j].classList.contains('actions')) {
                        row.push(cols[j].innerText);
                        if (colIdx === custoFinalColIndex) {
                            let valor = parseFloat(cols[j].innerText.replace(/[^\d,.-]/g, '').replace(',', '.'));
                            if (!isNaN(valor)) totalGasto += valor;
                        }
                        colIdx++;
                    }
                }
                data.push(row);
            }
        }

        // Adicionar linha de total gasto ao final
        if (custoFinalColIndex !== -1 && data.length > 1) {
            const totalRow = Array(data[0].length).fill('');
            totalRow[custoFinalColIndex] = 'Total: ' + totalGasto.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
            data.push(totalRow);
        }

        // Criar workbook e worksheet
        const worksheet = XLSX.utils.aoa_to_sheet(data);
        worksheet['!cols'] = [
            { wch: 5 },   // Coluna # (largura 5 caracteres)
            { wch: 15 },  // Coluna Data/Hora (largura 15 caracteres)
            { wch: 30 },  // Coluna OS (largura 30 caracteres)
            { wch: 20 },  // Coluna Custo Final (largura 20 caracteres)
            { wch: 15 }   // Coluna Responsável (largura 15 caracteres)
        ];

        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, worksheet, 'SaidasEstoque');
        XLSX.writeFile(workbook, `historico_saidas_${period}.xlsx`);
    }

    // Adicionar evento ao botão "Imprimir Histórico"
    const exportBtn = document.getElementById('export-excel');
    if (exportBtn) {
        exportBtn.textContent = 'Imprimir Histórico';
        exportBtn.removeAttribute('onclick');
        exportBtn.addEventListener('click', downloadExcel);
    }
});