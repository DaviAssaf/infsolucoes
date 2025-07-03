<?php
require_once '../../verificacao_sessao.php';
include '../../conn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $placa = $_POST['placa'];
    $km = $_POST['km'];
    $marca = $_POST['marca'];

    $query = "INSERT INTO veiculos (nome, placa, km, marca) 
              VALUES (?, ?, ?, ?)";
    
    $stmt = $conn->prepare($query);
    
    if ($stmt) {
        $stmt->bind_param('ssis', $nome, $placa, $km, $marca);
        
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
            }

            header("location: ..");
            exit();
        } else {
            echo "Erro ao cadastrar veículo: " . $stmt->error;
        }
        
        $stmt->close();
    } else {
        echo "Erro na preparação da consulta: " . $conn->error;
    }

    $conn->close();
}
?>