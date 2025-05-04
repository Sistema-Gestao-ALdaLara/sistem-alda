<?php
require_once __DIR__ . '/../../../database/conexao.php';

header('Content-Type: application/json');


if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['error' => 'Método não permitido']);
    exit();
}

// Obtém parâmetros
$professor_id = filter_input(INPUT_GET, 'professor_id', FILTER_VALIDATE_INT);
$turma_id = filter_input(INPUT_GET, 'turma_id', FILTER_VALIDATE_INT);

if (!$professor_id || !$turma_id) {
    echo json_encode(['error' => 'Parâmetros inválidos']);
    exit();
}

try {
    // Obtém o curso da turma
    $query_curso = "SELECT curso_id_curso FROM turma WHERE id_turma = ?";
    $stmt_curso = $conn->prepare($query_curso);
    $stmt_curso->bind_param("i", $turma_id);
    $stmt_curso->execute();
    $result_curso = $stmt_curso->get_result();

    if ($result_curso->num_rows === 0) {
        throw new Exception('Turma não encontrada');
    }

    $curso_id = $result_curso->fetch_assoc()['curso_id_curso'];

    // Consulta para obter disciplinas do professor naquela turma
    $query = "SELECT DISTINCT d.id_disciplina, d.nome as nome_disciplina
              FROM professor_tem_disciplina ptd
              JOIN disciplina d ON ptd.disciplina_id_disciplina = d.id_disciplina
              JOIN turma t ON t.curso_id_curso = d.curso_id_curso
              WHERE ptd.professor_id_professor = ?
              AND t.id_turma = ?
              ORDER BY d.nome";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $professor_id, $turma_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $disciplinas = [];
    while ($row = $result->fetch_assoc()) {
        $disciplinas[] = $row;
    }

    echo json_encode($disciplinas);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}