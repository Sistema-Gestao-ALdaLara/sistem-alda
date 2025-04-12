<?php
require_once '../../database/conexao.php';

header('Content-Type: application/json');

$id_turma = isset($_GET['id_turma']) ? intval($_GET['id_turma']) : 0;

try {
    $query = "SELECT 
                p.id_professor,
                u.nome,
                COUNT(pt.turma_id_turma) as total_turmas,
                (SELECT turma_id_turma FROM professor_tem_turma WHERE professor_id_professor = p.id_professor LIMIT 1) as id_turma_atual
              FROM professor p
              JOIN usuario u ON p.usuario_id_usuario = u.id_usuario
              LEFT JOIN professor_tem_turma pt ON pt.professor_id_professor = p.id_professor
              GROUP BY p.id_professor
              ORDER BY u.nome";

    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();

    $professores = [];
    while ($row = $result->fetch_assoc()) {
        $professores[] = $row;
    }

    echo json_encode(['professores' => $professores]);
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

$conn->close();
?>