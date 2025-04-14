<?php
require_once '../../database/conexao.php';
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar e sanitizar dados
    $id_matricula = intval($_POST['id_matricula']);
    $id_curso = intval($_POST['id_curso']);
    $id_turma = intval($_POST['id_turma']);
    $classe = $_POST['classe'];
    $turno = $_POST['turno'];
    $status = $_POST['status'];
    
    // Atualizar matrícula
    $stmt = $conn->prepare("UPDATE matricula 
                           SET curso_id_curso = ?, turma_id_turma = ?, classe = ?, turno = ?, status_matricula = ?
                           WHERE id_matricula = ?");
    $stmt->bind_param("iisssi", $id_curso, $id_turma, $classe, $turno, $status, $id_matricula);
    
    if ($stmt->execute()) {
        // Obter ID do aluno para atualizar seus dados
        $stmt = $conn->prepare("SELECT aluno_id_aluno FROM matricula WHERE id_matricula = ?");
        $stmt->bind_param("i", $id_matricula);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $id_aluno = $row['aluno_id_aluno'];
        
        // Atualizar aluno com nova turma e curso
        $stmt = $conn->prepare("UPDATE aluno SET turma_id_turma = ?, curso_id_curso = ? WHERE id_aluno = ?");
        $stmt->bind_param("iii", $id_turma, $id_curso, $id_aluno);
        $stmt->execute();
        
        $_SESSION['sucesso'] = "Matrícula atualizada com sucesso!";
        echo json_encode([
            'success' => true,
            'redirect' => 'matricula.php'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Erro ao atualizar matrícula: ' . $conn->error
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Método de requisição inválido'
    ]);
}

$conn->close();
?>