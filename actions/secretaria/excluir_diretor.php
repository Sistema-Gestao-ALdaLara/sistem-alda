<?php
require_once 'conexao.php';

header('Content-Type: application/json');

$id = isset($_POST['id']) ? intval($_POST['id']) : null;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID do diretor não fornecido']);
    exit;
}

try {
    // Verifica se é realmente um diretor
    $sql = "SELECT tipo FROM usuario WHERE id_usuario = ? AND tipo IN ('diretor_geral', 'diretor_pedagogico')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Diretor não encontrado ou não é um diretor válido");
    }
    
    $row = $result->fetch_assoc();
    $tipo = $row['tipo'];
    
    // Remove o diretor (que é um usuário)
    $sql = "DELETE FROM usuario WHERE id_usuario = ? AND tipo IN ('diretor_geral', 'diretor_pedagogico')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    echo json_encode([
        'success' => true, 
        'message' => ($tipo === 'diretor_geral' ? 'Diretor Geral' : 'Diretor Pedagógico') . ' excluído com sucesso'
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>