<?php
require_once '../../database/conexao.php';

$id_aluno = isset($_GET['id_aluno']) ? intval($_GET['id_aluno']) : null;

if (!$id_aluno) {
    http_response_code(400);
    echo json_encode(['error' => 'ID do aluno não fornecido']);
    exit;
}

$stmt = $pdo->prepare("SELECT 
                      c.nome AS curso, 
                      t.nome AS turma
                      FROM aluno a
                      LEFT JOIN curso c ON a.id_curso = c.id_curso
                      LEFT JOIN turma t ON a.id_turma = t.id_turma
                      WHERE a.id_aluno = ?");

$stmt->execute([$id_aluno]);
$aluno = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$aluno) {
    http_response_code(404);
    echo json_encode(['error' => 'Aluno não encontrado']);
    exit;
}

header('Content-Type: application/json');
echo json_encode($aluno);
?>