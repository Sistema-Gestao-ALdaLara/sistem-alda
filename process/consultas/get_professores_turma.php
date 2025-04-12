<?php
require_once '../../database/conexao.php';

header('Content-Type: application/json');

if (isset($_GET['id_turma'])) {
    $id_turma = intval($_GET['id_turma']);
    
    // Primeiro obtém o curso da turma
    $query_curso = "SELECT curso_id_curso FROM turma WHERE id_turma = ?";
    $stmt = $conn->prepare($query_curso);
    $stmt->bind_param("i", $id_turma);
    $stmt->execute();
    $result = $stmt->get_result();
    $turma = $result->fetch_assoc();
    $curso_id = $turma['curso_id_curso'];
    
    // Agora busca todos os professores do curso, marcando quais estão na turma
    $query = "SELECT 
                 p.id_professor, 
                 u.nome,
                 CASE WHEN pt.turma_id_turma IS NOT NULL THEN 1 ELSE 0 END AS na_turma
              FROM professor p
              JOIN usuario u ON p.usuario_id_usuario = u.id_usuario
              LEFT JOIN professor_tem_turma pt ON pt.professor_id_professor = p.id_professor AND pt.turma_id_turma = ?
              WHERE p.curso_id_curso = ?
              ORDER BY u.nome";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $id_turma, $curso_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $professores = [];
    while ($row = $result->fetch_assoc()) {
        $professores[] = $row;
    }
    
    echo json_encode(['professores' => $professores]);
} else {
    echo json_encode(['error' => 'ID da turma não fornecido']);
}

$conn->close();
?>