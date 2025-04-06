<?php
require_once "conexao.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id_matricula = intval($_POST['id']);
    
    // Verifica se a matrícula existe
    $stmt = $conn->prepare("SELECT id_matricula FROM matricula WHERE id_matricula = ?");
    $stmt->bind_param("i", $id_matricula);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Matrícula não encontrada'
        ]);
        exit();
    }
    
    // Atualiza o status da matrícula para cancelada
    $stmt = $conn->prepare("UPDATE matricula SET status_matricula = 'cancelada' WHERE id_matricula = ?");
    $stmt->bind_param("i", $id_matricula);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Matrícula cancelada com sucesso'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Erro ao cancelar matrícula: ' . $conn->error
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Requisição inválida'
    ]);
}

$conn->close();
?>