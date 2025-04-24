<?php
require_once '../../includes/common/permissoes.php';
verificarPermissao(['coordenador']);
require_once '../../process/verificar_sessao.php';
require_once '../../database/conexao.php';

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
$nome_curso = $coordenador['nome_curso'];

// Filtros
$filtro_classe = isset($_GET['classe']) ? $_GET['classe'] : '';
$filtro_turma = isset($_GET['turma']) ? $_GET['turma'] : '';
$filtro_status = isset($_GET['status']) ? $_GET['status'] : 'ativos';

// Obter turmas disponíveis para filtro
$sql_turmas = "SELECT t.id_turma, t.nome, t.classe 
              FROM turma t
              WHERE t.curso_id_curso = ?
              ORDER BY t.classe, t.nome";
$stmt = $conn->prepare($sql_turmas);
$stmt->bind_param("i", $id_curso);
$stmt->execute();
$turmas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Obter alunos do curso
$sql_alunos = "SELECT a.id_aluno, u.nome, u.email, u.status, u.foto_perfil,
              m.numero_matricula, m.classe, t.nome as nome_turma, m.status_matricula,
              (SELECT COUNT(*) FROM nota WHERE aluno_id_aluno = a.id_aluno) as total_notas,
              (SELECT AVG(nota) FROM nota WHERE aluno_id_aluno = a.id_aluno) as media_geral
              FROM aluno a
              JOIN usuario u ON a.usuario_id_usuario = u.id_usuario
              JOIN matricula m ON m.aluno_id_aluno = a.id_aluno
              JOIN turma t ON m.turma_id_turma = t.id_turma
              WHERE t.curso_id_curso = ?
              AND u.status = ?
              " . ($filtro_classe ? " AND m.classe = ?" : "") . "
              " . ($filtro_turma ? " AND t.id_turma = ?" : "") . "
              ORDER BY u.nome";

$stmt = $conn->prepare($sql_alunos);
$status_filter = ($filtro_status == 'ativos') ? 'ativo' : 'inativo';

if ($filtro_classe && $filtro_turma) {
    $stmt->bind_param("issi", $id_curso, $status_filter, $filtro_classe, $filtro_turma);
} elseif ($filtro_classe) {
    $stmt->bind_param("iss", $id_curso, $status_filter, $filtro_classe);
} elseif ($filtro_turma) {
    $stmt->bind_param("isi", $id_curso, $status_filter, $filtro_turma);
} else {
    $stmt->bind_param("is", $id_curso, $status_filter);
}

$stmt->execute();
$alunos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$title = "Alunos - " . htmlspecialchars($nome_curso);
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
                                                    <h4>Alunos do Curso</h4>
                                                    <span><?= htmlspecialchars($nome_curso) ?></span>
                                                </div>
                                            </div>
                                            <div class="col-lg-4">
                                                <div class="page-header-breadcrumb">
                                                    <ul class="breadcrumb-title">
                                                        <li class="breadcrumb-item">
                                                            <a href="index.php"><i class="feather icon-home"></i></a>
                                                        </li>
                                                        <li class="breadcrumb-item active">Alunos</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="page-body">
                                        <div class="card mb-4">
                                            <div class="card-header">
                                                <h5>Filtrar Alunos</h5>
                                            </div>
                                            <div class="card-block">
                                                <form method="GET" action="" class="row">
                                                    <div class="col-md-3 col-6 mb-2">
                                                        <select class="form-control" name="status">
                                                            <option value="ativos" <?= $filtro_status == 'ativos' ? 'selected' : '' ?>>Alunos Ativos</option>
                                                            <option value="inativos" <?= $filtro_status == 'inativos' ? 'selected' : '' ?>>Alunos Inativos</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-3 col-6 mb-2">
                                                        <select class="form-control" name="classe">
                                                            <option value="">Todas as Classes</option>
                                                            <?php 
                                                            $classes = array_unique(array_column($turmas, 'classe'));
                                                            foreach ($classes as $classe): ?>
                                                                <option value="<?= $classe ?>" <?= $filtro_classe == $classe ? 'selected' : '' ?>>
                                                                    <?= $classe ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-3 col-6 mb-2">
                                                        <select class="form-control" name="turma">
                                                            <option value="">Todas as Turmas</option>
                                                            <?php foreach ($turmas as $turma): ?>
                                                                <option value="<?= $turma['id_turma'] ?>" <?= $filtro_turma == $turma['id_turma'] ? 'selected' : '' ?>>
                                                                    <?= htmlspecialchars($turma['nome']) ?> (<?= $turma['classe'] ?>)
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-3 col-6 mb-2">
                                                        <button type="submit" class="btn btn-primary btn-block">
                                                            <i class="feather icon-filter"></i> Filtrar
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>

                                        <div class="card">
                                            <div class="card-header">
                                                <h5>Lista de Alunos</h5>
                                                <div class="card-header-right">
                                                    <span class="badge badge-primary">
                                                        <?= count($alunos) ?> aluno(s) encontrado(s)
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="card-block">
                                                <?php if (empty($alunos)): ?>
                                                    <div class="alert alert-info">
                                                        Nenhum aluno encontrado com os filtros selecionados.
                                                    </div>
                                                <?php else: ?>
                                                    <div class="table-responsive">
                                                        <table class="table table-hover">
                                                            <thead>
                                                                <tr>
                                                                    <th>Foto</th>
                                                                    <th>Nome</th>
                                                                    <th>Matrícula</th>
                                                                    <th>Turma</th>
                                                                    <th>Classe</th>
                                                                    <th>Notas</th>
                                                                    <th>Média</th>
                                                                    <th>Ações</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($alunos as $aluno): ?>
                                                                    <tr>
                                                                        <td>
                                                                            <img src="../../public/uploads/perfil/<?= !empty($aluno['foto_perfil']) ? htmlspecialchars($aluno['foto_perfil']) : 'default.png' ?>" 
                                                                                 class="img-thumbnail rounded-circle" style="width: 40px; height: 40px;" alt="Foto do aluno">
                                                                        </td>
                                                                        <td><?= htmlspecialchars($aluno['nome']) ?></td>
                                                                        <td><?= htmlspecialchars($aluno['numero_matricula']) ?></td>
                                                                        <td><?= htmlspecialchars($aluno['nome_turma']) ?></td>
                                                                        <td><?= htmlspecialchars($aluno['classe']) ?></td>
                                                                        <td><?= $aluno['total_notas'] ?></td>
                                                                        <td class="<?= $aluno['media_geral'] < 10 ? 'text-danger' : 'text-success' ?>">
                                                                            <?= $aluno['media_geral'] ? number_format($aluno['media_geral'], 2) : '-' ?>
                                                                        </td>
                                                                        <td>
                                                                            <a href="aluno_detalhes.php?id=<?= $aluno['id_aluno'] ?>" class="btn btn-sm btn-primary">
                                                                                <i class="feather icon-eye"></i> Ver
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
<?php $conn->close(); ?>