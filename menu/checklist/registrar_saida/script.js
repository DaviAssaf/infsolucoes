document.addEventListener('DOMContentLoaded', function () {
    const tipoSelect = document.getElementById('tipo');
    const ferramentaSelect = document.getElementById('ferramentas');
    const quantidadeInput = document.getElementById('quantidade');
    const addButton = document.getElementById('addItem');
    const caixaFerramentas = document.getElementById('caixa_ferramentas');
    const form = document.querySelector('form');
    const clienteSelect = document.getElementById('cliente');
    const localInput = document.getElementById('local');
    const cidadeInput = document.getElementById('cidade');
    const veiculoSelect = document.getElementById('veiculo');
    const kmInput = document.getElementById('km');
    const idClienteInput = document.getElementById('id_cliente'); // Campo hidden para id_cliente
    let itensAdicionados = []; // Armazena itens adicionados (ferramentas/maletas)
    let ferramentasDisponiveis = []; // Armazena a lista inicial de ferramentas para rastreamento

    // Preenchimento dinâmico das ferramentas com base no tipo
    tipoSelect.addEventListener('change', function () {
        const tipo = this.value;

        fetch('buscar_ferramentas.php?tipo=' + encodeURIComponent(tipo))
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erro na requisição: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                ferramentaSelect.innerHTML = '<option value="">Selecione</option>';
                if (data.ferramentas && Array.isArray(data.ferramentas)) {
                    data.ferramentas.forEach(item => {
                        ferramentaSelect.innerHTML += `<option value="${item.id}" data-tipo="${item.tipo}" data-medida="${item.medida || ''}">${item.nome}</option>`;
                    });
                    ferramentasDisponiveis = data.ferramentas; // Atualiza a lista de ferramentas disponíveis
                }
            })
            .catch(error => console.error('Erro ao buscar ferramentas:', error));
    });

    // Adicionar item à caixa de ferramentas com verificação de quantidade
    addButton.addEventListener('click', function () {
        const ferramenta = ferramentaSelect.value;
        const nomeFerramenta = ferramentaSelect.options[ferramentaSelect.selectedIndex].text;
        const tipoFerramentaSelecionado = ferramentaSelect.options[ferramentaSelect.selectedIndex].getAttribute('data-tipo');
        const medidaFerramenta = ferramentaSelect.options[ferramentaSelect.selectedIndex].getAttribute('data-medida') || '';
        let quantidade = tipoFerramentaSelecionado !== 'materia_prima' ? parseInt(quantidadeInput.value) : parseFloat(quantidadeInput.value);

        // Mapear o tipo selecionado para o tipo correto a ser enviado ao verificar_quantidade.php
        let tipoFerramenta;
        const tipoSelecionado = tipoSelect.value;
        if (tipoSelecionado === 'Ferramentas manuais' || tipoSelecionado === 'Ferramentas elétricas' || tipoSelecionado === 'Todas as Ferramentas') {
            tipoFerramenta = 'ferramenta';
        } else if (tipoSelecionado === 'Maletas') {
            tipoFerramenta = 'maleta';
            quantidade = 1; // Maletas têm quantidade fixa de 1
        } else {
            alert('Tipo inválido selecionado.');
            return;
        }

        if (tipoFerramenta !== 'materia_prima' && (!quantidade || quantidade <= 0)) {
            alert('Por favor, selecione uma quantidade válida (maior que 0).');
            return;
        }

        if (ferramenta) {
            // Verificar quantidade_atual via AJAX, incluindo o tipo mapeado
            fetch('verificar_quantidade.php?id=' + ferramenta + '&quantidade=' + quantidade + '&tipo=' + tipoFerramenta)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erro na verificação de quantidade: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        alert('Erro: ' + data.error);
                        return;
                    }
                    if (data.disponivel) {
                        itensAdicionados.push({ id: ferramenta, nome: nomeFerramenta, quantidade: quantidade, tipo: tipoFerramenta, medida: medidaFerramenta });
                        atualizarCaixaFerramentas();

                        // Remover o equipamento das opções disponíveis
                        const optionToRemove = Array.from(ferramentaSelect.options).find(option =>
                            option.value === ferramenta
                        );
                        if (optionToRemove) {
                            ferramentaSelect.removeChild(optionToRemove);
                        }

                        // Limpar seleções
                        ferramentaSelect.value = '';
                        quantidadeInput.value = '';
                    } else {
                        alert('Quantidade solicitada excede a quantidade atual disponível: ' + (data.quantidade_atual ?? 0));
                    }
                })
                .catch(error => {
                    console.error('Erro ao verificar quantidade:', error);
                    alert('Erro ao verificar quantidade. Verifique o console para mais detalhes.');
                });
        } else {
            alert('Selecione uma ferramenta.');
        }
    });

    // Função para atualizar a tabela de itens
    function atualizarCaixaFerramentas() {
        caixaFerramentas.innerHTML = `
            <table>
                <thead>
                    <tr>
                        <th>Ferramentas/Maletas</th>
                        <th>Quantidade</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody>
                    ${itensAdicionados.map((item, index) => `
                        <tr>
                            <td>${item.nome}</td>
                            <td>${item.quantidade} ${item.medida ? item.medida : 'Uni'}</td>
                            <td><button type="button" onclick="removerItem(${index})">Remover</button></td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        `;
    }

    // Função para remover item (restaura a opção se removido antes de enviar)
    window.removerItem = function (index) {
        const itemRemovido = itensAdicionados.splice(index, 1)[0];
        atualizarCaixaFerramentas();

        // Restaurar a opção removida na lista, se ainda disponível no banco
        const ferramentaExistente = ferramentasDisponiveis.find(f => f.id === itemRemovido.id);
        if (ferramentaExistente) {
            ferramentaSelect.innerHTML += `<option value="${ferramentaExistente.id}" data-tipo="${ferramentaExistente.tipo}" data-medida="${ferramentaExistente.medida || ''}">${ferramentaExistente.nome}</option>`;
        }
    };

    // Preencher os campos com base no cliente selecionado
    clienteSelect.addEventListener('change', function () {
        const selectedOption = this.options[this.selectedIndex];
        const clienteId = this.value;
        const customizavelDiv = document.getElementById('customizavel');

        // Aplicar máscara de telefone após inserir o campo, se jQuery e mask estiverem disponíveis
        setTimeout(function () {
            if (window.jQuery && $('#telefone_customizavel').length) {
                $('#telefone_customizavel').mask('(00) 00000-0000');
            }
        }, 0);

        // Atualizar o campo hidden com o id_cliente selecionado
        if (clienteId && clienteId !== 'servico_customizavel') {
            customizavelDiv.innerHTML = ``;
            idClienteInput.value = clienteId;
        } else {
            customizavelDiv.innerHTML = `
                <label for="cliente_customizavel">Cliente:</label>
                <input type="text" name="cliente_customizavel" id="cliente_customizavel">

                <label for="telefone_customizavel">Telefone:</label>
                <input type="text" name="telefone_customizavel" id="telefone_customizavel">
            `;
            idClienteInput.value = '';
        }

        // Preencher os campos "Local" e "Cidade" com os dados do endereço (se disponíveis)
        if (clienteId && clienteId !== 'servico_customizavel') {
            const endereco = selectedOption.getAttribute('data-endereco') || '';
            const numero = selectedOption.getAttribute('data-numero') || '';
            const bairro = selectedOption.getAttribute('data-bairro') || '';
            const cidade = selectedOption.getAttribute('data-cidade') || '';

            localInput.value = endereco && bairro ? `${endereco}, ${numero} - ${bairro}` : '';
            cidadeInput.value = cidade || '';
        } else {
            localInput.value = '';
            cidadeInput.value = '';
        }
    });

    // Auto-preencher o campo de KM de saída com base no veículo selecionado
    veiculoSelect.addEventListener('change', function () {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            const km = selectedOption.getAttribute('data-km');

            if (km) {
                kmInput.value = km;
            } else {
                kmInput.value = '';
                console.error('KM não encontrado para este veículo.');
            }
        } else {
            kmInput.value = '';
        }
    });

    // Serializar itensAdicionados e validar o formulário ao enviar
    form.addEventListener('submit', function (e) {
        // Validar campos obrigatórios
        const responsavel = document.getElementById('responsavel').value;
        const veiculo = document.getElementById('veiculo').value;
        const motorista = document.getElementById('motorista').value;
        const nomeNum = document.getElementById('nome_num').value;
        const saida = document.getElementById('saida').value;

        if (!responsavel || !nomeNum || !saida) {
            e.preventDefault();
            alert('Por favor, preencha todos os campos obrigatórios: Nome/Número do Serviço, Responsável, Veículo, Motorista e Data de Saída.');
            return;
        }

        if (!confirm('Deseja enviar o checklist?')) {
            e.preventDefault();
            return;
        }

        document.getElementById('itensAdicionados').value = JSON.stringify(itensAdicionados);
    });
});