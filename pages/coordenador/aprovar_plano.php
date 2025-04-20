<?php
require_once '../../includes/common/permissoes.php';
verificarPermissao(['coordenador']);
require_once '../../process/verificar_sessao.php';
require_once '../../database/conexao.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['mensagem'] = "ID do plano inválido.";
    $_SESSION['tipo_mensagem'] = "danger";
    header("Location: planos.php");
    exit();
}

$id_plano = $_GET['id'];
$status = $_GET['status'] ?? '';
$motivo = $_GET['motivo'] ?? '';

if (!in_array($status, ['aprovado', 'rejeitado'])) {
    $_SESSION['mensagem'] = "Status inválido.";
    $_SESSION['tipo_mensagem'] = "danger";
    header("Location: planos.php");
    exit();
}

// Obter ID do coordenador
$id_usuario = $_SESSION['id_usuario'];
$sql_coordenador = "SELECT id_coordenador FROM coordenador WHERE usuario_id_usuario = ?";
$stmt = $conn->prepare($sql_coordenador);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
$coordenador = $result->fetch_assoc();

if (!$coordenador) {
    $_SESSION['mensagem'] = "Coordenador não encontrado.";
    $_SESSION['tipo_mensagem'] = "danger";
    header("Location: planos.php");
    exit();
}

// Atualizar status do plano
$sql = "UPDATE plano_ensino 
       SET status = ?, 
           data_aprovacao = NOW(),
           id_coordenador_aprovador = ?,
           resposta_coordenador = ?
       WHERE id_plano = ?";
$stmt = $conn->prepare($sql);
$resposta = ($status == 'rejeitado') ? $motivo : 'Plano aprovado com sucesso';
$stmt->bind_param("sisi", $status, $coordenador['id_coordenador'], $resposta, $id_plano);

if ($stmt->execute()) {
    $_SESSION['mensagem'] = "Plano marcado como " . $status . " com sucesso!";
    $_SESSION['tipo_mensagem'] = "success";
} else {
    $_SESSION['mensagem'] = "Erro ao atualizar o plano: " . $conn->error;
    $_SESSION['tipo_mensagem'] = "danger";
}

header("Location: planos.php");
exit();
?>