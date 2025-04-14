-- Ordem importante devido às restrições de chave estrangeira
SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE materiais_apoio_tem_usuario;
TRUNCATE TABLE professor_tem_turma1;
TRUNCATE TABLE professor_tem_turma;
TRUNCATE TABLE matricula;
TRUNCATE TABLE historico_professor;
TRUNCATE TABLE cronograma_aula;
TRUNCATE TABLE materiais_apoio;
TRUNCATE TABLE frequencia_aluno;
TRUNCATE TABLE nota;
TRUNCATE TABLE comunicado;
TRUNCATE TABLE disciplina;
TRUNCATE TABLE aluno;
TRUNCATE TABLE professor;
TRUNCATE TABLE coordenador;
TRUNCATE TABLE secretaria;
TRUNCATE TABLE turma;
TRUNCATE TABLE curso;
TRUNCATE TABLE password_reset;
TRUNCATE TABLE usuario;

SET FOREIGN_KEY_CHECKS = 1;