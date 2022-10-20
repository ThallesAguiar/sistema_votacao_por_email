-- --------------------------------------------------------
-- Servidor:                     192.168.2.100
-- Versão do servidor:           5.5.62-0+deb8u1 - (Debian)
-- OS do Servidor:               debian-linux-gnu
-- HeidiSQL Versão:              11.3.0.6295
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Copiando estrutura do banco de dados para votacao
CREATE DATABASE IF NOT EXISTS `votacao` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `votacao`;

-- Copiando estrutura para tabela votacao.candidatos
CREATE TABLE IF NOT EXISTS `candidatos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) DEFAULT NULL,
  `cargo` varchar(100) DEFAULT NULL,
  `chapa` int(11) NOT NULL COMMENT 'guarda a chapa criada',
  PRIMARY KEY (`id`),
  KEY `chapa_fk_candidatos` (`chapa`),
  CONSTRAINT `chapa_fk_candidatos` FOREIGN KEY (`chapa`) REFERENCES `chapas` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8 COMMENT='tabela que guarda os candidatos';

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela votacao.chapas
CREATE TABLE IF NOT EXISTS `chapas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(50) NOT NULL,
  `periodo` int(11) NOT NULL,
  `foto` text,
  PRIMARY KEY (`id`),
  KEY `periodo_fk_chapa` (`periodo`),
  CONSTRAINT `periodo_fk_chapa` FOREIGN KEY (`periodo`) REFERENCES `periodos` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela votacao.eleitores
CREATE TABLE IF NOT EXISTS `eleitores` (
  `categoria` enum('1','2','3') NOT NULL COMMENT 'identifica a categoria que o eleitor pertence, 1:professor, 2:aluno ou 3:funcionario',
  `email` varchar(100) NOT NULL COMMENT 'com esse email o eleitor pode votar',
  `periodo` int(11) NOT NULL COMMENT 'valor para ajudar a filtrar votações futuros. Caso o usuario não votou e tem outra eleição ocorrendo, ajuda o sistema a buscar os eleitores da eleição atual ',
  `data_votacao` datetime DEFAULT NULL COMMENT 'data/hora que ocorreu a ação do voto',
  `votou` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'campo que identifica se o eleitor já votou, por padrão ele inicia com zero, que é false',
  KEY `periodo_fk_eleitores` (`periodo`),
  CONSTRAINT `periodo_fk_eleitores` FOREIGN KEY (`periodo`) REFERENCES `periodos` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='tabela de eleitores que tem permissão para votar no sistema';

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela votacao.emails
CREATE TABLE IF NOT EXISTS `emails` (
  `remetente` varchar(50) NOT NULL COMMENT 'quem dispara o email',
  `descricao` varchar(250) NOT NULL COMMENT 'descrição do que foi disparado, sem guardar informação de voto',
  `destinatario` varchar(50) NOT NULL COMMENT 'quem recebe o email',
  `enviado` datetime NOT NULL COMMENT 'quando disparado'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='controle de disparo de email';

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela votacao.periodos
CREATE TABLE IF NOT EXISTS `periodos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `descricao` varchar(100) DEFAULT NULL COMMENT 'descreve o periodo de eleição',
  `inicio` datetime NOT NULL COMMENT 'quando inicia o periodo de votação',
  `fim` datetime NOT NULL COMMENT 'quando finaliza o periodo de votação',
  `resultado` datetime NOT NULL COMMENT 'data/horário da divulgação do resultado',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COMMENT='controle de periodo ';

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela votacao.votos
CREATE TABLE IF NOT EXISTS `votos` (
  `chapa` int(11) NOT NULL COMMENT 'identificação da chapa',
  `categoria_eleitor` enum('1','2','3') NOT NULL,
  KEY `FK_votos_chapas` (`chapa`),
  CONSTRAINT `FK_votos_chapas` FOREIGN KEY (`chapa`) REFERENCES `chapas` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='tabela que guarda os votos processados';

-- Exportação de dados foi desmarcado.

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
