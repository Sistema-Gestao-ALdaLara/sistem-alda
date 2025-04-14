<?php
require_once '../../includes/common/permissoes.php';
verificarPermissao(['professor']);
require_once '../../process/verificar_sessao.php';
require_once '../../database/conexao.php';

// Verificar parâmetros
if (!isset($_GET['id']) || !isset($_GET['ano'])) {
    header("Location: turmas.php");
    exit();
}

$turma_id = intval($_GET['id']);
$ano_letivo = intval($_GET['ano']);

// Obter informações da turma
$query_turma = "SELECT t.nome AS nome_turma, c.nome AS nome_curso 
                FROM turma t
                JOIN curso c ON t.curso_id_curso = c.id_curso
                WHERE t.id_turma = ?";
$stmt_turma = $conn->prepare($query_turma);
$stmt_turma->bind_param("i", $turma_id);
$stmt_turma->execute();
$turma = $stmt_turma->get_result()->fetch_assoc();

if (!$turma) {
    header("Location: turmas.php");
    exit();
}

// Obter alunos da turma
$query_alunos = "SELECT u.id_usuario, u.nome, u.email, u.bi_numero, 
                a.data_nascimento, a.genero, a.naturalidade, a.nacionalidade,
                m.numero_matricula, m.classe, m.turno, m.status_matricula
                FROM matricula m
                JOIN aluno a ON m.aluno_id_aluno = a.id_aluno
                JOIN usuario u ON a.usuario_id_usuario = u.id_usuario
                WHERE m.turma_id_turma = ? AND m.ano_letivo = ?
                ORDER BY u.nome";
$stmt_alunos = $conn->prepare($query_alunos);
$stmt_alunos->bind_param("ii", $turma_id, $ano_letivo);
$stmt_alunos->execute();
$alunos = $stmt_alunos->get_result()->fetch_all(MYSQLI_ASSOC);

// Obter professores da turma
$query_professores = "SELECT u.nome, u.email, d.nome AS disciplina
                     FROM professor_tem_turma pt
                     JOIN professor p ON pt.professor_id_professor = p.id_professor
                     JOIN usuario u ON p.usuario_id_usuario = u.id_usuario
                     LEFT JOIN disciplina d ON d.professor_id_professor = p.id_professor AND d.curso_id_curso = (SELECT curso_id_curso FROM turma WHERE id_turma = ?)
                     WHERE pt.turma_id_turma = ?
                     GROUP BY u.id_usuario, u.nome, u.email, d.nome";
$stmt_professores = $conn->prepare($query_professores);
$stmt_professores->bind_param("ii", $turma_id, $turma_id);
$stmt_professores->execute();
$professores = $stmt_professores->get_result()->fetch_all(MYSQLI_ASSOC);

$title = "Detalhes da Turma: " . $turma['nome_turma'];
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
                                    <div class="page-header">
                                        <div class="row align-items-end">
                                            <div class="col-lg-8">
                                                <div class="page-header-title">
                                                    <h4>Detalhes da Turma</h4>
                                                    <span><?= htmlspecialchars($turma['nome_turma']) ?> - <?= htmlspecialchars($turma['nome_curso']) ?> (<?= $ano_letivo ?>)</span>
                                                </div>
                                            </div>
                                            <div class="col-lg-4">
                                                <div class="page-header-breadcrumb">
                                                    <ul class="breadcrumb-title">
                                                        <li class="breadcrumb-item">
                                                            <a href="index.php"><i class="feather icon-home"></i></a>
                                                        </li>
                                                        <li class="breadcrumb-item"><a href="turmas.php">Turmas</a></li>
                                                        <li class="breadcrumb-item"><a href="#!">Detalhes</a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="page-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="card card-table">
                                                    <div class="card-header">
                                                        <h5>Alunos da Turma (<?= count($alunos) ?>)</h5>
                                                    </div>
                                                    <div class="card-block">
                                                        <div class="table-responsive">
                                                            <table class="table table-hover">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Nome</th>
                                                                        <th>Matrícula</th>
                                                                        <th>Status</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php if (empty($alunos)): ?>
                                                                        <tr>
                                                                            <td colspan="3" class="text-center">Nenhum aluno nesta turma</td>
                                                                        </tr>
                                                                    <?php else: ?>
                                                                        <?php foreach ($alunos as $aluno): ?>
                                                                            <tr>
                                                                                <td><?= htmlspecialchars($aluno['nome']) ?></td>
                                                                                <td><?= htmlspecialchars($aluno['numero_matricula']) ?></td>
                                                                                <td>
                                                                                    <span class="badge <?= $aluno['status_matricula'] == 'ativa' ? 'badge-success' : 'badge-danger' ?>">
                                                                                        <?= ucfirst($aluno['status_matricula']) ?>
                                                                                    </span>
                                                                                </td>
                                                                            </tr>
                                                                        <?php endforeach; ?>
                                                                    <?php endif; ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="card card-table">
                                                    <div class="card-header">
                                                        <h5>Professores da Turma</h5>
                                                    </div>
                                                    <div class="card-block">
                                                        <div class="table-responsive">
                                                            <table class="table table-hover">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Nome</th>
                                                                        <th>Disciplina</th>
                                                                        <th>Contato</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php if (empty($professores)): ?>
                                                                        <tr>
                                                                            <td colspan="3" class="text-center">Nenhum professor nesta turma</td>
                                                                        </tr>
                                                                    <?php else: ?>
                                                                        <?php foreach ($professores as $professor): ?>
                                                                            <tr>
                                                                                <td><?= htmlspecialchars($professor['nome']) ?></td>
                                                                                <td><?= !empty($professor['disciplina']) ? htmlspecialchars($professor['disciplina']) : 'N/A' ?></td>
                                                                                <td>
                                                                                    <a href="mailto:<?= htmlspecialchars($professor['email']) ?>" class="btn btn-sm btn-primary">
                                                                                        <i class="feather icon-mail"></i>
                                                                                    </a>
                                                                                </td>
                                                                            </tr>
                                                                        <?php endforeach; ?>
                                                                    <?php endif; ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mt-4">
                                            <div class="col-12">
                                                <div class="card card-table">
                                                    <div class="card-header">
                                                        <h5>Estatísticas da Turma</h5>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div class="col-md-3">
                                                                <div class="card card-estatistica card-provas text-white card-table container-fluid">
                                                                    <div class="card-body">
                                                                        <div class="row">
                                                                            <div class="col-8">
                                                                                <h4 class="mb-1"><?= count($alunos) ?></h4>
                                                                                <p class="mb-0">Total de Alunos</p>
                                                                            </div>
                                                                            <div class="col-4 text-right">
                                                                                <i class="feather icon-users f-40"></i>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <div class="card card-estatistica card-avaliacoes card-table text-white container-fluid">
                                                                    <div class="card-body">
                                                                        <div class="row">
                                                                            <div class="col-8">
                                                                                <h4 class="mb-1"><?= count(array_filter($alunos, function($a) { return $a['status_matricula'] == 'ativa'; })) ?></h4>
                                                                                <p class="mb-0">Matrículas Ativas</p>
                                                                            </div>
                                                                            <div class="col-4 text-right">
                                                                                <i class="feather icon-check-circle f-40"></i>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <div class="card card-estatistica card-trabalhos card-table text-white container-fluid">
                                                                    <div class="card-body">
                                                                        <div class="row">
                                                                            <div class="col-8">
                                                                                <h4 class="mb-1"><?= count($professores) ?></h4>
                                                                                <p class="mb-0">Professores</p>
                                                                            </div>
                                                                            <div class="col-4 text-right">
                                                                                <i class="feather icon-users f-40"></i>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <div class="card card-estatistica card-table text-white container-fluid">
                                                                    <div class="card-body">
                                                                        <div class="row">
                                                                            <div class="col-8">
                                                                                <h4 class="mb-1"><?= $ano_letivo ?></h4>
                                                                                <p class="mb-0">Ano Letivo</p>
                                                                            </div>
                                                                            <div class="col-4 text-right">
                                                                                <i class="feather icon-calendar f-40"></i>
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
        </div>
    </div>

    <?php require_once '../../includes/common/js_imports.php'; ?>
</body>
</html>
<?php $conn->close(); ?>