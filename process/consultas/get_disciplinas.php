<?php
require_once '../../database/conexao.php';

$id_curso = isset($_GET['id_curso']) ? intval($_GET['id_curso']) : null;

if (!$id_curso) {
    die(json_encode([]));
}

$query = "SELECT id_disciplina, nome FROM disciplina WHERE curso_id_curso = ? ORDER BY nome";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_curso);
$stmt->execute();
$result = $stmt->get_result();

$disciplinas = [];
while ($row = $result->fetch_assoc()) {
    $disciplinas[] = $row;
}

header('Content-Type: application/json');
echo json_encode($disciplinas);
?>