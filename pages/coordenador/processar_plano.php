<?php
require_once '../../includes/common/permissoes.php';
verificarPermissao(['coordenador']);
require_once '../../process/verificar_sessao.php';
require_once '../../database/conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: planos.php");
    exit();
}

// Validar dados
$id_disciplina = $_POST['id_disciplina'] ?? null;
$nome = $_POST['nome'] ?? '';
$descricao = $_POST['descricao'] ?? '';
$ano_letivo = $_POST['ano_letivo'] ?? date('Y');
$trimestre = $_POST['trimestre'] ?? '1';
$conteudo = $_POST['conteudo'] ?? '';
$metodologia = $_POST['metodologia'] ?? '';
$criterios = $_POST['criterios'] ?? '';
$bibliografia = $_POST['bibliografia'] ?? '';

if (empty($id_disciplina) || empty($nome) || empty($descricao) || !isset($_FILES['arquivo'])) {
    $_SESSION['mensagem'] = "Preencha todos os campos obrigatórios.";
    $_SESSION['tipo_mensagem'] = "danger";
    header("Location: planos.php");
    exit();
}

// Verificar se a disciplina pertence ao curso do coordenador
$id_usuario = $_SESSION['id_usuario'];
$sql = "SELECT d.id_disciplina, p.id_professor
       FROM disciplina d
       JOIN coordenador c ON d.curso_id_curso = c.curso_id_curso
       LEFT JOIN professor p ON d.professor_id_professor = p.id_professor
       WHERE d.id_disciplina = ? AND c.usuario_id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id_disciplina, $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
$dados = $result->fetch_assoc();

if (!$dados) {
    $_SESSION['mensagem'] = "Disciplina não encontrada ou sem permissão.";
    $_SESSION['tipo_mensagem'] = "danger";
    header("Location: planos.php");
    exit();
}

// Processar upload do arquivo
$diretorioUpload = "../../uploads/planos_ensino/";
if (!file_exists($diretorioUpload)) {
    mkdir($diretorioUpload, 0777, true);
}

$extensao = pathinfo($_FILES['arquivo']['name'], PATHINFO_EXTENSION);
$nomeArquivo = uniqid() . '.' . $extensao;
$caminhoCompleto = $diretorioUpload . $nomeArquivo;

// Validar tipo e tamanho do arquivo
$tamanhoMaximo = 5 * 1024 * 1024; // 5MB
$extensoesPermitidas = ['pdf', 'doc', 'docx'];

if (!in_array(strtolower($extensao), $extensoesPermitidas)) {
    $_SESSION['mensagem'] = "Tipo de arquivo não permitido. Use PDF, DOC ou DOCX.";
    $_SESSION['tipo_mensagem'] = "danger";
    header("Location: planos.php");
    exit();
}

if ($_FILES['arquivo']['size'] > $tamanhoMaximo) {
    $_SESSION['mensagem'] = "Arquivo muito grande. Tamanho máximo: 5MB.";
    $_SESSION['tipo_mensagem'] = "danger";
    header("Location: planos.php");
    exit();
}

if (move_uploaded_file($_FILES['arquivo']['tmp_name'], $caminhoCompleto)) {
    // Inserir no banco de dados na tabela plano_ensino
    $sql = "INSERT INTO plano_ensino (
            ano_letivo, trimestre, 
            conteudo_programatico, metodologia, criterios_avaliacao, 
            bibliografia, caminho_arquivo, id_disciplina, id_professor
           ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $caminhoRelativo = '../../uploads/planos_ensino/'.$nomeArquivo;
    $stmt->bind_param(
        "sssssssii", // Changed parameter types to match the table structure
        $ano_letivo,
        $trimestre,
        $conteudo,
        $metodologia,
        $criterios,
        $bibliografia,
        $caminhoRelativo,
        $id_disciplina,
        $dados['id_professor']
    );
    
    if ($stmt->execute()) {
        $_SESSION['mensagem'] = "Plano de ensino cadastrado com sucesso!";
        $_SESSION['tipo_mensagem'] = "success";
    } else {
        // Se falhar, remove o arquivo enviado
        unlink($caminhoCompleto);
        $_SESSION['mensagem'] = "Erro ao salvar no banco de dados: " . $conn->error;
        $_SESSION['tipo_mensagem'] = "danger";
    }
} else {
    $_SESSION['mensagem'] = "Erro ao fazer upload do arquivo.";
    $_SESSION['tipo_mensagem'] = "danger";
}

header("Location: planos.php");
exit();
?>