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

    // Obter dados do formulário
    $cursoId = isset($_POST['cursoId']) ? intval($_POST['cursoId']) : 0;
    $nome = trim($_POST['nome']);

    // Validações
    if (empty($nome)) {
        throw new Exception('O nome do curso é obrigatório');
    }

    // Verificar se o curso já existe (para evitar duplicatas)
    $queryVerifica = "SELECT id_curso FROM curso WHERE nome = ? AND id_curso != ?";
    $stmtVerifica = $conn->prepare($queryVerifica);
    $stmtVerifica->bind_param('si', $nome, $cursoId);
    $stmtVerifica->execute();
    $resultVerifica = $stmtVerifica->get_result();

    if ($resultVerifica->num_rows > 0) {
        throw new Exception('Já existe um curso com este nome');
    }

    // Operação de inserção ou atualização
    if ($cursoId > 0) {
        // Atualizar curso existente
        $query = "UPDATE curso SET nome = ? WHERE id_curso = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('si', $nome, $cursoId);
        $action = 'atualizado';
    } else {
        // Inserir novo curso
        $query = "INSERT INTO curso (nome) VALUES (?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('s', $nome);
        $action = 'cadastrado';
    }

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = "Curso $action com sucesso!";
    } else {
        throw new Exception('Erro ao salvar curso no banco de dados');
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
} finally {
    echo json_encode($response);
}