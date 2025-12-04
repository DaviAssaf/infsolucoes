document.addEventListener('DOMContentLoaded', function () {
    const tipoSelect = document.getElementById('tipo');
    const itemSelect = document.getElementById('itens');
    const addButton = document.getElementById('addItem');
    const itensLista = document.getElementById('itens_lista');
    const form = document.getElementById('checklistForm');
    const removedIdsInput = document.getElementById('removed_ids');
    let ferramentasDisponiveis = []; // Armazena ferramentas/maletas disponíveis
    let itensAdicionados = []; // Armazena IDs dos itens já na tabela

    document.querySelectorAll('.toggle-btn').forEach(button => {
        button.addEventListener('click', () => {
            const miniTable = button.closest('tr').nextElementSibling.querySelector('.mini-table-content');
            const icon = button.querySelector('i');
            const isHidden = miniTable.style.display === 'none' || miniTable.style.display === '';
            if (isHidden) {
                miniTable.style.display = 'table';
                icon.classList.remove('fa-angle-down');
                icon.classList.add('fa-angle-up');
            } else {
                miniTable.style.display = 'none';
                icon.classList.remove('fa-angle-up');
                icon.classList.add('fa-angle-down');
            }
        });
    });

    // Coletar itens já adicionados na tabela
    const itemInputs = itensLista.querySelectorAll('input[name="item_ids[]"]');
    itemInputs.forEach(input => {
        itensAdicionados.push(input.value);
    });

    // Toggle de sub-tabelas
    document.querySelectorAll('.toggle-btn').forEach(button => {
        button.addEventListener('click', () => {
            const row = button.closest('tr');
            const miniTable = row.nextElementSibling;
            const icon = button.querySelector('i');
            const isHidden = miniTable.style.display === 'none' || !miniTable.style.display;
            miniTable.style.display = isHidden ? 'table-row' : 'none';
            icon.classList.toggle('fa-angle-down', !isHidden);
            icon.classList.toggle('fa-angle-up', isHidden);
        });
    });

    // Preenchimento dinâmico de itens com base no tipo
    tipoSelect.addEventListener('change', function () {
        const tipo = this.value;
        if (!tipo) {
            itemSelect.innerHTML = '<option value="" selected>Selecione uma ferramenta ou maleta</option>';
            return;
        }

        const fetchUrl = tipo === 'Todas as Ferramentas'
            ? '../registrar_saida/buscar_ferramentas.php?tipo=' + encodeURIComponent('Todas as Ferramentas')
            : '../registrar_saida/buscar_ferramentas.php?tipo=' + encodeURIComponent(tipo);

        fetch(fetchUrl)
            .then(response => {
                if (!response.ok) throw new Error('Erro na requisição: ' + response.status);
                return response.json();
            })
            .then(data => {
                itemSelect.innerHTML = '<option value="" selected>Selecione uma ferramenta ou maleta</option>';
                if (data.ferramentas && Array.isArray(data.ferramentas)) {
                    ferramentasDisponiveis = data.ferramentas;
                    data.ferramentas.forEach(item => {
                        if (!itensAdicionados.includes(`${item.tipo}_${item.id}`)) {
                            itemSelect.innerHTML += `<option value="${item.tipo}_${item.id}" data-nome="${item.nome}" data-quantidade="${item.quantidade_atual || 1}">${item.nome}</option>`;
                        }
                    });
                }
            })
            .catch(error => {
                console.error('Erro ao buscar itens:', error);
                alert('Erro ao carregar itens. Verifique o console para mais detalhes.');
            });
    });

    // Adicionar item à tabela
    addButton.addEventListener('click', function () {
        const item = itemSelect.value;
        const nome = itemSelect.options[itemSelect.selectedIndex]?.dataset.nome;
        const maxQuantidade = parseFloat(itemSelect.options[itemSelect.selectedIndex]?.dataset.quantidade || 1);
        const tipo = item.startsWith('ferramenta_') ? 'ferramenta' : 'maleta';
        const id = item.replace(/^(ferramenta|maleta)_/, '');
        const quantidadeInput = document.createElement('input');
        quantidadeInput.type = 'number';
        quantidadeInput.min = '0';
        quantidadeInput.value = tipo === 'maleta' ? '1' : '1';
        quantidadeInput.required = true;
        quantidadeInput.dataset.max = maxQuantidade;
        quantidadeInput.name = 'quantidades[]'; // Vincular ao array de quantidades

        if (!item || !nome) {
            alert('Selecione um item válido.');
            return;
        }

        // Verificar quantidade disponível
        fetch(`../registrar_saida/verificar_quantidade.php?id=${id}&quantidade=${quantidadeInput.value}&tipo=${tipo}`)
            .then(response => {
                if (!response.ok) throw new Error('Erro na verificação: ' + response.status);
                return response.json();
            })
            .then(data => {
                if (data.error) {
                    alert('Erro: ' + data.error);
                    return;
                }
                if (data.disponivel) {
                    const row = document.createElement('tr');
                    row.dataset.id = `new_${item}`; // Identificador temporário
                    row.innerHTML = `
                    <td>
                        <button type="button" class="delete-button btn-danger btn-sm remove-item" aria-label="Remover item">Remover</button>
                        ${tipo === 'maleta' ? '<button type="button" class="toggle-btn" aria-label="Expandir maleta"><i class="fas fa-angle-down"></i></button>' : ''}
                    </td>
                    <td>
                        <input type="hidden" name="item_ids[]" value="${item}">${nome} <!-- Vincular ao array de item_ids -->
                    </td>
                    <td>
                        <div class="input-table">
                            ${quantidadeInput.outerHTML} <!-- Inserir o input de quantidade -->
                        </div>
                    </td>`;

                    itensLista.querySelector('tbody').appendChild(row);

                    // Se for maleta, buscar e adicionar as ferramentas associadas
                    if (tipo === 'maleta') {
                        fetch(`tools_case.php?id_maleta=${id}&id_checklist=${window.id_checklist || ''}`)
                            .then(response => {
                                if (!response.ok) throw new Error('Erro ao buscar ferramentas da maleta');
                                return response.json();
                            })
                            .then(ferramentas => {
                                const miniRow = document.createElement('tr');
                                miniRow.classList.add('mini-table');
                                miniRow.style.display = 'none';
                                let miniTableRows = ferramentas.length > 0 ? ferramentas.map(ferramenta => `
                                <tr>
                                    <td>${ferramenta.nome_ferramenta}</td>
                                    <td>${ferramenta.quantidade_levada}</td>
                                </tr>
                            `).join('') : '<tr><td colspan="2" style="text-align: center;">Nenhuma ferramenta associada</td></tr>';
                                miniRow.innerHTML = `
                                <td colspan="3">
                                    <table class="mini-table-content" style="display: none;">
                                        <thead>
                                            <tr>
                                                <th>Nome</th>
                                                <th>Saída</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${miniTableRows}
                                        </tbody>
                                    </table>
                                </td>
                            `;
                                row.after(miniRow);

                                // Adicionar evento de toggle
                                const toggleBtn = row.querySelector('.toggle-btn');
                                toggleBtn.addEventListener('click', () => {
                                    const miniTableContent = miniRow.querySelector('.mini-table-content');
                                    const icon = toggleBtn.querySelector('i');
                                    const isHidden = miniTableContent.style.display === 'none' || miniTableContent.style.display === '';
                                    miniRow.style.display = isHidden ? 'table-row' : 'none';
                                    miniTableContent.style.display = isHidden ? 'table' : 'none';
                                    icon.classList.toggle('fa-angle-down', !isHidden);
                                    icon.classList.toggle('fa-angle-up', isHidden);
                                });
                            })
                            .catch(error => {
                                console.error('Erro ao buscar ferramentas da maleta:', error);
                                alert('Erro ao carregar ferramentas da maleta. Verifique o console.');
                            });
                    }

                    itensAdicionados.push(item); // Adicionar à lista de itens adicionados
                    itemSelect.value = ''; // Limpar seleção
                    itemSelect.querySelector(`option[value="${item}"]`)?.remove(); // Remover da lista
                } else {
                    alert(`Quantidade solicitada excede o disponível: ${data.quantidade_atual ?? 0}`);
                }
            })
            .catch(error => {
                console.error('Erro ao verificar quantidade:', error);
                alert('Erro ao verificar quantidade. Verifique o console.');
            });
    });

    // Remover item
    itensLista.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-item')) {
            const row = e.target.closest('tr');
            const id = row.dataset.id;
            if (id && !id.startsWith('new_')) {
                const removedIds = removedIdsInput.value.split(',').filter(Boolean);
                removedIds.push(id);
                removedIdsInput.value = removedIds.join(',');
            }
            const itemId = row.querySelector('input[name="item_ids[]"]').value;
            const nome = row.querySelector('td:nth-child(2)').textContent.trim();
            const ferramentaExistente = ferramentasDisponiveis.find(f => `${f.tipo}_${f.id}` === itemId);
            if (ferramentaExistente) {
                itemSelect.innerHTML += `<option value="${itemId}" data-nome="${ferramentaExistente.nome}" data-quantidade="${ferramentaExistente.quantidade_atual || 1}">${ferramentaExistente.nome}</option>`;
            }
            itensAdicionados = itensAdicionados.filter(item => item !== itemId); // Remover da lista de itens adicionados
            row.nextElementSibling?.remove(); // Remover sub-tabela, se houver
            row.remove();
        }
    });

    // Validar quantidades
    itensLista.addEventListener('input', function (e) {
        if (e.target.name === 'quantidades[]' || e.target.name === 'subitem_quantidades[]') {
            const max = parseFloat(e.target.dataset.max);
            let val = parseFloat(e.target.value);
            if (val < 0) {
                e.target.value = 0;
                alert('Quantidade não pode ser negativa.');
            } else if (val > max) {
                e.target.value = max;
                alert('Quantidade excede o estoque disponível!');
            }
        }
    });

    // Validar formulário
    form.addEventListener('submit', function (e) {
        const rows = itensLista.querySelectorAll('tbody tr:not(.mini-table)');
        if (rows.length === 0) {
            e.preventDefault();
            alert('Adicione pelo menos um item ao checklist.');
            return;
        }
        if (!confirm('Deseja salvar as alterações no checklist?')) {
            e.preventDefault();
        }
    });
});