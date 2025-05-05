<?php
require_once '../../includes/common/permissoes.php';
verificarPermissao(['secretaria', 'diretor_geral', 'diretor_pedagogico', 'coordenador', 'professor', 'aluno']);
require_once '../../process/verificar_sessao.php';
require_once '../../database/conexao.php';

$title = "Comunicados";
$tipo_usuario = $_SESSION['tipo_usuario'];
$id_usuario = $_SESSION['id_usuario'];

// Função auxiliar para obter foto do perfil
function obterFotoPerfil($id_usuario) {
    global $conn;
    $query = "SELECT foto_perfil FROM usuario WHERE id_usuario = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    $usuario = $result->fetch_assoc();
    
    return $usuario['foto_perfil'] ?? '/assets/images/avatar-default.png';
}

// Função para formatar data
function formatarData($data) {
    return date('d/m/Y H:i', strtotime($data));
}

// Obter comunicados relevantes para o usuário logado
$query = "SELECT c.*, u.nome AS remetente, u.foto_perfil AS remetente_foto,
                 GROUP_CONCAT(DISTINCT cd.tipo_destinatario SEPARATOR ', ') AS destinatarios
          FROM comunicado c
          JOIN usuario u ON c.usuario_id_usuario = u.id_usuario
          JOIN comunicado_destinatario cd ON c.id_comunicado = cd.comunicado_id
          WHERE cd.tipo_destinatario = ? OR cd.tipo_destinatario = 'todos'
          GROUP BY c.id_comunicado
          ORDER BY c.data DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $tipo_usuario);
$stmt->execute();
$result = $stmt->get_result();
$comunicados = $result->fetch_all(MYSQLI_ASSOC);

// Função para verificar se o comunicado é destinado ao usuário atual
function comunicadoParaUsuario($comunicado, $tipo_usuario) {
    $destinatarios = explode(', ', $comunicado['destinatarios']);
    return in_array($tipo_usuario, $destinatarios) || in_array('todos', $destinatarios);
}

// Função para traduzir tipos de destinatários
function traduzirDestinatarios($tipos) {
    $traducao = [
        'diretor_geral' => 'Diretor Geral',
        'diretor_pedagogico' => 'Diretor Pedagógico',
        'coordenador' => 'Coordenadores',
        'professor' => 'Professores',
        'aluno' => 'Alunos',
        'secretaria' => 'Secretaria',
        'todos' => 'Todos os Usuários'
    ];
    
    $tipos_array = explode(', ', $tipos);
    $traduzidos = array_map(function($tipo) use ($traducao) {
        return $traducao[$tipo] ?? $tipo;
    }, $tipos_array);
    
    return implode(', ', $traduzidos);
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <?php require_once '../../includes/common/head.php'; ?>
    <style>
        .comunicado-card {
            margin-bottom: 20px;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            padding: 15px;
        }
        .comunicado-header {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .comunicado-remetente-img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 15px;
            object-fit: cover;
        }
        .comunicado-destinatario {
            background-color: #f8f9fa;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.9em;
            margin-top: 10px;
            display: inline-block;
        }
        .comunicado-data {
            color: #6c757d;
            font-size: 0.9em;
        }
        .comunicado-mensagem {
            margin-top: 15px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .comunicado-mensagem img {
            max-width: 100%;
            height: auto;
        }
        .nenhum-comunicado {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <?php require_once '../../includes/common/preloader.php'; ?>

    <div id="pcoded" class="pcoded">
        <div class="pcoded-overlay-box"></div>
        <div class="pcoded-container navbar-wrapper">
            <?php require_once "../../includes/$tipo_usuario/navbar.php"; ?>

            <div class="pcoded-main-container">
                <div class="pcoded-wrapper">
                    <?php require_once "../../includes/$tipo_usuario/sidebar.php"; ?>
                    
                    <div class="pcoded-content">
                        <div class="pcoded-inner-content">
                            <div class="main-body bg-img">
                                <div class="page-wrapper">
                                    <div class="page-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="card card-table">
                                                    <div class="card-header">
                                                        <h5>Comunicados</h5>
                                                        <span class="text-dark">Mensagens enviadas para você ou seu grupo</span>
                                                    </div>
                                                    <div class="card-block">
                                                        <?php if (empty($comunicados)): ?>
                                                            <div class="nenhum-comunicado">
                                                                <i class="feather icon-bell" style="font-size: 50px; margin-bottom: 20px;"></i>
                                                                <h4>Nenhum comunicado disponível</h4>
                                                                <p>Quando houver comunicados destinados a você, eles aparecerão aqui.</p>
                                                            </div>
                                                        <?php else: ?>
                                                            <?php foreach ($comunicados as $comunicado): ?>
                                                                <?php if (comunicadoParaUsuario($comunicado, $tipo_usuario)): ?>
                                                                    <div class="comunicado-card">
                                                                        <div class="comunicado-header">
                                                                            <img src="<?= obterFotoPerfil($comunicado['usuario_id_usuario']) ?>" 
                                                                                 alt="Remetente" class="comunicado-remetente-img">
                                                                            <div class="text-dark">
                                                                                <h5 class="text-dark"><?= htmlspecialchars($comunicado['remetente']) ?></h5>
                                                                                <span class="comunicado-data badge-light text-dark"><?= formatarData($comunicado['data']) ?></span>
                                                                            </div>
                                                                        </div>
                                                                        
                                                                        <div class="comunicado-destinatario text-dark">
                                                                            <i class="feather icon-users"></i> 
                                                                            <?= traduzirDestinatarios($comunicado['destinatarios']) ?>
                                                                        </div>
                                                                        
                                                                        <h4><?= htmlspecialchars($comunicado['titulo']) ?></h4>
                                                                        
                                                                        <div class="comunicado-mensagem text-dark">
                                                                            <?= $comunicado['mensagem'] ?>
                                                                        </div>
                                                                    </div>
                                                                <?php endif; ?>
                                                            <?php endforeach; ?>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require_once '../../includes/common/js_imports.php'; ?>
</body>
</html>