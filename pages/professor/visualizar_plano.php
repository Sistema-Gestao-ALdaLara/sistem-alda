<?php
require_once '../../includes/common/permissoes.php';
verificarPermissao(['professor']);
require_once '../../process/verificar_sessao.php';
require_once '../../database/conexao.php';

if (!isset($_GET['id'])) {
    header("Location: meus_planos.php");
    exit();
}

$id_plano = intval($_GET['id']);
$id_usuario = $_SESSION['id_usuario'];

// Verificar se o plano pertence ao professor
$sql = "SELECT pe.*, d.nome as disciplina_nome, u.nome as diretor_aprovador
        FROM plano_ensino pe
        JOIN disciplina d ON pe.id_disciplina = d.id_disciplina
        JOIN professor p ON pe.id_professor = p.id_professor
        LEFT JOIN usuario u ON pe.id_diretor_aprovador = u.id_usuario
        WHERE pe.id_plano = ?
        AND p.usuario_id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id_plano, $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
$plano = $result->fetch_assoc();

if (!$plano) {
    $_SESSION['mensagem'] = "Plano não encontrado ou acesso não autorizado.";
    $_SESSION['tipo_mensagem'] = "danger";
    header("Location: meus_planos.php");
    exit();
}

$title = "Visualizar Plano: " . htmlspecialchars($plano['titulo']);
?>

<!DOCTYPE html>
<html lang="pt">

<?php require_once '../../includes/common/head.php'; ?>

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
                                        <div class="card card-table">
                                            <div class="card-header">
                                                <h5>Plano de Ensino: <?= htmlspecialchars($plano['titulo']) ?></h5>
                                            </div>
                                            <div class="card-block">
                                                <div class="row mb-4">
                                                    <div class="col-md-6">
                                                        <p><strong>Disciplina:</strong> <?= htmlspecialchars($plano['disciplina_nome']) ?></p>
                                                        <p><strong>Ano Letivo:</strong> <?= $plano['ano_letivo'] ?></p>
                                                        <p><strong>Trimestre:</strong> <?= $plano['trimestre'] == 'anual' ? 'Anual' : $plano['trimestre'] . 'º Trimestre' ?></p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <p><strong>Status:</strong> 
                                                            <?php 
                                                                $badge_class = [
                                                                    'rascunho' => 'warning',
                                                                    'submetido' => 'info',
                                                                    'aprovado' => 'success',
                                                                    'rejeitado' => 'danger'
                                                                ][$plano['status']];
                                                            ?>
                                                            <span class="badge badge-<?= $badge_class ?>">
                                                                <?= ucfirst($plano['status']) ?>
                                                            </span>
                                                        </p>
                                                        <?php if ($plano['status'] == 'aprovado' && $plano['diretor_aprovador']): ?>
                                                            <p><strong>Aprovado por:</strong> <?= htmlspecialchars($plano['diretor_aprovador']) ?></p>
                                                            <p><strong>Data aprovação:</strong> <?= date('d/m/Y H:i', strtotime($plano['data_aprovacao'])) ?></p>
                                                        <?php elseif ($plano['status'] == 'rejeitado'): ?>
                                                            <p><strong>Motivo rejeição:</strong> <?= htmlspecialchars($plano['motivo_rejeicao']) ?></p>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>

                                                <div class="row mb-4">
                                                    <div class="col-md-12">
                                                        <h4>Conteúdo Programático</h4>
                                                        <div class="border p-3 bg-light"><?= nl2br(htmlspecialchars($plano['conteudo_programatico'])) ?></div>
                                                    </div>
                                                </div>

                                                <div class="row mb-4">
                                                    <div class="col-md-6">
                                                        <h4>Metodologia</h4>
                                                        <div class="border p-3 bg-light"><?= nl2br(htmlspecialchars($plano['metodologia'])) ?></div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <h4>Critérios de Avaliação</h4>
                                                        <div class="border p-3 bg-light"><?= nl2br(htmlspecialchars($plano['criterios_avaliacao'])) ?></div>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <h4>Bibliografia</h4>
                                                        <div class="border p-3 bg-light"><?= nl2br(htmlspecialchars($plano['bibliografia'])) ?></div>
                                                    </div>
                                                </div>

                                                <div class="row mt-4">
                                                    <div class="col-md-12">
                                                        <?php if ($plano['caminho_arquivo']): ?>
                                                            <a href="../../uploads/<?= htmlspecialchars($plano['caminho_arquivo']) ?>" 
                                                               class="btn btn-primary" download>
                                                                <i class="feather icon-download"></i> Baixar Plano Completo
                                                            </a>
                                                        <?php endif; ?>
                                                        <a href="meus_planos.php" class="btn btn-secondary">
                                                            <i class="feather icon-arrow-left"></i> Voltar
                                                        </a>
                                                        <?php if ($plano['status'] == 'rascunho' || $plano['status'] == 'rejeitado'): ?>
                                                            <a href="editar_plano.php?id=<?= $plano['id_plano'] ?>" class="btn btn-warning">
                                                                <i class="feather icon-edit"></i> Editar Plano
                                                            </a>
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