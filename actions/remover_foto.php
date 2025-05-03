<?php
require_once '../../database/conexao.php';
require_once '../../process/verificar_sessao.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método não permitido');
    }

    $id_usuario = $_SESSION['id_usuario'];
    
    // Obter o caminho da foto atual
    $query = "SELECT foto_perfil FROM usuario WHERE id_usuario = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    $usuario = $result->fetch_assoc();
    
    if (!$usuario) {
        throw new Exception('Usuário não encontrado');
    }
    
    $foto_perfil = $usuario['foto_perfil'];
    $diretorio = '../../uploads/perfil/';
    
    // Remover a foto do servidor se existir e não for a padrão
    if ($foto_perfil && !str_contains($foto_perfil, 'default')) {
        if (file_exists($diretorio . $foto_perfil)) {
            unlink($diretorio . $foto_perfil);
        }
    }
    
    // Atualizar no banco de dados para NULL ou caminho da foto padrão
    $query_update = "UPDATE usuario SET foto_perfil = NULL WHERE id_usuario = ?";
    $stmt_update = $conn->prepare($query_update);
    $stmt_update->bind_param("i", $id_usuario);
    
    if ($stmt_update->execute()) {
        // Atualizar na sessão
        $_SESSION['foto_perfil'] = null;
        $response['success'] = true;
        $response['message'] = 'Foto removida com sucesso!';
    } else {
        throw new Exception('Erro ao atualizar o banco de dados');
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>