<?php
require_once '../../includes/common/permissoes.php';
verificarPermissao(['secretaria', 'diretor_geral', 'diretor_pedagogico', 'coordenador']);
require_once '../../process/verificar_sessao.php';
require_once '../../database/conexao.php';

// Verificar se foi submetido via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['erro'] = "Método de requisição inválido!";
    header('Location: ../compartilhados/relatorios.php');
    exit();
}

// Obter parâmetros do formulário
$tipo = $_POST['tipo'] ?? null;
$ano_letivo = $_POST['ano_letivo'] ?? date('Y');
$trimestre = $_POST['trimestre'] ?? null;
$id_curso = $_POST['id_curso'] ?? null;
$id_turma = $_POST['id_turma'] ?? null;
$destinatarios = $_POST['destinatarios'] ?? [];
$usuario_id_gerador = $_SESSION['id_usuario'];
$mensagem = $_POST['mensagem'] ?? '';

// Verificar se foi enviado um arquivo
if (!isset($_FILES['arquivo_pdf']) || $_FILES['arquivo_pdf']['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['erro'] = "Por favor, selecione um arquivo PDF válido!";
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit();
}

// Verificar o tipo do arquivo
$arquivo_info = pathinfo($_FILES['arquivo_pdf']['name']);
if (strtolower($arquivo_info['extension']) !== 'pdf') {
    $_SESSION['erro'] = "Apenas arquivos PDF são permitidos!";
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit();
}

// Criar diretório de uploads se não existir
$upload_dir = "../../uploads/relatorios/";
if (!file_exists($upload_dir)) {
    if (!mkdir($upload_dir, 0777, true)) {
        $_SESSION['erro'] = "Falha ao criar diretório de upload!";
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }
}

// Gerar nome único para o arquivo
$nome_arquivo = "relatorio_" . uniqid() . ".pdf";
$caminho_completo = $upload_dir . $nome_arquivo;

// Mover o arquivo enviado para o diretório de uploads
if (!move_uploaded_file($_FILES['arquivo_pdf']['tmp_name'], $caminho_completo)) {
    $_SESSION['erro'] = "Erro ao salvar o arquivo! Verifique as permissões do diretório.";
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit();
}

// Verificar se o arquivo foi realmente criado
if (!file_exists($caminho_completo)) {
    $_SESSION['erro'] = "O arquivo não foi salvo corretamente!";
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit();
}

// Preparar dados para inserção no banco de dados
$caminho_relativo = "uploads/relatorios/" . $nome_arquivo;
$titulo = "Relatório de " . ucfirst($tipo) . " - " . $ano_letivo;

// Converter valores vazios para NULL
$id_curso = (!empty($id_curso)) ? (int)$id_curso : null;
$id_turma = (!empty($id_turma)) ? (int)$id_turma : null;
$trimestre = (!empty($trimestre)) ? (int)$trimestre : null;

// Inserir no banco de dados
$query = "INSERT INTO relatorio 
          (titulo, tipo, ano_letivo, trimestre, curso_id, turma_id, 
           caminho_arquivo, usuario_id_gerador, data_geracao)
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";

$stmt = $conn->prepare($query);
if ($stmt === false) {
    $_SESSION['erro'] = "Erro ao preparar a consulta: " . $conn->error;
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit();
}

// Bind dos parâmetros
$stmt->bind_param("ssiiissi", 
    $titulo,
    $tipo,
    $ano_letivo,
    $trimestre,
    $id_curso,
    $id_turma,
    $caminho_relativo,
    $usuario_id_gerador
);

if (!$stmt->execute()) {
    $_SESSION['erro'] = "Erro ao salvar o relatório: " . $stmt->error;
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit();
}

$id_relatorio = $conn->insert_id;

// Registrar destinatários (se houver)
if (!empty($destinatarios)) {
    foreach ($destinatarios as $destinatario) {
        if (strpos($destinatario, ':') !== false) {
            list($tipo_destino, $valor) = explode(':', $destinatario, 2);
            
            $query_dest = "INSERT INTO relatorio_destinatario 
                          (relatorio_id, tipo_destino, tipo_usuario)
                          VALUES (?, ?, ?)";
            
            $stmt_dest = $conn->prepare($query_dest);
            if ($stmt_dest) {
                $stmt_dest->bind_param("iss", $id_relatorio, $tipo_destino, $valor);
                $stmt_dest->execute();
            }
        }
    }
}

// Redirecionar com mensagem de sucesso
$_SESSION['sucesso'] = "Relatório enviado com sucesso para os destinatários selecionados!";
header('Location: ../compartilhados/relatorios.php');
exit();
?>