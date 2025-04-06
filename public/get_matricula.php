<?php
require_once "conexao.php";

header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $id_matricula = intval($_GET['id']);
    
    $query = "SELECT m.*, u.nome, a.usuario_id_usuario 
              FROM matricula m
              JOIN aluno a ON m.aluno_id_aluno = a.id_aluno
              JOIN usuario u ON a.usuario_id_usuario = u.id_usuario
              WHERE m.id_matricula = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_matricula);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $matricula = $result->fetch_assoc();
        
        // Formata os dados para retorno
        $response = [
            'success' => true,
            'data' => [
                'id_matricula' => $matricula['id_matricula'],
                'numero_matricula' => $matricula['numero_matricula'],
                'nome' => $matricula['nome'],
                'curso_id_curso' => $matricula['curso_id_curso'],
                'turma_id_turma' => $matricula['turma_id_turma'],
                'classe' => $matricula['classe'],
                'turno' => $matricula['turno'],
                'status_matricula' => $matricula['status_matricula'],
                'ano_letivo' => $matricula['ano_letivo']
            ]
        ];
    } else {
        $response = [
            'success' => false,
            'message' => 'Matrícula não encontrada'
        ];
    }
    
    echo json_encode($response);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'ID da matrícula não fornecido'
    ]);
}

$conn->close();
?>