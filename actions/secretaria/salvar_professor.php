<?php
require_once '../../database/conexao.php';

header('Content-Type: application/json');

// Verifica se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Recebe os dados do formulário
$professorId = isset($_POST['professorId']) ? intval($_POST['professorId']) : null;
$nome = $conn->real_escape_string($_POST['nome']);
$bi_numero = $conn->real_escape_string($_POST['bi_numero']);
$email = $conn->real_escape_string($_POST['email']);
$senha = $_POST['senha'] ?? null;
$id_curso = intval($_POST['id_curso']);
$status = $conn->real_escape_string($_POST['status']);
$disciplinas = isset($_POST['disciplinas']) ? $_POST['disciplinas'] : [];

try {
    $conn->begin_transaction();
    
    if ($professorId) {
        // Atualização de professor existente
        
        // 1. Atualiza o usuário
        $sql = "UPDATE usuario u
                JOIN professor p ON u.id_usuario = p.usuario_id_usuario
                SET u.nome = '$nome',
                    u.email = '$email',
                    u.bi_numero = '$bi_numero',
                    u.status = '$status'
                WHERE p.id_professor = $professorId";
        
        if (!$conn->query($sql)) {
            throw new Exception("Erro ao atualizar usuário: " . $conn->error);
        }
        
        // Se foi fornecida uma nova senha, atualiza
        if (!empty($senha)) {
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $sql = "UPDATE usuario u
                    JOIN professor p ON u.id_usuario = p.usuario_id_usuario
                    SET u.senha = '$senha_hash'
                    WHERE p.id_professor = $professorId";
            
            if (!$conn->query($sql)) {
                throw new Exception("Erro ao atualizar senha: " . $conn->error);
            }
        }
        
        // 2. Atualiza o professor (curso)
        $sql = "UPDATE professor SET 
                curso_id_curso = $id_curso
                WHERE id_professor = $professorId";
        
        if (!$conn->query($sql)) {
            throw new Exception("Erro ao atualizar professor: " . $conn->error);
        }
        
        // 3. Atualiza as disciplinas do professor
        // Primeiro remove todas as associações atuais
        $sql = "UPDATE disciplina SET professor_id_professor = NULL WHERE professor_id_professor = $professorId";
        if (!$conn->query($sql)) {
            throw new Exception("Erro ao remover disciplinas anteriores: " . $conn->error);
        }
        
        // Depois associa as novas disciplinas
        if (!empty($disciplinas)) {
            // Sanitiza os IDs das disciplinas
            $disciplinas_ids = array_map('intval', $disciplinas);
            $ids_str = implode(',', $disciplinas_ids);
            
            $sql = "UPDATE disciplina SET professor_id_professor = $professorId 
                    WHERE id_disciplina IN ($ids_str) AND curso_id_curso = $id_curso";
            
            if (!$conn->query($sql)) {
                throw new Exception("Erro ao atualizar disciplinas: " . $conn->error);
            }
        }
        
        $message = "Professor e suas disciplinas atualizados com sucesso!";
    } else {
        // Cadastro de novo professor
        
        // 1. Verifica se o email já existe
        $sql = "SELECT id_usuario FROM usuario WHERE email = '$email'";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            throw new Exception("Este email já está cadastrado no sistema");
        }
        
        // 2. Insere o usuário (com tipo 'professor')
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
        $sql = "INSERT INTO usuario 
                (nome, email, senha, bi_numero, tipo, status) 
                VALUES ('$nome', '$email', '$senha_hash', '$bi_numero', 'professor', '$status')";
        
        if (!$conn->query($sql)) {
            throw new Exception("Erro ao cadastrar usuário: " . $conn->error);
        }
        
        $usuario_id = $conn->insert_id;
        
        // 3. Insere o professor
        $sql = "INSERT INTO professor 
                (usuario_id_usuario, curso_id_curso) 
                VALUES ($usuario_id, $id_curso)";
        
        if (!$conn->query($sql)) {
            throw new Exception("Erro ao cadastrar professor: " . $conn->error);
        }
        
        $professorId = $conn->insert_id;
        
        // 4. Associa as disciplinas ao novo professor
        if (!empty($disciplinas)) {
            $disciplinas_ids = array_map('intval', $disciplinas);
            $ids_str = implode(',', $disciplinas_ids);
            
            $sql = "UPDATE disciplina SET professor_id_professor = $professorId 
                    WHERE id_disciplina IN ($ids_str) AND curso_id_curso = $id_curso";
            
            if (!$conn->query($sql)) {
                throw new Exception("Erro ao associar disciplinas: " . $conn->error);
            }
        }
        
        $message = "Professor cadastrado e disciplinas associadas com sucesso!";
    }
    
    $conn->commit();
    echo json_encode(['success' => true, 'message' => $message]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>