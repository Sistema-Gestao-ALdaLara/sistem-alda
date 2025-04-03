<?php
require_once "../config/conexao.php";

$id_curso = isset($_GET['id_curso']) ? intval($_GET['id_curso']) : null;

if ($id_curso) {
    $stmt = $pdo->prepare("SELECT id_turma, nome FROM turma WHERE id_curso = ? ORDER BY nome");
    $stmt->execute([$id_curso]);
    $turmas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo '<option value="">Todas as turmas</option>';
    foreach ($turmas as $turma) {
        echo '<option value="' . $turma['id_turma'] . '">' . htmlspecialchars($turma['nome']) . '</option>';
    }
} else {
    echo '<option value="">Selecione um curso primeiro</option>';
}
?>