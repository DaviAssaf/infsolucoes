document.addEventListener('DOMContentLoaded', () => {
    const addMp = document.getElementById('addMp');
    const materiasPrimas = document.getElementById('materias_primas');
    const mpLista = document.getElementById('mp_lista');
    const custoTotalGeralTfoot = document.getElementById('custo-total-geral');
    const form = document.getElementById('saidaForm');
    const removedIdsInput = document.getElementById('removed_ids');

    // Função para atualizar o custo total de uma linha
    function atualizarCustoTotal(quantidadeInput, custoTotalInput, custoUnitario) {
        const quantidade = parseFloat(quantidadeInput.value) || 0;
        const total = quantidade * custoUnitario;
        custoTotalInput.value = total.toFixed(2);
    }

    // Função para atualizar o custo total geral
    function atualizarCustoTotalGeral() {
        // Atualizar todos os custos_totais antes de somar
        document.querySelectorAll('#mp_lista tbody tr').forEach(row => {
            const quantidadeInput = row.querySelector('input[name="quantidades[]"]');
            const custoTotalInput = row.querySelector('input[name="custos_totais[]"]');
            const custoUnitario = parseFloat(row.querySelector('td:nth-child(4)').textContent.replace('R$ ', '').replace(',', '.')) || 0;
            atualizarCustoTotal(quantidadeInput, custoTotalInput, custoUnitario);
        });

        let total = 0;
        document.querySelectorAll('input[name="custos_totais[]"]').forEach(input => {
            total += parseFloat(input.value) || 0;
        });

        custoTotalGeralTfoot.innerHTML = '';
        const totalRow = document.createElement('tr');
        totalRow.innerHTML = `
            <td colspan="4" style="text-align: right; font-weight: bold;">Custo Final:</td>
            <td><input type="text" id="custo-total-geral-input" value="${total.toFixed(2)}" readonly style="font-weight: bold;"></td>
        `;
        custoTotalGeralTfoot.appendChild(totalRow);
    }

    // Inicializar linhas existentes com eventos
    document.querySelectorAll('#mp_lista tbody tr').forEach(row => {
        const quantidadeInput = row.querySelector('input[name="quantidades[]"]');
        const custoTotalInput = row.querySelector('input[name="custos_totais[]"]');
        const custoUnitario = parseFloat(row.querySelector('td:nth-child(4)').textContent.replace('R$ ', '').replace(',', '.')) || 0;

        quantidadeInput.addEventListener('blur', function () {
            atualizarCustoTotal(quantidadeInput, custoTotalInput, custoUnitario);
            atualizarCustoTotalGeral();
        });

        quantidadeInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === 'Escape' || e.key === 'Tab') {
                const quantidade = parseFloat(quantidadeInput.value) || 0;
                const quantidadeEstoque = parseFloat(row.querySelector('input[name="quantidades[]"]').closest('td').querySelector('p').getAttribute('data-quantidade')) || 0;
                if (quantidade > quantidadeEstoque) {
                    alert('Quantidade insuficiente em estoque.');
                    quantidadeInput.value = quantidadeEstoque;
                    atualizarCustoTotal(quantidadeInput, custoTotalInput, custoUnitario);
                } else {
                    atualizarCustoTotal(quantidadeInput, custoTotalInput, custoUnitario);
                    atualizarCustoTotalGeral();
                    quantidadeInput.blur();
                }
            }
        });
    });

    // Adicionar nova matéria-prima
    addMp.addEventListener('click', function () {
        const mpId = materiasPrimas.value;
        const selectedOption = materiasPrimas.options[materiasPrimas.selectedIndex];
        if (!mpId) {
            alert('Por favor, selecione uma matéria-prima válida.');
            return;
        }

        const medida = selectedOption.getAttribute('data-medida') || '';
        const custo = parseFloat(selectedOption.getAttribute('data-custo')) || 0;
        const quantidadeEstoque = parseFloat(selectedOption.getAttribute('data-quantidade')) || 0;

        const row = document.createElement('tr');
        row.innerHTML = `
            <td><button type="button" class="delete-button btn-danger btn-sm remove-mp">Remover</button></td>
            <td><input type="hidden" name="mp_ids[]" value="${mpId}">${selectedOption.text}</td>
            <td><div class="input-table"><input type="number" name="quantidades[]" min="0" value="0" required><p>${medida}</p></div></td>
            <td>R$ ${(custo).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
            <td><div class="input-table"><p>R$</p><input type="number" name="custos_totais[]" min="0" step="0.01" value="0" readonly></div></td>
        `;

        const quantidadeInput = row.querySelector('input[name="quantidades[]"]');
        const custoTotalInput = row.querySelector('input[name="custos_totais[]"]');

        quantidadeInput.addEventListener('blur', function () {
            atualizarCustoTotal(quantidadeInput, custoTotalInput, custo);
            atualizarCustoTotalGeral();
        });

        quantidadeInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === 'Escape' || e.key === 'Tab') {
                const quantidade = parseFloat(quantidadeInput.value) || 0;
                if (quantidade > quantidadeEstoque) {
                    alert('Quantidade insuficiente em estoque.');
                    quantidadeInput.value = quantidadeEstoque;
                    atualizarCustoTotal(quantidadeInput, custoTotalInput, custo);
                } else {
                    atualizarCustoTotal(quantidadeInput, custoTotalInput, custo);
                    atualizarCustoTotalGeral();
                    quantidadeInput.blur();
                }
            }
        });

        mpLista.querySelector('tbody').appendChild(row);
        atualizarCustoTotalGeral();
    });

    // Remover matéria-prima
    mpLista.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-mp')) {
            const row = e.target.closest('tr');
            const id = row.getAttribute('data-id');
            if (id) {
                let removedIds = removedIdsInput.value.split(',').filter(Boolean);
                removedIds.push(id);
                removedIdsInput.value = removedIds.join(',');
            }
            row.remove();
            atualizarCustoTotalGeral();
        }
    });

    // Submeter o formulário
    form.addEventListener('submit', function (e) {
        // Forçar atualização de todos os custos antes de submeter
        atualizarCustoTotalGeral();

        const linhas = mpLista.querySelectorAll('tbody tr');
        const dados = [];
        const ordemServico = document.getElementById('ordem_servico')?.value.trim() || null;

        if (ordemServico === null && document.getElementById('ordem_servico')) {
            alert('Por favor, preencha a ordem de serviço.');
            e.preventDefault();
            return;
        }

        linhas.forEach(linha => {
            const mpIdInput = linha.querySelector('input[name="mp_ids[]"]');
            const quantidadeInput = linha.querySelector('input[name="quantidades[]"]');
            const custoTotalInput = linha.querySelector('input[name="custos_totais[]"]');
            const mpId = mpIdInput ? mpIdInput.value : null;
            const quantidade = quantidadeInput ? parseFloat(quantidadeInput.value) : null;
            const custoTotal = custoTotalInput ? parseFloat(custoTotalInput.value) : null;

            if (mpId && quantidade !== null && custoTotal !== null && quantidade > 0) {
                dados.push({
                    id_mp: mpId,
                    quantidade: quantidade,
                    custo_total: custoTotal
                });
            }
        });

        if (dados.length === 0) {
            alert('Por favor, adicione pelo menos uma matéria-prima com quantidade maior que zero.');
            e.preventDefault();
            return;
        }

        const custoFinalInput = document.createElement('input');
        custoFinalInput.type = 'hidden';
        custoFinalInput.name = 'custo_final';
        custoFinalInput.value = document.getElementById('custo-total-geral-input')?.value || '0';
        form.appendChild(custoFinalInput);

        if (!confirm('Você tem certeza que deseja salvar as alterações?')) {
            e.preventDefault();
        }
    });

    // Inicializar custo total geral
    atualizarCustoTotalGeral();
});