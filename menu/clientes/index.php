<?php
require_once '../verificacao_sessao.php';
include '../conn.php';

$query = "SELECT c.id_cliente, c.cnpj, c.nome_empresa, c.contato, c.telefone, c.email, 
                 os.cep, os.endereco, os.numero, os.complemento, os.bairro, os.cidade, os.estado 
          FROM cliente c 
          LEFT JOIN os_endereco os ON c.id_cliente = os.id_cliente 
          ORDER BY c.nome_empresa ASC";

$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Visualizar Clientes</title>
    <link rel="icon" type="image/x-icon" href="../../images/icon.ico">
    <link rel="stylesheet" href="style.css">
    <script src="../confirmDelete.js"></script>
    <script>
        function mostrarFormularioEdicao(botao) {
            var dados = botao.getAttribute('data-cliente');
            var cliente = JSON.parse(dados);

            document.getElementById('id_cliente_editar').value = cliente.id_cliente;
            document.getElementById('cnpj_editar').value = cliente.cnpj || '';
            document.getElementById('nome_empresa_editar').value = cliente.nome_empresa || '';
            document.getElementById('contato_editar').value = cliente.contato || '';
            document.getElementById('telefone_editar').value = cliente.telefone || '';
            document.getElementById('email_editar').value = cliente.email || '';
            document.getElementById('cep_editar').value = cliente.cep || '';
            document.getElementById('endereco_editar').value = cliente.endereco || '';
            document.getElementById('numero_editar').value = cliente.numero || '';
            document.getElementById('complemento_editar').value = cliente.complemento || '';
            document.getElementById('bairro_editar').value = cliente.bairro || '';
            document.getElementById('cidade_editar').value = cliente.cidade || '';
            document.getElementById('estado_editar').value = cliente.estado || '';

            document.getElementById('modalEdicao').style.display = 'flex';
        }

        function ocultarFormularioEdicao() {
            document.getElementById('modalEdicao').style.display = 'none';
        }

        window.onclick = function(event) {
            var modal = document.getElementById('modalEdicao');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</head>

<body>

    <div class="back-button">
        <a href=".." class="back-btn">Voltar</a>
    </div>

    <h1>Visualizar Clientes</h1>

    <div class="cadastro-button">
        <a href="cadastrar_cliente" class="new-client-btn">
            <button>Cadastrar Novo Cliente</button>
        </a>
    </div>

    <table border="1" class="table-style">
        <thead>
            <tr>
                <th>Nome do Cliente</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['nome_empresa']) ?></td>
                        <td>
                            <div style="display:inline-block; margin-right:10px;">
                                <form action="deletar_cliente.php" method="POST">
                                    <input type="hidden" name="id_cliente" value="<?= htmlspecialchars($row['id_cliente']) ?>" />
                                    <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 1): ?>
                                        <button type="submit" class="delete-button" onclick="confirmDelete(event)">Excluir</button>
                                    <?php endif; ?>
                                </form>
                            </div>
                            <button
                                class="edit-btn"
                                data-cliente='<?= json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT) ?>'
                                onclick="mostrarFormularioEdicao(this)">
                                Editar
                            </button>

                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="2">Nenhum cliente cadastrado.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Modal de edição -->
    <div id="modalEdicao" class="modal" style="display:none;">
        <div class="modal-conteudo">
            <h2>Editar Cliente</h2>
            <form id="formEditar" action="editar_cliente.php" method="POST">
                <input type="hidden" id="id_cliente_editar" name="id_cliente" />
                <label for="cnpj_editar">CNPJ:</label>
                <input type="text" id="cnpj_editar" name="cnpj" required /><br />

                <label for="nome_empresa_editar">Nome da Empresa:</label>
                <input type="text" id="nome_empresa_editar" name="nome_empresa" required /><br />

                <label for="contato_editar">Contato:</label>
                <input type="text" id="contato_editar" name="contato" required /><br />

                <label for="telefone_editar">Telefone:</label>
                <input type="tel" id="telefone_editar" name="telefone" required /><br />

                <label for="email_editar">Email:</label>
                <input type="email" id="email_editar" name="email" /><br />

                <h3>Endereço</h3>

                <label for="cep_editar">CEP:</label>
                <input type="text" id="cep_editar" name="cep" required /><br />

                <label for="endereco_editar">Endereço:</label>
                <input type="text" id="endereco_editar" name="endereco" required /><br />

                <label for="numero_editar">Número:</label>
                <input type="number" id="numero_editar" name="numero" /><br />

                <label for="complemento_editar">Complemento:</label>
                <input type="text" id="complemento_editar" name="complemento" /><br />

                <label for="bairro_editar">Bairro:</label>
                <input type="text" id="bairro_editar" name="bairro" required /><br />

                <label for="cidade_editar">Cidade:</label>
                <input type="text" id="cidade_editar" name="cidade" required /><br />

                <label for="estado_editar">Estado:</label>
                <input type="text" id="estado_editar" name="estado" required /><br />

                <button type="submit">Salvar Alterações</button>
                <button type="button" onclick="ocultarFormularioEdicao()">Cancelar</button>
            </form>
        </div>
    </div>

</body>

</html>

<?php
$conn->close();
?>