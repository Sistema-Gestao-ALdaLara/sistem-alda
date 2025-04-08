<?php
require_once 'conexao.php';

header('Content-Type: application/json');

$id = isset($_POST['id']) ? intval($_POST['id']) : null;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID do aluno não fornecido']);
    exit;
}

try {
    // Primeiro obtemos o ID do usuário associado ao aluno
    $sql = "SELECT usuario_id_usuario FROM aluno WHERE id_aluno = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Aluno não encontrado");
    }
    
    $row = $result->fetch_assoc();
    $usuario_id = $row['usuario_id_usuario'];
    
    // Depois deletamos o aluno
    $sql = "DELETE FROM aluno WHERE id_aluno = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    // Finalmente deletamos o usuário
    $sql = "DELETE FROM usuario WHERE id_usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    
    echo json_encode(['success' => true, 'message' => 'Aluno excluído com sucesso']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>