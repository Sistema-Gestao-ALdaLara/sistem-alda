<?php
require_once 'conexao.php';

$id_curso = isset($_GET['id_curso']) ? intval($_GET['id_curso']) : null;

if ($id_curso) {
    $stmt = $conn->prepare("SELECT id_turma, nome FROM turma WHERE curso_id_curso = ? ORDER BY nome");
    $stmt->bind_param("i", $id_curso);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $options = '<option value="">Selecione a turma</option>';
    while ($turma = $result->fetch_assoc()) {
        $options .= "<option value='{$turma['id_turma']}'>{$turma['nome']}</option>";
    }
    echo $options;
} else {
    echo '<option value="">Selecione um curso primeiro</option>';
}
?>