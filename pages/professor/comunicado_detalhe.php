<?php
require_once '../../includes/common/permissoes.php';
require_once '../../process/verificar_sessao.php';
require_once '../../database/conexao.php';
require_once '../../includes/common/funcoes.php';

if (!isset($_GET['id'])) {
    header('Location: comunicados_usuario.php');
    exit();
}

$id_comunicado = intval($_GET['id']);
$id_usuario = $_SESSION['id_usuario'];

// Obter detalhes do comunicado
$query = "SELECT c.*, u.nome AS remetente, u.foto_perfil
          FROM comunicado c
          JOIN usuario u ON c.usuario_id_usuario = u.id_usuario
          WHERE c.id_comunicado = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_comunicado);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: comunicados_usuario.php');
    exit();
}

$comunicado = $result->fetch_assoc();

// Marcar como lido
$query = "INSERT IGNORE INTO comunicado_lido (comunicado_id, usuario_id) VALUES (?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $id_comunicado, $id_usuario);
$stmt->execute();
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <title>Comunicado | <?= htmlspecialchars($comunicado['titulo']) ?> | Sistema Escolar</title>
    <?php require_once '../../includes/common/head.php'; ?>
    <style>
        .comunicado-header {
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .comunicado-remetente img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
        }
        .comunicado-mensagem {
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }
        .comunicado-mensagem img {
            max-width: 100%;
            height: auto;
        }
        .comunicado-acoes {
            border-top: 1px solid #eee;
            padding-top: 15px;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <?php require_once '../../includes/common/preloader.php'; ?>

    <div id="pcoded" class="pcoded">
        <div class="pcoded-overlay-box"></div>
        <div class="pcoded-container navbar-wrapper">
            <?php require_once '../../includes/common/navbar.php'; ?>

            <div class="pcoded-main-container">
                <div class="pcoded-wrapper">
                    <?php require_once '../../includes/common/sidebar.php'; ?>
                    
                    <div class="pcoded-content">
                        <div class="pcoded-inner-content">
                            <div class="main-body bg-img">
                                <div class="page-wrapper">
                                    <div class="page-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="card">
                                                    <div class="card-header">
                                                        <a href="comunicados_usuario.php" class="btn btn-primary btn-sm">
                                                            <i class="feather icon-arrow-left"></i> Voltar
                                                        </a>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="comunicado-header">
                                                            <div class="comunicado-remetente d-flex align-items-center">
                                                                <img src="<?= obterFotoPerfil($comunicado['usuario_id_usuario']) ?>" 
                                                                     alt="<?= htmlspecialchars($comunicado['remetente']) ?>" 
                                                                     class="mr-4">
                                                                <div>
                                                                    <h3 class="mb-1"><?= htmlspecialchars($comunicado['titulo']) ?></h3>
                                                                    <p class="mb-1"><strong>De:</strong> <?= htmlspecialchars($comunicado['remetente']) ?></p>
                                                                    <p class="mb-1"><strong>Data:</strong> <?= date('d/m/Y H:i', strtotime($comunicado['data'])) ?></p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="comunicado-mensagem">
                                                            <?= $comunicado['mensagem'] ?>
                                                        </div>
                                                        
                                                        <div class="comunicado-acoes">
                                                            <button class="btn btn-secondary" onclick="window.print()">
                                                                <i class="feather icon-printer"></i> Imprimir
                                                            </button>
                                                            <button class="btn btn-info" id="btn-compartilhar">
                                                                <i class="feather icon-share-2"></i> Compartilhar
                                                            </button>
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
    </div>

    <?php require_once '../../includes/common/js_imports.php'; ?>
    
    <script>
        // Compartilhar comunicado
        document.getElementById('btn-compartilhar').addEventListener('click', function() {
            if (navigator.share) {
                navigator.share({
                    title: 'Comunicado: <?= addslashes($comunicado['titulo']) ?>',
                    text: 'Confira este comunicado importante:',
                    url: window.location.href
                }).catch(err => {
                    console.log('Erro ao compartilhar:', err);
                });
            } else {
                // Fallback para navegadores que não suportam Web Share API
                alert('Copie o link para compartilhar: ' + window.location.href);
            }
        });
    </script>
</body>
</html>