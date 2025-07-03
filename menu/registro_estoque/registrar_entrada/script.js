const addMp = document.getElementById('addMp');
const materiasPrimas = document.getElementById('materias_primas');
const mpLista = document.getElementById('mp_lista');
const custoTotalGeralTfoot = document.getElementById('custo-total-geral');
const form = document.getElementById('saidaForm');

addMp.addEventListener('click', function () {
    const mpId = materiasPrimas.value;
    const mpNome = materiasPrimas.options[materiasPrimas.selectedIndex].text;

    // Cria a linha da tabela
    const row = document.createElement('tr');

    // You need to get medida and custo from a JS object or data attribute, not PHP directly
    const selectedOption = materiasPrimas.options[materiasPrimas.selectedIndex];
    const medida = selectedOption.getAttribute('data-medida') || '';
    const custo = selectedOption.getAttribute('data-custo') || '';
    const quantidadeEstoque = parseFloat(selectedOption.getAttribute('data-quantidade'));

    if (selectedOption.value === '') {
        alert('Por favor, selecione uma matéria-prima válida.');
        return;
    }

    row.innerHTML = `
        <td>
            <button type="button" class="delete-button btn-danger btn-sm remove-mp">Remover</button>
        </td>
        <td>
            <input type="hidden" name="mp_ids[]" value="${mpId}">
            ${mpNome}
        </td>
        <td>
            <div class="input-table">
            <input type="number" name="quantidades[]" min="0" value="0" required>
            <p>${medida}</p>
            </div>
        </td>
        <td>
            R$ ${(parseFloat(custo) || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
        </td>
        <td>
            <div class="input-table">
            <p>R$</p>
            <input type="number" name="custos_totais[]" min="0" step="0.01" value="0" readonly>
            </div>
        </td>
    `;

    // Seleciona os inputs da linha recém criada
    const quantidadeInput = row.querySelector('input[name="quantidades[]"]');
    const custoTotalInput = row.querySelector('input[name="custos_totais[]"]');

    function atualizarCustoTotal() {
        const quantidade = parseFloat(quantidadeInput.value) || 0;
        const custoUnitario = parseFloat(custo) || 0;
        const total = quantidade * custoUnitario;
        custoTotalInput.value = total.toFixed(2);
    }
    quantidadeInput.addEventListener('blur', function () {
        atualizarCustoTotal();
        atualizarCustoTotalGeral();
    });
    quantidadeInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === 'Escape' || e.key === 'Tab') {
            const quantidade = parseFloat(quantidadeInput.value) || 0;
            if (quantidade > quantidadeEstoque) {
                alert('Quantidade insuficiente em estoque.');
                quantidadeInput.value = quantidadeEstoque; // Reseta o valor para a quantidade em estoque
                atualizarCustoTotal();
            } else {
                atualizarCustoTotal();
                atualizarCustoTotalGeral();
                quantidadeInput.blur();
            }
        }
    });
    mpLista.appendChild(row);
    atualizarCustoTotalGeral();
});

// Remove a linha da matéria-prima
mpLista.addEventListener('click', function (e) {
    if (e.target.classList.contains('remove-mp')) {
        e.target.closest('tr').remove();
        atualizarCustoTotalGeral();
    }
});

// Função para atualizar o custo total geral
function atualizarCustoTotalGeral() {
    let total = 0;
    document.querySelectorAll('input[name="custos_totais[]"]').forEach(input => {
        total += parseFloat(input.value) || 0;
    });

    // Limpa o tfoot antes de adicionar a linha de total
    custoTotalGeralTfoot.innerHTML = '';
    const totalRow = document.createElement('tr');
    totalRow.id = 'total-row';
    totalRow.innerHTML = `
        <td colspan="4" style="text-align:right;font-weight:bold;">Custo Final</td>
        <td><input type="text" id="custo-total-geral-input" value="${total.toFixed(2)}" readonly style="font-weight:bold;"></td>
    `;
    custoTotalGeralTfoot.appendChild(totalRow);
}


form.addEventListener('submit', function (e) {
    const linhas = mpLista.querySelectorAll('tr');
    const dados = [];
    const ordemServico = document.getElementById('ordem_servico').value.trim();

    // Valida o campo ordem de serviço
    if (!ordemServico) {
        alert('Por favor, preencha a ordem de serviço.');
        e.preventDefault();
        return;
    }

    // Preenche o array dados com as informações das linhas
    linhas.forEach(linha => {
        const mpIdInput = linha.querySelector('input[name="mp_ids[]"]');
        const quantidadeInput = linha.querySelector('input[name="quantidades[]"]');
        const custoTotalInput = linha.querySelector('input[name="custos_totais[]"]');
        const mpId = mpIdInput ? mpIdInput.value : null;
        const quantidade = quantidadeInput ? parseFloat(quantidadeInput.value) : null;
        const custoTotal = custoTotalInput ? parseFloat(custoTotalInput.value) : null;

        if (mpId != null && quantidade != null && custoTotal != null && quantidade > 0) {
            dados.push({
                id_mp: mpId,
                quantidade: quantidade,
                custo_total: custoTotal
            });
        }
    });

    // Verifica se há dados válidos
    if (dados.length === 0) {
        alert('Por favor, adicione pelo menos uma matéria-prima com quantidade maior que zero.');
        e.preventDefault();
        return;
    }

    // Adiciona o custo final como um campo oculto no formulário
    const custoFinalInput = document.createElement('input');
    custoFinalInput.type = 'hidden';
    custoFinalInput.name = 'custo_final';
    custoFinalInput.value = document.getElementById('custo-total-geral-input')?.value || '0';
    form.appendChild(custoFinalInput);

    // Exibe o diálogo de confirmação
    if (!confirm('Você tem certeza que deseja registrar a saída?')) {
        e.preventDefault();
    }
});