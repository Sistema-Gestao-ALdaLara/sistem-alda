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

// Obter caminho do arquivo para excluir
$sql = "SELECT caminho_arquivo FROM plano_ensino WHERE id_plano = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_plano);
$stmt->execute();
$result = $stmt->get_result();
$plano = $result->fetch_assoc();

if (!$plano) {
    $_SESSION['mensagem'] = "Plano não encontrado.";
    $_SESSION['tipo_mensagem'] = "danger";
    header("Location: planos.php");
    exit();
}

// Excluir do banco de dados
$sql = "DELETE FROM plano_ensino WHERE id_plano = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_plano);

if ($stmt->execute()) {
    // Excluir arquivo físico
    $caminhoArquivo = "../../uploads/" . $plano['caminho_arquivo'];
    if (file_exists($caminhoArquivo)) {
        unlink($caminhoArquivo);
    }
    
    $_SESSION['mensagem'] = "Plano excluído com sucesso!";
    $_SESSION['tipo_mensagem'] = "success";
} else {
    $_SESSION['mensagem'] = "Erro ao excluir o plano: " . $conn->error;
    $_SESSION['tipo_mensagem'] = "danger";
}

header("Location: planos.php");
exit();
?>