<?php
require_once __DIR__ . '/../../../database/conexao.php';
require_once __DIR__ . '/../../../process/verificar_sessao.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['error' => 'Não autorizado']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['error' => 'Método não permitido']);
    exit();
}

$professor_id = isset($_GET['professor_id']) ? (int)$_GET['professor_id'] : 0;
$classe = isset($_GET['classe']) ? $_GET['classe'] : '';

if (!$professor_id || !$classe) {
    echo json_encode(['error' => 'Parâmetros inválidos']);
    exit();
}

$query = "SELECT d.id_disciplina, d.nome as nome_disciplina
          FROM professor_tem_disciplina ptd
          JOIN disciplina d ON ptd.disciplina_id_disciplina = d.id_disciplina
          WHERE ptd.professor_id_professor = ? AND ptd.classe = ?
          ORDER BY d.nome";
$stmt = $conn->prepare($query);
$stmt->bind_param("is", $professor_id, $classe);
$stmt->execute();
$result = $stmt->get_result();

$disciplinas = [];
while ($row = $result->fetch_assoc()) {
    $disciplinas[] = $row;
}

echo json_encode($disciplinas);