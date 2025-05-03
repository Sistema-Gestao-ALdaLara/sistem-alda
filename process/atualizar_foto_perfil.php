<?php
session_start();
require_once '../database/conexao.php';

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

if (!isset($_FILES['foto_perfil'])) {
    echo json_encode(['success' => false, 'message' => 'Nenhuma imagem enviada']);
    exit;
}

$id_usuario = $_POST['id_usuario'];
$arquivo = $_FILES['foto_perfil'];

// Validar o arquivo
if ($arquivo['error'] !== 0) {
    echo json_encode(['success' => false, 'message' => 'Erro no upload do arquivo']);
    exit;
}

if (!in_array($arquivo['type'], ['image/jpeg', 'image/jpg', 'image/png'])) {
    echo json_encode(['success' => false, 'message' => 'Formato de arquivo não permitido']);
    exit;
}

if ($arquivo['size'] > 2 * 1024 * 1024) { // 2MB
    echo json_encode(['success' => false, 'message' => 'Arquivo muito grande (máximo 2MB)']);
    exit;
}

// Criar diretório se não existir
$diretorio = '../uploads/alunos/';
if (!is_dir($diretorio)) {
    mkdir($diretorio, 0755, true);
}

// Gerar nome único para o arquivo
$nome_arquivo = uniqid() . '_' . basename($arquivo['name']);
$caminho_completo = $diretorio . $nome_arquivo;

// Mover o arquivo para o diretório de destino
if (move_uploaded_file($arquivo['tmp_name'], $caminho_completo)) {
    // Atualizar o caminho da foto no banco de dados
    $sql = "UPDATE usuario SET foto_perfil = ? WHERE id_usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $nome_arquivo, $id_usuario);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Foto atualizada com sucesso']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar o banco de dados']);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao mover o arquivo']);
}
?>
