<?php
require_once '../../database/conexao.php';

$id_aluno = isset($_GET['id_aluno']) ? intval($_GET['id_aluno']) : null;

if (!$id_aluno) {
    http_response_code(400);
    echo json_encode(['error' => 'ID do aluno não fornecido']);
    exit;
}

// Obter histórico de matrículas
$stmt = $pdo->prepare("SELECT 
                      m.ano_letivo, c.nome AS curso, t.nome AS turma,
                      m.status_matricula AS status, m.tipo_matricula AS tipo,
                      DATE_FORMAT(m.data_matricula, '%d/%m/%Y') AS data
                      FROM matricula m
                      LEFT JOIN curso c ON m.id_curso = c.id_curso
                      LEFT JOIN turma t ON m.id_turma = t.id_turma
                      WHERE m.id_aluno = ?
                      ORDER BY m.ano_letivo DESC");
$stmt->execute([$id_aluno]);
$matriculas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obter documentos associados ao aluno
$stmt = $pdo->prepare("SELECT 
                      tipo, descricao, arquivo, 
                      DATE_FORMAT(data_upload, '%d/%m/%Y') AS data
                      FROM documentos
                      WHERE id_aluno = ? AND tipo IN ('historico', 'transferencia', 'declaracao')
                      ORDER BY data_upload DESC");
$stmt->execute([$id_aluno]);
$documentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode([
    'matriculas' => $matriculas,
    'documentos' => $documentos
]);
?>