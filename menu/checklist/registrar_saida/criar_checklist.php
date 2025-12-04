<?php
require_once '../../verificacao_sessao.php';
include '../../conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Dados recebidos do formulário
    $id_cliente = isset($_POST['id_cliente']) && !empty($_POST['id_cliente']) ? (int)$_POST['id_cliente'] : null;
    $criador = $_SESSION['user_id'];
    $nome_num = mb_strtoupper($_POST['nome_num'] ?? '');
    $responsavel = (int)$_POST['responsavel'] ?? 0;
    $motorista = (int)$_POST['motorista'] ?? 0;
    $veiculo = (int)$_POST['veiculo'] ?? null;
    $acompanhantes = $_POST['acompanhantes'] ?? null;
    $destino = $_POST['local'] ?? null;
    $cidade = $_POST['cidade'] ?? null;
    $saida = $_POST['saida'] ?? date('Y-m-d H:i:s');
    $km = (int)$_POST['km'] ?? 0;
    $situacao = null;
    $observacoes = null;

    // Variáveis para cliente, contato e telefone (inicialmente vazias)
    $cliente = null;
    $contato = null;
    $telefone = null;

    // Iniciar transação
    $conn->begin_transaction();

    try {
        // Buscar informações do cliente apenas se id_cliente for fornecido
        if ($id_cliente !== null) {
            $query_os = "SELECT nome_empresa, contato, telefone FROM cliente WHERE id_cliente = ?";
            $stmt_os = $conn->prepare($query_os);
            $stmt_os->bind_param("i", $id_cliente);
            $stmt_os->execute();
            $result_os = $stmt_os->get_result();

            if ($result_os->num_rows > 0) {
                $row_os = $result_os->fetch_assoc();
                $cliente = $row_os['nome_empresa'] ?? '';
                $contato = $row_os['contato'] ?? '';
                $telefone = $row_os['telefone'] ?? '';

                // Buscar endereço (se necessário)
                $query_endereco = "SELECT id_endereco FROM os_endereco WHERE id_cliente = ? LIMIT 1";
                $stmt_endereco = $conn->prepare($query_endereco);
                $stmt_endereco->bind_param("i", $id_cliente);
                $stmt_endereco->execute();
                $result_endereco = $stmt_endereco->get_result();

                if ($result_endereco->num_rows > 0) {
                    $row_endereco = $result_endereco->fetch_assoc();
                }
                $stmt_endereco->close();
            } else {
                throw new Exception("Cliente não encontrado para o ID fornecido.");
            }

            $stmt_os->close();
        } else {
            // Se for um serviço customizável, usar o nome do serviço como "cliente"
            $cliente = $_POST['cliente_customizavel'] ?? '';
            $telefone = $_POST['telefone_customizavel'] ?? '';
        }

        $query_veiculo2 = "UPDATE veiculos SET motorista = ? WHERE id_veiculo = ?";
        $stmt_veiculo2 = $conn->prepare($query_veiculo2);
        $stmt_veiculo2->bind_param("ii", $motorista, $veiculo);
        $stmt_veiculo2->execute();

        $query_veiculo = "SELECT km FROM veiculos WHERE id_veiculo = ?";
        $stmt_veiculo = $conn->prepare($query_veiculo);
        $stmt_veiculo->bind_param("i", $veiculo);
        $stmt_veiculo->execute();
        $result_veiculo = $stmt_veiculo->get_result();
        $km_saida = 0;
        if ($result_veiculo->num_rows > 0) {
            $row_veiculo = $result_veiculo->fetch_assoc();
            $km_saida = $row_veiculo['km'] ?? 0;
        }

        // Inserir na tabela checklist com km_saida
        $query_checklist = "INSERT INTO checklist (criador, nome_num, responsavel, motorista, veiculo, acompanhantes, cliente, contato, telefone, destino, cidade, saida, situacao, km_saida, observacoes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_checklist = $conn->prepare($query_checklist);
        $stmt_checklist->bind_param("isiiissssssssis", $criador, $nome_num, $responsavel, $motorista, $veiculo, $acompanhantes, $cliente, $contato, $telefone, $destino, $cidade, $saida, $situacao, $km_saida, $observacoes);
        $stmt_checklist->execute();

        $id_checklist = $conn->insert_id;

        // Processar itensAdicionados (recebidos como JSON)
        if (!empty($_POST['itensAdicionados'])) {
            $itens = json_decode($_POST['itensAdicionados'], true);
            error_log("Itens decodificados: " . print_r($itens, true));

            foreach ($itens as $item) {
                $id_equipamento = (int)$item['id'];
                $quantidade_levada = $item['tipo'] === 'materia_prima' ? (float)$item['quantidade'] : (int)$item['quantidade'];
                $tipo = $item['tipo'];
                error_log("Processando item: ID=$id_equipamento, Quantidade=$quantidade_levada, Tipo=$tipo");

                if ($tipo === 'maleta') {
                    $query_maleta_nome = "SELECT nome FROM maletas WHERE id_maleta = ?";
                    $stmt_maleta_nome = $conn->prepare($query_maleta_nome);
                    $stmt_maleta_nome->bind_param("i", $id_equipamento);
                    $stmt_maleta_nome->execute();
                    $result_maleta_nome = $stmt_maleta_nome->get_result();
                    $nome_maleta = $result_maleta_nome->num_rows > 0 ? $result_maleta_nome->fetch_assoc()['nome'] : "Maleta desconhecida";
                    $stmt_maleta_nome->close();

                    // Alterado: usar nome_num em vez de id_checklist
                    $query_maleta = "UPDATE maletas SET situacao = 'Em uso', nome_num = ? WHERE id_maleta = ?";
                    $stmt_maleta = $conn->prepare($query_maleta);
                    $stmt_maleta->bind_param("si", $nome_num, $id_equipamento);
                    $stmt_maleta->execute();
                    $stmt_maleta->close();

                    $quantidade_devolvida = 0;
                    $confirmado = 'NOK';

                    $query_caixa_item = "INSERT INTO caixa_ferramentas (id_checklist, id_maleta, quantidade_levada, quantidade_devolvida, retornado) VALUES (?, ?, ?, ?, ?)";
                    $stmt_caixa_item = $conn->prepare($query_caixa_item);
                    $stmt_caixa_item->bind_param("iiiis", $id_checklist, $id_equipamento, $quantidade_levada, $quantidade_devolvida, $confirmado);
                    $stmt_caixa_item->execute();
                    $id_caixa_ferramenta_maleta = $conn->insert_id;
                    $stmt_caixa_item->close();

                    $query_ferramentas_maleta = "SELECT id_ferramenta, quantidade FROM ferramenta_maleta WHERE id_maleta = ?";
                    $stmt_ferramentas_maleta = $conn->prepare($query_ferramentas_maleta);
                    $stmt_ferramentas_maleta->bind_param("i", $id_equipamento);
                    $stmt_ferramentas_maleta->execute();
                    $result_ferramentas_maleta = $stmt_ferramentas_maleta->get_result();

                    $query_caixa_ferramenta = "INSERT INTO caixa_ferramentas (id_checklist, id_maleta, id_ferramenta, quantidade_levada, quantidade_devolvida, retornado) VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt_caixa_ferramenta = $conn->prepare($query_caixa_ferramenta);


                    while ($row_ferramenta = $result_ferramentas_maleta->fetch_assoc()) {
                        $id_ferramenta = $row_ferramenta['id_ferramenta'];
                        $quantidade_levada_ferramenta = $row_ferramenta['quantidade'];
                        $quantidade_devolvida = 0;
                        $retornado = 'NOK';

                        $stmt_caixa_ferramenta->bind_param("iiiiis", $id_checklist, $id_equipamento, $id_ferramenta, $quantidade_levada_ferramenta, $quantidade_devolvida, $retornado);
                        $stmt_caixa_ferramenta->execute();
                    }

                    $query_ferramentas_maleta = "UPDATE ferramentas SET nome_num = ? WHERE situacao = ?";
                    $stmt_ferramentas_maleta = $conn->prepare($query_ferramentas_maleta);
                    $stmt_ferramentas_maleta->bind_param('si', $nome_num, $id_equipamento);
                    $stmt_ferramentas_maleta->execute();

                    $stmt_caixa_ferramenta->close();
                    $stmt_ferramentas_maleta->close();
                } elseif ($tipo === 'ferramenta') {
                    $query_ferramenta_nome = "SELECT nome FROM ferramentas WHERE id_ferramenta = ?";
                    $stmt_ferramenta_nome = $conn->prepare($query_ferramenta_nome);
                    $stmt_ferramenta_nome->bind_param("i", $id_equipamento);
                    $stmt_ferramenta_nome->execute();
                    $result_ferramenta_nome = $stmt_ferramenta_nome->get_result();
                    $nome_ferramenta = $result_ferramenta_nome->num_rows > 0 ? $result_ferramenta_nome->fetch_assoc()['nome'] : "Ferramenta desconhecida";
                    $stmt_ferramenta_nome->close();

                    // Alterado: usar nome_num em vez de id_checklist
                    $query_ferramenta = "UPDATE ferramentas SET quantidade_atual = quantidade_atual - ?, situacao = IF(quantidade_atual - ? <= 0, 'Em uso', 'Disponível'), nome_num = ? WHERE id_ferramenta = ?";
                    $stmt_ferramenta = $conn->prepare($query_ferramenta);
                    $stmt_ferramenta->bind_param("iisi", $quantidade_levada, $quantidade_levada, $nome_num, $id_equipamento);
                    $stmt_ferramenta->execute();
                    $stmt_ferramenta->close();

                    $quantidade_devolvida = 0;
                    $confirmado = 'NOK';

                    $query_caixa_item = "INSERT INTO caixa_ferramentas (id_checklist, id_ferramenta, quantidade_levada, quantidade_devolvida, retornado) VALUES (?, ?, ?, ?, ?)";
                    $stmt_caixa_item = $conn->prepare($query_caixa_item);
                    $stmt_caixa_item->bind_param("iiiis", $id_checklist, $id_equipamento, $quantidade_levada, $quantidade_devolvida, $confirmado);
                    $stmt_caixa_item->execute();
                    $stmt_caixa_item->close();
                } else {
                    error_log("Tipo inválido para item: $tipo, ID: $id_equipamento");
                }
            }
        }

        // Commit da transação
        $conn->commit();
        $stmt_checklist->close();
        $stmt_veiculo->close();
        $stmt_veiculo2->close();
        $conn->close();
        header('Location: ..?success=1');
        exit;
    } catch (Exception $e) {
        // Rollback em caso de erro
        $conn->rollback();
        $conn->close();
        die("Erro ao criar checklist: " . $e->getMessage());
    }
}
