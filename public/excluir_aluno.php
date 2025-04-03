<?php
require_once "../auth/permissoes.php";
verificarPermissao(['secretaria']);

require_once "../config/conexao.php";

header('Content-Type: application/json');

$id = isset($_POST['id']) ? intval($_POST['id']) : null;

if (!$id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID do aluno não fornecido']);
    exit;
}

try {
    $pdo->beginTransaction();
    
    // Primeiro obtemos o id_usuario associado ao aluno
    $stmt = $pdo->prepare("SELECT id_usuario FROM aluno WHERE id_aluno = ?");
    $stmt->execute([$id]);
    $aluno = $stmt->fetch();
    
    if (!$aluno) {
        throw new Exception('Aluno não encontrado');
    }
    
    // Excluir o aluno
    $stmt = $pdo->prepare("DELETE FROM aluno WHERE id_aluno = ?");
    $stmt->execute([$id]);
    
    // Excluir o usuário associado
    $stmt = $pdo->prepare("DELETE FROM usuario WHERE id_usuario = ?");
    $stmt->execute([$aluno['id_usuario']]);
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Aluno excluído com sucesso'
    ]);
    
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao excluir aluno: ' . $e->getMessage()
    ]);
}
?>