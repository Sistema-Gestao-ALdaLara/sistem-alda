<?php
require_once '../database/conexao.php';
require_once '../includes/common/funcoes.php';

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Verificar se o usuário está logado
session_start();
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

// Verificar se os dados foram enviados
if (!isset($_POST['id_usuario']) || !isset($_FILES['foto_perfil'])) {
    echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
    exit;
}

$id_usuario = (int)$_POST['id_usuario'];
$usuario_logado_id = (int)$_SESSION['id_usuario'];

// Verificar se o usuário tem permissão para alterar esta foto
if ($id_usuario !== $usuario_logado_id) {
    // Aqui você pode adicionar verificação adicional para diretores/secretaria se necessário
    echo json_encode(['success' => false, 'message' => 'Permissão negada']);
    exit;
}

// Configurações do upload
$diretorio = '../uploads/perfil/';
$extensoesPermitidas = ['jpg', 'jpeg', 'png', 'gif'];
$tamanhoMaximo = 5 * 1024 * 1024; // 5MB

// Criar diretório se não existir
if (!file_exists($diretorio)) {
    mkdir($diretorio, 0777, true);
}

$arquivo = $_FILES['foto_perfil'];
$nomeArquivo = $arquivo['name'];
$tamanhoArquivo = $arquivo['size'];
$arquivoTmp = $arquivo['tmp_name'];
$extensao = strtolower(pathinfo($nomeArquivo, PATHINFO_EXTENSION));

// Validar arquivo
if (!in_array($extensao, $extensoesPermitidas)) {
    echo json_encode(['success' => false, 'message' => 'Tipo de arquivo não permitido']);
    exit;
}

if ($tamanhoArquivo > $tamanhoMaximo) {
    echo json_encode(['success' => false, 'message' => 'Arquivo muito grande (máx. 5MB)']);
    exit;
}

// Gerar nome único para o arquivo
$novoNome = 'perfil_' . $id_usuario . '_' . time() . '.' . $extensao;
$caminhoCompleto = $diretorio . $novoNome;

// Mover arquivo para o diretório de uploads
if (move_uploaded_file($arquivoTmp, $caminhoCompleto)) {
    try {
        // Atualizar no banco de dados
        $sql = "UPDATE usuario SET foto_perfil = ? WHERE id_usuario = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$novoNome, $id_usuario]);
        
        // Se houver foto antiga, removê-la
        if (isset($_POST['foto_atual']) && !empty($_POST['foto_atual'])) {
            $fotoAntiga = $diretorio . $_POST['foto_atual'];
            if (file_exists($fotoAntiga)) {
                unlink($fotoAntiga);
            }
        }
        
        echo json_encode(['success' => true, 'message' => 'Foto atualizada com sucesso', 'novo_caminho' => $novoNome]);
    } catch (PDOException $e) {
        unlink($caminhoCompleto); // Remover arquivo em caso de erro no banco
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar no banco de dados']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao fazer upload do arquivo']);
}
?>