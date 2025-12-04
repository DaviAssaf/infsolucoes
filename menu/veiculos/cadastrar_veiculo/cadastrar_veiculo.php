<?php
require_once '../../verificacao_sessao.php';
include '../../conn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = strtoupper($_POST['nome']);
    $placa = strtoupper($_POST['placa']);
    $km = intval($_POST['km']);
    $marca = strtoupper($_POST['marca']);

    $conn->begin_transaction();

    $query = "INSERT INTO veiculos (nome, placa, km, marca) 
              VALUES (?, ?, ?, ?)";
    
    $stmt = $conn->prepare($query);
    
    if ($stmt) {
        $stmt->bind_param('ssis', $nome, $placa, $km, $marca);

        try {
            if ($stmt->execute()) {
                $id_veiculo = $stmt->insert_id;

                $condicoes_padrão = [
                    'Para-brisa sem avaria',
                    'Limpadores para-brisa',
                    'Água do reservatório do para-brisa',
                    'Nível de água do radiador',
                    'Nível do óleo do motor',
                    'Faróis e sinalizadores de direção',
                    'Antena',
                    'Documento atualizado',
                    'Difusores de ar',
                    'Luzes do painel apagadas',
                    'Revisão de Km',
                    'Buzina',
                    'Tapetes',
                    'Sem odores',
                    'Funcionamento do rádio/multimídia',
                    'Porta-luvas limpo',
                    'Pneu Dianteiro Esquerdo',
                    'Pneu Dianteiro Direito',
                    'Pneu Traseiro Esquerdo',
                    'Pneu Traseiro Direito',
                    'Pneu Estepe'
                ];

                foreach ($condicoes_padrão as $item) {
                    $query_condicao = "INSERT INTO veiculo_condicao (id_veiculo, item, status) VALUES (?, ?, 'NOK')";
                    $stmt_condicao = $conn->prepare($query_condicao);
                    $stmt_condicao->bind_param("is", $id_veiculo, $item);
                    $stmt_condicao->execute();
                    $stmt_condicao->close();
                }

                // Gera os detalhes da inserção
                $detalhes = "$nome cadastrado com sucesso";

                // Inserir no histórico
                $acao = "CADASTRO";
                $item = "$nome (Placa: $placa)";
                $query_historico = "INSERT INTO historico (funcionario_id, secao, item, acao, detalhes) VALUES (?, ?, ?, ?, ?)";
                $stmt_historico = $conn->prepare($query_historico);
                $user_id = $_SESSION['user_id'];
                $secao = 'Veículos';
                $stmt_historico->bind_param("issss", $user_id, $secao, $item, $acao, $detalhes);
                $stmt_historico->execute();
                $stmt_historico->close();

                $conn->commit();
                header("location: ..?Sucesso no cadastro do veículo");
                exit();
            } else {
                throw new Exception("Erro ao cadastrar veículo: " . $stmt->error);
            }
        } catch (Exception $e) {
            $conn->rollback();
            echo "Erro ao processar dados: " . $e->getMessage();
        }

        $stmt->close();
    } else {
        echo "Erro na preparação da consulta: " . $conn->error;
    }

    $conn->close();
}
?>