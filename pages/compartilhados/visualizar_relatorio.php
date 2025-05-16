<?php
require_once '../../includes/common/permissoes.php';
verificarPermissao(['secretaria', 'diretor_geral', 'diretor_pedagogico', 'coordenador', 'aluno']);
require_once '../../process/verificar_sessao.php';
require_once '../../database/conexao.php';

$id_usuario = $_SESSION['id_usuario'];
$tipo_usuario = $_SESSION['tipo_usuario'];

// Consulta para obter todos os relatórios onde o usuário é destinatário
$query = "SELECT DISTINCT r.id_relatorio, r.titulo, r.tipo, r.data_geracao, 
           u.nome as autor_nome, r.caminho_arquivo
          FROM relatorio r
          JOIN usuario u ON r.usuario_id_gerador = u.id_usuario
          JOIN relatorio_destinatario rd ON r.id_relatorio = rd.relatorio_id
          WHERE (
              rd.tipo_destino = 'usuario' AND rd.usuario_id = ? OR
              rd.tipo_destino = 'tipo_usuario' AND rd.tipo_usuario = ? OR
              r.usuario_id_gerador = ?
          )
          ORDER BY r.data_geracao DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("isi", $id_usuario, $tipo_usuario, $id_usuario);
$stmt->execute();
$relatorios = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$title = "Meus Relatórios";
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <?php require_once '../../includes/common/head.php'; ?>
    <style>
        .table-relatorios {
            width: 100%;
            margin-top: 20px;
        }
        .table-relatorios th {
            background-color: #f8f9fa;
            padding: 10px;
            text-align: left;
        }
        .table-relatorios td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .badge-tipo {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        .action-buttons .btn {
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <div id="pcoded" class="pcoded">
        <div class="pcoded-overlay-box"></div>
        <div class="pcoded-container navbar-wrapper">
            <?php require_once "../../includes/{$_SESSION['tipo_usuario']}/navbar.php"; ?>

            <div class="pcoded-main-container">
                <div class="pcoded-wrapper">
                    <?php require_once "../../includes/{$_SESSION['tipo_usuario']}/sidebar.php"; ?>
                    
                    <div class="pcoded-content">
                        <div class="pcoded-inner-content">
                            <div class="main-body bg-img">
                                <div class="page-wrapper">
                                    <div class="page-body">
                                        <?php if (isset($_SESSION['sucesso'])): ?>
                                        <div class="alert alert-success alert-dismissible fade show">
                                            <?= $_SESSION['sucesso'] ?>
                                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                                        </div>
                                        <?php unset($_SESSION['sucesso']); endif; ?>
                                        
                                        <?php if (isset($_SESSION['erro'])): ?>
                                        <div class="alert alert-danger alert-dismissible fade show">
                                            <?= $_SESSION['erro'] ?>
                                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                                        </div>
                                        <?php unset($_SESSION['erro']); endif; ?>

                                        <div class="card card-table">
                                            <div class="card-header">
                                                <h5>Relatórios Disponíveis</h5>
                                            </div>
                                            <div class="card-body">
                                                <?php if (empty($relatorios)): ?>
                                                <div class="alert alert-info">
                                                    Nenhum relatório disponível para visualização.
                                                </div>
                                                <?php else: ?>
                                                <div class="table-responsive">
                                                    <table class="table table-custom">
                                                        <thead>
                                                            <tr>
                                                                <th>Título</th>
                                                                <th>Tipo</th>
                                                                <th>Autor</th>
                                                                <th>Data</th>
                                                                <th>Ações</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($relatorios as $relatorio): ?>
                                                            <tr>
                                                                <td><?= htmlspecialchars($relatorio['titulo']) ?></td>
                                                                <td>
                                                                    <span class="badge-tipo" style="background-color: #<?= substr(md5($relatorio['tipo']), 0, 6) ?>; color: white;">
                                                                        <?= ucfirst($relatorio['tipo']) ?>
                                                                    </span>
                                                                </td>
                                                                <td><?= htmlspecialchars($relatorio['autor_nome']) ?></td>
                                                                <td><?= date('d/m/Y H:i', strtotime($relatorio['data_geracao'])) ?></td>
                                                                <td class="action-buttons">
                                                                    <a href="../../../<?= $relatorio['caminho_arquivo'] ?>" download class="btn btn-sm btn-primary">
                                                                        <i class="feather icon-download"></i> Baixar
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
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

    <?php require_once '../../includes/common/js_imports.php'; ?>
</body>
</html>