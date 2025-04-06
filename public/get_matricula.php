<?php
require_once "conexao.php";

header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $id_matricula = intval($_GET['id']);
    
    $query = "SELECT m.*, a.*, u.*, m.id_matricula, a.id_aluno, u.id_usuario
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
        
        $response = [
            'success' => true,
            'data' => [
                'id_matricula' => $matricula['id_matricula'],
                'id_aluno' => $matricula['id_aluno'],
                'usuario_id_usuario' => $matricula['id_usuario'],
                'numero_matricula' => $matricula['numero_matricula'],
                'nome' => $matricula['nome'],
                'bi_numero' => $matricula['bi_numero'],
                'email' => $matricula['email'],
                'data_nascimento' => $matricula['data_nascimento'],
                'genero' => $matricula['genero'],
                'naturalidade' => $matricula['naturalidade'],
                'nacionalidade' => $matricula['nacionalidade'],
                'municipio' => $matricula['municipio'],
                'nome_encarregado' => $matricula['nome_encarregado'],
                'contacto_encarregado' => $matricula['contacto_encarregado'],
                'curso_id_curso' => $matricula['curso_id_curso'],
                'turma_id_turma' => $matricula['turma_id_turma'],
                'ano_letivo' => $matricula['ano_letivo'],
                'classe' => $matricula['classe'],
                'turno' => $matricula['turno'],
                'status_matricula' => $matricula['status_matricula']
            ]
        ];
    } else {
        $response = [
            'success' => false,
            'message' => 'Matrícula não encontrada'
        ];
    }
} else {
    $response = [
        'success' => false,
        'message' => 'ID da matrícula não fornecido'
    ];
}

echo json_encode($response);
$conn->close();
?>