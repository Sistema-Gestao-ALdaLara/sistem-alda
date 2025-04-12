<?php
require_once '../../database/conexao.php';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=turmas_' . date('Y-m-d') . '.csv');

$output = fopen('php://output', 'w');

// Cabeçalho do CSV
fputcsv($output, [
    'ID', 
    'Nome da Turma', 
    'Curso', 
    'Ano Letivo', 
    'Total de Alunos', 
    'Professores'
], ';');

$id_curso = isset($_GET['id_curso']) ? intval($_GET['id_curso']) : null;
$ano_letivo = isset($_GET['ano_letivo']) ? intval($_GET['ano_letivo']) : date('Y');

// Query para exportação
$query = "SELECT 
             t.id_turma,
             t.nome AS nome_turma,
             c.nome AS nome_curso,
             ? AS ano_letivo,
             COUNT(DISTINCT m.id_matricula) AS total_alunos,
             GROUP_CONCAT(DISTINCT u.nome SEPARATOR ', ') AS professores_nomes
          FROM turma t
          JOIN curso c ON t.curso_id_curso = c.id_curso
          LEFT JOIN matricula m ON m.turma_id_turma = t.id_turma AND m.ano_letivo = ?
          LEFT JOIN professor_tem_turma pt ON pt.turma_id_turma = t.id_turma
          LEFT JOIN professor p ON pt.professor_id_professor = p.id_professor
          LEFT JOIN usuario u ON p.usuario_id_usuario = u.id_usuario";

$where = [];
$params = [$ano_letivo, $ano_letivo];
$types = "ii";

if ($id_curso) {
    $query .= " WHERE c.id_curso = ?";
    $params[] = $id_curso;
    $types .= "i";
}

$query .= " GROUP BY t.id_turma, t.nome, c.nome";
$query .= " ORDER BY c.nome, t.nome";

$stmt = $conn->prepare($query);

if ($params) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['id_turma'],
        $row['nome_turma'],
        $row['nome_curso'],
        $ano_letivo,
        $row['total_alunos'],
        $row['professores_nomes'] ?: 'Nenhum'
    ], ';');
}

$conn->close();
?>