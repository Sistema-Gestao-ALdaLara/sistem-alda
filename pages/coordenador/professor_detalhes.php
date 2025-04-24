<?php
require_once '../../includes/common/permissoes.php';
verificarPermissao(['coordenador']);
require_once '../../process/verificar_sessao.php';
require_once '../../database/conexao.php';

// Verificar se o ID do professor foi fornecido
$professor_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$professor_id) {
    header("Location: supervisao_professores.php");
    exit();
}

// Obter informações do coordenador e curso
$id_usuario = $_SESSION['id_usuario'];
$sql_coordenador = "SELECT c.curso_id_curso, cr.nome as nome_curso 
                   FROM coordenador c
                   JOIN curso cr ON c.curso_id_curso = cr.id_curso
                   WHERE c.usuario_id_usuario = ?";
$stmt = $conn->prepare($sql_coordenador);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
$coordenador = $result->fetch_assoc();

if (!$coordenador) {
    die("Acesso negado ou coordenador não encontrado.");
}

$id_curso = $coordenador['curso_id_curso'];

// Verificar se o professor pertence ao curso do coordenador
$sql_professor = "SELECT p.id_professor, u.nome, u.email, u.status, u.foto_perfil, u.bi_numero
                 FROM professor p
                 JOIN usuario u ON p.usuario_id_usuario = u.id_usuario
                 WHERE p.id_professor = ? AND p.curso_id_curso = ?";
$stmt = $conn->prepare($sql_professor);
$stmt->bind_param("ii", $professor_id, $id_curso);
$stmt->execute();
$professor = $stmt->get_result()->fetch_assoc();

if (!$professor) {
    die("Professor não encontrado ou não pertence ao seu curso.");
}

// Obter disciplinas ministradas pelo professor
$sql_disciplinas = "SELECT d.id_disciplina, d.nome, d.classe, 
                   (SELECT COUNT(*) FROM nota WHERE disciplina_id_disciplina = d.id_disciplina) as total_avaliacoes,
                   (SELECT AVG(nota) FROM nota WHERE disciplina_id_disciplina = d.id_disciplina) as media_notas
                   FROM disciplina d
                   JOIN professor_tem_disciplina pd ON d.id_disciplina = pd.disciplina_id_disciplina
                   WHERE pd.professor_id_professor = ?
                   ORDER BY d.classe, d.nome";
$stmt = $conn->prepare($sql_disciplinas);
$stmt->bind_param("i", $professor_id);
$stmt->execute();
$disciplinas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Obter turmas do professor
$sql_turmas = "SELECT t.id_turma, t.nome as nome_turma, t.classe, t.turno, 
              c.nome as nome_curso,
              (SELECT COUNT(*) FROM aluno a JOIN matricula m ON a.id_aluno = m.aluno_id_aluno WHERE m.turma_id_turma = t.id_turma) as total_alunos
              FROM turma t
              JOIN curso c ON t.curso_id_curso = c.id_curso
              JOIN professor_tem_turma pt ON t.id_turma = pt.turma_id_turma
              WHERE pt.professor_id_professor = ?
              ORDER BY t.classe, t.nome";
$stmt = $conn->prepare($sql_turmas);
$stmt->bind_param("i", $professor_id);
$stmt->execute();
$turmas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Obter cronograma de aulas
$sql_aulas = "SELECT ca.id_cronograma_aula, ca.dia_semana, ca.horario_inicio, ca.horario_fim, ca.sala,
             d.nome as disciplina_nome, t.nome as turma_nome
             FROM cronograma_aula ca
             JOIN disciplina d ON ca.id_disciplina = d.id_disciplina
             JOIN turma t ON ca.turma_id_turma = t.id_turma
             WHERE ca.id_professor = ?
             ORDER BY FIELD(ca.dia_semana, 'segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado'), 
             ca.horario_inicio";
$stmt = $conn->prepare($sql_aulas);
$stmt->bind_param("i", $professor_id);
$stmt->execute();
$aulas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Obter materiais de apoio
$sql_materiais = "SELECT ma.id_material, ma.nome, ma.descricao, ma.data_upload,
                 d.nome as disciplina_nome
                 FROM materiais_apoio ma
                 JOIN disciplina d ON ma.id_disciplina = d.id_disciplina
                 JOIN professor_tem_disciplina pd ON d.id_disciplina = pd.disciplina_id_disciplina
                 WHERE pd.professor_id_professor = ?
                 ORDER BY ma.data_upload DESC
                 LIMIT 10";
$stmt = $conn->prepare($sql_materiais);
$stmt->bind_param("i", $professor_id);
$stmt->execute();
$materiais = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Obter planos de ensino
$sql_planos = "SELECT pe.id_plano, pe.ano_letivo, pe.trimestre, pe.status,
              pe.data_submissao, pe.data_aprovacao, d.nome as disciplina_nome
              FROM plano_ensino pe
              JOIN disciplina d ON pe.id_disciplina = d.id_disciplina
              WHERE pe.id_professor = ?
              ORDER BY pe.ano_letivo DESC, pe.trimestre
              LIMIT 5";
$stmt = $conn->prepare($sql_planos);
$stmt->bind_param("i", $professor_id);
$stmt->execute();
$planos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$title = "Detalhes do Professor - " . htmlspecialchars($professor['nome']);
?>

<!DOCTYPE html>
<html lang="pt">

<?php require_once '../../includes/common/head.php'; ?>

<body>
    <?php require_once '../../includes/common/preloader.php'; ?>

    <div id="pcoded" class="pcoded">
        <div class="pcoded-overlay-box"></div>
        <div class="pcoded-container navbar-wrapper">
            <?php require_once '../../includes/coordenador/navbar.php'; ?>

            <div class="pcoded-main-container">
                <div class="pcoded-wrapper">
                    <?php require_once '../../includes/coordenador/sidebar.php'; ?>
                    
                    <div class="pcoded-content">
                        <div class="pcoded-inner-content">
                            <div class="main-body bg-img">
                                <div class="page-wrapper">
                                    <div class="page-header">
                                        <div class="row align-items-end">
                                            <div class="col-lg-8">
                                                <div class="page-header-title">
                                                    <h4>Detalhes do Professor</h4>
                                                    <span><?= htmlspecialchars($professor['nome']) ?></span>
                                                </div>
                                            </div>
                                            <div class="col-lg-4">
                                                <div class="page-header-breadcrumb">
                                                    <ul class="breadcrumb-title">
                                                        <li class="breadcrumb-item">
                                                            <a href="index.php"><i class="feather icon-home"></i></a>
                                                        </li>
                                                        <li class="breadcrumb-item"><a href="supervisao_professores.php">Professores</a></li>
                                                        <li class="breadcrumb-item active">Detalhes</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="page-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="card">
                                                    <div class="card-header text-center">
                                                        <img src="../../public/uploads/perfil/<?= !empty($professor['foto_perfil']) ? htmlspecialchars($professor['foto_perfil']) : 'default.png' ?>" 
                                                             class="img-thumbnail rounded-circle" style="width: 150px; height: 150px;" alt="Foto do professor">
                                                    </div>
                                                    <div class="card-body">
                                                        <h4 class="text-center"><?= htmlspecialchars($professor['nome']) ?></h4>
                                                        <hr>
                                                        <div class="table-responsive">
                                                            <table class="table table-sm">
                                                                <tr>
                                                                    <th>Email</th>
                                                                    <td><?= htmlspecialchars($professor['email']) ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>BI/Nº Documento</th>
                                                                    <td><?= htmlspecialchars($professor['bi_numero']) ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Status</th>
                                                                    <td>
                                                                        <span class="badge badge-<?= $professor['status'] == 'ativo' ? 'success' : 'danger' ?>">
                                                                            <?= ucfirst($professor['status']) ?>
                                                                        </span>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-8">
                                                <ul class="nav nav-tabs" id="professorTabs" role="tablist">
                                                    <li class="nav-item">
                                                        <a class="nav-link active" id="disciplinas-tab" data-toggle="tab" href="#disciplinas" role="tab">
                                                            <i class="feather icon-book"></i> Disciplinas
                                                        </a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a class="nav-link" id="turmas-tab" data-toggle="tab" href="#turmas" role="tab">
                                                            <i class="feather icon-users"></i> Turmas
                                                        </a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a class="nav-link" id="horario-tab" data-toggle="tab" href="#horario" role="tab">
                                                            <i class="feather icon-clock"></i> Horário
                                                        </a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a class="nav-link" id="materiais-tab" data-toggle="tab" href="#materiais" role="tab">
                                                            <i class="feather icon-file-text"></i> Materiais
                                                        </a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a class="nav-link" id="planos-tab" data-toggle="tab" href="#planos" role="tab">
                                                            <i class="feather icon-file"></i> Planos
                                                        </a>
                                                    </li>
                                                </ul>

                                                <div class="tab-content" id="professorTabsContent">
                                                    <!-- Tab Disciplinas -->
                                                    <div class="tab-pane fade show active" id="disciplinas" role="tabpanel">
                                                        <div class="card">
                                                            <div class="card-header">
                                                                <h5>Disciplinas Ministradas</h5>
                                                            </div>
                                                            <div class="card-body">
                                                                <?php if (empty($disciplinas)): ?>
                                                                    <div class="alert alert-info">
                                                                        Nenhuma disciplina atribuída a este professor.
                                                                    </div>
                                                                <?php else: ?>
                                                                    <div class="table-responsive">
                                                                        <table class="table table-hover">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th>Disciplina</th>
                                                                                    <th>Classe</th>
                                                                                    <th>Avaliações</th>
                                                                                    <th>Média</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                <?php foreach ($disciplinas as $disciplina): ?>
                                                                                    <tr>
                                                                                        <td><?= htmlspecialchars($disciplina['nome']) ?></td>
                                                                                        <td><?= htmlspecialchars($disciplina['classe']) ?></td>
                                                                                        <td><?= $disciplina['total_avaliacoes'] ?></td>
                                                                                        <td class="<?= $disciplina['media_notas'] < 10 ? 'text-danger' : 'text-success' ?>">
                                                                                            <?= $disciplina['media_notas'] ? number_format($disciplina['media_notas'], 2) : '-' ?>
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

                                                    <!-- Tab Turmas -->
                                                    <div class="tab-pane fade" id="turmas" role="tabpanel">
                                                        <div class="card">
                                                            <div class="card-header">
                                                                <h5>Turmas Atribuídas</h5>
                                                            </div>
                                                            <div class="card-body">
                                                                <?php if (empty($turmas)): ?>
                                                                    <div class="alert alert-info">
                                                                        Nenhuma turma atribuída a este professor.
                                                                    </div>
                                                                <?php else: ?>
                                                                    <div class="table-responsive">
                                                                        <table class="table table-hover">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th>Turma</th>
                                                                                    <th>Curso</th>
                                                                                    <th>Classe</th>
                                                                                    <th>Turno</th>
                                                                                    <th>Alunos</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                <?php foreach ($turmas as $turma): ?>
                                                                                    <tr>
                                                                                        <td><?= htmlspecialchars($turma['nome_turma']) ?></td>
                                                                                        <td><?= htmlspecialchars($turma['nome_curso']) ?></td>
                                                                                        <td><?= htmlspecialchars($turma['classe']) ?></td>
                                                                                        <td><?= htmlspecialchars($turma['turno']) ?></td>
                                                                                        <td><?= $turma['total_alunos'] ?></td>
                                                                                    </tr>
                                                                                <?php endforeach; ?>
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Tab Horário -->
                                                    <div class="tab-pane fade" id="horario" role="tabpanel">
                                                        <div class="card">
                                                            <div class="card-header">
                                                                <h5>Horário de Aulas</h5>
                                                            </div>
                                                            <div class="card-body">
                                                                <?php if (empty($aulas)): ?>
                                                                    <div class="alert alert-info">
                                                                        Nenhuma aula registrada no cronograma deste professor.
                                                                    </div>
                                                                <?php else: ?>
                                                                    <div class="table-responsive">
                                                                        <table class="table table-hover">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th>Dia</th>
                                                                                    <th>Horário</th>
                                                                                    <th>Disciplina</th>
                                                                                    <th>Turma</th>
                                                                                    <th>Sala</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                <?php foreach ($aulas as $aula): ?>
                                                                                    <tr>
                                                                                        <td><?= ucfirst($aula['dia_semana']) ?></td>
                                                                                        <td><?= date('H:i', strtotime($aula['horario_inicio'])) ?> - <?= date('H:i', strtotime($aula['horario_fim'])) ?></td>
                                                                                        <td><?= htmlspecialchars($aula['disciplina_nome']) ?></td>
                                                                                        <td><?= htmlspecialchars($aula['turma_nome']) ?></td>
                                                                                        <td><?= htmlspecialchars($aula['sala']) ?></td>
                                                                                    </tr>
                                                                                <?php endforeach; ?>
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Tab Materiais -->
                                                    <div class="tab-pane fade" id="materiais" role="tabpanel">
                                                        <div class="card">
                                                            <div class="card-header">
                                                                <h5>Materiais de Apoio Recentes</h5>
                                                            </div>
                                                            <div class="card-body">
                                                                <?php if (empty($materiais)): ?>
                                                                    <div class="alert alert-info">
                                                                        Nenhum material de apoio enviado por este professor.
                                                                    </div>
                                                                <?php else: ?>
                                                                    <div class="list-group">
                                                                        <?php foreach ($materiais as $material): ?>
                                                                            <a href="#" class="list-group-item list-group-item-action flex-column align-items-start">
                                                                                <div class="d-flex w-100 justify-content-between">
                                                                                    <h6 class="mb-1"><?= htmlspecialchars($material['nome']) ?></h6>
                                                                                    <small><?= date('d/m/Y H:i', strtotime($material['data_upload'])) ?></small>
                                                                                </div>
                                                                                <p class="mb-1"><?= htmlspecialchars($material['descricao']) ?></p>
                                                                                <small>Disciplina: <?= htmlspecialchars($material['disciplina_nome']) ?></small>
                                                                            </a>
                                                                        <?php endforeach; ?>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Tab Planos -->
                                                    <div class="tab-pane fade" id="planos" role="tabpanel">
                                                        <div class="card">
                                                            <div class="card-header">
                                                                <h5>Planos de Ensino</h5>
                                                            </div>
                                                            <div class="card-body">
                                                                <?php if (empty($planos)): ?>
                                                                    <div class="alert alert-info">
                                                                        Nenhum plano de ensino submetido por este professor.
                                                                    </div>
                                                                <?php else: ?>
                                                                    <div class="table-responsive">
                                                                        <table class="table table-hover">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th>Disciplina</th>
                                                                                    <th>Ano Letivo</th>
                                                                                    <th>Trimestre</th>
                                                                                    <th>Status</th>
                                                                                    <th>Submissão</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                <?php foreach ($planos as $plano): ?>
                                                                                    <tr>
                                                                                        <td><?= htmlspecialchars($plano['disciplina_nome']) ?></td>
                                                                                        <td><?= $plano['ano_letivo'] ?></td>
                                                                                        <td><?= $plano['trimestre'] ?>º</td>
                                                                                        <td>
                                                                                            <?php 
                                                                                                $badge_class = [
                                                                                                    'rascunho' => 'secondary',
                                                                                                    'submetido' => 'info',
                                                                                                    'aprovado' => 'success',
                                                                                                    'rejeitado' => 'danger'
                                                                                                ][$plano['status']];
                                                                                            ?>
                                                                                            <span class="badge badge-<?= $badge_class ?>">
                                                                                                <?= ucfirst($plano['status']) ?>
                                                                                            </span>
                                                                                        </td>
                                                                                        <td><?= $plano['data_submissao'] ? date('d/m/Y H:i', strtotime($plano['data_submissao'])) : '-' ?></td>
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
                </div>
            </div>
        </div>
    </div>

    <?php require_once '../../includes/common/js_imports.php'; ?>
    
    <script>
        $(document).ready(function() {
            // Ativar tooltips
            $('[data-toggle="tooltip"]').tooltip();
            
            // Alternar entre abas e manter estado
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                localStorage.setItem('lastProfessorTab', $(e.target).attr('href'));
            });
            
            var lastTab = localStorage.getItem('lastProfessorTab');
            if (lastTab) {
                $('[href="' + lastTab + '"]').tab('show');
            }
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>