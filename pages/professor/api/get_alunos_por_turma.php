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

$turma_id = isset($_GET['turma_id']) ? (int)$_GET['turma_id'] : 0;

if (!$turma_id) {
    echo json_encode(['error' => 'ID da turma não fornecido']);
    exit();
}

$query = "SELECT a.id_aluno, u.nome as nome_aluno, u.bi_numero
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