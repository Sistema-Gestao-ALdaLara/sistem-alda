<?php
require_once '../../database/conexao.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : null;

if (!$id) {
    die(json_encode(['error' => 'ID da secretaria não fornecido']));
}

$sql = "SELECT 
            s.id_secretaria,
            s.setor,
            s.pode_registrar,
            u.id_usuario,
            u.nome, 
            u.email,
            u.bi_numero,
            u.status
        FROM secretaria s
        JOIN usuario u ON s.usuario_id_usuario = u.id_usuario
        WHERE s.id_secretaria = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode($result->fetch_assoc());
} else {
    echo json_encode(['error' => 'Secretaria não encontrada']);
}
?>