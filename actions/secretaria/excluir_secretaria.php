<?php
require_once '../../database/conexao.php';

header('Content-Type: application/json');

$id = isset($_POST['id']) ? intval($_POST['id']) : null;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID da secretaria não fornecido']);
    exit;
}

try {
    // Obtém o ID do usuário associado à secretaria
    $sql = "SELECT usuario_id_usuario FROM secretaria WHERE id_secretaria = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Secretaria não encontrada");
    }
    
    $row = $result->fetch_assoc();
    $usuario_id = $row['usuario_id_usuario'];
    
    // Remove a secretaria
    $sql = "DELETE FROM secretaria WHERE id_secretaria = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    // Remove o usuário
    $sql = "DELETE FROM usuario WHERE id_usuario = ? AND tipo = 'secretaria'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    
    echo json_encode(['success' => true, 'message' => 'Secretaria excluída com sucesso']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>