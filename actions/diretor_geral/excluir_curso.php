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

    // Obter ID do curso
    $cursoId = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if ($cursoId <= 0) {
        throw new Exception('ID do curso inválido');
    }

    // Iniciar transação para garantir integridade dos dados
    $conn->begin_transaction();

    try {
        // 1. Verificar se existem turmas associadas
        $queryTurmas = "SELECT COUNT(*) as total FROM turma WHERE curso_id_curso = ?";
        $stmtTurmas = $conn->prepare($queryTurmas);
        $stmtTurmas->bind_param('i', $cursoId);
        $stmtTurmas->execute();
        $resultTurmas = $stmtTurmas->get_result()->fetch_assoc();

        if ($resultTurmas['total'] > 0) {
            throw new Exception('Não é possível excluir o curso pois existem turmas associadas a ele.');
        }

        // 2. Excluir registros relacionados (em ordem reversa de dependência)
        // Professor tem disciplina
        $queryDeleteProfDisc = "DELETE pt FROM professor_tem_disciplina pt
                               JOIN disciplina d ON pt.disciplina_id_disciplina = d.id_disciplina
                               WHERE d.curso_id_curso = ?";
        $stmtDeleteProfDisc = $conn->prepare($queryDeleteProfDisc);
        $stmtDeleteProfDisc->bind_param('i', $cursoId);
        $stmtDeleteProfDisc->execute();

        // Disciplinas
        $queryDeleteDisc = "DELETE FROM disciplina WHERE curso_id_curso = ?";
        $stmtDeleteDisc = $conn->prepare($queryDeleteDisc);
        $stmtDeleteDisc->bind_param('i', $cursoId);
        $stmtDeleteDisc->execute();

        // Coordenadores
        $queryDeleteCoord = "DELETE FROM coordenador WHERE curso_id_curso = ?";
        $stmtDeleteCoord = $conn->prepare($queryDeleteCoord);
        $stmtDeleteCoord->bind_param('i', $cursoId);
        $stmtDeleteCoord->execute();

        // Professores
        $queryDeleteProf = "DELETE FROM professor WHERE curso_id_curso = ?";
        $stmtDeleteProf = $conn->prepare($queryDeleteProf);
        $stmtDeleteProf->bind_param('i', $cursoId);
        $stmtDeleteProf->execute();

        // Finalmente, excluir o curso
        $queryDeleteCurso = "DELETE FROM curso WHERE id_curso = ?";
        $stmtDeleteCurso = $conn->prepare($queryDeleteCurso);
        $stmtDeleteCurso->bind_param('i', $cursoId);
        $stmtDeleteCurso->execute();

        $conn->commit();
        $response['success'] = true;
        $response['message'] = 'Curso e todos os registros associados foram excluídos com sucesso.';
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
} finally {
    echo json_encode($response);
}