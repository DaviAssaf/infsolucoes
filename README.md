# InfSoluções

Sistema web em PHP para gestão de estoque, checklists, cadastros e histórico operacional da Infinity Soluções.

## Visão geral

O projeto centraliza rotinas usadas no dia a dia da operação, como:

- cadastro de funcionários, clientes e veículos
- controle de ferramentas, maletas e matérias-primas
- registro de saídas e retornos
- acompanhamento de ocorrências
- histórico de alterações para rastreabilidade
- manual de uso interno dentro do próprio sistema

O acesso começa pela tela de login e, após autenticação, o usuário é redirecionado para o menu principal.
O ponto de entrada recomendado é a pasta `login/`; o `index.html` da raiz apenas redireciona para `menu/`.

## Principais módulos

- `Login`: autenticação por e-mail e senha.
- `Funcionários`: cadastro, edição e exclusão de usuários do sistema.
- `Veículos`: gestão de veículos e informações associadas.
- `Clientes`: cadastro e manutenção de clientes e endereços.
- `Ferramentas`: controle de ferramentas, quantidades e situação.
- `Matérias-primas`: cadastro, estoque mínimo e alertas de reposição.
- `Maletas`: organização de conjuntos de ferramentas.
- `Checklists`: registro de saída, retorno, impressão e ocorrências.
- `Registro de estoque`: entradas, saídas, filtros e impressão.
- `Histórico`: auditoria das ações executadas no sistema.
- `Ajuda`: manual de uso integrado.

## Requisitos

- PHP 8.0 ou superior
- MySQL ou MariaDB
- Servidor web com Apache, como WAMP, XAMPP ou ambiente equivalente

## Instalação local

1. Copie o projeto para a pasta pública do seu servidor web.
2. Crie um banco de dados chamado `infsolucoes`.
3. Importe o arquivo `infsolucoes-struct.sql` no banco criado.
4. Ajuste a conexão com o banco em `menu/conn.php` se o seu ambiente usar usuário, senha ou host diferentes.
5. Verifique o caminho base definido em `config.php`. O projeto está configurado para o subdiretório `/infsolucoes`.
6. Acesse o sistema pelo navegador e faça login.

## Estrutura principal

- `index.html`: redireciona para o menu principal.
- `login/`: tela e processamento de autenticação.
- `menu/`: área principal do sistema e seus módulos.
- `images/`: logotipos e ícones.
- `infsolucoes-struct.sql`: estrutura completa do banco de dados.
- `style.css`: estilos globais.

## Banco de dados

O schema inclui tabelas para:

- `funcionarios`
- `cliente` e `os_endereco`
- `veiculos` e `veiculo_condicao`
- `ferramentas` e `ferramenta_maleta`
- `maletas`
- `materia_prima`
- `checklist` e `caixa_ferramentas`
- `registro_estoque` e `saida_estoque`
- `ocorrencias`
- `historico`

## Acesso e permissões

- Usuários com perfil administrativo têm acesso à área de cadastros e às exclusões.
- O menu lateral se adapta ao tipo de usuário autenticado.
- Alertas de estoque baixo aparecem quando a quantidade de matérias-primas fica abaixo do mínimo configurado.

## Manual de uso

O sistema possui um manual interno em `menu/ajuda`, com instruções detalhadas para cada módulo.

## Observações

- O projeto foi pensado para execução local em ambiente PHP + MySQL.
- Se você mover o sistema para outro subdiretório, revise os caminhos absolutos definidos no projeto.
- Recomenda-se manter o banco salvo e versionado separadamente do código para facilitar restauração e testes.

## Suporte

Contato e referências do projeto estão disponíveis no manual interno do sistema.
