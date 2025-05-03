<?php
require_once '../../database/conexao.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    // Verificar se é POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método não permitido');
    }

    // Verificar permissões
    require_once '../../includes/common/permissoes.php';
    verificarPermissao(['diretor_geral']);

    // Obter dados do formulário
    $professorId = isset($_POST['professorId']) ? intval($_POST['professorId']) : 0;
    $nome = trim($_POST['nome']);
    $bi_numero = trim($_POST['bi_numero']);
    $email = trim($_POST['email']);
    $senha = isset($_POST['senha']) ? $_POST['senha'] : '';
    $id_curso = intval($_POST['id_curso']);
    $status = $_POST['status'];
    $tipo = 'professor';

    // Validações
    if (empty($nome) || empty($bi_numero) || empty($email) || empty($id_curso)) {
        throw new Exception('Todos os campos obrigatórios devem ser preenchidos');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Email inválido');
    }

    if (!preg_match('/^[0-9]{9}[A-Z]{2}[0-9]{3}$/', $bi_numero)) {
        throw new Exception('Número de BI inválido. Formato correto: 123456789LA123');
    }

    // Verificar se email ou BI já existem
    $queryVerifica = "SELECT id_usuario FROM usuario WHERE (email = ? OR bi_numero = ?) AND id_usuario != 
                     (SELECT usuario_id_usuario FROM professor WHERE id_professor = ?)";
    $stmtVerifica = $conn->prepare($queryVerifica);
    $stmtVerifica->bind_param('ssi', $email, $bi_numero, $professorId);
    $stmtVerifica->execute();
    $resultVerifica = $stmtVerifica->get_result();

    if ($resultVerifica->num_rows > 0) {
        throw new Exception('Email ou número de BI já estão em uso por outro usuário');
    }

    // Processar upload da foto
    $foto_perfil = null;
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] == UPLOAD_ERR_OK) {
        $file = $_FILES['foto_perfil'];
        
        // Verificar tipo e tamanho do arquivo
        $allowedTypes = ['image/jpeg', 'image/png'];
        $maxSize = 2 * 1024 * 1024; // 2MB
        
        if (!in_array($file['type'], $allowedTypes)) {
            throw new Exception('Tipo de arquivo não permitido. Use JPEG ou PNG.');
        }
        
        if ($file['size'] > $maxSize) {
            throw new Exception('Tamanho do arquivo excede o limite de 2MB');
        }
        
        // Mover arquivo para diretório de uploads
        $uploadDir = '../../uploads/professores/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'prof_' . time() . '.' . $ext;
        $destination = $uploadDir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            $foto_perfil = 'uploads/professores/' . $filename;
        } else {
            throw new Exception('Erro ao fazer upload da foto');
        }
    }

    // Iniciar transação
    $conn->begin_transaction();

    try {
        if ($professorId > 0) {
            // Atualizar professor existente
            $professor = getProfessorById($professorId, $conn);
            $usuarioId = $professor['usuario_id_usuario'];
            
            // Atualizar usuário
            $queryUsuario = "UPDATE usuario SET nome = ?, email = ?, bi_numero = ?, status = ?, tipo = ?";
            $params = [$nome, $email, $bi_numero, $status, $tipo];
            $types = "sssss";
            
            // Se senha foi fornecida, atualizar
            if (!empty($senha)) {
                if (strlen($senha) < 6) {
                    throw new Exception('A senha deve ter no mínimo 6 caracteres');
                }
                $hashedPassword = password_hash($senha, PASSWORD_DEFAULT);
                $queryUsuario .= ", senha = ?";
                $params[] = $hashedPassword;
                $types .= "s";
            }
            
            // Se foto foi enviada, atualizar
            if ($foto_perfil) {
                $queryUsuario .= ", foto_perfil = ?";
                $params[] = $foto_perfil;
                $types .= "s";
                
                // Remover foto antiga se existir
                if ($professor['foto_perfil']) {
                    @unlink('../../' . $professor['foto_perfil']);
                }
            }
            
            $queryUsuario .= " WHERE id_usuario = ?";
            $params[] = $usuarioId;
            $types .= "i";
            
            $stmtUsuario = $conn->prepare($queryUsuario);
            $stmtUsuario->bind_param($types, ...$params);
            $stmtUsuario->execute();
            
            // Atualizar professor
            $queryProfessor = "UPDATE professor SET curso_id_curso = ? WHERE id_professor = ?";
            $stmtProfessor = $conn->prepare($queryProfessor);
            $stmtProfessor->bind_param('ii', $id_curso, $professorId);
            $stmtProfessor->execute();
            
            $action = 'atualizado';
        } else {
            // Criar novo professor
            if (empty($senha)) {
                throw new Exception('Senha é obrigatória para novo professor');
            }
            
            if (strlen($senha) < 6) {
                throw new Exception('A senha deve ter no mínimo 6 caracteres');
            }
            
            $hashedPassword = password_hash($senha, PASSWORD_DEFAULT);
            
            // Inserir usuário
            $queryUsuario = "INSERT INTO usuario (nome, email, senha, bi_numero, tipo, status, foto_perfil) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmtUsuario = $conn->prepare($queryUsuario);
            $stmtUsuario->bind_param('sssssss', $nome, $email, $hashedPassword, $bi_numero, $tipo, $status, $foto_perfil);
            $stmtUsuario->execute();
            $usuarioId = $conn->insert_id;
            
            // Inserir professor
            $queryProfessor = "INSERT INTO professor (usuario_id_usuario, curso_id_curso) VALUES (?, ?)";
            $stmtProfessor = $conn->prepare($queryProfessor);
            $stmtProfessor->bind_param('ii', $usuarioId, $id_curso);
            $stmtProfessor->execute();
            
            $action = 'cadastrado';
        }
        
        $conn->commit();
        $response['success'] = true;
        $response['message'] = "Professor $action com sucesso!";
    } catch (Exception $e) {
        $conn->rollback();
        
        // Remover arquivo de foto se houve erro
        if ($foto_perfil) {
            @unlink('../../' . $foto_perfil);
        }
        
        throw $e;
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
} finally {
    echo json_encode($response);
}

function getProfessorById($id, $conn) {
    $query = "SELECT p.*, u.foto_perfil, u.id_usuario as usuario_id_usuario 
              FROM professor p 
              JOIN usuario u ON p.usuario_id_usuario = u.id_usuario 
              WHERE p.id_professor = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Professor não encontrado');
    }
    
    return $result->fetch_assoc();
}