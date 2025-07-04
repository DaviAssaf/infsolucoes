-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Tempo de geração: 04/07/2025 às 12:58
-- Versão do servidor: 8.0.42
-- Versão do PHP: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `infsolucoes`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `caixa_ferramentas`
--

DROP TABLE IF EXISTS `caixa_ferramentas`;
CREATE TABLE IF NOT EXISTS `caixa_ferramentas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_checklist` int NOT NULL,
  `id_ferramenta` int DEFAULT NULL,
  `id_maleta` int DEFAULT NULL,
  `quantidade_levada` float NOT NULL DEFAULT '0',
  `quantidade_devolvida` float NOT NULL DEFAULT '0',
  `retornado` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'NOK',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_entry` (`id_checklist`,`id_ferramenta`,`id_maleta`),
  KEY `id_ferramenta` (`id_ferramenta`),
  KEY `id_maleta` (`id_maleta`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `checklist`
--

DROP TABLE IF EXISTS `checklist`;
CREATE TABLE IF NOT EXISTS `checklist` (
  `id_checklist` int NOT NULL AUTO_INCREMENT,
  `nome_num` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `criador` int DEFAULT NULL,
  `responsavel` int DEFAULT NULL,
  `motorista` int DEFAULT NULL,
  `veiculo` int DEFAULT NULL,
  `acompanhantes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `km_saida` int DEFAULT '0',
  `km_retorno` int DEFAULT NULL,
  `cliente` varchar(75) DEFAULT NULL,
  `contato` varchar(255) DEFAULT NULL,
  `telefone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `destino` varchar(250) DEFAULT NULL,
  `cidade` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `saida` datetime DEFAULT CURRENT_TIMESTAMP,
  `retorno` datetime DEFAULT NULL,
  `situacao` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT '',
  `observacoes` text,
  PRIMARY KEY (`id_checklist`),
  KEY `responsavel` (`responsavel`),
  KEY `motorista` (`motorista`),
  KEY `veiculo` (`veiculo`),
  KEY `cliente` (`cliente`),
  KEY `destino` (`destino`),
  KEY `cidade` (`cidade`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `cliente`
--

DROP TABLE IF EXISTS `cliente`;
CREATE TABLE IF NOT EXISTS `cliente` (
  `id_cliente` int NOT NULL AUTO_INCREMENT,
  `cnpj` varchar(18) NOT NULL,
  `nome_empresa` varchar(250) NOT NULL,
  `contato` varchar(100) DEFAULT NULL,
  `telefone` varchar(16) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id_cliente`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `estoque`
--

DROP TABLE IF EXISTS `estoque`;
CREATE TABLE IF NOT EXISTS `estoque` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(250) NOT NULL,
  `quantidade` int NOT NULL,
  `categoria` enum('Normal','Eletronicos','Matéria Prima') DEFAULT NULL,
  `fornecedor` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `nome` (`nome`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `ferramentas`
--

DROP TABLE IF EXISTS `ferramentas`;
CREATE TABLE IF NOT EXISTS `ferramentas` (
  `id_ferramenta` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(250) NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `quantidade_total` int NOT NULL,
  `quantidade_atual` int NOT NULL,
  `tipo` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `situacao` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT 'Disponível',
  `nome_num` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  PRIMARY KEY (`id_ferramenta`),
  UNIQUE KEY `id` (`id_ferramenta`),
  KEY `fk_ordem_servico` (`nome_num`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `ferramenta_maleta`
--

DROP TABLE IF EXISTS `ferramenta_maleta`;
CREATE TABLE IF NOT EXISTS `ferramenta_maleta` (
  `id_ferramenta` int NOT NULL,
  `id_maleta` int NOT NULL,
  `quantidade` int DEFAULT NULL,
  KEY `id_ferramenta_fk` (`id_ferramenta`),
  KEY `id_maleta_fk` (`id_maleta`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `funcionarios`
--

DROP TABLE IF EXISTS `funcionarios`;
CREATE TABLE IF NOT EXISTS `funcionarios` (
  `id_funcionario` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `telefone` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `email` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `senha` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `administrador` tinyint NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_funcionario`),
  UNIQUE KEY `id` (`id_funcionario`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `historico`
--

DROP TABLE IF EXISTS `historico`;
CREATE TABLE IF NOT EXISTS `historico` (
  `id` int NOT NULL AUTO_INCREMENT,
  `data_hora` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `funcionario_id` int NOT NULL,
  `secao` varchar(50) NOT NULL,
  `item` varchar(250) NOT NULL,
  `acao` varchar(30) NOT NULL,
  `detalhes` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `maletas`
--

DROP TABLE IF EXISTS `maletas`;
CREATE TABLE IF NOT EXISTS `maletas` (
  `id_maleta` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(250) NOT NULL,
  `custo` float NOT NULL,
  `situacao` enum('Disponível','Em uso') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'Disponível',
  `nome_num` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id_maleta`),
  UNIQUE KEY `id` (`id_maleta`),
  UNIQUE KEY `nome` (`nome`),
  KEY `fk_ordem_servico` (`nome_num`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `materia_prima`
--

DROP TABLE IF EXISTS `materia_prima`;
CREATE TABLE IF NOT EXISTS `materia_prima` (
  `id_mp` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(250) NOT NULL,
  `custo` decimal(10,2) NOT NULL,
  `quantidade` float NOT NULL DEFAULT '0',
  `medida` varchar(10) NOT NULL DEFAULT 'uni',
  `descricao` mediumtext,
  `quantidade_min` float DEFAULT NULL,
  PRIMARY KEY (`id_mp`),
  UNIQUE KEY `id_mp` (`id_mp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `ocorrencias`
--

DROP TABLE IF EXISTS `ocorrencias`;
CREATE TABLE IF NOT EXISTS `ocorrencias` (
  `num_ocorrencia` int NOT NULL AUTO_INCREMENT,
  `nome_num` varchar(250) NOT NULL,
  `responsavel` int NOT NULL,
  `id_ferramenta` int DEFAULT NULL,
  `id_maleta` int DEFAULT NULL,
  `id_mp` int DEFAULT NULL,
  `data_ocorrencia` datetime DEFAULT CURRENT_TIMESTAMP,
  `descricao` text,
  `situacao` enum('A resolver','Resolvido','Em andamento') DEFAULT 'A resolver',
  PRIMARY KEY (`num_ocorrencia`),
  KEY `num_ordem_servico` (`nome_num`),
  KEY `responsavel` (`responsavel`),
  KEY `id_ferramenta` (`id_ferramenta`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `os_endereco`
--

DROP TABLE IF EXISTS `os_endereco`;
CREATE TABLE IF NOT EXISTS `os_endereco` (
  `id_endereco` int NOT NULL AUTO_INCREMENT,
  `id_cliente` int NOT NULL,
  `cep` varchar(9) NOT NULL,
  `endereco` varchar(250) NOT NULL,
  `numero` int DEFAULT NULL,
  `complemento` varchar(100) DEFAULT NULL,
  `bairro` varchar(50) DEFAULT NULL,
  `cidade` varchar(50) DEFAULT NULL,
  `estado` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_endereco`) USING BTREE,
  KEY `id_cliente` (`id_cliente`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `registro_estoque`
--

DROP TABLE IF EXISTS `registro_estoque`;
CREATE TABLE IF NOT EXISTS `registro_estoque` (
  `id` int NOT NULL AUTO_INCREMENT,
  `data_hora` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `id_responsavel` int NOT NULL,
  `os` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `custo_final` float NOT NULL,
  `tipo` tinyint NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `saida_estoque`
--

DROP TABLE IF EXISTS `saida_estoque`;
CREATE TABLE IF NOT EXISTS `saida_estoque` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_registro_estoque` int NOT NULL,
  `id_mp` int NOT NULL,
  `quantidade` float NOT NULL DEFAULT '0',
  `custo_total` float NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_entry` (`id_registro_estoque`,`id_mp`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `veiculos`
--

DROP TABLE IF EXISTS `veiculos`;
CREATE TABLE IF NOT EXISTS `veiculos` (
  `id_veiculo` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(250) NOT NULL,
  `motorista` int DEFAULT NULL,
  `placa` varchar(10) NOT NULL,
  `km` int NOT NULL,
  `marca` varchar(100) DEFAULT NULL,
  `imagens` longblob,
  `avarias` longblob,
  PRIMARY KEY (`id_veiculo`),
  KEY `motorista` (`motorista`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `veiculo_condicao`
--

DROP TABLE IF EXISTS `veiculo_condicao`;
CREATE TABLE IF NOT EXISTS `veiculo_condicao` (
  `id_condicao` int NOT NULL AUTO_INCREMENT,
  `id_veiculo` int DEFAULT NULL,
  `item` varchar(255) DEFAULT NULL,
  `status` enum('OK','NOK') DEFAULT 'OK',
  PRIMARY KEY (`id_condicao`),
  KEY `id_veiculo` (`id_veiculo`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
