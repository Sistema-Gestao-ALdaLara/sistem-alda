<?php
header('Content-Type: application/json');
require 'conexao.php'; // Arquivo para conexão com o banco

// Verifica se os dados foram enviados via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idAluno = $_POST['idAluno'] ?? '';
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $curso = $_POST['curso'] ?? '';
    $turma = $_POST['turma'] ?? '';
    $status = $_POST['status'] ?? '';

    // Verifica se o ID do aluno foi passado
    if (empty($idAluno)) {
        echo json_encode(["error" => "ID do aluno não informado"]);
        exit;
    }

    // Atualiza os dados do aluno no banco
    $sql = "UPDATE alunos SET nome = ?, email = ?, curso = ?, turma = ?, status = ? WHERE id_aluno = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $nome, $email, $curso, $turma, $status, $idAluno);
    
    if ($stmt->execute()) {
        echo json_encode(["success" => "Aluno atualizado com sucesso"]);
    } else {
        echo json_encode(["error" => "Erro ao atualizar aluno"]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["error" => "Método inválido"]);
}
