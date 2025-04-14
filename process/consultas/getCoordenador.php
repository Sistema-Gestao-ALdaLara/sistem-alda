<?php
require_once '../../database/conexao.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : null;

if (!$id) {
    die(json_encode(['error' => 'ID do coordenador não fornecido']));
}

$sql = "SELECT 
            c.id_coordenador, 
            u.nome, 
            u.email, 
            u.bi_numero,
            u.status,
            c.curso_id_curso as id_curso
        FROM coordenador c
        JOIN usuario u ON c.usuario_id_usuario = u.id_usuario
        WHERE c.id_coordenador = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode($result->fetch_assoc());
} else {
    echo json_encode(['error' => 'Coordenador não encontrado']);
}
?>
