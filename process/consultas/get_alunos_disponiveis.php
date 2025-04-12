<?php
require_once '../../database/conexao.php';

header('Content-Type: application/json');

if (isset($_GET['id_turma']) && isset($_GET['ano_letivo'])) {
    $id_turma = intval($_GET['id_turma']);
    $ano_letivo = intval($_GET['ano_letivo']);
    
    // Primeiro obtém o curso da turma
    $query_curso = "SELECT curso_id_curso FROM turma WHERE id_turma = ?";
    $stmt = $conn->prepare($query_curso);
    $stmt->bind_param("i", $id_turma);
    $stmt->execute();
    $result = $stmt->get_result();
    $turma = $result->fetch_assoc();
    $curso_id = $turma['curso_id_curso'];
    
    // Busca alunos do mesmo curso que não estão na turma
    $query = "SELECT 
                 m.id_matricula,
                 u.nome
              FROM matricula m
              JOIN aluno a ON m.aluno_id_aluno = a.id_aluno
              JOIN usuario u ON a.usuario_id_usuario = u.id_usuario
              WHERE m.curso_id_curso = ? 
                AND m.ano_letivo = ?
                AND (m.turma_id_turma IS NULL OR m.turma_id_turma != ?)
              ORDER BY u.nome";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iii", $curso_id, $ano_letivo, $id_turma);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $alunos = [];
    while ($row = $result->fetch_assoc()) {
        $alunos[] = $row;
    }
    
    echo json_encode($alunos);
} else {
    echo json_encode(['error' => 'Parâmetros incompletos']);
}

$conn->close();
?>