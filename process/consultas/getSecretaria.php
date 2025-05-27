<?php
require_once '../../database/conexao.php';

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'ID não fornecido']);
    exit;
}

$id = (int)$_GET['id'];

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
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode($result->fetch_assoc());
} else {
    echo json_encode(['error' => 'Secretaria não encontrada']);
}