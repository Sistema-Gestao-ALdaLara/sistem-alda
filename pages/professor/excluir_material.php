<?php
require_once '../../includes/common/permissoes.php';
verificarPermissao(['professor']);
require_once '../../process/verificar_sessao.php';
require_once '../../database/conexao.php';

// Obter informações do professor
$id_usuario = $_SESSION['id_usuario'];

// Obter ID do material a ser excluído
$material_id = $_GET['id'] ?? 0;

// Verificar se o material pertence ao professor
$sql_verificar = "SELECT caminho_arquivo FROM materiais_apoio WHERE id_material = ? AND usuario_id_upload = ?";
$stmt = $conn->prepare($sql_verificar);
$stmt->bind_param("ii", $material_id, $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
$material = $result->fetch_assoc();

if (!$material) {
    header("Location: materiais.php");
    exit();
}

// Excluir o material
try {
    $conn->begin_transaction();
    
    // Excluir destinatários primeiro
    $sql_destinos = "DELETE FROM material_destinatario WHERE material_id = ?";
    $stmt = $conn->prepare($sql_destinos);
    $stmt->bind_param("i", $material_id);
    $stmt->execute();
    
    // Excluir o material
    $sql_material = "DELETE FROM materiais_apoio WHERE id_material = ?";
    $stmt = $conn->prepare($sql_material);
    $stmt->bind_param("i", $material_id);
    $stmt->execute();
    
    // Excluir o arquivo físico
    if (file_exists($material['caminho_arquivo'])) {
        unlink($material['caminho_arquivo']);
    }
    
    $conn->commit();
    $_SESSION['mensagem_sucesso'] = "Material excluído com sucesso!";
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['mensagem_erro'] = "Erro ao excluir material: " . $e->getMessage();
}

header("Location: materiais.php");
exit();
?>