<?php
require_once '../../database/conexao.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : null;

if (!$id) {
    die(json_encode(['error' => 'ID do professor não fornecido']));
}

$sql = "SELECT 
            p.id_professor, 
            u.nome, 
            u.email, 
            u.bi_numero,
            u.status,
            p.curso_id_curso as id_curso
        FROM professor p
        JOIN usuario u ON p.usuario_id_usuario = u.id_usuario
        WHERE p.id_professor = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode($result->fetch_assoc());
} else {
    echo json_encode(['error' => 'Professor não encontrado']);
}
?>