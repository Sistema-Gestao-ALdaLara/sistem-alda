<?php
require_once '../../database/conexao.php';

if (isset($_GET['id_aluno']) && isset($_GET['id_disciplina']) && isset($_GET['trimestre'])) {
    // Consulta para buscar notas conforme parâmetros
    $query = "SELECT n.*, d.nome as disciplina_nome 
              FROM nota n
              JOIN disciplina d ON n.disciplina_id_disciplina = d.id_disciplina
              WHERE n.aluno_id_aluno = ? 
              AND n.disciplina_id_disciplina = ?
              AND n.trimestre = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iii", $_GET['id_aluno'], $_GET['id_disciplina'], $_GET['trimestre']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Gerar HTML do relatório de notas
    header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <style>
        /* Estilos para o relatório de notas */
    </style>
</head>
<body>
    <!-- Conteúdo do relatório de notas -->
    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>
<?php
}
?>