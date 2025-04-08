<?php
require_once "../auth/permissoes.php";
verificarPermissao(['secretaria']);
require_once '../../database/conexao.php';

// Filtros recebidos via GET
$id_curso = isset($_GET['id_curso']) ? intval($_GET['id_curso']) : null;

// Query base para listar professores
$query = "SELECT 
             p.id_professor,
             u.nome, 
             u.email,
             u.bi_numero,
             u.status,
             c.nome AS curso
          FROM professor p
          JOIN usuario u ON p.usuario_id_usuario = u.id_usuario
          JOIN curso c ON p.curso_id_curso = c.id_curso";

// Adiciona filtros dinamicamente
$where = [];
$params = [];

if ($id_curso) {
    $where[] = "c.id_curso = ?";
    $params[] = $id_curso;
}

if (!empty($where)) {
    $query .= " WHERE " . implode(" AND ", $where);
}

$query .= " ORDER BY u.nome ASC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$professores = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Configurar cabeçalhos para download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=professores_' . date('Y-m-d') . '.csv');

// Criar arquivo CSV
$output = fopen('php://output', 'w');

// Cabeçalhos do CSV
fputcsv($output, [
    'Nome',
    'BI',
    'Email',
    'Curso',
    'Status'
], ';');

// Dados
foreach ($professores as $professor) {
    fputcsv($output, [
        $professor['nome'],
        $professor['bi_numero'],
        $professor['email'],
        $professor['curso'],
        $professor['status']
    ], ';');
}

fclose($output);
exit;
?>