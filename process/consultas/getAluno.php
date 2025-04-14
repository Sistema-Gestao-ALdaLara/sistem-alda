<?php
require_once '../../database/conexao.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : null;

if (!$id) {
    die(json_encode(['error' => 'ID do aluno não fornecido']));
}

$sql = "SELECT 
            a.id_aluno, 
            u.nome, 
            u.email, 
            u.bi_numero,
            a.numero_matricula,
            a.turma_id_turma as id_turma,
            a.curso_id_curso as id_curso,
            a.ano_letivo
        FROM aluno a
        JOIN usuario u ON a.usuario_id_usuario = u.id_usuario
        WHERE a.id_aluno = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode($result->fetch_assoc());
} else {
    echo json_encode(['error' => 'Aluno não encontrado']);
}
?>