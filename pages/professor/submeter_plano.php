<?php
require_once '../../includes/common/permissoes.php';
verificarPermissao(['professor']);
require_once '../../process/verificar_sessao.php';
require_once '../../database/conexao.php';

if (!isset($_GET['id'])) {
    header("Location: meus_planos.php");
    exit();
}

$id_plano = intval($_GET['id']);
$id_usuario = $_SESSION['id_usuario'];

// Verificar se o plano pertence ao professor e está como rascunho
$sql = "SELECT 1 FROM plano_ensino pe
        JOIN professor p ON pe.id_professor = p.id_professor
        WHERE pe.id_plano = ?
        AND p.usuario_id_usuario = ?
        AND pe.status = 'rascunho'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id_plano, $id_usuario);
$stmt->execute();

if ($stmt->get_result()->num_rows === 0) {
    $_SESSION['mensagem'] = "Plano não encontrado ou não pode ser submetido.";
    $_SESSION['tipo_mensagem'] = "danger";
    header("Location: meus_planos.php");
    exit();
}

// Submeter o plano
$sql = "UPDATE plano_ensino 
       SET status = 'submetido',
           data_submissao = NOW()
       WHERE id_plano = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_plano);

if ($stmt->execute()) {
    $_SESSION['mensagem'] = "Plano submetido para aprovação com sucesso!";
    $_SESSION['tipo_mensagem'] = "success";
} else {
    $_SESSION['mensagem'] = "Erro ao submeter plano: " . $conn->error;
    $_SESSION['tipo_mensagem'] = "danger";
}

header("Location: meus_planos.php");
exit();
?>