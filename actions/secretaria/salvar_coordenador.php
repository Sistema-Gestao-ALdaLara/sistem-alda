<?php
require_once 'conexao.php';

header('Content-Type: application/json');

// Verifica se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Recebe os dados do formulário
$coordenadorId = isset($_POST['coordenadorId']) ? intval($_POST['coordenadorId']) : null;
$nome = $conn->real_escape_string($_POST['nome']);
$bi_numero = $conn->real_escape_string($_POST['bi_numero']);
$email = $conn->real_escape_string($_POST['email']);
$senha = $_POST['senha'] ?? null;
$id_curso = intval($_POST['id_curso']);
$status = $conn->real_escape_string($_POST['status']);

try {
    $conn->begin_transaction();
    
    if ($coordenadorId) {
        // Atualização de coordenador existente
        
        // 1. Atualiza o usuário
        $sql = "UPDATE usuario u
                JOIN coordenador c ON u.id_usuario = c.usuario_id_usuario
                SET u.nome = '$nome',
                    u.email = '$email',
                    u.bi_numero = '$bi_numero',
                    u.status = '$status'
                WHERE c.id_coordenador = $coordenadorId";
        
        if (!$conn->query($sql)) {
            throw new Exception("Erro ao atualizar usuário: " . $conn->error);
        }
        
        // Se foi fornecida uma nova senha, atualiza
        if (!empty($senha)) {
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $sql = "UPDATE usuario u
                    JOIN coordenador c ON u.id_usuario = c.usuario_id_usuario
                    SET u.senha = '$senha_hash'
                    WHERE c.id_coordenador = $coordenadorId";
            
            if (!$conn->query($sql)) {
                throw new Exception("Erro ao atualizar senha: " . $conn->error);
            }
        }
        
        // 2. Atualiza o coordenador (curso)
        $sql = "UPDATE coordenador SET 
                curso_id_curso = $id_curso
                WHERE id_coordenador = $coordenadorId";
        
        if (!$conn->query($sql)) {
            throw new Exception("Erro ao atualizar coordenador: " . $conn->error);
        }
        
        $message = "Coordenador atualizado com sucesso!";
    } else {
        // Cadastro de novo coordenador
        
        // 1. Verifica se o email já existe
        $sql = "SELECT id_usuario FROM usuario WHERE email = '$email'";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            throw new Exception("Este email já está cadastrado no sistema");
        }
        
        // 2. Insere o usuário (com tipo 'coordenador')
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
        $sql = "INSERT INTO usuario 
                (nome, email, senha, bi_numero, tipo, status) 
                VALUES ('$nome', '$email', '$senha_hash', '$bi_numero', 'coordenador', '$status')";
        
        if (!$conn->query($sql)) {
            throw new Exception("Erro ao cadastrar usuário: " . $conn->error);
        }
        
        $usuario_id = $conn->insert_id;
        
        // 3. Insere o coordenador
        $sql = "INSERT INTO coordenador 
                (usuario_id_usuario, curso_id_curso) 
                VALUES ($usuario_id, $id_curso)";
        
        if (!$conn->query($sql)) {
            throw new Exception("Erro ao cadastrar coordenador: " . $conn->error);
        }
        
        $message = "Coordenador cadastrado com sucesso!";
    }
    
    $conn->commit();
    echo json_encode(['success' => true, 'message' => $message]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>