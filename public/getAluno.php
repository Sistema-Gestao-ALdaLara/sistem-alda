<?php
require_once "../config/conexao.php";

$id = isset($_GET['id']) ? intval($_GET['id']) : null;

if ($id) {
    $stmt = $pdo->prepare("SELECT 
        a.id_aluno,
        u.nome, 
        u.email,
        u.bi_numero,
        a.numero_matricula,
        a.id_turma,
        a.id_curso,
        a.ano_letivo
    FROM aluno a
    JOIN usuario u ON a.id_usuario = u.id_usuario
    WHERE a.id_aluno = ?");
    
    $stmt->execute([$id]);
    $aluno = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($aluno) {
        header('Content-Type: application/json');
        echo json_encode($aluno);
        exit;
    }
}

http_response_code(404);
echo json_encode(['error' => 'Aluno não encontrado']);
?>