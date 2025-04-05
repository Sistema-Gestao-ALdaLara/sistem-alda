<?php
require_once 'conexao.php';

header('Content-Type: application/json');

// Verifica se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Recebe os dados do formulário
$secretariaId = isset($_POST['secretariaId']) ? intval($_POST['secretariaId']) : null;
$usuarioId = isset($_POST['usuarioId']) ? intval($_POST['usuarioId']) : null;
$nome = $conn->real_escape_string($_POST['nome']);
$bi_numero = $conn->real_escape_string($_POST['bi_numero']);
$email = $conn->real_escape_string($_POST['email']);
$senha = $_POST['senha'] ?? null;
$setor = $conn->real_escape_string($_POST['setor']);
$status = $conn->real_escape_string($_POST['status']);
$pode_registrar = isset($_POST['pode_registrar']) ? 1 : 0;
$tipo = 'secretaria';

try {
    $conn->begin_transaction();
    
    if ($secretariaId) {
        // Atualização de secretaria existente
        
        // 1. Atualiza o usuário
        $sql = "UPDATE usuario SET 
                nome = '$nome',
                email = '$email',
                bi_numero = '$bi_numero',
                status = '$status'
                WHERE id_usuario = $usuarioId AND tipo = 'secretaria'";
        
        if (!$conn->query($sql)) {
            throw new Exception("Erro ao atualizar usuário: " . $conn->error);
        }
        
        // Se foi fornecida uma nova senha, atualiza
        if (!empty($senha)) {
            if (strlen($senha) < 8) {
                throw new Exception("A senha deve ter no mínimo 8 caracteres");
            }
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $sql = "UPDATE usuario SET 
                    senha = '$senha_hash'
                    WHERE id_usuario = $usuarioId AND tipo = 'secretaria'";
            
            if (!$conn->query($sql)) {
                throw new Exception("Erro ao atualizar senha: " . $conn->error);
            }
        }
        
        // 2. Atualiza a secretaria
        $sql = "UPDATE secretaria SET 
                setor = '$setor',
                pode_registrar = $pode_registrar
                WHERE id_secretaria = $secretariaId";
        
        if (!$conn->query($sql)) {
            throw new Exception("Erro ao atualizar secretaria: " . $conn->error);
        }
        
        $message = "Secretaria atualizada com sucesso!";
    } else {
        // Cadastro de nova secretaria
        
        // 1. Verifica se o email já existe
        $sql = "SELECT id_usuario FROM usuario WHERE email = '$email'";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            throw new Exception("Este email já está cadastrado no sistema");
        }
        
        if (empty($senha) || strlen($senha) < 8) {
            throw new Exception("A senha deve ter no mínimo 8 caracteres");
        }
        
        // 2. Insere o usuário (com tipo 'secretaria')
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
        $sql = "INSERT INTO usuario 
                (nome, email, senha, bi_numero, tipo, status) 
                VALUES ('$nome', '$email', '$senha_hash', '$bi_numero', '$tipo', '$status')";
        
        if (!$conn->query($sql)) {
            throw new Exception("Erro ao cadastrar usuário: " . $conn->error);
        }
        
        $usuario_id = $conn->insert_id;
        
        // 3. Insere a secretaria
        $sql = "INSERT INTO secretaria 
                (setor, pode_registrar, usuario_id_usuario) 
                VALUES ('$setor', $pode_registrar, $usuario_id)";
        
        if (!$conn->query($sql)) {
            throw new Exception("Erro ao cadastrar secretaria: " . $conn->error);
        }
        
        $message = "Secretaria cadastrada com sucesso!";
    }
    
    $conn->commit();
    echo json_encode(['success' => true, 'message' => $message]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>