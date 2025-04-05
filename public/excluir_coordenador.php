<?php
require_once 'conexao.php';

header('Content-Type: application/json');

$id = isset($_POST['id']) ? intval($_POST['id']) : null;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID do coordenador não fornecido']);
    exit;
}

try {
    // Obtém o ID do usuário associado ao coordenador
    $sql = "SELECT usuario_id_usuario FROM coordenador WHERE id_coordenador = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Coordenador não encontrado");
    }
    
    $row = $result->fetch_assoc();
    $usuario_id = $row['usuario_id_usuario'];
    
    // Remove o coordenador
    $sql = "DELETE FROM coordenador WHERE id_coordenador = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    // Remove o usuário
    $sql = "DELETE FROM usuario WHERE id_usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    
    echo json_encode(['success' => true, 'message' => 'Coordenador excluído com sucesso']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>