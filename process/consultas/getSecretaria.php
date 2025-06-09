<?php
require_once '../../database/conexao.php';
require_once '../../includes/common/permissoes.php';
require_once '../../process/verificar_sessao.php';

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['error' => 'Acesso não autorizado']);
    exit;
}

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'ID não fornecido']);
    exit;
}

$id = $_GET['id'];

// Consulta para obter os dados da secretaria
$query = "SELECT 
             s.id_secretaria,
             s.setor,
             s.pode_registrar,
             u.id_usuario,
             u.nome, 
             u.email,
             u.bi_numero,
             u.status,
             u.foto_perfil
          FROM secretaria s
          JOIN usuario u ON s.usuario_id_usuario = u.id_usuario
          WHERE s.id_secretaria = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['error' => 'Secretaria não encontrada']);
    exit;
}

$secretaria = $result->fetch_assoc();

echo json_encode($secretaria);
?>