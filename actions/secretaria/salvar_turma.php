<?php
require_once '../../database/conexao.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Erro desconhecido'];

try {
    // Verifica se todos os campos necessários foram enviados
    if (!isset($_POST['nome']) || !isset($_POST['id_curso']) || !isset($_POST['turno']) || !isset($_POST['classe'])) {
        throw new Exception('Dados incompletos');
    }

    $turmaId = isset($_POST['turmaId']) ? intval($_POST['turmaId']) : null;
    $nome = trim($_POST['nome']);
    $id_curso = intval($_POST['id_curso']);
    $turno = trim($_POST['turno']);
    $classe = trim($_POST['classe']);

    if (empty($nome) || $id_curso <= 0) {
        throw new Exception('Dados inválidos');
    }

    if ($turmaId) {
        // Atualizar turma existente
        $query = "UPDATE turma SET nome = ?, curso_id_curso = ?, turno = ?, classe = ? WHERE id_turma = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sissi", $nome, $id_curso, $turno, $classe, $turmaId);
        $action = 'atualizada';
    } else {
        // Criar nova turma
        $query = "INSERT INTO turma (nome, curso_id_curso, turno, classe) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("siss", $nome, $id_curso, $turno, $classe);
        $action = 'criada';
    }

    if ($stmt->execute()) {
        $response = [
            'success' => true,
            'message' => "Turma $action com sucesso!",
            'id' => $turmaId ?: $conn->insert_id
        ];
    } else {
        throw new Exception('Erro ao salvar no banco de dados: ' . $conn->error);
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

// Garante que a conexão está fechada
if ($conn) {
    $conn->close();
}

// Retorna a resposta como JSON
echo json_encode($response);
exit;
?>