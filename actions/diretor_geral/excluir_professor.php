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

    // Obter ID do professor
    $professorId = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if ($professorId <= 0) {
        throw new Exception('ID do professor inválido');
    }

    // Iniciar transação para garantir integridade dos dados
    $conn->begin_transaction();

    try {
        // 1. Obter informações do professor e usuário associado
        $queryProfessor = "SELECT p.usuario_id_usuario, u.foto_perfil 
                          FROM professor p 
                          JOIN usuario u ON p.usuario_id_usuario = u.id_usuario 
                          WHERE p.id_professor = ?";
        $stmtProfessor = $conn->prepare($queryProfessor);
        $stmtProfessor->bind_param('i', $professorId);
        $stmtProfessor->execute();
        $resultProfessor = $stmtProfessor->get_result();
        
        if ($resultProfessor->num_rows === 0) {
            throw new Exception('Professor não encontrado');
        }
        
        $professor = $resultProfessor->fetch_assoc();
        $usuarioId = $professor['usuario_id_usuario'];
        $fotoPerfil = $professor['foto_perfil'];

        // 2. Remover associações com disciplinas (ON DELETE CASCADE já cuida disso)
        // 3. Remover associações com turmas (ON DELETE CASCADE já cuida disso)
        // 4. Remover o professor
        $queryDeleteProfessor = "DELETE FROM professor WHERE id_professor = ?";
        $stmtDeleteProfessor = $conn->prepare($queryDeleteProfessor);
        $stmtDeleteProfessor->bind_param('i', $professorId);
        $stmtDeleteProfessor->execute();

        // 5. Remover o usuário associado
        $queryDeleteUsuario = "DELETE FROM usuario WHERE id_usuario = ?";
        $stmtDeleteUsuario = $conn->prepare($queryDeleteUsuario);
        $stmtDeleteUsuario->bind_param('i', $usuarioId);
        $stmtDeleteUsuario->execute();

        // 6. Remover a foto de perfil se existir
        if ($fotoPerfil) {
            @unlink('../../' . $fotoPerfil);
        }

        $conn->commit();
        $response['success'] = true;
        $response['message'] = 'Professor e usuário associado foram removidos com sucesso.';
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
} finally {
    echo json_encode($response);
}