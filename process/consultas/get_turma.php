<?php
require_once '../../database/conexao.php';

header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    $query = "SELECT t.id_turma, t.nome, t.curso_id_curso 
              FROM turma t
              WHERE t.id_turma = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode($result->fetch_assoc());
    } else {
        echo json_encode(['error' => 'Turma não encontrada']);
    }
} else {
    echo json_encode(['error' => 'ID da turma não fornecido']);
}

$conn->close();
?>