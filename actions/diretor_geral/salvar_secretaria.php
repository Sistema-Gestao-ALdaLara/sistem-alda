<?php
require_once '../../database/conexao.php';
require_once '../../process/verificar_sessao.php';

// Verificar permissões
if (!in_array($_SESSION['tipo'], ['diretor_geral', 'diretor_pedagogico'])) {
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado']);
    exit;
}

// Receber dados do formulário
$secretariaId = $_POST['secretariaId'] ?? null;
$usuarioId = $_POST['usuarioId'] ?? null;
$nome = trim($_POST['nome']);
$bi_numero = trim($_POST['bi_numero']);
$email = trim($_POST['email']);
$senha = $_POST['senha'] ?? null;
$setor = trim($_POST['setor']);
$status = $_POST['status'];
$pode_registrar = isset($_POST['pode_registrar']) ? 1 : 0;

try {
    $conn->begin_transaction();
    
    if ($secretariaId) {
        // Atualizar secretaria existente
        // Atualizar usuário
        $sqlUsuario = "UPDATE usuario SET 
                      nome = ?, 
                      email = ?, 
                      bi_numero = ?, 
                      status = ?
                      WHERE id_usuario = ?";
        $stmtUsuario = $conn->prepare($sqlUsuario);
        $stmtUsuario->bind_param('ssssi', $nome, $email, $bi_numero, $status, $usuarioId);
        $stmtUsuario->execute();
        
        // Atualizar secretaria
        $sqlSecretaria = "UPDATE secretaria SET 
                         setor = ?, 
                         pode_registrar = ?
                         WHERE id_secretaria = ?";
        $stmtSecretaria = $conn->prepare($sqlSecretaria);
        $stmtSecretaria->bind_param('sii', $setor, $pode_registrar, $secretariaId);
        $stmtSecretaria->execute();
        
        // Se foi fornecida uma senha, atualizar
        if (!empty($senha)) {
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
            $sqlSenha = "UPDATE usuario SET senha = ? WHERE id_usuario = ?";
            $stmtSenha = $conn->prepare($sqlSenha);
            $stmtSenha->bind_param('si', $senhaHash, $usuarioId);
            $stmtSenha->execute();
        }
        
        $message = "Secretaria atualizada com sucesso!";
    } else {
        // Criar nova secretaria
        // Primeiro criar o usuário
        if (empty($senha)) {
            throw new Exception("Senha é obrigatória para novo cadastro");
        }
        
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        
        $sqlUsuario = "INSERT INTO usuario 
                      (nome, email, senha, bi_numero, tipo, status) 
                      VALUES (?, ?, ?, ?, 'secretaria', ?)";
        $stmtUsuario = $conn->prepare($sqlUsuario);
        $stmtUsuario->bind_param('sssss', $nome, $email, $senhaHash, $bi_numero, $status);
        $stmtUsuario->execute();
        
        $usuarioId = $conn->insert_id;
        
        // Criar registro na tabela secretaria
        $sqlSecretaria = "INSERT INTO secretaria 
                         (setor, pode_registrar, usuario_id_usuario) 
                         VALUES (?, ?, ?)";
        $stmtSecretaria = $conn->prepare($sqlSecretaria);
        $stmtSecretaria->bind_param('sii', $setor, $pode_registrar, $usuarioId);
        $stmtSecretaria->execute();
        
        $message = "Secretaria cadastrada com sucesso!";
    }
    
    $conn->commit();
    echo json_encode(['success' => true, 'message' => $message]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
}