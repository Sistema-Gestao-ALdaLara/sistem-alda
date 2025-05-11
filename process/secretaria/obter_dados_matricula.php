<?php
require_once '../../database/conexao.php';

// Configurar cabeçalho para resposta JSON
header('Content-Type: application/json');

// Verificar se o ID da matrícula foi enviado
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ID da matrícula não fornecido ou inválido.'
    ]);
    exit;
}

$id_matricula = (int)$_GET['id'];

try {
    // Consulta para obter todos os dados da matrícula
    $query = "SELECT 
                m.id_matricula, m.numero_matricula, m.ano_letivo, m.data_matricula, 
                m.status_matricula, m.comprovativo_pagamento, m.turma_id_turma,
                a.id_aluno, a.data_nascimento, a.genero, a.naturalidade, 
                a.nacionalidade, a.municipio, a.nome_encarregado, a.contacto_encarregado,
                u.id_usuario, u.nome, u.email, u.bi_numero, u.foto_perfil,
                t.id_turma, t.nome AS nome_turma, t.classe, t.turno,
                c.id_curso, c.nome AS nome_curso
              FROM matricula m
              JOIN aluno a ON m.aluno_id_aluno = a.id_aluno
              JOIN usuario u ON a.usuario_id_usuario = u.id_usuario
              LEFT JOIN turma t ON m.turma_id_turma = t.id_turma
              LEFT JOIN curso c ON t.curso_id_curso = c.id_curso
              WHERE m.id_matricula = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_matricula);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Matrícula não encontrada.'
        ]);
        exit;
    }
    
    $matricula = $result->fetch_assoc();
    
    // Formatar os dados para retorno
    $dados = [
        'id_matricula' => $matricula['id_matricula'],
        'numero_matricula' => $matricula['numero_matricula'],
        'ano_letivo' => $matricula['ano_letivo'],
        'data_matricula' => $matricula['data_matricula'],
        'status_matricula' => $matricula['status_matricula'],
        'comprovativo_pagamento' => $matricula['comprovativo_pagamento'],
        'id_turma' => $matricula['turma_id_turma'],
        'id_curso' => $matricula['id_curso'],
        
        // Dados do aluno
        'id_aluno' => $matricula['id_aluno'],
        'data_nascimento' => $matricula['data_nascimento'],
        'genero' => $matricula['genero'],
        'naturalidade' => $matricula['naturalidade'],
        'nacionalidade' => $matricula['nacionalidade'],
        'municipio' => $matricula['municipio'],
        'nome_encarregado' => $matricula['nome_encarregado'],
        'contacto_encarregado' => $matricula['contacto_encarregado'],
        
        // Dados do usuário
        'id_usuario' => $matricula['id_usuario'],
        'nome' => $matricula['nome'],
        'email' => $matricula['email'],
        'bi_numero' => $matricula['bi_numero'],
        'foto_perfil' => $matricula['foto_perfil'],
        
        // Dados da turma
        'turma_nome' => $matricula['nome_turma'],
        'classe' => $matricula['classe'],
        'turno' => $matricula['turno'],
        
        // Dados do curso
        'curso_nome' => $matricula['nome_curso']
    ];
    
    echo json_encode([
        'success' => true,
        'dados' => $dados
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar dados da matrícula: ' . $e->getMessage()
    ]);
}
?>