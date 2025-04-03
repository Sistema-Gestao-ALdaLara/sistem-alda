<?php
require_once "../auth/permissoes.php";
verificarPermissao(['secretaria']);

require_once "../config/conexao.php";

header('Content-Type: application/json');

try {
    $dados = $_POST;
    
    // Validações básicas
    if (empty($dados['nome']) {
        throw new Exception('Nome é obrigatório');
    }
    
    if (!preg_match('/^[0-9]{9}[A-Z]{2}[0-9]{3}$/', $dados['bi_numero'])) {
        throw new Exception('Número de BI inválido');
    }
    
    if (empty($dados['email']) {
        throw new Exception('Email é obrigatório');
    }
    
    if (empty($dados['id_curso'])) {
        throw new Exception('Curso é obrigatório');
    }
    
    // Iniciar transação
    $pdo->beginTransaction();
    
    if (empty($dados['alunoId'])) {
        // Inserir novo aluno
        // 1. Primeiro inserir o usuário
        $stmtUsuario = $pdo->prepare("INSERT INTO usuario (nome, email, bi_numero, tipo) VALUES (?, ?, ?, 'aluno')");
        $stmtUsuario->execute([$dados['nome'], $dados['email'], $dados['bi_numero']]);
        $id_usuario = $pdo->lastInsertId();
        
        // 2. Inserir o aluno
        $stmtAluno = $pdo->prepare("INSERT INTO aluno 
            (id_usuario, numero_matricula, id_curso, id_turma, ano_letivo) 
            VALUES (?, ?, ?, ?, ?)");
        $stmtAluno->execute([
            $id_usuario,
            $dados['numero_matricula'],
            $dados['id_curso'],
            $dados['id_turma'] ?: null,
            $dados['ano_letivo']
        ]);
        
        $mensagem = 'Aluno cadastrado com sucesso';
    } else {
        // Atualizar aluno existente
        // 1. Primeiro atualizar o usuário
        $stmtUsuario = $pdo->prepare("UPDATE usuario SET nome = ?, email = ?, bi_numero = ? WHERE id_usuario = (SELECT id_usuario FROM aluno WHERE id_aluno = ?)");
        $stmtUsuario->execute([$dados['nome'], $dados['email'], $dados['bi_numero'], $dados['alunoId']]);
        
        // 2. Atualizar o aluno
        $stmtAluno = $pdo->prepare("UPDATE aluno SET 
            numero_matricula = ?,
            id_curso = ?,
            id_turma = ?,
            ano_letivo = ?
            WHERE id_aluno = ?");
        $stmtAluno->execute([
            $dados['numero_matricula'],
            $dados['id_curso'],
            $dados['id_turma'] ?: null,
            $dados['ano_letivo'],
            $dados['alunoId']
        ]);
        
        $mensagem = 'Aluno atualizado com sucesso';
    }
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => $mensagem
    ]);
    
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>