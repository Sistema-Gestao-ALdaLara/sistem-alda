<?php
require_once "conexao.php";

if (isset($_GET['id_curso'])) {
    $id_curso = intval($_GET['id_curso']);
    
    $query = "SELECT id_turma, nome FROM turma WHERE curso_id_curso = ? ORDER BY nome";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_curso);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $options = '<option value="">Selecione uma turma</option>';
    
    while ($turma = $result->fetch_assoc()) {
        $options .= '<option value="' . $turma['id_turma'] . '">' . htmlspecialchars($turma['nome']) . '</option>';
    }
    
    echo $options;
} else {
    echo '<option value="">Nenhum curso selecionado</option>';
}

$conn->close();
?>