<?php
require_once '../../database/conexao.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    $id_matricula = intval($_POST['id_matricula']);

    // Remove o aluno da turma (define turma_id_turma como NULL)
    $query = "UPDATE matricula SET turma_id_turma = NULL WHERE id_matricula = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_matricula);

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Aluno removido da turma com sucesso!';
    } else {
        throw new Exception('Erro ao remover aluno da turma');
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
$conn->close();
?>