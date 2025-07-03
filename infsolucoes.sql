-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Tempo de geração: 03/07/2025 às 22:12
-- Versão do servidor: 8.0.40
-- Versão do PHP: 8.2.25

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
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `caixa_ferramentas`
--

INSERT INTO `caixa_ferramentas` (`id`, `id_checklist`, `id_ferramenta`, `id_maleta`, `quantidade_levada`, `quantidade_devolvida`, `retornado`) VALUES
(1, 1, 29, NULL, 1, 1, 'OK'),
(2, 1, 27, NULL, 1, 1, 'OK'),
(3, 2, 1, NULL, 1, 1, 'OK'),
(4, 2, 12, NULL, 1, 1, 'OK');

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
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `checklist`
--

INSERT INTO `checklist` (`id_checklist`, `nome_num`, `criador`, `responsavel`, `motorista`, `veiculo`, `acompanhantes`, `km_saida`, `km_retorno`, `cliente`, `contato`, `telefone`, `destino`, `cidade`, `saida`, `retorno`, `situacao`, `observacoes`) VALUES
(1, '250', 2, 2, 0, 0, '', 0, 0, 'FORMOSA', 'Manu', '(91) 98370-8688', 'Rua Curuçá, 580 - Telégrafo', 'Belém', '2025-07-02 09:19:00', '2025-07-03 15:29:00', 'Concluida', ''),
(2, 'TESTE', 2, 2, 0, 0, '', 0, 0, 'FORMOSA', 'Manu', '(91) 98370-8688', 'Rua Curuçá, 580 - Telégrafo', 'Belém', '2025-07-03 15:26:00', '2025-07-03 15:29:00', 'Concluida', '');

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
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `cliente`
--

INSERT INTO `cliente` (`id_cliente`, `cnpj`, `nome_empresa`, `contato`, `telefone`, `email`) VALUES
(4, '63.864.771/0001-47', 'FORMOSA', 'Manuela', '(91) 98370-8688', NULL);

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
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `estoque`
--

INSERT INTO `estoque` (`id`, `nome`, `quantidade`, `categoria`, `fornecedor`) VALUES
(1, 'Cabo de aço 15m', 1, 'Normal', 'Fornecedor1'),
(2, 'Lata de Tinta 1L', 50, 'Matéria Prima', 'Fornecedor2');

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
) ENGINE=MyISAM AUTO_INCREMENT=105 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `ferramentas`
--

INSERT INTO `ferramentas` (`id_ferramenta`, `nome`, `valor`, `quantidade_total`, `quantidade_atual`, `tipo`, `situacao`, `nome_num`) VALUES
(5, 'ALICATE MEIA CANA DE 8', 0.00, 1, 0, 'Ferramentas manuais', '2', NULL),
(9, 'BATERIA 12V CXT', 0.00, 2, 0, 'Ferramentas manuais', '1', NULL),
(10, 'BATERIA RESERVA', 0.00, 1, 0, 'Ferramentas elétricas', '1', NULL),
(13, 'CABO DE EXTENSÃO REFORÇADO', 0.00, 1, 1, 'Ferramentas elétricas', 'Disponível', NULL),
(14, 'CABO PP EXTENSÃO', 0.00, 3, 3, 'Ferramentas elétricas', 'Disponível', NULL),
(15, 'CABO T10 E EXTENSÃO 2 EM 1', 0.00, 1, 0, 'Ferramentas manuais', '2', NULL),
(16, 'CAPACETE EPI 3M COM JUGOLAR', 0.00, 6, 6, 'Ferramentas manuais', 'Disponível', NULL),
(17, 'CARREGADOR DE BATERIA 12V', 0.00, 1, 1, 'Ferramentas elétricas', 'Disponível', NULL),
(18, 'CARREGADOR TURBO', 0.00, 1, 0, 'Ferramentas elétricas', '1', NULL),
(19, 'CATRACA RESERSIVEL 72 DENTES', 0.00, 2, 0, 'Ferramentas manuais', '2', NULL),
(20, 'CESTO LIXEIRO PLÁSTICO', 0.00, 2, 2, 'Ferramentas manuais', 'Disponível', NULL),
(21, 'CHAPINHA DOBRADORA DE ACRILICO', 0.00, 2, 2, 'Ferramentas elétricas', 'Disponível', NULL),
(22, 'CHAVE DE FENDA - 5,5 x 75mm - 6,5 x 100mm', 0.00, 2, 2, 'Ferramentas manuais', 'Disponível', NULL),
(23, 'CHAVE DE FENDA PHILIPS - N° 1 x 75mm — N° 2 x 100mm', 0.00, 2, 2, 'Ferramentas manuais', 'Disponível', NULL),
(24, 'CHAVE PHILLIPS', 0.00, 6, 6, 'Ferramentas manuais', 'Disponível', NULL),
(25, 'CHAVES COMBINADAS - 8 A 19mm', 0.00, 11, 0, 'Ferramentas manuais', '2', NULL),
(26, 'CHAVES HEXAGONAIS - 1,5 A 6mm', 0.00, 8, 0, 'Ferramentas manuais', '2', NULL),
(27, 'CINTA CATRACA', 0.00, 2, 2, 'Ferramentas manuais', 'Disponível', NULL),
(28, 'CINTO E TALABARTE DE SEGURANÇA', 0.00, 6, 6, 'Ferramentas manuais', 'Disponível', NULL),
(29, 'COMPRESSOR DE AR', 0.00, 1, 1, 'Ferramentas elétricas', 'Disponível', NULL),
(30, 'CONE RIGIDO', 0.00, 9, 9, 'Ferramentas manuais', 'Disponível', NULL),
(31, 'CORDA GRANDE', 0.00, 1, 1, 'Ferramentas manuais', 'Disponível', NULL),
(32, 'CORDA PEQUENA', 0.00, 1, 1, 'Ferramentas manuais', 'Disponível', NULL),
(33, 'DESCASCADOR DE FIO DE ISOLAMENTO', 0.00, 1, 1, 'Ferramentas manuais', 'Disponível', NULL),
(34, 'ENVELOPAMENTO MAGNETO', 0.00, 4, 4, 'Ferramentas manuais', 'Disponível', NULL),
(35, 'ESCADA DE DOIS LANCES', 0.00, 1, 1, 'Ferramentas manuais', 'Disponível', NULL),
(36, 'ESCADA DE DOIS LANÇES ALUMINIO', 0.00, 1, 1, 'Ferramentas manuais', 'Disponível', NULL),
(37, 'ESCADA DOBRAVÉL', 0.00, 1, 1, 'Ferramentas manuais', 'Disponível', NULL),
(38, 'ESMERILHADEIRA', 0.00, 1, 1, 'Ferramentas elétricas', 'Disponível', NULL),
(39, 'ESPÁTULA', 0.00, 3, 3, 'Ferramentas manuais', 'Disponível', NULL),
(40, 'ESPÁTULA DE FERRO', 0.00, 4, 4, 'Ferramentas manuais', 'Disponível', NULL),
(41, 'ESQUADRO', 0.00, 2, 2, 'Ferramentas manuais', 'Disponível', NULL),
(42, 'ESQUADRO MAGNÉTICO', 0.00, 4, 4, 'Ferramentas manuais', 'Disponível', NULL),
(43, 'EXTENSÃO CABO PP 10M', 0.00, 1, 1, 'Ferramentas elétricas', 'Disponível', NULL),
(44, 'EXTENSÃO COM 2 SOQUETES SEXTAVADO DE VELA', 0.00, 1, 0, 'Ferramentas manuais', '2', NULL),
(45, 'EXTENSÃO DE 12 METROS', 0.00, 1, 1, 'Ferramentas manuais', 'Disponível', NULL),
(46, 'EXTENSÃO FLEXÍVEL DE \"6\"', 0.00, 1, 0, 'Ferramentas manuais', '2', NULL),
(47, 'EXTENSÃO TRIPLO 5M - 127/220V', 0.00, 1, 1, 'Ferramentas elétricas', 'Disponível', NULL),
(48, 'EXTENSÕES \"2 E 4\"', 0.00, 2, 0, 'Ferramentas manuais', '2', NULL),
(49, 'FERRO DE SOLDA GRANDE', 0.00, 1, 1, 'Ferramentas elétricas', 'Disponível', NULL),
(50, 'FERRO DE SOLDA PARTE ELETRICA', 0.00, 1, 1, 'Ferramentas elétricas', 'Disponível', NULL),
(51, 'FERRO DE SOLDA PEQUENO', 0.00, 1, 1, 'Ferramentas elétricas', 'Disponível', NULL),
(52, 'FURADEIRA', 0.00, 1, 1, 'Ferramentas elétricas', 'Disponível', NULL),
(53, 'FURADEIRA DE BANCADA', 0.00, 0, 0, 'Ferramentas elétricas', 'Disponível', NULL),
(54, 'FURADEIRA DE IMPACTO', 0.00, 0, 0, 'Ferramentas elétricas', 'Disponível', NULL),
(55, 'ILHOSEIRA MANUAL', 0.00, 1, 1, 'Ferramentas manuais', 'Disponível', NULL),
(56, 'JOGO CHAVE L', 0.00, 3, 3, 'Ferramentas manuais', 'Disponível', NULL),
(57, 'JOGO DE CHAVE TORX', 0.00, 1, 1, 'Ferramentas manuais', 'Disponível', NULL),
(58, 'JUNTA UNIVERSAL', 0.00, 1, 0, 'Ferramentas manuais', '2', NULL),
(59, 'LIIMA', 0.00, 2, 2, 'Ferramentas manuais', 'Disponível', NULL),
(60, 'LIMA', 0.00, 2, 2, 'Ferramentas manuais', 'Disponível', NULL),
(61, 'LUVA DE SOLDADOR EZABB', 0.00, 0, 0, 'Ferramentas manuais', 'Disponível', NULL),
(62, 'LUVA PIGMENTADA', 0.00, 10, 10, 'Ferramentas manuais', 'Disponível', NULL),
(63, 'LUVA TERMICA', 0.00, 1, 1, 'Ferramentas manuais', 'Disponível', NULL),
(64, 'MAQUINA DE SOLDA', 0.00, 1, 1, 'Ferramentas elétricas', 'Disponível', NULL),
(65, 'MÁQUINA DE SOLDA BANNER', 0.00, 1, 1, 'Ferramentas elétricas', 'Disponível', NULL),
(66, 'MARRETA', 0.00, 1, 1, 'Ferramentas manuais', 'Disponível', NULL),
(67, 'MARTELO DE BICO SOLDADOR', 0.00, 1, 1, 'Ferramentas manuais', 'Disponível', NULL),
(68, 'MARTELO DE BORRACHA', 0.00, 1, 1, 'Ferramentas manuais', 'Disponível', NULL),
(69, 'MARTELO PEQUENO', 0.00, 1, 1, 'Ferramentas manuais', 'Disponível', NULL),
(70, 'MASCARA DE SOLDA', 0.00, 1, 1, 'Ferramentas manuais', 'Disponível', NULL),
(71, 'MASCARA SEMI-FACIAL', 0.00, 2, 2, 'Ferramentas manuais', 'Disponível', NULL),
(72, 'NIVÉL', 0.00, 3, 3, 'Ferramentas manuais', 'Disponível', NULL),
(73, 'ÓCULOS DE PROTEÇÃO 3M', 0.00, 10, 10, 'Ferramentas manuais', 'Disponível', NULL),
(74, 'Parafusadeira / Furadeira - HP333D', 0.00, 1, 1, 'Ferramentas elétricas', 'Disponível', NULL),
(75, 'Parafusadeira de Impacto - TD110D', 0.00, 1, 1, 'Ferramentas elétricas', 'Disponível', NULL),
(76, 'POLICORTE', 0.00, 2, 2, 'Ferramentas manuais', 'Disponível', NULL),
(77, 'POLITRIZ', 0.00, 1, 1, 'Ferramentas manuais', 'Disponível', NULL),
(78, 'PONTAS PHILIPS PH0', 0.00, 2, 0, 'Ferramentas manuais', '2', NULL),
(79, 'PONTAS PHILIPS PH1', 0.00, 2, 0, 'Ferramentas manuais', '2', NULL),
(80, 'PONTAS PHILIPS PH2', 0.00, 1, 0, 'Ferramentas manuais', '2', NULL),
(81, 'PONTAS PHILIPS PH3', 0.00, 2, 0, 'Ferramentas manuais', '2', NULL),
(82, 'PORTA BIT 3 PONTAS POZI DRIVE', 0.00, 3, 0, 'Ferramentas manuais', '2', NULL),
(83, 'PORTA BIT 7 PONTAS DE FENDA', 0.00, 7, 0, 'Ferramentas manuais', '2', NULL),
(84, 'PORTA BIT 7 PONTAS TORK', 0.00, 7, 0, 'Ferramentas manuais', '2', NULL),
(85, 'PORTA BIT PONTA QUADRADA S1 S2 S3', 0.00, 3, 0, 'Ferramentas manuais', '2', NULL),
(87, 'SOPRADOR TERMICO DEWALTER', 0.00, 1, 1, 'Ferramentas elétricas', 'Disponível', NULL),
(88, 'SOQUETE MAGNÉTICO', 0.00, 5, 5, 'Ferramentas manuais', 'Disponível', NULL),
(89, 'SOQUETES SEXTAVADOS', 0.00, 15, 15, 'Ferramentas manuais', 'Disponível', NULL),
(90, 'SOQUETES SEXTAVADOS 4 A 14mm', 0.00, 11, 0, 'Ferramentas manuais', '2', NULL),
(91, 'TALHADEIRA', 0.00, 1, 1, 'Ferramentas manuais', 'Disponível', NULL),
(92, 'TESOURA DE CORTE DE CHAPA', 0.00, 1, 1, 'Ferramentas manuais', 'Disponível', NULL),
(93, 'TESOURA GUILHOTINA', 0.00, 1, 1, 'Ferramentas manuais', 'Disponível', NULL),
(94, 'TICO TICO', 0.00, 1, 1, 'Ferramentas manuais', 'Disponível', NULL),
(95, 'TORNO DE BANCADA', 0.00, 1, 1, 'Ferramentas manuais', 'Disponível', NULL),
(96, 'TRANSFORMADOR BIVOLT', 0.00, 1, 1, 'Ferramentas elétricas', 'Disponível', NULL),
(99, 'TUPIA', 0.00, 2, 2, 'Ferramentas elétricas', 'Disponível', NULL),
(98, 'PARAFUSADEIRA', 0.00, 1, 1, 'Ferramentas elétricas', '1', NULL);

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

--
-- Despejando dados para a tabela `ferramenta_maleta`
--

INSERT INTO `ferramenta_maleta` (`id_ferramenta`, `id_maleta`, `quantidade`) VALUES
(8, 1, 3),
(10, 1, 1),
(18, 1, 1),
(3, 2, 1),
(15, 2, 1),
(1, 2, 1),
(26, 2, 8),
(5, 2, 1),
(48, 2, 2),
(19, 2, 2),
(46, 2, 1),
(25, 2, 11),
(44, 2, 1),
(58, 2, 1),
(101, 2, 1),
(78, 2, 2),
(79, 2, 2),
(80, 2, 1),
(81, 2, 2),
(82, 2, 3),
(83, 2, 7),
(84, 2, 7),
(85, 2, 3),
(90, 2, 11),
(9, 1, 2);

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
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `funcionarios`
--

INSERT INTO `funcionarios` (`id_funcionario`, `nome`, `telefone`, `email`, `senha`, `administrador`) VALUES
(5, 'Camila Silva', '(91) 99222-3232', 'infinity.contato1@gmail.com', '$2y$10$H4aLrc1/u5yxLwTaU4WNRuKGZFhvNzn/t6TnFdeHt48YLtVfqcVF6', 1),
(2, 'Davi Assaf', '(91) 98314-8688', 'daviassafmp1@gmail.com', '$2y$10$UK0thZSTTT9e5RO/yZWrDOiGsiTgsRANn9/ZM7VtOJZGVpQu7DunO', 1),
(6, 'Hélio Nascimento Júnior', '(91) 99334-2529', 'infinity.sproducao@gmail.com', '$2y$10$yd6A7KEukL5jQLKTIpQP9egji.OgJtxsxeGh0Tj34jDQ9MeCCKkj6', 1),
(8, 'Thiago', '(91) 98590-4833', NULL, NULL, 0),
(9, 'Michel', '(91) 98234-7715', NULL, NULL, 0),
(10, 'Elias Nascimento', '(91) 98376-5225', NULL, NULL, 0),
(11, 'Valdemi', '(91) 99235-6604', NULL, NULL, 0),
(12, 'Vandercley', '(91) 99288-1337', NULL, NULL, 0),
(13, 'Elton', '(91) 98230-0686', '', NULL, 0),
(14, 'Ailton Noya', '(91) 98453-5042', NULL, NULL, 0),
(15, 'Fernanda', '(91) 99183-1212', 'infinity.1financeiro@gmail.com', '$2y$10$vkOcFi0JRW7o5NXCwoNxAOUjgXY6PI3HSeheE88l.L7KfOATzQ0HW', 0);

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
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `maletas`
--

INSERT INTO `maletas` (`id_maleta`, `nome`, `custo`, `situacao`, `nome_num`) VALUES
(1, 'Maleta Makita', 0, 'Disponível', NULL),
(2, 'Maleta Worker', 0, 'Disponível', NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=81 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `materia_prima`
--

INSERT INTO `materia_prima` (`id_mp`, `nome`, `custo`, `quantidade`, `medida`, `descricao`, `quantidade_min`) VALUES
(22, 'Água desmeralizada s 3', 1.00, 0, 'uni', '', 0),
(23, 'Módulos de led', 0.00, 300, 'uni', NULL, NULL),
(24, 'Cola de acrílico', 0.00, 0, 'uni', NULL, NULL),
(25, 'Abraçadeiras 300mm', 0.10, 3000, 'uni', '', 1000),
(26, 'ABRAçADEIRA MENOR 200MM', 0.10, 3000, 'uni', '', 1000),
(27, 'Manta líquida', 0.00, 1, 'uni', NULL, NULL),
(28, 'Cimento branco 500/pacote', 0.00, 4, 'uni', NULL, NULL),
(29, 'Acelerador', 30.00, 2, 'uni', 'TECBOND', 2),
(30, 'Teckbonde 20ml', 0.00, 19, 'uni', NULL, NULL),
(32, 'Silicone PU', 0.00, 2, 'uni', NULL, NULL),
(33, 'Silicone acético', 0.00, 1, 'uni', NULL, NULL),
(34, 'Luva piguimentada', 0.00, 10, 'uni', NULL, NULL),
(35, 'Limpa alumínio', 0.00, 0, 'uni', NULL, NULL),
(36, 'Eletrodo', 0.00, 2000, 'uni', NULL, NULL),
(37, 'Ilhos', 0.00, 0, 'uni', '', 0),
(38, 'Prime Acm', 0.00, 0, 'uni', NULL, NULL),
(39, 'Sacos de lixo G', 0.00, 0, 'uni', NULL, NULL),
(40, 'Saco de lixo P', 0.00, 0, 'uni', NULL, NULL),
(41, 'Parafuso Philips 5x65mm', 0.00, 64, 'uni', NULL, NULL),
(42, 'Parafuso Philips 5x50mm', 0.00, 59, 'uni', NULL, NULL),
(43, 'Parafuso autobrocante', 0.00, 4124, 'uni', NULL, NULL),
(44, 'Parafuso sextavado 14', 0.00, 23, 'uni', NULL, NULL),
(45, 'Parafuso sextavado 12', 0.00, 19, 'uni', NULL, NULL),
(46, 'Parafuso Philips grande', 0.00, 9, 'uni', NULL, NULL),
(47, 'Parafuso autobrocante telha 30mm', 0.00, 45, 'uni', NULL, NULL),
(48, 'Parafuso autobrocante telha 50mm', 0.00, 51, 'uni', NULL, NULL),
(49, 'Porcas', 0.00, 84, 'uni', NULL, NULL),
(50, 'Arruela', 0.50, 59, 'uni', '', 0),
(51, 'Parafuso stopper', 0.00, 463, 'uni', NULL, NULL),
(52, 'Porca stoppers', 0.00, 426, 'uni', NULL, NULL),
(53, 'Cantoneira', 0.00, 322, 'uni', NULL, NULL),
(54, 'Parafuso letras', 0.00, 235, 'uni', NULL, NULL),
(55, 'Parafuso madeira Philips', 0.00, 82, 'uni', NULL, NULL),
(56, 'Parafuso Philips n6', 0.00, 74, 'uni', NULL, NULL),
(57, 'Parafuso Philips n8', 0.00, 34, 'uni', NULL, NULL),
(58, 'Parafuso drywol', 0.00, 265, 'uni', NULL, NULL),
(59, 'Parafuso autobrocante sextavado', 0.00, 327, 'uni', NULL, NULL),
(60, 'Cantoneira preta', 0.00, 115, 'uni', NULL, NULL),
(61, 'Bucha para gesso', 0.00, 26, 'uni', NULL, NULL),
(62, 'Bucha n8', 0.00, 76, 'uni', NULL, NULL),
(63, 'Bucha n10', 0.00, 32, 'uni', NULL, NULL),
(64, 'Broca 3/16', 0.00, 7, 'uni', NULL, NULL),
(65, 'Broca 6mm', 0.00, 2, 'uni', NULL, NULL),
(66, 'Fita crepe 50 mm', 0.00, 0, 'uni', NULL, NULL),
(67, 'Fita demarcava', 0.00, 0, 'uni', NULL, NULL),
(68, 'Rabo de rato 1 rolo', 0.00, 0, 'uni', NULL, NULL),
(69, 'Ponteira', 0.00, 214, 'uni', NULL, NULL),
(70, 'Lâmina larga', 0.00, 0, 'uni', NULL, NULL),
(71, 'Lâmina estreita', 0.00, 0, 'uni', NULL, NULL),
(72, 'Mascara de Transferência', 0.00, 46, 'uni', '', 20),
(73, 'Sobra de Mascara de Transferência', 0.00, 49, 'm²', '', 20),
(74, 'Sobra de Mascara de Transferência', 0.00, 25, 'm²', '', 20),
(75, 'Sobra de Mascara de Transferência', 0.00, 38, 'm²', '', 20),
(76, 'Lona Titanium 500x500 Fosca', 21.00, 22, 'm²', '1.60m x 65m', 15),
(77, 'Sobra de Lona Titanium 500x500 Fosca', 0.00, 99, 'm²', '1.62m x 65m', 3),
(78, 'CHAPA DE PVC EXPANDIDO 15 MM', 0.00, 2, 'uni', '', 1),
(79, 'VINIL BRANCO FOSCO 1,27X50', 9.00, 100, 'm²', '', 50),
(80, 'VINIL BRANCO FOSCO 1,06X50', 7.00, 50, 'm²', '', 50);

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
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `ocorrencias`
--

INSERT INTO `ocorrencias` (`num_ocorrencia`, `nome_num`, `responsavel`, `id_ferramenta`, `id_maleta`, `id_mp`, `data_ocorrencia`, `descricao`, `situacao`) VALUES
(1, '0', 14, 13, 0, NULL, '0000-00-00 00:00:00', '', 'Resolvido'),
(2, '0', 14, 13, 0, 0, '0000-00-00 00:00:00', '', 'Resolvido'),
(3, '0', 14, NULL, 3, NULL, '2025-06-24 11:44:54', '', 'Resolvido');

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
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `os_endereco`
--

INSERT INTO `os_endereco` (`id_endereco`, `id_cliente`, `cep`, `endereco`, `numero`, `complemento`, `bairro`, `cidade`, `estado`) VALUES
(12, 5, '66635-110', 'Rodovia Augusto Montenegro', 580, NULL, 'Parque Verde', 'Belém', 'PA'),
(11, 4, '66050-080', 'Rua Curuçá', NULL, NULL, 'Telégrafo', 'Belém', 'PA'),
(13, 6, '66040-143', 'Av. Nª Sra. de Nazaré', 902, NULL, 'Nazaré', 'Belém', 'PA');

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
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `registro_estoque`
--

INSERT INTO `registro_estoque` (`id`, `data_hora`, `id_responsavel`, `os`, `custo_final`, `tipo`) VALUES
(1, '2025-07-02 11:12:53', 2, '102', 9, 0),
(2, '2025-07-02 11:13:27', 2, NULL, 6.1, 1);

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
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `saida_estoque`
--

INSERT INTO `saida_estoque` (`id`, `id_registro_estoque`, `id_mp`, `quantidade`, `custo_total`) VALUES
(1, 1, 26, 90, 9),
(2, 2, 26, 61, 6.1);

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
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `veiculos`
--

INSERT INTO `veiculos` (`id_veiculo`, `nome`, `motorista`, `placa`, `km`, `marca`, `imagens`, `avarias`) VALUES
(5, 'Strada', NULL, 'RWP9J28', 43086, 'Fiat', NULL, NULL);

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
) ENGINE=MyISAM AUTO_INCREMENT=127 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `veiculo_condicao`
--

INSERT INTO `veiculo_condicao` (`id_condicao`, `id_veiculo`, `item`, `status`) VALUES
(1, 1, 'Para-brisa sem avaria', 'OK'),
(2, 1, 'Limpadores para-brisa', 'OK'),
(3, 1, 'Água do reservatório do para-brisa', 'OK'),
(4, 1, 'Nível de água do radiador', 'OK'),
(5, 1, 'Nível do óleo do motor', 'OK'),
(6, 1, 'Faróis e sinalizadores de direção', 'OK'),
(7, 1, 'Antena', 'OK'),
(8, 1, 'Documento atualizado', 'OK'),
(9, 1, 'Difusores de ar', 'OK'),
(10, 1, 'Luzes do painel apagadas', 'OK'),
(11, 1, 'Revisão de Km', 'OK'),
(12, 1, 'Buzina', 'OK'),
(13, 1, 'Tapetes', 'OK'),
(14, 1, 'Sem odores', 'OK'),
(15, 1, 'Funcionamento do rádio/multimídia', 'OK'),
(16, 1, 'Porta-luvas limpo', 'OK'),
(17, 1, 'Pneu Dianteiro Esquerdo', 'OK'),
(18, 1, 'Pneu Dianteiro Direito', 'OK'),
(19, 1, 'Pneu Traseiro Esquerdo', 'OK'),
(20, 1, 'Pneu Traseiro Direito', 'OK'),
(21, 1, 'Pneu Estepe', 'OK'),
(22, 2, 'Para-brisa sem avaria', 'OK'),
(23, 2, 'Limpadores para-brisa', 'OK'),
(24, 2, 'Água do reservatório do para-brisa', 'OK'),
(25, 2, 'Nível de água do radiador', 'OK'),
(26, 2, 'Nível do óleo do motor', 'OK'),
(27, 2, 'Faróis e sinalizadores de direção', 'OK'),
(28, 2, 'Antena', 'OK'),
(29, 2, 'Documento atualizado', 'OK'),
(30, 2, 'Difusores de ar', 'OK'),
(31, 2, 'Luzes do painel apagadas', 'OK'),
(32, 2, 'Revisão de Km', 'OK'),
(33, 2, 'Buzina', 'OK'),
(34, 2, 'Tapetes', 'OK'),
(35, 2, 'Sem odores', 'OK'),
(36, 2, 'Funcionamento do rádio/multimídia', 'OK'),
(37, 2, 'Porta-luvas limpo', 'OK'),
(38, 2, 'Pneu Dianteiro Esquerdo', 'OK'),
(39, 2, 'Pneu Dianteiro Direito', 'OK'),
(40, 2, 'Pneu Traseiro Esquerdo', 'OK'),
(41, 2, 'Pneu Traseiro Direito', 'OK'),
(42, 2, 'Pneu Estepe', 'OK'),
(43, 3, 'Para-brisa sem avaria', 'OK'),
(44, 3, 'Limpadores para-brisa', 'OK'),
(45, 3, 'Água do reservatório do para-brisa', 'OK'),
(46, 3, 'Nível de água do radiador', 'OK'),
(47, 3, 'Nível do óleo do motor', 'OK'),
(48, 3, 'Faróis e sinalizadores de direção', 'OK'),
(49, 3, 'Antena', 'OK'),
(50, 3, 'Documento atualizado', 'OK'),
(51, 3, 'Difusores de ar', 'OK'),
(52, 3, 'Luzes do painel apagadas', 'OK'),
(53, 3, 'Revisão de Km', 'OK'),
(54, 3, 'Buzina', 'OK'),
(55, 3, 'Tapetes', 'OK'),
(56, 3, 'Sem odores', 'OK'),
(57, 3, 'Funcionamento do rádio/multimídia', 'OK'),
(58, 3, 'Porta-luvas limpo', 'OK'),
(59, 3, 'Pneu Dianteiro Esquerdo', 'OK'),
(60, 3, 'Pneu Dianteiro Direito', 'OK'),
(61, 3, 'Pneu Traseiro Esquerdo', 'OK'),
(62, 3, 'Pneu Traseiro Direito', 'OK'),
(63, 3, 'Pneu Estepe', 'OK'),
(64, 5, 'Para-brisa sem avaria', 'OK'),
(65, 5, 'Limpadores para-brisa', 'OK'),
(66, 5, 'Água do reservatório do para-brisa', 'OK'),
(67, 5, 'Nível de água do radiador', 'OK'),
(68, 5, 'Nível do óleo do motor', 'OK'),
(69, 5, 'Faróis e sinalizadores de direção', 'OK'),
(70, 5, 'Antena', 'OK'),
(71, 5, 'Documento atualizado', 'OK'),
(72, 5, 'Difusores de ar', 'OK'),
(73, 5, 'Luzes do painel apagadas', 'OK'),
(74, 5, 'Revisão de Km', 'OK'),
(75, 5, 'Buzina', 'OK'),
(76, 5, 'Tapetes', 'OK'),
(77, 5, 'Sem odores', 'OK'),
(78, 5, 'Funcionamento do rádio/multimídia', 'OK'),
(79, 5, 'Porta-luvas limpo', 'OK'),
(80, 5, 'Pneu Dianteiro Esquerdo', 'OK'),
(81, 5, 'Pneu Dianteiro Direito', 'OK'),
(82, 5, 'Pneu Traseiro Esquerdo', 'OK'),
(83, 5, 'Pneu Traseiro Direito', 'OK'),
(84, 5, 'Pneu Estepe', 'OK'),
(85, 6, 'Para-brisa sem avaria', 'NOK'),
(86, 6, 'Limpadores para-brisa', 'NOK'),
(87, 6, 'Água do reservatório do para-brisa', 'NOK'),
(88, 6, 'Nível de água do radiador', 'NOK'),
(89, 6, 'Nível do óleo do motor', 'NOK'),
(90, 6, 'Faróis e sinalizadores de direção', 'NOK'),
(91, 6, 'Antena', 'NOK'),
(92, 6, 'Documento atualizado', 'NOK'),
(93, 6, 'Difusores de ar', 'NOK'),
(94, 6, 'Luzes do painel apagadas', 'NOK'),
(95, 6, 'Revisão de Km', 'NOK'),
(96, 6, 'Buzina', 'NOK'),
(97, 6, 'Tapetes', 'NOK'),
(98, 6, 'Sem odores', 'NOK'),
(99, 6, 'Funcionamento do rádio/multimídia', 'NOK'),
(100, 6, 'Porta-luvas limpo', 'NOK'),
(101, 6, 'Pneu Dianteiro Esquerdo', 'NOK'),
(102, 6, 'Pneu Dianteiro Direito', 'NOK'),
(103, 6, 'Pneu Traseiro Esquerdo', 'NOK'),
(104, 6, 'Pneu Traseiro Direito', 'NOK'),
(105, 6, 'Pneu Estepe', 'NOK'),
(106, 7, 'Para-brisa sem avaria', 'NOK'),
(107, 7, 'Limpadores para-brisa', 'NOK'),
(108, 7, 'Água do reservatório do para-brisa', 'NOK'),
(109, 7, 'Nível de água do radiador', 'NOK'),
(110, 7, 'Nível do óleo do motor', 'NOK'),
(111, 7, 'Faróis e sinalizadores de direção', 'NOK'),
(112, 7, 'Antena', 'NOK'),
(113, 7, 'Documento atualizado', 'NOK'),
(114, 7, 'Difusores de ar', 'NOK'),
(115, 7, 'Luzes do painel apagadas', 'NOK'),
(116, 7, 'Revisão de Km', 'NOK'),
(117, 7, 'Buzina', 'NOK'),
(118, 7, 'Tapetes', 'NOK'),
(119, 7, 'Sem odores', 'NOK'),
(120, 7, 'Funcionamento do rádio/multimídia', 'NOK'),
(121, 7, 'Porta-luvas limpo', 'NOK'),
(122, 7, 'Pneu Dianteiro Esquerdo', 'NOK'),
(123, 7, 'Pneu Dianteiro Direito', 'NOK'),
(124, 7, 'Pneu Traseiro Esquerdo', 'NOK'),
(125, 7, 'Pneu Traseiro Direito', 'NOK'),
(126, 7, 'Pneu Estepe', 'NOK');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
