<?php
require_once '../../database/conexao.php';

header('Content-Type: application/json');


// Processar dados do formulário
$secretariaId = isset($_POST['secretariaId']) ? intval($_POST['secretariaId']) : 0;
$usuarioId = isset($_POST['usuarioId']) ? intval($_POST['usuarioId']) : 0;
$nome = trim($_POST['nome']);
$bi_numero = trim($_POST['bi_numero']);
$email = trim($_POST['email']);
$senha = isset($_POST['senha']) ? trim($_POST['senha']) : '';
$setor = trim($_POST['setor']);
$status = trim($_POST['status']);
$pode_registrar = isset($_POST['pode_registrar']) ? 1 : 0;

// Validações básicas
if (empty($nome) || empty($bi_numero) || empty($email) || empty($setor)) {
    echo json_encode(['success' => false, 'message' => 'Preencha todos os campos obrigatórios']);
    exit;
}

// Validar formato do BI
if (!preg_match('/^[0-9]{9}[A-Z]{2}[0-9]{3}$/', $bi_numero)) {
    echo json_encode(['success' => false, 'message' => 'Número de BI inválido. Formato correto: 123456789LA123']);
    exit;
}

// Verificar se é um novo registro ou edição
if ($secretariaId == 0) {
    // Novo registro - verificar senha
    if (strlen($senha) < 8) {
        echo json_encode(['success' => false, 'message' => 'A senha deve ter no mínimo 8 caracteres']);
        exit;
    }
    
    // Verificar se email já existe
    $stmt = $conn->prepare("SELECT id_usuario FROM usuario WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Este email já está em uso']);
        exit;
    }
    
    // Verificar se BI já existe
    $stmt = $conn->prepare("SELECT id_usuario FROM usuario WHERE bi_numero = ?");
    $stmt->bind_param('s', $bi_numero);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Este número de BI já está em uso']);
        exit;
    }
    
    // Iniciar transação
    $conn->begin_transaction();
    
    try {
        // Criar usuário
        $senhaHash = password_hash($senha, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("INSERT INTO usuario (nome, email, senha, bi_numero, tipo, status) VALUES (?, ?, ?, ?, 'secretaria', ?)");
        $stmt->bind_param('sssss', $nome, $email, $senhaHash, $bi_numero, $status);
        $stmt->execute();
        
        $usuarioId = $conn->insert_id;
        
        // Processar upload da foto de perfil
        $foto_perfil = null;
        if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] == UPLOAD_ERR_OK) {
            $extensao = pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION);
            $nomeArquivo = 'perfil_' . $usuarioId . '.' . $extensao;
            $diretorio = '../../uploads/perfil/';
            
            if (!file_exists($diretorio)) {
                mkdir($diretorio, 0777, true);
            }
            
            if (move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $diretorio . $nomeArquivo)) {
                $foto_perfil = $nomeArquivo;
                
                // Atualizar usuário com o nome da foto
                $stmt = $conn->prepare("UPDATE usuario SET foto_perfil = ? WHERE id_usuario = ?");
                $stmt->bind_param('si', $foto_perfil, $usuarioId);
                $stmt->execute();
            }
        }
        
        // Criar secretaria
        $stmt = $conn->prepare("INSERT INTO secretaria (setor, pode_registrar, usuario_id_usuario) VALUES (?, ?, ?)");
        $stmt->bind_param('sii', $setor, $pode_registrar, $usuarioId);
        $stmt->execute();
        
        $conn->commit();
        
        echo json_encode(['success' => true, 'message' => 'Secretaria cadastrada com sucesso']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Erro ao cadastrar secretaria: ' . $e->getMessage()]);
    }
} else {
    // Edição de registro existente
    $conn->begin_transaction();
    
    try {
        // Atualizar usuário
        $updateUsuario = "UPDATE usuario SET nome = ?, email = ?, bi_numero = ?, status = ?";
        $params = [$nome, $email, $bi_numero, $status];
        $types = "ssss";
        
        // Se senha foi fornecida, atualizar também
        if (!empty($senha)) {
            $senhaHash = password_hash($senha, PASSWORD_BCRYPT);
            $updateUsuario .= ", senha = ?";
            $params[] = $senhaHash;
            $types .= "s";
        }
        
        // Processar upload da foto de perfil
        if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] == UPLOAD_ERR_OK) {
            $extensao = pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION);
            $nomeArquivo = 'perfil_' . $usuarioId . '.' . $extensao;
            $diretorio = '../../uploads/perfil/';
            
            if (!file_exists($diretorio)) {
                mkdir($diretorio, 0777, true);
            }
            
            // Remover foto antiga se existir
            $stmt = $conn->prepare("SELECT foto_perfil FROM usuario WHERE id_usuario = ?");
            $stmt->bind_param('i', $usuarioId);
            $stmt->execute();
            $result = $stmt->get_result();
            $fotoAntiga = $result->fetch_assoc()['foto_perfil'];
            
            if ($fotoAntiga && file_exists($diretorio . $fotoAntiga)) {
                unlink($diretorio . $fotoAntiga);
            }
            
            if (move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $diretorio . $nomeArquivo)) {
                $updateUsuario .= ", foto_perfil = ?";
                $params[] = $nomeArquivo;
                $types .= "s";
            }
        }
        
        $updateUsuario .= " WHERE id_usuario = ?";
        $params[] = $usuarioId;
        $types .= "i";
        
        $stmt = $conn->prepare($updateUsuario);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        
        // Atualizar secretaria
        $stmt = $conn->prepare("UPDATE secretaria SET setor = ?, pode_registrar = ? WHERE id_secretaria = ?");
        $stmt->bind_param('sii', $setor, $pode_registrar, $secretariaId);
        $stmt->execute();
        
        $conn->commit();
        
        echo json_encode(['success' => true, 'message' => 'Secretaria atualizada com sucesso']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar secretaria: ' . $e->getMessage()]);
    }
}
?>