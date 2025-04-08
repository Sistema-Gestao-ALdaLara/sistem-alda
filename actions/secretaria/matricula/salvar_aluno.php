<?php
require_once 'conexao.php';

header('Content-Type: application/json');

// Verifica se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Recebe os dados do formulário
$alunoId = isset($_POST['alunoId']) ? intval($_POST['alunoId']) : null;
$nome = $conn->real_escape_string($_POST['nome']);
$bi_numero = $conn->real_escape_string($_POST['bi_numero']);
$email = $conn->real_escape_string($_POST['email']);
$numero_matricula = $conn->real_escape_string($_POST['numero_matricula']);
$id_curso = intval($_POST['id_curso']);
$id_turma = intval($_POST['id_turma']);
$ano_letivo = intval($_POST['ano_letivo']);

try {
    $conn->begin_transaction();
    
    if ($alunoId) {
        // Atualização de aluno existente
        // Primeiro atualiza o usuário
        $sql = "UPDATE usuario u 
                JOIN aluno a ON u.id_usuario = a.usuario_id_usuario
                SET u.nome = '$nome', u.email = '$email', u.bi_numero = '$bi_numero'
                WHERE a.id_aluno = $alunoId";
        
        if (!$conn->query($sql)) {
            throw new Exception("Erro ao atualizar usuário: " . $conn->error);
        }
        
        // Depois atualiza o aluno
        $sql = "UPDATE aluno SET 
                numero_matricula = '$numero_matricula',
                turma_id_turma = $id_turma,
                curso_id_curso = $id_curso,
                ano_letivo = $ano_letivo
                WHERE id_aluno = $alunoId";
    } else {
        // Inserção de novo aluno
        // Primeiro insere o usuário
        $sql = "INSERT INTO usuario (nome, email, senha, bi_numero, tipo, status) 
                VALUES ('$nome', '$email', 'senha_temp', '$bi_numero', 'aluno', 'ativo')";
        
        if (!$conn->query($sql)) {
            throw new Exception("Erro ao inserir usuário: " . $conn->error);
        }
        
        $usuario_id = $conn->insert_id;
        
        // Depois insere o aluno
        $sql = "INSERT INTO aluno (numero_matricula, ano_letivo, usuario_id_usuario, turma_id_turma, curso_id_curso) 
                VALUES ('$numero_matricula', $ano_letivo, $usuario_id, $id_turma, $id_curso)";
    }
    
    if (!$conn->query($sql)) {
        throw new Exception("Erro ao salvar aluno: " . $conn->error);
    }
    
    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Aluno salvo com sucesso']);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>