<?php
    require_once '../../includes/common/permissoes.php';
    verificarPermissao(['professor']);
    require_once '../../process/verificar_sessao.php';
require_once '../../database/conexao.php';
require_once '../../includes/common/funcoes.php';

$title = "Comunicados";

// Obter comunicados relevantes para o usuário
$query = "SELECT c.*, u.nome AS remetente, u.foto_perfil
          FROM comunicado c
          JOIN usuario u ON c.usuario_id_usuario = u.id_usuario
          ORDER BY c.data DESC";
$result = $conn->query($query);
$comunicados = $result->fetch_all(MYSQLI_ASSOC);
?>


<!DOCTYPE html>
<html lang="pt">

<head>
    <?php require_once '../../includes/common/head.php'; ?>
    <style>
        .comunicado-card {
            border-left: 4px solid #4680ff;
            margin-bottom: 20px;
            transition: all 0.3s;
        }
        .comunicado-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .comunicado-remetente img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }
        .comunicado-mensagem {
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }
        .comunicado-mensagem img {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>

<body>
    <?php require_once '../../includes/common/preloader.php'; ?>

    <div id="pcoded" class="pcoded">
        <div class="pcoded-overlay-box"></div>
        <div class="pcoded-container navbar-wrapper">
            <?php require_once '../../includes/professor/navbar.php'; ?>

            <div class="pcoded-main-container">
                <div class="pcoded-wrapper">
                    <?php require_once '../../includes/professor/sidebar.php'; ?>
                    
                    <div class="pcoded-content">
                        <div class="pcoded-inner-content">
                            <div class="main-body bg-img">
                                <div class="page-wrapper">
                                    <div class="page-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="card card-table">
                                                    <div class="card-header">
                                                        <h5 class="text-white">Comunicados</h5>
                                                        <span class="text-dark">Mensagens importantes da escola</span>
                                                    </div>
                                                    <div class="card-body">
                                                        <?php if (empty($comunicados)): ?>
                                                            <div class="alert alert-info">
                                                                Nenhum comunicado disponível no momento.
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="row">
                                                                <div class="col-md-8">
                                                                    <!-- Lista de Comunicados -->
                                                                    <?php foreach ($comunicados as $comunicado): ?>
                                                                        <div class="card comunicado-card card-table">
                                                                            <div class="card-body">
                                                                                <div class="comunicado-remetente d-flex align-items-center mb-3">
                                                                                    <img src="<?= obterFotoPerfil($comunicado['usuario_id_usuario']) ?>" 
                                                                                         alt="<?= htmlspecialchars($comunicado['remetente']) ?>" 
                                                                                         class="mr-3">
                                                                                    <div>
                                                                                        <h5 class="mb-0"><?= htmlspecialchars($comunicado['remetente']) ?></h5>
                                                                                        <small class="text-white bg-primary p-1"><?= date('d/m/Y H:i', strtotime($comunicado['data'])) ?></small>
                                                                                    </div>
                                                                                </div>
                                                                                
                                                                                <h4><?= htmlspecialchars($comunicado['titulo']) ?></h4>
                                                                                
                                                                                <div class="comunicado-mensagem mt-3 bg-primary">
                                                                                    <?= $comunicado['mensagem'] ?>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    <?php endforeach; ?>
                                                                </div>
                                                                
                                                                <div class="col-md-4">
                                                                    <!-- Filtros Simples -->
                                                                    <div class="card card-table">
                                                                        <div class="card-header">
                                                                            <h5>Filtrar Comunicados</h5>
                                                                        </div>
                                                                        <div class="card-body">
                                                                            <form method="GET" action="">
                                                                                <div class="form-group">
                                                                                    <label>Período</label>
                                                                                    <select class="form-control" name="periodo">
                                                                                        <option value="todos">Todos</option>
                                                                                        <option value="7dias">Últimos 7 dias</option>
                                                                                        <option value="30dias">Últimos 30 dias</option>
                                                                                        <option value="3meses">Últimos 3 meses</option>
                                                                                    </select>
                                                                                </div>
                                                                                <button type="submit" class="btn btn-primary btn-block">
                                                                                    <i class="feather icon-filter"></i> Aplicar Filtros
                                                                                </button>
                                                                            </form>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
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