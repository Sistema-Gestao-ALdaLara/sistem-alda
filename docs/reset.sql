-- Desativar verificações de chave estrangeira temporariamente
SET FOREIGN_KEY_CHECKS = 0;

-- Limpar tabelas de relacionamento primeiro
TRUNCATE TABLE `escoladb`.`professor_tem_disciplina`;
TRUNCATE TABLE `escoladb`.`professor_tem_turma`;
TRUNCATE TABLE `escoladb`.`professor_tem_turma1`;
TRUNCATE TABLE `escoladb`.`materiais_apoio_tem_usuario`;

-- Limpar tabelas de dados acadêmicos
TRUNCATE TABLE `escoladb`.`nota`;
TRUNCATE TABLE `escoladb`.`frequencia_aluno`;
TRUNCATE TABLE `escoladb`.`historico_professor`;
TRUNCATE TABLE `escoladb`.`matricula`;
TRUNCATE TABLE `escoladb`.`cronograma_aula`;
TRUNCATE TABLE `escoladb`.`plano_ensino`;
TRUNCATE TABLE `escoladb`.`materiais_apoio`;
TRUNCATE TABLE `escoladb`.`comunicado`;
TRUNCATE TABLE `escoladb`.`password_reset`;
TRUNCATE TABLE `escoladb`.`documentos_administrativos`;

-- Limpar tabelas de entidades principais
TRUNCATE TABLE `escoladb`.`aluno`;
TRUNCATE TABLE `escoladb`.`turma`;
TRUNCATE TABLE `escoladb`.`disciplina`;
TRUNCATE TABLE `escoladb`.`professor`;
TRUNCATE TABLE `escoladb`.`coordenador`;
TRUNCATE TABLE `escoladb`.`secretaria`;

-- Manter os usuários padrão do sistema
-- Desativar o modo seguro temporariamente
SET SQL_SAFE_UPDATES = 0;

-- Executar seu DELETE
DELETE FROM `escoladb`.`usuario` 
WHERE `tipo` IN ('aluno', 'professor', 'coordenador', 'secretaria') 
AND `email` NOT IN ('secretaria@aldalara.com', 'diretor@gmail.com', 'pedagogico@gmail.com', 'secretaria@gmail.com');

-- Reativar o modo seguro
SET SQL_SAFE_UPDATES = 1;
-- Reativar verificações de chave estrangeira
SET FOREIGN_KEY_CHECKS = 1;

-- Reiniciar auto-incrementos para manter a organização
ALTER TABLE `escoladb`.`usuario` AUTO_INCREMENT = 100;
ALTER TABLE `escoladb`.`aluno` AUTO_INCREMENT = 1;
ALTER TABLE `escoladb`.`professor` AUTO_INCREMENT = 1;
ALTER TABLE `escoladb`.`coordenador` AUTO_INCREMENT = 1;
ALTER TABLE `escoladb`.`secretaria` AUTO_INCREMENT = 1;
ALTER TABLE `escoladb`.`turma` AUTO_INCREMENT = 1;
ALTER TABLE `escoladb`.`disciplina` AUTO_INCREMENT = 1;
ALTER TABLE `escoladb`.`nota` AUTO_INCREMENT = 1;
ALTER TABLE `escoladb`.`matricula` AUTO_INCREMENT = 1;