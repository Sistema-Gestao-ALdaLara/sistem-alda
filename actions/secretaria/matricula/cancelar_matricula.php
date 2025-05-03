<?php
require_once '../../../database/conexao.php';
require_once '../../../process/verificar_sessao.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    // Verificar se é POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método não permitido');
    }

    // Verificar permissões
    require_once '../../../includes/common/permissoes.php';
    verificarPermissao(['secretaria', 'diretor_geral', 'diretor_pedagogico']);

    // Obter ID da matrícula
    $id_matricula = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if ($id_matricula <= 0) {
        throw new Exception('ID da matrícula inválido');
    }

    // Atualizar status da matrícula
    $query = "UPDATE matricula SET status_matricula = 'cancelada' WHERE id_matricula = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $id_matricula);
    
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Matrícula cancelada com sucesso';
    } else {
        throw new Exception('Erro ao cancelar matrícula no banco de dados');
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
} finally {
    echo json_encode($response);
}