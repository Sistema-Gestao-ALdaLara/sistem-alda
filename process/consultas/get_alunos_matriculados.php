<?php
require_once '../../database/conexao.php';

$ano_letivo = isset($_GET['ano_letivo']) ? intval($_GET['ano_letivo']) : date('Y');

$stmt = $pdo->prepare("SELECT a.id_aluno, u.nome 
                      FROM aluno a
                      JOIN usuario u ON a.id_usuario = u.id_usuario
                      JOIN matricula m ON m.id_aluno = a.id_aluno
                      WHERE m.ano_letivo = ? AND m.status_matricula = 'aprovada'
                      ORDER BY u.nome");

$stmt->execute([$ano_letivo]);
$alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo '<option value="">Selecione um aluno</option>';
foreach ($alunos as $aluno) {
    echo '<option value="' . $aluno['id_aluno'] . '">' . htmlspecialchars($aluno['nome']) . '</option>';
}
?>