<?php
require_once '../../database/conexao.php';

header('Content-Type: application/json');

$id = isset($_POST['id']) ? intval($_POST['id']) : null;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID do professor não fornecido']);
    exit;
}

try {
    // Verifica se o professor tem disciplinas associadas
    $sql = "SELECT COUNT(*) as total FROM disciplina WHERE professor_id_professor = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['total'] > 0) {
        throw new Exception("Não é possível excluir - professor tem disciplinas associadas");
    }

    // Obtém o ID do usuário associado ao professor
    $sql = "SELECT usuario_id_usuario FROM professor WHERE id_professor = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Professor não encontrado");
    }
    
    $row = $result->fetch_assoc();
    $usuario_id = $row['usuario_id_usuario'];
    
    // Remove o professor
    $sql = "DELETE FROM professor WHERE id_professor = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    // Remove o usuário
    $sql = "DELETE FROM usuario WHERE id_usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    
    echo json_encode(['success' => true, 'message' => 'Professor excluído com sucesso']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>