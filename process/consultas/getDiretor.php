<?php
require_once '../../database/conexao.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : null;

if (!$id) {
    die(json_encode(['error' => 'ID do diretor não fornecido']));
}

$sql = "SELECT 
            id_usuario,
            nome, 
            email, 
            bi_numero,
            tipo,
            status
        FROM usuario
        WHERE id_usuario = ? AND tipo IN ('diretor_geral', 'diretor_pedagogico')";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode($result->fetch_assoc());
} else {
    echo json_encode(['error' => 'Diretor não encontrado']);
}
?>