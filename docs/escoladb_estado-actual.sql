-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 06, 2025 at 06:26 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `escoladb`
--

-- --------------------------------------------------------

--
-- Table structure for table `aluno`
--

CREATE TABLE `aluno` (
  `id_aluno` int(11) NOT NULL,
  `data_nascimento` date NOT NULL,
  `genero` varchar(10) NOT NULL,
  `naturalidade` varchar(100) NOT NULL,
  `nacionalidade` varchar(100) NOT NULL,
  `municipio` varchar(100) NOT NULL,
  `nome_encarregado` varchar(100) NOT NULL,
  `contacto_encarregado` varchar(20) NOT NULL,
  `numero_matricula` varchar(20) NOT NULL,
  `ano_letivo` year(4) NOT NULL,
  `usuario_id_usuario` int(11) NOT NULL,
  `turma_id_turma` int(11) NOT NULL,
  `curso_id_curso` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `aluno`
--

INSERT INTO `aluno` (`id_aluno`, `data_nascimento`, `genero`, `naturalidade`, `nacionalidade`, `municipio`, `nome_encarregado`, `contacto_encarregado`, `numero_matricula`, `ano_letivo`, `usuario_id_usuario`, `turma_id_turma`, `curso_id_curso`) VALUES
(1, '0000-00-00', '', '', '', '', '', '', 'AL-2025-4002', '2025', 1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `comunicado`
--

CREATE TABLE `comunicado` (
  `id_comunicado` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `mensagem` text NOT NULL,
  `data` timestamp NOT NULL DEFAULT current_timestamp(),
  `usuario_id_usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `coordenador`
--

CREATE TABLE `coordenador` (
  `id_coordenador` int(11) NOT NULL,
  `usuario_id_usuario` int(11) NOT NULL,
  `curso_id_curso` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `coordenador`
--

INSERT INTO `coordenador` (`id_coordenador`, `usuario_id_usuario`, `curso_id_curso`) VALUES
(1, 4, 1);

-- --------------------------------------------------------

--
-- Table structure for table `cronograma_aula`
--

CREATE TABLE `cronograma_aula` (
  `id_cronograma_aula` int(11) NOT NULL,
  `dia_semana` enum('segunda','terca','quarta','quinta','sexta','sabado') NOT NULL,
  `horario_inicio` time NOT NULL,
  `horario_fim` time NOT NULL,
  `sala` varchar(10) NOT NULL,
  `id_professor` int(11) NOT NULL,
  `turma_id_turma` int(11) NOT NULL,
  `id_disciplina` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `curso`
--

CREATE TABLE `curso` (
  `id_curso` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `curso`
--

INSERT INTO `curso` (`id_curso`, `nome`) VALUES
(1, 'Tecnico de Informatica'),
(2, 'desenhador projetista'),
(3, 'eletricidade ');

-- --------------------------------------------------------

--
-- Table structure for table `disciplina`
--

CREATE TABLE `disciplina` (
  `id_disciplina` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `curso_id_curso` int(11) NOT NULL,
  `professor_id_professor` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `disciplina`
--

INSERT INTO `disciplina` (`id_disciplina`, `nome`, `curso_id_curso`, `professor_id_professor`) VALUES
(1, 'Matematica', 1, NULL),
(6, 'TLP', 1, 2),
(7, 'Electrotecnia', 1, NULL),
(8, 'Lingua Portuguesa', 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `frequencia_aluno`
--

CREATE TABLE `frequencia_aluno` (
  `id_frequencia_aluno` int(11) NOT NULL,
  `data_aula` date NOT NULL,
  `presenca` enum('presente','ausente','justificado') NOT NULL DEFAULT 'ausente',
  `aluno_id_aluno` int(11) NOT NULL,
  `disciplina_id_disciplina` int(11) NOT NULL,
  `turma_id_turma` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `historico_professor`
--

CREATE TABLE `historico_professor` (
  `id_historico` int(11) NOT NULL,
  `data_inicio` date NOT NULL,
  `data_fim` date DEFAULT NULL,
  `descricao` text NOT NULL,
  `professor_id_professor` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `materiais_apoio`
--

CREATE TABLE `materiais_apoio` (
  `id_material` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `descricao` text NOT NULL,
  `caminho_arquivo` varchar(255) NOT NULL,
  `id_disciplina` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `materiais_apoio_tem_usuario`
--

CREATE TABLE `materiais_apoio_tem_usuario` (
  `materiais_apoio_id_material` int(11) NOT NULL,
  `usuario_id_usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `matricula`
--

CREATE TABLE `matricula` (
  `id_matricula` int(11) NOT NULL,
  `ano_letivo` year(4) NOT NULL,
  `classe` varchar(10) NOT NULL,
  `turno` varchar(20) NOT NULL,
  `numero_matricula` varchar(20) NOT NULL,
  `data_matricula` date NOT NULL,
  `turma_id_turma` int(11) NOT NULL,
  `aluno_id_aluno` int(11) NOT NULL,
  `curso_id_curso` int(11) NOT NULL,
  `status_matricula` enum('ativa','trancada','cancelada') NOT NULL DEFAULT 'ativa',
  `comprovativo_pagamento` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `matricula`
--

INSERT INTO `matricula` (`id_matricula`, `ano_letivo`, `classe`, `turno`, `numero_matricula`, `data_matricula`, `turma_id_turma`, `aluno_id_aluno`, `curso_id_curso`, `status_matricula`, `comprovativo_pagamento`) VALUES
(1, '2025', '', '', '', '2025-04-04', 7, 1, 2, 'ativa', '');

-- --------------------------------------------------------

--
-- Table structure for table `nota`
--

CREATE TABLE `nota` (
  `id_nota` int(11) NOT NULL,
  `nota` decimal(5,2) NOT NULL,
  `data` date NOT NULL,
  `aluno_id_aluno` int(11) NOT NULL,
  `disciplina_id_disciplina` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset`
--

CREATE TABLE `password_reset` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expira_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `professor`
--

CREATE TABLE `professor` (
  `id_professor` int(11) NOT NULL,
  `usuario_id_usuario` int(11) NOT NULL,
  `curso_id_curso` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `professor`
--

INSERT INTO `professor` (`id_professor`, `usuario_id_usuario`, `curso_id_curso`) VALUES
(1, 2, 1),
(2, 3, 1);

-- --------------------------------------------------------

--
-- Table structure for table `professor_tem_turma`
--

CREATE TABLE `professor_tem_turma` (
  `professor_id_professor` int(11) NOT NULL,
  `turma_id_turma` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `secretaria`
--

CREATE TABLE `secretaria` (
  `id_secretaria` int(11) NOT NULL,
  `setor` varchar(50) NOT NULL,
  `pode_registrar` tinyint(1) NOT NULL DEFAULT 0,
  `usuario_id_usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `secretaria`
--

INSERT INTO `secretaria` (`id_secretaria`, `setor`, `pode_registrar`, `usuario_id_usuario`) VALUES
(1, 'Administrativo', 1, 8),
(3, 'Administrativo', 0, 9);

-- --------------------------------------------------------

--
-- Table structure for table `turma`
--

CREATE TABLE `turma` (
  `id_turma` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `curso_id_curso` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `turma`
--

INSERT INTO `turma` (`id_turma`, `nome`, `curso_id_curso`) VALUES
(1, 'I13AT', 1),
(7, 'C10AM', 2),
(8, 'E10AM', 3);

-- --------------------------------------------------------

--
-- Table structure for table `usuario`
--

CREATE TABLE `usuario` (
  `id_usuario` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `bi_numero` varchar(14) NOT NULL,
  `tipo` enum('diretor_geral','diretor_pedagogico','coordenador','professor','aluno','secretaria') NOT NULL,
  `status` enum('ativo','inativo') NOT NULL DEFAULT 'ativo',
  `foto_perfil` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `usuario`
--

INSERT INTO `usuario` (`id_usuario`, `nome`, `email`, `senha`, `bi_numero`, `tipo`, `status`, `foto_perfil`) VALUES
(1, 'Flavio Pinto Garcia', 'flaviofuxi86@gmail.com', 'senha_temp', '009097235LA046', 'aluno', 'ativo', NULL),
(3, 'Flavio', 'flaviofuxe86@gmail.com', '$2y$10$zqGQ2i3HS1q6sgo6V4jhTuKekEIEhNgUJDPcqGK8Vp3BnTvvIGRLy', '005097235LA046', 'professor', 'ativo', NULL),
(4, 'flavio', 'kunai86@gmail.com', '$2y$10$4PGGIHAn.aEV5gTmO14yCukzyj7KwvZiD.OjEd7gDDX0hVa6208wu', '009097235LA044', 'coordenador', 'ativo', NULL),
(5, 'Garcia', 'flaviofuxe@gmail.com', '$2y$10$6dLGw4uZ/GUtCzUF1AaeG.BshFoi5Bxf5O5rObuPbW.kLShfbSHuC', '005097235LA049', 'diretor_geral', 'ativo', NULL),
(6, 'Pinto', 'flaviofux@gmail.com', '$2y$10$R12Y7p4vBXKP5NdsxaLKQOjCOUgPi0ad9E6POBru0UbUboywExlzC', '009097235LA046', 'diretor_pedagogico', 'ativo', NULL),
(9, 'Secretaria Padrão', 'secretaria@gmail.com', '$2y$10$1M7SbPlAk4Zm64m2jG4vPOZcDRwHiidDB.Forp/kkyRJhXmOJ7cai', '123456789LA123', 'secretaria', 'ativo', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `aluno`
--
ALTER TABLE `aluno`
  ADD PRIMARY KEY (`id_aluno`),
  ADD KEY `fk_usuario1_idx` (`usuario_id_usuario`),
  ADD KEY `fk_turma1_idx` (`turma_id_turma`),
  ADD KEY `fk_curso1_idx` (`curso_id_curso`);

--
-- Indexes for table `comunicado`
--
ALTER TABLE `comunicado`
  ADD PRIMARY KEY (`id_comunicado`),
  ADD KEY `fk_usuario5_idx` (`usuario_id_usuario`);

--
-- Indexes for table `coordenador`
--
ALTER TABLE `coordenador`
  ADD PRIMARY KEY (`id_coordenador`),
  ADD KEY `fk_usuario2_idx` (`usuario_id_usuario`),
  ADD KEY `fk_curso2_idx` (`curso_id_curso`);

--
-- Indexes for table `cronograma_aula`
--
ALTER TABLE `cronograma_aula`
  ADD PRIMARY KEY (`id_cronograma_aula`),
  ADD KEY `fk_professor3_idx` (`id_professor`),
  ADD KEY `fk_turma7_idx` (`turma_id_turma`),
  ADD KEY `fk_disciplina5_idx` (`id_disciplina`);

--
-- Indexes for table `curso`
--
ALTER TABLE `curso`
  ADD PRIMARY KEY (`id_curso`);

--
-- Indexes for table `disciplina`
--
ALTER TABLE `disciplina`
  ADD PRIMARY KEY (`id_disciplina`,`curso_id_curso`),
  ADD UNIQUE KEY `id_disciplina_UNIQUE` (`id_disciplina`),
  ADD KEY `fk_curso5_idx` (`curso_id_curso`),
  ADD KEY `fk_professor5_idx` (`professor_id_professor`);

--
-- Indexes for table `frequencia_aluno`
--
ALTER TABLE `frequencia_aluno`
  ADD PRIMARY KEY (`id_frequencia_aluno`),
  ADD KEY `fk_aluno2_idx` (`aluno_id_aluno`),
  ADD KEY `fk_disciplina4_idx` (`disciplina_id_disciplina`),
  ADD KEY `fk_turma4_idx` (`turma_id_turma`);

--
-- Indexes for table `historico_professor`
--
ALTER TABLE `historico_professor`
  ADD PRIMARY KEY (`id_historico`),
  ADD KEY `fk_professor2_idx` (`professor_id_professor`);

--
-- Indexes for table `materiais_apoio`
--
ALTER TABLE `materiais_apoio`
  ADD PRIMARY KEY (`id_material`),
  ADD KEY `fk_disciplina2_idx` (`id_disciplina`);

--
-- Indexes for table `materiais_apoio_tem_usuario`
--
ALTER TABLE `materiais_apoio_tem_usuario`
  ADD PRIMARY KEY (`materiais_apoio_id_material`,`usuario_id_usuario`),
  ADD KEY `fk_materiais_apoio1_idx` (`materiais_apoio_id_material`),
  ADD KEY `fk_usuario6_idx` (`usuario_id_usuario`);

--
-- Indexes for table `matricula`
--
ALTER TABLE `matricula`
  ADD PRIMARY KEY (`id_matricula`),
  ADD UNIQUE KEY `numero_matricula` (`numero_matricula`),
  ADD KEY `fk_turma6_idx` (`turma_id_turma`),
  ADD KEY `fk_aluno3_idx` (`aluno_id_aluno`),
  ADD KEY `fk_curso6_idx` (`curso_id_curso`);

--
-- Indexes for table `nota`
--
ALTER TABLE `nota`
  ADD PRIMARY KEY (`id_nota`),
  ADD KEY `fk_aluno1_idx` (`aluno_id_aluno`),
  ADD KEY `fk_disciplina1_idx` (`disciplina_id_disciplina`);

--
-- Indexes for table `password_reset`
--
ALTER TABLE `password_reset`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `professor`
--
ALTER TABLE `professor`
  ADD PRIMARY KEY (`id_professor`),
  ADD KEY `fk_usuario3_idx` (`usuario_id_usuario`),
  ADD KEY `fk_curso3_idx` (`curso_id_curso`);

--
-- Indexes for table `professor_tem_turma`
--
ALTER TABLE `professor_tem_turma`
  ADD PRIMARY KEY (`professor_id_professor`,`turma_id_turma`),
  ADD KEY `idx_turma2` (`turma_id_turma`),
  ADD KEY `idx_professor2` (`professor_id_professor`);

--
-- Indexes for table `secretaria`
--
ALTER TABLE `secretaria`
  ADD PRIMARY KEY (`id_secretaria`),
  ADD KEY `fk_usuario_idx` (`usuario_id_usuario`);

--
-- Indexes for table `turma`
--
ALTER TABLE `turma`
  ADD PRIMARY KEY (`id_turma`),
  ADD KEY `fk_curso4_idx` (`curso_id_curso`);

--
-- Indexes for table `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id_usuario`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `aluno`
--
ALTER TABLE `aluno`
  MODIFY `id_aluno` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `comunicado`
--
ALTER TABLE `comunicado`
  MODIFY `id_comunicado` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `coordenador`
--
ALTER TABLE `coordenador`
  MODIFY `id_coordenador` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `cronograma_aula`
--
ALTER TABLE `cronograma_aula`
  MODIFY `id_cronograma_aula` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `curso`
--
ALTER TABLE `curso`
  MODIFY `id_curso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `disciplina`
--
ALTER TABLE `disciplina`
  MODIFY `id_disciplina` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `frequencia_aluno`
--
ALTER TABLE `frequencia_aluno`
  MODIFY `id_frequencia_aluno` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `historico_professor`
--
ALTER TABLE `historico_professor`
  MODIFY `id_historico` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `materiais_apoio`
--
ALTER TABLE `materiais_apoio`
  MODIFY `id_material` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `matricula`
--
ALTER TABLE `matricula`
  MODIFY `id_matricula` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `nota`
--
ALTER TABLE `nota`
  MODIFY `id_nota` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_reset`
--
ALTER TABLE `password_reset`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `professor`
--
ALTER TABLE `professor`
  MODIFY `id_professor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `secretaria`
--
ALTER TABLE `secretaria`
  MODIFY `id_secretaria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `turma`
--
ALTER TABLE `turma`
  MODIFY `id_turma` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `aluno`
--
ALTER TABLE `aluno`
  ADD CONSTRAINT `fk_curso1` FOREIGN KEY (`curso_id_curso`) REFERENCES `curso` (`id_curso`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_turma1` FOREIGN KEY (`turma_id_turma`) REFERENCES `turma` (`id_turma`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_usuario1` FOREIGN KEY (`usuario_id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `comunicado`
--
ALTER TABLE `comunicado`
  ADD CONSTRAINT `fk_usuario5` FOREIGN KEY (`usuario_id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `coordenador`
--
ALTER TABLE `coordenador`
  ADD CONSTRAINT `fk_curso2` FOREIGN KEY (`curso_id_curso`) REFERENCES `curso` (`id_curso`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_usuario2` FOREIGN KEY (`usuario_id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `cronograma_aula`
--
ALTER TABLE `cronograma_aula`
  ADD CONSTRAINT `fk_disciplina5` FOREIGN KEY (`id_disciplina`) REFERENCES `disciplina` (`id_disciplina`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_professor3` FOREIGN KEY (`id_professor`) REFERENCES `professor` (`id_professor`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_turma7` FOREIGN KEY (`turma_id_turma`) REFERENCES `turma` (`id_turma`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `disciplina`
--
ALTER TABLE `disciplina`
  ADD CONSTRAINT `fk_curso5` FOREIGN KEY (`curso_id_curso`) REFERENCES `curso` (`id_curso`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_professor5` FOREIGN KEY (`professor_id_professor`) REFERENCES `professor` (`id_professor`) ON DELETE SET NULL ON UPDATE NO ACTION;

--
-- Constraints for table `frequencia_aluno`
--
ALTER TABLE `frequencia_aluno`
  ADD CONSTRAINT `fk_aluno2` FOREIGN KEY (`aluno_id_aluno`) REFERENCES `aluno` (`id_aluno`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_disciplina4` FOREIGN KEY (`disciplina_id_disciplina`) REFERENCES `disciplina` (`id_disciplina`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_turma4` FOREIGN KEY (`turma_id_turma`) REFERENCES `turma` (`id_turma`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `historico_professor`
--
ALTER TABLE `historico_professor`
  ADD CONSTRAINT `fk_professor2` FOREIGN KEY (`professor_id_professor`) REFERENCES `professor` (`id_professor`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `materiais_apoio`
--
ALTER TABLE `materiais_apoio`
  ADD CONSTRAINT `fk_disciplina2` FOREIGN KEY (`id_disciplina`) REFERENCES `disciplina` (`id_disciplina`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `materiais_apoio_tem_usuario`
--
ALTER TABLE `materiais_apoio_tem_usuario`
  ADD CONSTRAINT `fk_materiais_apoio1` FOREIGN KEY (`materiais_apoio_id_material`) REFERENCES `materiais_apoio` (`id_material`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_usuario6` FOREIGN KEY (`usuario_id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `matricula`
--
ALTER TABLE `matricula`
  ADD CONSTRAINT `fk_aluno3` FOREIGN KEY (`aluno_id_aluno`) REFERENCES `aluno` (`id_aluno`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_curso6` FOREIGN KEY (`curso_id_curso`) REFERENCES `curso` (`id_curso`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_turma6` FOREIGN KEY (`turma_id_turma`) REFERENCES `turma` (`id_turma`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `nota`
--
ALTER TABLE `nota`
  ADD CONSTRAINT `fk_aluno1` FOREIGN KEY (`aluno_id_aluno`) REFERENCES `aluno` (`id_aluno`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_disciplina1` FOREIGN KEY (`disciplina_id_disciplina`) REFERENCES `disciplina` (`id_disciplina`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `professor`
--
ALTER TABLE `professor`
  ADD CONSTRAINT `fk_curso3` FOREIGN KEY (`curso_id_curso`) REFERENCES `curso` (`id_curso`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_usuario3` FOREIGN KEY (`usuario_id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `professor_tem_turma`
--
ALTER TABLE `professor_tem_turma`
  ADD CONSTRAINT `fk_professor_tem_turma_professor` FOREIGN KEY (`professor_id_professor`) REFERENCES `professor` (`id_professor`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_professor_tem_turma_turma` FOREIGN KEY (`turma_id_turma`) REFERENCES `turma` (`id_turma`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `secretaria`
--
ALTER TABLE `secretaria`
  ADD CONSTRAINT `fk_usuario` FOREIGN KEY (`usuario_id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `turma`
--
ALTER TABLE `turma`
  ADD CONSTRAINT `fk_curso4` FOREIGN KEY (`curso_id_curso`) REFERENCES `curso` (`id_curso`) ON DELETE NO ACTION ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
