<?php
require_once "../auth/permissoes.php";
verificarPermissao(['secretaria']);

require_once "../config/conexao.php";

// Query para listar diretores
$query = "SELECT 
             u.nome, 
             u.email,
             u.bi_numero,
             CASE u.tipo 
                 WHEN 'diretor_geral' THEN 'Diretor Geral'
                 WHEN 'diretor_pedagogico' THEN 'Diretor Pedagógico'
             END as tipo,
             u.status
          FROM usuario u
          WHERE u.tipo IN ('diretor_geral', 'diretor_pedagogico')
          ORDER BY FIELD(u.tipo, 'diretor_geral', 'diretor_pedagogico'), u.nome";

$stmt = $pdo->prepare($query);
$stmt->execute();
$diretores = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Configurar cabeçalhos para download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=diretores_' . date('Y-m-d') . '.csv');

// Criar arquivo CSV
$output = fopen('php://output', 'w');

// Cabeçalhos do CSV
fputcsv($output, [
    'Tipo',
    'Nome',
    'BI',
    'Email',
    'Status'
], ';');

// Dados
foreach ($diretores as $diretor) {
    fputcsv($output, [
        $diretor['tipo'],
        $diretor['nome'],
        $diretor['bi_numero'],
        $diretor['email'],
        $diretor['status']
    ], ';');
}

fclose($output);
exit;
?>