<?php
require_once '../../database/conexao.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    $turmaId = intval($_POST['turmaId']);
    $professores = isset($_POST['professores']) ? $_POST['professores'] : [];

    // Primeiro remove todas as associações existentes para esta turma
    $queryDelete = "DELETE FROM professor_tem_turma WHERE turma_id_turma = ?";
    $stmtDelete = $conn->prepare($queryDelete);
    $stmtDelete->bind_param("i", $turmaId);
    $stmtDelete->execute();

    // Depois insere as novas associações
    if (!empty($professores)) {
        $queryInsert = "INSERT INTO professor_tem_turma (professor_id_professor, turma_id_turma) VALUES (?, ?)";
        $stmtInsert = $conn->prepare($queryInsert);
        
        foreach ($professores as $profId) {
            $profId = intval($profId);
            $stmtInsert->bind_param("ii", $profId, $turmaId);
            $stmtInsert->execute();
        }
    }

    $response['success'] = true;
    $response['message'] = 'Professores atualizados com sucesso!';
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
$conn->close();
?>