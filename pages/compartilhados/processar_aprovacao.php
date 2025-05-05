<?php
require_once '../../includes/common/permissoes.php';
verificarPermissao(['diretor_geral', 'diretor_pedagogico']);
require_once '../../process/verificar_sessao.php';
require_once '../../database/conexao.php';

if (!isset($_GET['id'])) {
    $_SESSION['mensagem'] = "Plano não especificado.";
    $_SESSION['tipo_mensagem'] = "danger";
    header("Location: aprovacao_planos.php");
    exit();
}

$id_plano = intval($_GET['id']);
$acao = isset($_GET['acao']) ? $_GET['acao'] : '';
$id_diretor = $_SESSION['id_usuario'];

// Verificar se o usuário é realmente um diretor
$sql_verifica_diretor = "SELECT 1 FROM usuario 
                        WHERE id_usuario = ? 
                        AND tipo IN ('diretor_geral', 'diretor_pedagogico')";
$stmt = $conn->prepare($sql_verifica_diretor);
$stmt->bind_param("i", $id_diretor);
$stmt->execute();

if ($stmt->get_result()->num_rows === 0) {
    $_SESSION['mensagem'] = "Apenas diretores podem aprovar/rejeitar planos de ensino.";
    $_SESSION['tipo_mensagem'] = "danger";
    header("Location: aprovacao_planos.php");
    exit();
}

if ($acao === 'aprovar') {
    $sql = "UPDATE plano_ensino 
           SET status = 'aprovado', 
               data_aprovacao = NOW(),
               id_diretor_aprovador = ?,
               motivo_rejeicao = NULL
           WHERE id_plano = ?";
    $mensagem = "Plano aprovado com sucesso!";
} elseif ($acao === 'rejeitar' && isset($_GET['motivo'])) {
    $motivo = $_GET['motivo'];
    $sql = "UPDATE plano_ensino 
           SET status = 'rejeitado', 
               data_aprovacao = NOW(),
               id_diretor_aprovador = ?,
               motivo_rejeicao = ?
           WHERE id_plano = ?";
    $mensagem = "Plano rejeitado com sucesso!";
} else {
    $_SESSION['mensagem'] = "Ação inválida.";
    $_SESSION['tipo_mensagem'] = "danger";
    header("Location: aprovacao_planos.php");
    exit();
}

$stmt = $conn->prepare($sql);

if ($acao === 'aprovar') {
    $stmt->bind_param("ii", $id_diretor, $id_plano);
} else {
    $stmt->bind_param("isi", $id_diretor, $motivo, $id_plano);
}

if ($stmt->execute()) {
    $_SESSION['mensagem'] = $mensagem;
    $_SESSION['tipo_mensagem'] = "success";
} else {
    $_SESSION['mensagem'] = "Erro ao processar solicitação: " . $conn->error;
    $_SESSION['tipo_mensagem'] = "danger";
}

header("Location: aprovacao_planos.php");
exit();
?>