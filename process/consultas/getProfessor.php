<?php
require_once '../../database/conexao.php';

header('Content-Type: application/json');

$response = ['success' => false, 'data' => null];

try {
    // Verificar se é GET
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        throw new Exception('Método não permitido');
    }

    // Verificar permissões
    require_once '../../includes/common/permissoes.php';
    verificarPermissao(['diretor_geral', 'secretaria']);

    // Obter ID do professor
    $professorId = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if ($professorId <= 0) {
        throw new Exception('ID do professor inválido');
    }

    // Consultar professor
    $query = "SELECT p.id_professor, u.nome, u.email, u.bi_numero, u.status, u.foto_perfil, 
                     p.curso_id_curso as id_curso
              FROM professor p
              JOIN usuario u ON p.usuario_id_usuario = u.id_usuario
              WHERE p.id_professor = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $professorId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $response['success'] = true;
        $response['data'] = $result->fetch_assoc();
    } else {
        throw new Exception('Professor não encontrado');
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
} finally {
    echo json_encode($response);
}