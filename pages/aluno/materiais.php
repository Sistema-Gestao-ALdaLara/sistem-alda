<?php
require_once __DIR__ . '/../../includes/common/permissoes.php';
verificarPermissao(['aluno']);
require_once __DIR__ . '/../../process/verificar_sessao.php';
require_once __DIR__ . '/../../database/conexao.php';


// Obter informações do aluno
$query_aluno = "SELECT a.id_aluno, t.id_turma, c.id_curso 
                FROM aluno a
                JOIN turma t ON a.turma_id_turma = t.id_turma
                JOIN curso c ON t.curso_id_curso = c.id_curso
                WHERE a.usuario_id_usuario = ?";
$stmt_aluno = $conn->prepare($query_aluno);
$stmt_aluno->bind_param("i", $_SESSION['id_usuario']);
$stmt_aluno->execute();
$aluno = $stmt_aluno->get_result()->fetch_assoc();

if (!$aluno) {
    header("Location: disciplinas.php");
    exit();
}

// Verificar se foi solicitada uma disciplina específica
$id_disciplina = $_GET['disciplina'] ?? null;
$filtro_disciplina = $id_disciplina ? " AND d.id_disciplina = ?" : "";

// Obter disciplinas com materiais
$query = "SELECT d.id_disciplina, d.nome as disciplina_nome,
                 COUNT(m.id_material) as total_materiais
          FROM disciplina d
          JOIN cronograma_aula ca ON ca.id_disciplina = d.id_disciplina
          LEFT JOIN materiais_apoio m ON m.id_disciplina = d.id_disciplina
          WHERE d.curso_id_curso = ? AND ca.turma_id_turma = ?
          $filtro_disciplina
          GROUP BY d.id_disciplina
          HAVING total_materiais > 0
          ORDER BY d.nome";

$stmt = $conn->prepare($query);

if ($id_disciplina) {
    $stmt->bind_param("iii", $aluno['id_curso'], $aluno['id_turma'], $id_disciplina);
} else {
    $stmt->bind_param("ii", $aluno['id_curso'], $aluno['id_turma']);
}

$stmt->execute();
$disciplinas_com_materiais = $stmt->get_result();

// Obter materiais de apoio
$query_materiais = "SELECT m.id_material, m.nome, m.descricao, 
                           m.caminho_arquivo, m.data_upload,
                           d.id_disciplina, d.nome as disciplina_nome
                    FROM materiais_apoio m
                    JOIN disciplina d ON m.id_disciplina = d.id_disciplina
                    JOIN cronograma_aula ca ON ca.id_disciplina = d.id_disciplina
                    WHERE d.curso_id_curso = ? AND ca.turma_id_turma = ?
                    $filtro_disciplina
                    ORDER BY d.nome, m.data_upload DESC";

$stmt_materiais = $conn->prepare($query_materiais);

if ($id_disciplina) {
    $stmt_materiais->bind_param("iii", $aluno['id_curso'], $aluno['id_turma'], $id_disciplina);
} else {
    $stmt_materiais->bind_param("ii", $aluno['id_curso'], $aluno['id_turma']);
}

$stmt_materiais->execute();
$materiais = $stmt_materiais->get_result();

// Obter nome da disciplina específica (se aplicável)
$disciplina_especifica = null;
if ($id_disciplina) {
    $query_nome = "SELECT nome FROM disciplina WHERE id_disciplina = ?";
    $stmt_nome = $conn->prepare($query_nome);
    $stmt_nome->bind_param("i", $id_disciplina);
    $stmt_nome->execute();
    $disciplina_especifica = $stmt_nome->get_result()->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <?php require_once __DIR__ . '/../../includes/common/head.php'; ?>
    <title>
        <?= $id_disciplina ? 
           'Materiais - ' . htmlspecialchars($disciplina_especifica['nome']) : 
           'Todos os Materiais' ?>
    </title>
    <style>
        .disciplina-card {
            transition: all 0.3s;
            margin-bottom: 20px;
        }
        .disciplina-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .material-card {
            border-left: 4px solid #4680ff;
            margin-bottom: 15px;
        }
        .badge-disciplina {
            position: absolute;
            top: -10px;
            right: -10px;
            font-size: 0.8rem;
        }
        .empty-message {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }
        .collapse-icon {
            transition: transform 0.3s;
        }
        .collapsed .collapse-icon {
            transform: rotate(-90deg);
        }
    </style>
</head>
<body>
    <?php require_once __DIR__ . '/../../includes/common/preloader.php'; ?>

    <div id="pcoded" class="pcoded">
        <div class="pcoded-overlay-box"></div>
        <div class="pcoded-container navbar-wrapper">
            <?php require_once __DIR__ . '/../../includes/aluno/navbar.php'; ?>

            <div class="pcoded-main-container">
                <div class="pcoded-wrapper">
                    <?php require_once __DIR__ . '/../../includes/aluno/sidebar.php'; ?>
                    
                    <div class="pcoded-content">
                        <div class="pcoded-inner-content">
                            <div class="main-body bg-img">
                                <div class="page-wrapper">
                                    <div class="page-body">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="card">
                                                    <div class="card-header">
                                                        <h5>
                                                            <i class="feather icon-folder"></i> 
                                                            <?= $id_disciplina ? 
                                                               'Materiais - ' . htmlspecialchars($disciplina_especifica['nome']) : 
                                                               'Todos os Materiais por Disciplina' ?>
                                                            <a href="disciplinas.php" class="btn btn-sm btn-outline-primary float-right">
                                                                <i class="feather icon-arrow-left"></i> Voltar
                                                            </a>
                                                        </h5>
                                                    </div>
                                                    <div class="card-body">
                                                        <?php if (!$id_disciplina && $disciplinas_com_materiais->num_rows > 0): ?>
                                                            <div class="accordion" id="disciplinasAccordion">
                                                                <?php while ($disciplina = $disciplinas_com_materiais->fetch_assoc()): ?>
                                                                    <div class="card disciplina-card">
                                                                        <div class="card-header" id="heading<?= $disciplina['id_disciplina'] ?>">
                                                                            <h5 class="mb-0">
                                                                                <button class="btn btn-link collapsed w-100 text-left d-flex justify-content-between align-items-center" 
                                                                                        type="button" 
                                                                                        data-toggle="collapse" 
                                                                                        data-target="#collapse<?= $disciplina['id_disciplina'] ?>" 
                                                                                        aria-expanded="false" 
                                                                                        aria-controls="collapse<?= $disciplina['id_disciplina'] ?>">
                                                                                    <span>
                                                                                        <?= htmlspecialchars($disciplina['disciplina_nome']) ?>
                                                                                        <span class="badge badge-primary ml-2"><?= $disciplina['total_materiais'] ?></span>
                                                                                    </span>
                                                                                    <i class="feather icon-chevron-down collapse-icon"></i>
                                                                                </button>
                                                                            </h5>
                                                                        </div>
                                                                        <div id="collapse<?= $disciplina['id_disciplina'] ?>" 
                                                                             class="collapse" 
                                                                             aria-labelledby="heading<?= $disciplina['id_disciplina'] ?>" 
                                                                             data-parent="#disciplinasAccordion">
                                                                            <div class="card-body">
                                                                                <?php
                                                                                // Obter materiais para esta disciplina específica
                                                                                $query_mat = "SELECT id_material, nome, descricao, caminho_arquivo, data_upload 
                                                                                              FROM materiais_apoio 
                                                                                              WHERE id_disciplina = ? 
                                                                                              ORDER BY data_upload DESC";
                                                                                $stmt_mat = $conn->prepare($query_mat);
                                                                                $stmt_mat->bind_param("i", $disciplina['id_disciplina']);
                                                                                $stmt_mat->execute();
                                                                                $materiais_disc = $stmt_mat->get_result();
                                                                                
                                                                                if ($materiais_disc->num_rows > 0): ?>
                                                                                    <div class="row">
                                                                                        <?php while ($material = $materiais_disc->fetch_assoc()): ?>
                                                                                            <div class="col-md-6">
                                                                                                <div class="card material-card">
                                                                                                    <div class="card-body">
                                                                                                        <h6><?= htmlspecialchars($material['nome']) ?></h6>
                                                                                                        <p class="text-muted small"><?= htmlspecialchars($material['descricao']) ?></p>
                                                                                                        <div class="d-flex justify-content-between align-items-center">
                                                                                                            <small class="text-muted">
                                                                                                                <i class="feather icon-calendar"></i> 
                                                                                                                <?= date('d/m/Y H:i', strtotime($material['data_upload'])) ?>
                                                                                                            </small>
                                                                                                            <a href="<?= htmlspecialchars($material['caminho_arquivo']) ?>" 
                                                                                                               class="btn btn-sm btn-outline-primary" 
                                                                                                               download="<?= htmlspecialchars($material['nome']) ?>">
                                                                                                                <i class="feather icon-download"></i> Baixar
                                                                                                            </a>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                        <?php endwhile; ?>
                                                                                    </div>
                                                                                <?php else: ?>
                                                                                    <div class="alert alert-info">Nenhum material encontrado para esta disciplina.</div>
                                                                                <?php endif; ?>
                                                                                <div class="text-center mt-3">
                                                                                    <a href="materiais.php?disciplina=<?= $disciplina['id_disciplina'] ?>" 
                                                                                       class="btn btn-sm btn-primary">
                                                                                        Ver todos os materiais desta disciplina
                                                                                    </a>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                <?php endwhile; ?>
                                                            </div>
                                                        <?php elseif ($materiais->num_rows > 0): ?>
                                                            <div class="row">
                                                                <?php 
                                                                $current_disciplina = null;
                                                                while ($material = $materiais->fetch_assoc()): 
                                                                    if ($current_disciplina !== $material['id_disciplina'] && !$id_disciplina):
                                                                        $current_disciplina = $material['id_disciplina'];
                                                                ?>
                                                                        <div class="col-12 mt-4 mb-2">
                                                                            <h5 class="border-bottom pb-2">
                                                                                <i class="feather icon-book"></i> 
                                                                                <?= htmlspecialchars($material['disciplina_nome']) ?>
                                                                            </h5>
                                                                        </div>
                                                                    <?php endif; ?>
                                                                    <div class="col-md-6 col-lg-4 mb-4">
                                                                        <div class="card h-100">
                                                                            <div class="card-header position-relative">
                                                                                <h6 class="text-truncate"><?= htmlspecialchars($material['nome']) ?></h6>
                                                                            </div>
                                                                            <div class="card-body">
                                                                                <p class="text-muted small"><?= htmlspecialchars($material['descricao']) ?></p>
                                                                                <small class="text-muted">
                                                                                    <i class="feather icon-calendar"></i> 
                                                                                    <?= date('d/m/Y H:i', strtotime($material['data_upload'])) ?>
                                                                                </small>
                                                                            </div>
                                                                            <div class="card-footer bg-white text-center">
                                                                                <a href="<?= htmlspecialchars($material['caminho_arquivo']) ?>" 
                                                                                   class="btn btn-sm btn-primary" 
                                                                                   target="_blank" 
                                                                                   download="<?= htmlspecialchars($material['nome']) ?>">
                                                                                    <i class="feather icon-download"></i> Baixar
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                <?php endwhile; ?>
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="empty-message">
                                                                <i class="feather icon-folder" style="font-size: 3rem;"></i>
                                                                <h5 class="mt-3">
                                                                    <?= $id_disciplina ? 
                                                                       'Nenhum material disponível para esta disciplina' : 
                                                                       'Nenhum material disponível em suas disciplinas' ?>
                                                                </h5>
                                                                <p class="text-muted">Aguardando upload de materiais pelos professores</p>
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

    <?php require_once __DIR__ . '/../../includes/common/js_imports.php'; ?>
    <script>
        // Persistir estado dos acordeões
        document.addEventListener('DOMContentLoaded', function() {
            $('.collapse').on('shown.bs.collapse', function() {
                localStorage.setItem('collapse_' + this.id, 'shown');
            });
            
            $('.collapse').on('hidden.bs.collapse', function() {
                localStorage.setItem('collapse_' + this.id, 'hidden');
            });
            
            // Restaurar estado
            $('.collapse').each(function() {
                var state = localStorage.getItem('collapse_' + this.id);
                if (state === 'shown') {
                    $(this).collapse('show');
                }
            });
        });
    </script>
</body>
</html>