<?php
require_once '../../database/conexao.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id_matricula = intval($_POST['id']);
    
    // Inicia transação para garantir integridade dos dados
    $conn->begin_transaction();
    
    try {
        // 1. Primeiro obtemos o ID do aluno associado à matrícula
        $stmt = $conn->prepare("SELECT aluno_id_aluno FROM matricula WHERE id_matricula = ?");
        $stmt->bind_param("i", $id_matricula);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception('Matrícula não encontrada');
        }
        
        $row = $result->fetch_assoc();
        $id_aluno = $row['aluno_id_aluno'];
        
        // 2. Obtemos o ID do usuário associado ao aluno
        $stmt = $conn->prepare("SELECT usuario_id_usuario FROM aluno WHERE id_aluno = ?");
        $stmt->bind_param("i", $id_aluno);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception('Aluno não encontrado');
        }
        
        $row = $result->fetch_assoc();
        $id_usuario = $row['usuario_id_usuario'];
        
        // 3. Excluímos os registros relacionados na ordem correta
        
        // Primeiro as frequências do aluno
        $stmt = $conn->prepare("DELETE FROM frequencia_aluno WHERE aluno_id_aluno = ?");
        $stmt->bind_param("i", $id_aluno);
        $stmt->execute();
        
        // Depois as notas do aluno
        $stmt = $conn->prepare("DELETE FROM nota WHERE aluno_id_aluno = ?");
        $stmt->bind_param("i", $id_aluno);
        $stmt->execute();
        
        // Depois a matrícula
        $stmt = $conn->prepare("DELETE FROM matricula WHERE aluno_id_aluno = ?");
        $stmt->bind_param("i", $id_aluno);
        $stmt->execute();
        
        // Depois o registro do aluno
        $stmt = $conn->prepare("DELETE FROM aluno WHERE id_aluno = ?");
        $stmt->bind_param("i", $id_aluno);
        $stmt->execute();
        
        // Finalmente o usuário (isso removerá automaticamente quaisquer relações em materiais_apoio_tem_usuario devido ao ON DELETE CASCADE)
        $stmt = $conn->prepare("DELETE FROM usuario WHERE id_usuario = ?");
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        
        // Confirma a transação
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Aluno e todos os dados relacionados foram excluídos com sucesso'
        ]);
        
    } catch (Exception $e) {
        // Desfaz a transação em caso de erro
        $conn->rollback();
        
        echo json_encode([
            'success' => false,
            'message' => 'Erro ao excluir aluno: ' . $e->getMessage()
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