<?php

require_once "../auth/permissoes.php";
verificarPermissao(['secretaria']);
require_once '../../database/conexao.php';

// Filtros recebidos via GET
$id_curso = isset($_GET['id_curso']) ? intval($_GET['id_curso']) : null;
$id_turma = isset($_GET['id_turma']) ? intval($_GET['id_turma']) : null;

// Query base para listar alunos
$query = "SELECT 
             a.id_aluno,
             u.nome, 
             u.email,
             u.bi_numero,
             a.numero_matricula,
             t.nome AS turma,
             c.nome AS curso,
             a.ano_letivo,
             m.status_matricula
          FROM aluno a
          JOIN usuario u ON a.id_usuario = u.id_usuario
          LEFT JOIN turma t ON a.id_turma = t.id_turma
          LEFT JOIN curso c ON a.id_curso = c.id_curso
          LEFT JOIN matricula m ON m.id_aluno = a.id_aluno AND m.ano_letivo = a.ano_letivo";

// Adiciona filtros dinamicamente
$where = [];
$params = [];

if ($id_curso) {
    $where[] = "c.id_curso = ?";
    $params[] = $id_curso;
}

if ($id_turma) {
    $where[] = "t.id_turma = ?";
    $params[] = $id_turma;
}

if (!empty($where)) {
    $query .= " WHERE " . implode(" AND ", $where);
}

$query .= " ORDER BY u.nome ASC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Configurar cabeçalhos para download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=alunos_' . date('Y-m-d') . '.csv');

// Criar arquivo CSV
$output = fopen('php://output', 'w');

// Cabeçalhos do CSV
fputcsv($output, [
    'Matrícula',
    'Nome',
    'BI',
    'Email',
    'Turma',
    'Curso',
    'Ano Letivo',
    'Status Matrícula'
], ';');

// Dados
foreach ($alunos as $aluno) {
    fputcsv($output, [
        $aluno['numero_matricula'],
        $aluno['nome'],
        $aluno['bi_numero'],
        $aluno['email'],
        $aluno['turma'] ?? 'N/D',
        $aluno['curso'] ?? 'N/D',
        $aluno['ano_letivo'],
        $aluno['status_matricula'] ?? 'N/D'
    ], ';');
}

fclose($output);
exit;
?>