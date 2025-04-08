<?php
require_once "../auth/permissoes.php";
verificarPermissao(['secretaria']);

require_once "../config/conexao.php";

header('Content-Type: application/json');

$id = isset($_POST['id']) ? intval($_POST['id']) : null;
$status = isset($_POST['status']) ? $_POST['status'] : null;

if (!$id || !in_array($status, ['aprovada', 'rejeitada'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Parâmetros inválidos']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE matricula SET status_matricula = ? WHERE id_matricula = ?");
    $stmt->execute([$status, $id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Status da matrícula atualizado com sucesso'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao atualizar status: ' . $e->getMessage()
    ]);
}
?>