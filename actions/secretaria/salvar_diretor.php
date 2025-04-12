<?php
require_once '../../database/conexao.php';

header('Content-Type: application/json');

// Verifica se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Recebe os dados do formulário
$diretorId = isset($_POST['diretorId']) ? intval($_POST['diretorId']) : null;
$nome = $conn->real_escape_string($_POST['nome']);
$bi_numero = $conn->real_escape_string($_POST['bi_numero']);
$email = $conn->real_escape_string($_POST['email']);
$senha = $_POST['senha'] ?? null;
$tipo = $conn->real_escape_string($_POST['tipo']);
$status = $conn->real_escape_string($_POST['status']);

try {
    $conn->begin_transaction();
    
    if ($diretorId) {
        // Atualização de diretor existente
        
        // 1. Atualiza o usuário
        $sql = "UPDATE usuario SET 
                nome = '$nome',
                email = '$email',
                bi_numero = '$bi_numero',
                status = '$status'
                WHERE id_usuario = $diretorId AND tipo IN ('diretor_geral', 'diretor_pedagogico')";
        
        if (!$conn->query($sql)) {
            throw new Exception("Erro ao atualizar usuário: " . $conn->error);
        }
        
        // Se foi fornecida uma nova senha, atualiza
        if (!empty($senha)) {
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $sql = "UPDATE usuario SET 
                    senha = '$senha_hash'
                    WHERE id_usuario = $diretorId AND tipo IN ('diretor_geral', 'diretor_pedagogico')";
            
            if (!$conn->query($sql)) {
                throw new Exception("Erro ao atualizar senha: " . $conn->error);
            }
        }
        
        $message = "Diretor atualizado com sucesso!";
    } else {
        // Cadastro de novo diretor
        
        // Verifica se já existe um diretor do mesmo tipo
        $sql = "SELECT id_usuario FROM usuario WHERE tipo = '$tipo'";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            throw new Exception("Já existe um " . ($tipo === 'diretor_geral' ? 'Diretor Geral' : 'Diretor Pedagógico') . " cadastrado. Exclua o existente antes de cadastrar um novo.");
        }
        
        // Verifica se o email já existe
        $sql = "SELECT id_usuario FROM usuario WHERE email = '$email'";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            throw new Exception("Este email já está cadastrado no sistema");
        }
        
        // Insere o usuário
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
        $sql = "INSERT INTO usuario 
                (nome, email, senha, bi_numero, tipo, status) 
                VALUES ('$nome', '$email', '$senha_hash', '$bi_numero', '$tipo', '$status')";
        
        if (!$conn->query($sql)) {
            throw new Exception("Erro ao cadastrar usuário: " . $conn->error);
        }
        
        $message = "Diretor cadastrado com sucesso!";
    }
    
    $conn->commit();
    echo json_encode(['success' => true, 'message' => $message]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>