<?php
require_once '../../database/conexao.php';
require_once '../../includes/common/permissoes.php';
require_once '../../process/verificar_sessao.php';

header('Content-Type: application/json');

// Verificar permissões
if (!in_array($_SESSION['tipo_usuario'], ['diretor_geral', 'diretor_pedagogico'])) {
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado']);
    exit;
}

if (!isset($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID não fornecido']);
    exit;
}

$id = intval($_POST['id']);

// Verificar se a secretaria existe
$stmt = $conn->prepare("SELECT s.id_secretaria, u.id_usuario, u.foto_perfil 
                       FROM secretaria s 
                       JOIN usuario u ON s.usuario_id_usuario = u.id_usuario 
                       WHERE s.id_secretaria = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Secretaria não encontrada']);
    exit;
}

$secretaria = $result->fetch_assoc();

// Iniciar transação
$conn->begin_transaction();

try {
    // Remover foto de perfil se existir
    if (!empty($secretaria['foto_perfil'])) {
        $diretorio = '../../uploads/perfil/';
        if (file_exists($diretorio . $secretaria['foto_perfil'])) {
            unlink($diretorio . $secretaria['foto_perfil']);
        }
    }
    
    // Excluir secretaria
    $stmt = $conn->prepare("DELETE FROM secretaria WHERE id_secretaria = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    
    // Excluir usuário
    $stmt = $conn->prepare("DELETE FROM usuario WHERE id_usuario = ?");
    $stmt->bind_param('i', $secretaria['id_usuario']);
    $stmt->execute();
    
    $conn->commit();
    
    echo json_encode(['success' => true, 'message' => 'Secretaria excluída com sucesso']);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Erro ao excluir secretaria: ' . $e->getMessage()]);
}
?>