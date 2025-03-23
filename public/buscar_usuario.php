<?php
header('Content-Type: application/json');
require 'conexao.php'; // Arquivo para conexão com o banco

$nome = $_GET['nome'] ?? '';
$id = $_GET['id'] ?? '';

if (!empty($id)) {
    // Buscar usuário pelo ID
    $sql = "SELECT id_usuario, nome, email, curso, status FROM usuarios WHERE id_usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
} elseif (!empty($nome)) {
    // Buscar usuários pelo nome (retorna lista)
    $sql = "SELECT id_usuario, nome FROM usuarios WHERE nome LIKE ? LIMIT 10";
    $stmt = $conn->prepare($sql);
    $searchTerm = "%$nome%";
    $stmt->bind_param("s", $searchTerm);
} else {
    echo json_encode([]);
    exit;
}

$stmt->execute();
$result = $stmt->get_result();

$dados = [];
while ($row = $result->fetch_assoc()) {
    $dados[] = $row;
}

$stmt->close();
$conn->close();

echo json_encode($dados);
?>
