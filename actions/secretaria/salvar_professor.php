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
$disciplinas = isset($_POST['disciplinas']) ? array_map('intval', $_POST['disciplinas']) : [];

try {
    $conn->begin_transaction();

    if ($professorId) {
        // Atualização de professor existente

        // Atualiza dados do usuário
        $sql = "UPDATE usuario 
                JOIN professor ON usuario.id_usuario = professor.usuario_id_usuario
                SET usuario.nome = '$nome',
                    usuario.email = '$email',
                    usuario.bi_numero = '$bi_numero',
                    usuario.status = '$status'
                WHERE professor.id_professor = $professorId";

        if (!$conn->query($sql)) {
            throw new Exception("Erro ao atualizar usuário: " . $conn->error);
        }

        // Atualiza senha se fornecida
        if (!empty($senha)) {
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $sql = "UPDATE usuario 
                    JOIN professor ON usuario.id_usuario = professor.usuario_id_usuario
                    SET usuario.senha = '$senha_hash'
                    WHERE professor.id_professor = $professorId";

            if (!$conn->query($sql)) {
                throw new Exception("Erro ao atualizar senha: " . $conn->error);
            }
        }

        // Atualiza curso
        $sql = "UPDATE professor SET curso_id_curso = $id_curso WHERE id_professor = $professorId";
        if (!$conn->query($sql)) {
            throw new Exception("Erro ao atualizar professor: " . $conn->error);
        }

        // Remove as disciplinas atuais
        $sql = "DELETE FROM professor_tem_disciplina WHERE professor_id_professor = $professorId";
        if (!$conn->query($sql)) {
            throw new Exception("Erro ao limpar disciplinas anteriores: " . $conn->error);
        }

        // Insere novas disciplinas
        foreach ($disciplinas as $id_disciplina) {
            $id_disciplina = intval($id_disciplina);
            $sql = "INSERT INTO professor_tem_disciplina (professor_id_professor, disciplina_id_disciplina) 
                    VALUES ($professorId, $id_disciplina)";
            if (!$conn->query($sql)) {
                throw new Exception("Erro ao associar disciplina ID $id_disciplina: " . $conn->error);
            }
        }


        $message = "Professor atualizado com sucesso!";
    } else {
        // Cadastro de novo professor

        // Verifica se email já existe
        $sql = "SELECT id_usuario FROM usuario WHERE email = '$email'";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            throw new Exception("Este email já está cadastrado no sistema.");
        }

        // Insere usuário
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
        $sql = "INSERT INTO usuario (nome, email, senha, bi_numero, tipo, status)
                VALUES ('$nome', '$email', '$senha_hash', '$bi_numero', 'professor', '$status')";
        if (!$conn->query($sql)) {
            throw new Exception("Erro ao cadastrar usuário: " . $conn->error);
        }

        $usuario_id = $conn->insert_id;

        // Insere professor
        $sql = "INSERT INTO professor (usuario_id_usuario, curso_id_curso)
                VALUES ($usuario_id, $id_curso)";
        if (!$conn->query($sql)) {
            throw new Exception("Erro ao cadastrar professor: " . $conn->error);
        }

        $professorId = $conn->insert_id;

        // Insere novas disciplinas
        foreach ($disciplinas as $id_disciplina) {
            $id_disciplina = intval($id_disciplina);
            $sql = "INSERT INTO professor_tem_disciplina (professor_id_professor, disciplina_id_disciplina) 
                    VALUES ($professorId, $id_disciplina)";
            if (!$conn->query($sql)) {
                throw new Exception("Erro ao associar disciplina ID $id_disciplina: " . $conn->error);
            }
        }

        $message = "Professor cadastrado com sucesso!";
    }

    $conn->commit();
    echo json_encode(['success' => true, 'message' => $message]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
