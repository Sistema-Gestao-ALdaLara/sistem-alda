<?php
require_once '../../database/conexao.php';

header('Content-Type: application/json');

if (isset($_GET['id_turma']) && isset($_GET['ano_letivo'])) {
    $id_turma = intval($_GET['id_turma']);
    $ano_letivo = intval($_GET['ano_letivo']);
    
    $query = "SELECT 
                 m.id_matricula,
                 u.nome
              FROM matricula m
              JOIN aluno a ON m.aluno_id_aluno = a.id_aluno
              JOIN usuario u ON a.usuario_id_usuario = u.id_usuario
              WHERE m.turma_id_turma = ? AND m.ano_letivo = ?
              ORDER BY u.nome";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $id_turma, $ano_letivo);
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