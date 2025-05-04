<?php
require_once __DIR__ . '/../../../database/conexao.php';

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");


if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['error' => 'Método não permitido']);
    exit();
}

$turma_id = filter_input(INPUT_GET, 'turma_id', FILTER_VALIDATE_INT);

if (!$turma_id) {
    echo json_encode(['error' => 'ID da turma inválido']);
    exit();
}

try {
    $query = "SELECT a.id_aluno, u.nome as nome_aluno
              FROM aluno a
              JOIN usuario u ON a.usuario_id_usuario = u.id_usuario
              WHERE a.turma_id_turma = ?
              ORDER BY u.nome";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $turma_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $alunos = [];
    while ($row = $result->fetch_assoc()) {
        $alunos[] = $row;
    }

    echo json_encode($alunos);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}