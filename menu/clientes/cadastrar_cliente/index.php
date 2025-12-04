<?php
require_once '../../verificacao_sessao.php';
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Clientes</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/x-icon" href="../../../images/icon.ico">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
</head>

<body>
    <div class="back-button">
        <a href="..">Voltar</a>
    </div>
    <div class="container-box">
        <form action="cadastrar_clientes.php" method="POST">
            <div class="section">
                <h2>Dados do Cliente</h2>
                <label for="nome">Nome da Empresa:</label>
                <input type="text" name="nome" required>

                <label for="cnpj">CNPJ:</label>
                <input type="text" id="cnpj" name="cnpj" required>

                <label for="contato">Contato:</label>
                <input type="text" name="contato">

                <label for="telefone">Telefone:</label>
                <input type="tel" id="telefone" name="telefone">

                <label for="email">Email:</label>
                <input type="email" name="email">
            </div>

            <div class="section">
                <h2>Endereço do Cliente</h2>
                <label for="cep">CEP:</label>
                <input type="text" id="cep" name="cep" required>

                <label for="endereco">Endereço:</label>
                <input type="text" id="endereco" name="endereco" required>

                <label for="numero_endereco">Número:</label>
                <input type="text" name="numero_endereco">


                <label for="complemento">Complemento:</label>
                <input type="text" name="complemento">

                <label for="bairro">Bairro:</label>
                <input type="text" id="bairro" name="bairro" required>

                <label for="cidade">Cidade:</label>
                <input type="text" id="cidade" name="cidade" required>

                <label for="UF">UF:</label>
                <select id="UF" name="estado" required>
                    <option value="">Selecione o estado</option>
                    <option value="AC">Acre</option>
                    <option value="AL">Alagoas</option>
                    <option value="AP">Amapá</option>
                    <option value="AM">Amazonas</option>
                    <option value="BA">Bahia</option>
                    <option value="CE">Ceará</option>
                    <option value="DF">Distrito Federal</option>
                    <option value="ES">Espírito Santo</option>
                    <option value="GO">Goiás</option>
                    <option value="MA">Maranhão</option>
                    <option value="MT">Mato Grosso</option>
                    <option value="MS">Mato Grosso do Sul</option>
                    <option value="MG">Minas Gerais</option>
                    <option value="PA">Pará</option>
                    <option value="PB">Paraíba</option>
                    <option value="PR">Paraná</option>
                    <option value="PE">Pernambuco</option>
                    <option value="PI">Piauí</option>
                    <option value="RJ">Rio de Janeiro</option>
                    <option value="RN">Rio Grande do Norte</option>
                    <option value="RS">Rio Grande do Sul</option>
                    <option value="RO">Rondônia</option>
                    <option value="RR">Roraima</option>
                    <option value="SC">Santa Catarina</option>
                    <option value="SP">São Paulo</option>
                    <option value="SE">Sergipe</option>
                    <option value="TO">Tocantins</option>
                    <option value="EX">Estrangeiro</option>
                </select>
            </div>

            <div class="section">
                <button type="submit">Cadastrar Cliente</button>
            </div>
        </form>
    </div>

    <script>
        $(document).ready(function() {
            $('#cnpj').mask('00.000.000/0000-00', {
                reverse: true
            });
            $('#telefone').mask('(00) 00000-0000');
            $('#cep').mask('00000-000');

            $('#cep').blur(function() {
                var cep = $(this).val().replace(/\D/g, '');
                if (cep != "") {
                    var validacep = /^[0-9]{8}$/;
                    if (validacep.test(cep)) {
                        $.getJSON("https://viacep.com.br/ws/" + cep + "/json/?callback=?", function(dados) {
                            if (!("erro" in dados)) {
                                $("#endereco").val(dados.logradouro);
                                $("#bairro").val(dados.bairro);
                                $("#cidade").val(dados.localidade);
                                $("#UF").val(dados.uf).change();
                            } else {
                                alert("CEP não encontrado.");
                            }
                        });
                    } else {
                        alert("Formato de CEP inválido.");
                    }
                }
            });
        });
    </script>
</body>

</html>