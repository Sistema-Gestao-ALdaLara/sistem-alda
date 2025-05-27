<?php
require_once '../../database/conexao.php';
require_once '../../process/verificar_sessao.php';

// Verificar permissões
if (!in_array($_SESSION['tipo'], ['diretor_geral', 'diretor_pedagogico'])) {
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado']);
    exit;
}

if (!isset($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID não fornecido']);
    exit;
}

$id = (int)$_POST['id'];

try {
    $conn->begin_transaction();
    
    // Primeiro obter o ID do usuário associado à secretaria
    $sqlGetUsuario = "SELECT usuario_id_usuario FROM secretaria WHERE id_secretaria = ?";
    $stmtGetUsuario = $conn->prepare($sqlGetUsuario);
    $stmtGetUsuario->bind_param('i', $id);
    $stmtGetUsuario->execute();
    $result = $stmtGetUsuario->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Secretaria não encontrada");
    }
    
    $row = $result->fetch_assoc();
    $usuarioId = $row['usuario_id_usuario'];
    
    // Verificar se não é o próprio usuário tentando se excluir
    if ($usuarioId == $_SESSION['id_usuario']) {
        throw new Exception("Você não pode excluir sua própria conta");
    }
    
    // Excluir a secretaria
    $sqlDeleteSecretaria = "DELETE FROM secretaria WHERE id_secretaria = ?";
    $stmtDeleteSecretaria = $conn->prepare($sqlDeleteSecretaria);
    $stmtDeleteSecretaria->bind_param('i', $id);
    $stmtDeleteSecretaria->execute();
    
    // Excluir o usuário
    $sqlDeleteUsuario = "DELETE FROM usuario WHERE id_usuario = ?";
    $stmtDeleteUsuario = $conn->prepare($sqlDeleteUsuario);
    $stmtDeleteUsuario->bind_param('i', $usuarioId);
    $stmtDeleteUsuario->execute();
    
    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Secretaria excluída com sucesso']);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}