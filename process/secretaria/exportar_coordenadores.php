<?php

require_once "../auth/permissoes.php";
verificarPermissao(['secretaria']);

require_once '../../database/conexao.php';

// Filtros recebidos via GET
$id_curso = isset($_GET['id_curso']) ? intval($_GET['id_curso']) : null;

// Query base para listar coordenadores
$query = "SELECT 
             c.id_coordenador,
             u.nome, 
             u.email,
             u.bi_numero,
             u.status,
             cr.nome AS curso
          FROM coordenador c
          JOIN usuario u ON c.usuario_id_usuario = u.id_usuario
          JOIN curso cr ON c.curso_id_curso = cr.id_curso";

// Adiciona filtros dinamicamente
$where = [];
$params = [];

if ($id_curso) {
    $where[] = "cr.id_curso = ?";
    $params[] = $id_curso;
}

if (!empty($where)) {
    $query .= " WHERE " . implode(" AND ", $where);
}

$query .= " ORDER BY u.nome ASC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$coordenadores = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Configurar cabeçalhos para download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=coordenadores_' . date('Y-m-d') . '.csv');

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
foreach ($coordenadores as $coordenador) {
    fputcsv($output, [
        $coordenador['nome'],
        $coordenador['bi_numero'],
        $coordenador['email'],
        $coordenador['curso'],
        $coordenador['status']
    ], ';');
}

fclose($output);
exit;
?>