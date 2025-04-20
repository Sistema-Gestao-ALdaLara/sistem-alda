<?php
require_once '../../database/conexao.php';

if (isset($_GET['id_curso'])) {
    $id_curso = intval($_GET['id_curso']);
    
    // Modifique a query para incluir o campo turno
    $query = "SELECT id_turma, nome, turno FROM turma WHERE curso_id_curso = ? ORDER BY nome";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_curso);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $options = '<option value="">Selecione uma turma</option>';
    
    while ($turma = $result->fetch_assoc()) {
        // Adicione o data-turno aqui
        $options .= '<option value="' . $turma['id_turma'] . '" data-turno="' . htmlspecialchars($turma['turno']) . '">' . 
                   htmlspecialchars($turma['nome']) . '</option>';
    }
    
    echo $options;
} else {
    echo '<option value="">Nenhum curso selecionado</option>';
}

$conn->close();
?>