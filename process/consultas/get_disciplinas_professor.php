<?php
require_once '../../database/conexao.php';

$id_professor = isset($_GET['id_professor']) ? intval($_GET['id_professor']) : null;

if (!$id_professor) {
    die(json_encode([]));
}

$query = "SELECT id_disciplina FROM disciplina WHERE professor_id_professor = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_professor);
$stmt->execute();
$result = $stmt->get_result();

$disciplinas = [];
while ($row = $result->fetch_assoc()) {
    $disciplinas[] = $row['id_disciplina'];
}

header('Content-Type: application/json');
echo json_encode($disciplinas);
?>