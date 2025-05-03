<?php
require_once '../../database/conexao.php';

// Verificar permissões
require_once '../../includes/common/permissoes.php';
verificarPermissao(['diretor_geral']);

// Obter filtro de curso se existir
$id_curso = isset($_GET['id_curso']) ? intval($_GET['id_curso']) : null;

// Consultar professores
$query = "SELECT 
             p.id_professor,
             u.nome, 
             u.email,
             u.bi_numero,
             c.nome AS curso,
             u.status
          FROM professor p
          JOIN usuario u ON p.usuario_id_usuario = u.id_usuario
          JOIN curso c ON p.curso_id_curso = c.id_curso";

if ($id_curso) {
    $query .= " WHERE c.id_curso = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $id_curso);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($query);
}

// Configurar cabeçalhos para download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=professores_' . date('Y-m-d') . '.csv');

// Criar arquivo de saída
$output = fopen('php://output', 'w');

// Escrever cabeçalho
fputcsv($output, ['ID', 'Nome', 'Email', 'BI', 'Curso', 'Status'], ';');

// Escrever dados
while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['id_professor'],
        $row['nome'],
        $row['email'],
        $row['bi_numero'],
        $row['curso'],
        $row['status']
    ], ';');
}

fclose($output);
exit;