<?php
require_once '../../database/conexao.php';
require_once '../../includes/common/permissoes.php';
require_once '../../process/verificar_sessao.php';

header('Content-Type: application/json');

// Verificar se o usuário é professor
verificarPermissao(['professor']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_nota'])) {
    $id_nota = intval($_POST['id_nota']);
    $id_usuario = $_SESSION['id_usuario'];
    
    // Verificar se a nota pertence a uma disciplina do professor
    $query = "SELECT n.id_nota 
              FROM nota n
              JOIN disciplina d ON n.disciplina_id_disciplina = d.id_disciplina
              JOIN professor p ON d.professor_id_professor = p.id_professor
              WHERE n.id_nota = ? AND p.usuario_id_usuario = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $id_nota, $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Excluir a nota
        $query_delete = "DELETE FROM nota WHERE id_nota = ?";
        $stmt_delete = $conn->prepare($query_delete);
        $stmt_delete->bind_param("i", $id_nota);
        
        if ($stmt_delete->execute()) {
            echo json_encode(['success' => true, 'message' => 'Nota excluída com sucesso']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao excluir nota']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Você não tem permissão para excluir esta nota']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Requisição inválida']);
}

$conn->close();
?>