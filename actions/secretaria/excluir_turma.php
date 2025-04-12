<?php
require_once '../../database/conexao.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    $id = intval($_POST['id']);

    // Verifica se a turma tem alunos matriculados
    $queryCheck = "SELECT COUNT(*) AS total FROM matricula WHERE turma_id_turma = ?";
    $stmtCheck = $conn->prepare($queryCheck);
    $stmtCheck->bind_param("i", $id);
    $stmtCheck->execute();
    $result = $stmtCheck->get_result();
    $row = $result->fetch_assoc();

    if ($row['total'] > 0) {
        throw new Exception('Não é possível excluir a turma pois existem alunos matriculados');
    }

    // Remove primeiro as associações com professores
    $queryDeleteAssoc = "DELETE FROM professor_tem_turma WHERE turma_id_turma = ?";
    $stmtDeleteAssoc = $conn->prepare($queryDeleteAssoc);
    $stmtDeleteAssoc->bind_param("i", $id);
    $stmtDeleteAssoc->execute();

    // Depois exclui a turma
    $queryDelete = "DELETE FROM turma WHERE id_turma = ?";
    $stmtDelete = $conn->prepare($queryDelete);
    $stmtDelete->bind_param("i", $id);

    if ($stmtDelete->execute()) {
        $response['success'] = true;
        $response['message'] = 'Turma excluída com sucesso!';
    } else {
        throw new Exception('Erro ao excluir a turma');
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
$conn->close();
?>