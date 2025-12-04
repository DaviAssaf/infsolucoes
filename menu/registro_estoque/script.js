document.addEventListener("DOMContentLoaded", () => {
	const table = document.getElementById("historico-saidas");
	const detailsData = window.detailsData; // Acessa a variável global passada pelo PHP

	function downloadPDF() {
		if (!table) {
			alert("Table element not found.");
			return;
		}

		const { jsPDF } = window.jspdf;
		const doc = new jsPDF();
		let totalGasto = 0;

		// Pegar cabeçalho
		const headers = [];
		const headerCells = table.querySelectorAll("thead th:not(.actions)");
		headerCells.forEach((th) => headers.push(th.innerText.trim()));

		// Pegar linhas da tabela
		const body = [];
		const rows = table.querySelectorAll("tbody tr");

		rows.forEach((row) => {
			const rowData = [];
			const cols = row.querySelectorAll("td:not(.actions)");

			cols.forEach((col, idx) => {
				rowData.push(col.innerText.trim());

				// Pegar custo final
				if (col.dataset.totalGasto !== undefined || headers[idx]?.toLowerCase().includes("custo final")) {
					let valor = parseFloat(col.innerText.replace(/[^\d,.-]/g, "").replace(",", "."));
					if (!isNaN(valor)) totalGasto += valor;
				}
			});

			body.push(rowData);
		});

		// Adicionar linha de total no final
		if (body.length > 0) {
			const totalRow = Array(headers.length).fill("");
			const totalColIndex = headers.findIndex((h) => h.toLowerCase().includes("custo final"));
			if (totalColIndex !== -1) totalRow[totalColIndex] = "Total: R$ " + totalGasto.toFixed(2).replace(".", ",");

			body.push(totalRow);
		}

		// Título
		doc.setFontSize(16);
		doc.text("Histórico de Movimentação de Estoque", 105, 15, { align: "center" });

		// Gerar tabela automaticamente
		doc.autoTable({
			head: [headers],
			body: body,
			startY: 25,
			styles: {
				fontSize: 10,
				cellPadding: 2,
			},
			headStyles: {
				fillColor: [22, 160, 133],
				textColor: 255,
				halign: "center",
			},
			columnStyles: {
				0: { halign: "center", cellWidth: 10 }, // ID
				1: { halign: "center", cellWidth: 30 }, // Data/hora
				2: { halign: "center", cellWidth: 25 }, // Tipo
				3: { cellWidth: 25 }, // OS
				4: { halign: "right", cellWidth: 25 }, // Custo
				5: { cellWidth: 40 }, // Responsável
			},
		});

		doc.save("historico_saidas.pdf");
	}

	// Conectar botão
	const pdfBtn = document.getElementById("export-pdf");
	if (pdfBtn) {
		pdfBtn.addEventListener("click", downloadPDF);
	}

	function downloadExcel() {
		if (!table) {
			alert("Table element not found.");
			return;
		}
		const periodElem = document.getElementById("period-select");
		const period = periodElem ? periodElem.value : "default";

		// Extrair dados da tabela
		let data = [];
		let totalGasto = 0;
		const rows = table.getElementsByTagName("tr");
		let custoFinalColIndex = -1;

		for (let i = 0; i < rows.length; i++) {
			const row = [];
			const headers = rows[i].getElementsByTagName("th");
			const cols = rows[i].getElementsByTagName("td");

			if (headers.length > 0) {
				// Cabeçalho
				for (let j = 0; j < headers.length; j++) {
					if (!headers[j].classList.contains("actions")) {
						row.push(headers[j].innerText);
						if (headers[j].innerText.trim().toLowerCase().includes("custo final")) {
							custoFinalColIndex = row.length - 1;
						}
					}
				}
				data.push(row);
			} else if (cols.length > 0) {
				// Dados
				let colIdx = 0;
				for (let j = 0; j < cols.length; j++) {
					if (!cols[j].classList.contains("actions")) {
						row.push(cols[j].innerText);
						if (colIdx === custoFinalColIndex) {
							let valor = parseFloat(cols[j].innerText.replace(/[^\d,.-]/g, "").replace(",", "."));
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
			const totalRow = Array(data[0].length).fill("");
			totalRow[custoFinalColIndex] =
				"Total: " + totalGasto.toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
			data.push(totalRow);
		}

		// Criar workbook e worksheet
		const worksheet = XLSX.utils.aoa_to_sheet(data);
		worksheet["!cols"] = [
			{ wch: 5 }, // Coluna # (largura 5 caracteres)
			{ wch: 15 }, // Coluna Data/Hora (largura 15 caracteres)
			{ wch: 30 }, // Coluna OS (largura 30 caracteres)
			{ wch: 20 }, // Coluna Custo Final (largura 20 caracteres)
			{ wch: 15 }, // Coluna Responsável (largura 15 caracteres)
		];

		const workbook = XLSX.utils.book_new();
		XLSX.utils.book_append_sheet(workbook, worksheet, "SaidasEstoque");
		XLSX.writeFile(workbook, `historico_saidas_${period}.xlsx`);
	}

	// Adicionar evento ao botão "Imprimir Histórico"
	const exportBtn = document.getElementById("export-excel");
	if (exportBtn) {
		exportBtn.textContent = "Imprimir Histórico";
		exportBtn.removeAttribute("onclick");
		exportBtn.addEventListener("click", downloadExcel);
	}
});
