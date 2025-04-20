<?php
require_once __DIR__ . '/../../includes/common/permissoes.php';
verificarPermissao(['aluno']);
require_once __DIR__ . '/../../process/verificar_sessao.php';
require_once __DIR__ . '/../../database/conexao.php';

// Verificação robusta da sessão
if (!isset($_SESSION['id_usuario'])) {
    header("Location: /login.php");
    exit();
}

// 1. Obter informações completas do aluno
$query_aluno = "SELECT a.id_aluno, a.turma_id_turma, a.curso_id_curso, 
                       t.nome as nome_turma, c.nome as nome_curso
                FROM aluno a
                JOIN turma t ON a.turma_id_turma = t.id_turma
                JOIN curso c ON a.curso_id_curso = c.id_curso
                WHERE a.usuario_id_usuario = ?";
$stmt_aluno = $conn->prepare($query_aluno);
$stmt_aluno->bind_param("i", $_SESSION['id_usuario']);
$stmt_aluno->execute();
$aluno = $stmt_aluno->get_result()->fetch_assoc();

if (!$aluno) {
    header("Location: disciplinas.php");
    exit();
}

// 2. Verificar se o ID da disciplina foi fornecido e é válido
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    header("Location: disciplinas.php");
    exit();
}

$id_disciplina = (int)$_GET['id'];

// 3. Verificar se a disciplina pertence ao curso do aluno
$query_disciplina = "SELECT d.id_disciplina, d.nome as nome_disciplina,
                            p.id_professor, u.nome as nome_professor, 
                            u.foto_perfil
                     FROM disciplina d
                     LEFT JOIN professor p ON d.professor_id_professor = p.id_professor
                     LEFT JOIN usuario u ON p.usuario_id_usuario = u.id_usuario
                     WHERE d.id_disciplina = ? 
                     AND d.curso_id_curso = ?";
$stmt_disciplina = $conn->prepare($query_disciplina);
$stmt_disciplina->bind_param("ii", $id_disciplina, $aluno['curso_id_curso']);
$stmt_disciplina->execute();
$disciplina = $stmt_disciplina->get_result()->fetch_assoc();

if (!$disciplina) {
    // Disciplina não existe ou não pertence ao curso do aluno
    header("Location: disciplinas.php");
    exit();
}

// 4. Verificar se há cronograma para a turma do aluno nesta disciplina
$query_cronograma_count = "SELECT COUNT(*) as total
                          FROM cronograma_aula
                          WHERE id_disciplina = ?
                          AND turma_id_turma = ?";
$stmt_cronograma_count = $conn->prepare($query_cronograma_count);
$stmt_cronograma_count->bind_param("ii", $id_disciplina, $aluno['turma_id_turma']);
$stmt_cronograma_count->execute();
$tem_cronograma = $stmt_cronograma_count->get_result()->fetch_assoc()['total'] > 0;

$disciplina['turmas'] = htmlspecialchars($aluno['nome_turma']) ?? 'Turma não definida';

// 6. Obter cronograma específico para a turma do aluno
$query_cronograma = "SELECT dia_semana, horario_inicio, horario_fim, sala
                     FROM cronograma_aula
                     WHERE id_disciplina = ? 
                     AND turma_id_turma = ?
                     ORDER BY FIELD(dia_semana, 'segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado'), 
                     horario_inicio";
$stmt_cronograma = $conn->prepare($query_cronograma);
$stmt_cronograma->bind_param("ii", $id_disciplina, $aluno['turma_id_turma']);
$stmt_cronograma->execute();
$cronograma = $stmt_cronograma->get_result();

// 7. Obter notas do aluno nesta disciplina
$query_notas = "SELECT nota, data, tipo_avaliacao, trimestre, descricao, peso
                FROM nota
                WHERE disciplina_id_disciplina = ? 
                AND aluno_id_aluno = ?
                ORDER BY trimestre, data DESC";
$stmt_notas = $conn->prepare($query_notas);
$stmt_notas->bind_param("ii", $id_disciplina, $aluno['id_aluno']);
$stmt_notas->execute();
$notas = $stmt_notas->get_result();

// 8. Obter frequência do aluno
$query_frequencia = "SELECT data_aula, presenca, tipo_aula, observacao
                     FROM frequencia_aluno
                     WHERE disciplina_id_disciplina = ? 
                     AND aluno_id_aluno = ?
                     AND turma_id_turma = ?
                     ORDER BY data_aula DESC";
$stmt_frequencia = $conn->prepare($query_frequencia);
$stmt_frequencia->bind_param("iii", $id_disciplina, $aluno['id_aluno'], $aluno['turma_id_turma']);
$stmt_frequencia->execute();
$frequencia = $stmt_frequencia->get_result();

// 9. Obter materiais de apoio
$query_materiais = "SELECT COUNT(*) as total_materiais
                    FROM materiais_apoio
                    WHERE id_disciplina = ?";
$stmt_materiais = $conn->prepare($query_materiais);
$stmt_materiais->bind_param("i", $id_disciplina);
$stmt_materiais->execute();
$total_materiais = $stmt_materiais->get_result()->fetch_assoc()['total_materiais'];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <?php require_once __DIR__ . '/../../includes/common/head.php'; ?>
    <title><?= htmlspecialchars($disciplina['nome_disciplina']) ?> - Detalhes</title>
    <style>
        .profile-img {
            width: 150px;
            height: 150px;
            object-fit: cover;
        }
        .tab-content {
            padding-top: 20px;
        }
        .table-success {
            background-color: rgba(40, 167, 69, 0.1);
        }
        .table-warning {
            background-color: rgba(255, 193, 7, 0.1);
        }
        .table-danger {
            background-color: rgba(220, 53, 69, 0.1);
        }
        .badge-material {
            font-size: 0.8rem;
            vertical-align: super;
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
                                                            <i class="feather icon-book"></i> 
                                                            <?= htmlspecialchars($disciplina['nome_disciplina']) ?>
                                                            <a href="disciplinas.php" class="btn btn-sm btn-outline-primary float-right">
                                                                <i class="feather icon-arrow-left"></i> Voltar
                                                            </a>
                                                        </h5>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div class="col-md-4 text-center mb-4 mb-md-0">
                                                                <img src="<?= !empty($disciplina['foto_perfil']) ? 
                                                                    htmlspecialchars($disciplina['foto_perfil']) : 
                                                                    'libraries/assets/images/avatar-2.jpg' ?>" 
                                                                     alt="Professor" class="img-fluid rounded-circle profile-img mb-3">
                                                                <h5><?= $disciplina['nome_professor'] ? htmlspecialchars($disciplina['nome_professor']) : 'Professor não atribuído' ?></h5>
                                                                <p class="text-muted">Professor da Disciplina</p>
                                                                <p><i class="feather icon-users"></i> Turmas: <?= htmlspecialchars($disciplina['turmas']) ?></p>
                                                            </div>
                                                            <div class="col-md-8">
                                                                <ul class="nav nav-tabs" id="disciplinaTabs" role="tablist">
                                                                    <li class="nav-item">
                                                                        <a class="nav-link active" id="cronograma-tab" data-toggle="tab" href="#cronograma" role="tab">
                                                                            <i class="feather icon-calendar"></i> Cronograma
                                                                        </a>
                                                                    </li>
                                                                    <li class="nav-item">
                                                                        <a class="nav-link" id="notas-tab" data-toggle="tab" href="#notas" role="tab">
                                                                            <i class="feather icon-file-text"></i> Notas
                                                                        </a>
                                                                    </li>
                                                                    <li class="nav-item">
                                                                        <a class="nav-link" id="frequencia-tab" data-toggle="tab" href="#frequencia" role="tab">
                                                                            <i class="feather icon-check-square"></i> Frequência
                                                                        </a>
                                                                    </li>
                                                                    <li class="nav-item">
                                                                        <a class="nav-link" id="materiais-tab" data-toggle="tab" href="#materiais" role="tab">
                                                                            <i class="feather icon-folder"></i> Materiais
                                                                            <?php if ($total_materiais > 0): ?>
                                                                                <span class="badge badge-primary badge-material"><?= $total_materiais ?></span>
                                                                            <?php endif; ?>
                                                                        </a>
                                                                    </li>
                                                                </ul>
                                                                <div class="tab-content pt-3" id="disciplinaTabContent">
                                                                    <div class="tab-pane fade show active" id="cronograma" role="tabpanel">
                                                                        <div class="table-responsive">
                                                                            <table class="table table-bordered table-hover">
                                                                                <thead class="thead-light">
                                                                                    <tr>
                                                                                        <th>Dia</th>
                                                                                        <th>Horário</th>
                                                                                        <th>Sala</th>
                                                                                    </tr>
                                                                                </thead>
                                                                                <tbody>
                                                                                    <?php if ($cronograma->num_rows > 0): ?>
                                                                                        <?php while ($aula = $cronograma->fetch_assoc()): ?>
                                                                                            <tr>
                                                                                                <td><?= ucfirst(htmlspecialchars($aula['dia_semana'])) ?></td>
                                                                                                <td><?= date('H:i', strtotime($aula['horario_inicio'])) ?> - <?= date('H:i', strtotime($aula['horario_fim'])) ?></td>
                                                                                                <td><?= htmlspecialchars($aula['sala']) ?></td>
                                                                                            </tr>
                                                                                        <?php endwhile; ?>
                                                                                    <?php else: ?>
                                                                                        <tr>
                                                                                            <td colspan="3" class="text-center text-muted">Nenhum horário cadastrado</td>
                                                                                        </tr>
                                                                                    <?php endif; ?>
                                                                                </tbody>
                                                                            </table>
                                                                        </div>
                                                                    </div>
                                                                    <div class="tab-pane fade" id="notas" role="tabpanel">
                                                                        <div class="table-responsive">
                                                                            <table class="table table-bordered table-hover">
                                                                                <thead class="thead-light">
                                                                                    <tr>
                                                                                        <th>Data</th>
                                                                                        <th>Tipo</th>
                                                                                        <th>Trimestre</th>
                                                                                        <th>Nota</th>
                                                                                        <th>Peso</th>
                                                                                        <th>Descrição</th>
                                                                                    </tr>
                                                                                </thead>
                                                                                <tbody>
                                                                                    <?php if ($notas->num_rows > 0): ?>
                                                                                        <?php while ($nota = $notas->fetch_assoc()): ?>
                                                                                            <tr>
                                                                                                <td><?= date('d/m/Y', strtotime($nota['data'])) ?></td>
                                                                                                <td><?= ucfirst(str_replace('_', ' ', htmlspecialchars($nota['tipo_avaliacao']))) ?></td>
                                                                                                <td><?= htmlspecialchars($nota['trimestre']) ?></td>
                                                                                                <td><strong><?= number_format($nota['nota'], 2) ?></strong></td>
                                                                                                <td><?= number_format($nota['peso'], 2) ?></td>
                                                                                                <td><?= htmlspecialchars($nota['descricao']) ?></td>
                                                                                            </tr>
                                                                                        <?php endwhile; ?>
                                                                                    <?php else: ?>
                                                                                        <tr>
                                                                                            <td colspan="6" class="text-center text-muted">Nenhuma nota registrada</td>
                                                                                        </tr>
                                                                                    <?php endif; ?>
                                                                                </tbody>
                                                                            </table>
                                                                        </div>
                                                                    </div>
                                                                    <div class="tab-pane fade" id="frequencia" role="tabpanel">
                                                                        <div class="table-responsive">
                                                                            <table class="table table-bordered table-hover">
                                                                                <thead class="thead-light">
                                                                                    <tr>
                                                                                        <th>Data</th>
                                                                                        <th>Presença</th>
                                                                                        <th>Tipo de Aula</th>
                                                                                        <th>Observação</th>
                                                                                    </tr>
                                                                                </thead>
                                                                                <tbody>
                                                                                    <?php if ($frequencia->num_rows > 0): ?>
                                                                                        <?php while ($freq = $frequencia->fetch_assoc()): ?>
                                                                                            <tr class="<?= $freq['presenca'] == 'presente' ? 'table-success' : ($freq['presenca'] == 'justificado' ? 'table-warning' : 'table-danger') ?>">
                                                                                                <td><?= date('d/m/Y', strtotime($freq['data_aula'])) ?></td>
                                                                                                <td><?= ucfirst(htmlspecialchars($freq['presenca'])) ?></td>
                                                                                                <td><?= ucfirst(str_replace('_', ' ', htmlspecialchars($freq['tipo_aula']))) ?></td>
                                                                                                <td><?= htmlspecialchars($freq['observacao']) ?></td>
                                                                                            </tr>
                                                                                        <?php endwhile; ?>
                                                                                    <?php else: ?>
                                                                                        <tr>
                                                                                            <td colspan="4" class="text-center text-muted">Nenhum registro de frequência</td>
                                                                                        </tr>
                                                                                    <?php endif; ?>
                                                                                </tbody>
                                                                            </table>
                                                                        </div>
                                                                    </div>
                                                                    <div class="tab-pane fade" id="materiais" role="tabpanel">
                                                                        <?php if ($total_materiais > 0): ?>
                                                                            <div class="alert alert-info">
                                                                                <i class="feather icon-info"></i> 
                                                                                Os materiais desta disciplina estão disponíveis na aba "Materiais" ou através do 
                                                                                <a href="materiais.php?disciplina=<?= $id_disciplina ?>" class="alert-link">link direto</a>.
                                                                            </div>
                                                                        <?php else: ?>
                                                                            <div class="alert alert-warning">
                                                                                <i class="feather icon-alert-triangle"></i> 
                                                                                Nenhum material disponível para esta disciplina no momento.
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
                </div>
            </div>
        </div>
    </div>

    <?php require_once __DIR__ . '/../../includes/common/js_imports.php'; ?>
</body>
</html>