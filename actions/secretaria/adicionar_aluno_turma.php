<?php
require_once '../../database/conexao.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    $id_matricula = intval($_POST['id_matricula']);
    $id_turma = intval($_POST['id_turma']);
    $ano_letivo = intval($_POST['ano_letivo']);

    // Verifica se a matrícula existe
    $queryCheck = "SELECT id_matricula FROM matricula WHERE id_matricula = ?";
    $stmtCheck = $conn->prepare($queryCheck);
    $stmtCheck->bind_param("i", $id_matricula);
    $stmtCheck->execute();
    $result = $stmtCheck->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Matrícula não encontrada');
    }

    // Atualiza a turma do aluno
    $queryUpdate = "UPDATE matricula SET turma_id_turma = ? WHERE id_matricula = ? AND ano_letivo = ?";
    $stmtUpdate = $conn->prepare($queryUpdate);
    $stmtUpdate->bind_param("iii", $id_turma, $id_matricula, $ano_letivo);

    if ($stmtUpdate->execute()) {
        $response['success'] = true;
        $response['message'] = 'Aluno adicionado à turma com sucesso!';
    } else {
        throw new Exception('Erro ao atualizar a matrícula');
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
$conn->close();