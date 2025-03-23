<?php
require 'conexao.php'; // Conexão com o banco

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['idUsuario'] ?? '';
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $curso = $_POST['curso'] ?? '';
    $status = $_POST['status'] ?? '';
    
    if (empty($id) || empty($nome) || empty($email)) {
        echo json_encode(["error" => "Todos os campos obrigatórios devem ser preenchidos."]);
        exit;
    }
    
    // Atualizar os dados do usuário
    $sql = "UPDATE usuarios SET nome = ?, email = ?, curso = ?, status = ? WHERE id_usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $nome, $email, $curso, $status, $id);
    
    if ($stmt->execute()) {
        echo json_encode(["success" => "Usuário atualizado com sucesso!"]);
    } else {
        echo json_encode(["error" => "Erro ao atualizar o usuário."]);
    }
    
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["error" => "Método inválido."]);
}
?>
