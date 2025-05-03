<?php
require_once '../../database/conexao.php';

if (isset($_GET['id_curso'])) {
    $id_curso = intval($_GET['id_curso']);
    
    $query = "SELECT t.id_turma, t.nome, t.classe, t.turno, c.nome AS curso_nome 
              FROM turma t
              JOIN curso c ON t.curso_id_curso = c.id_curso
              WHERE t.curso_id_curso = ?
              ORDER BY t.classe, t.nome";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_curso);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $options = '<option value="">Selecione uma turma...</option>';
    
    while ($turma = $result->fetch_assoc()) {
        $options .= sprintf(
            '<option value="%d" data-classe="%s" data-turno="%s"> %s</option>',
            $turma['id_turma'],
            htmlspecialchars($turma['classe']),
            htmlspecialchars($turma['turno']),
            htmlspecialchars($turma['nome'])
        );
    }
    
    echo $options;
} else {
    echo '<option value="">Selecione um curso primeiro</option>';
}

$conn->close();
?>
