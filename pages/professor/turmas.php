<?php
require_once '../../includes/common/permissoes.php';
verificarPermissao(['professor']);
require_once '../../process/verificar_sessao.php';
require_once '../../database/conexao.php';

$title = "Minhas Turmas";

// Obter o ID do professor logado
$id_usuario = $_SESSION['id_usuario'];
$professor_id = 0;

// Buscar o ID do professor
$query_professor = "SELECT id_professor FROM professor WHERE usuario_id_usuario = ?";
$stmt_professor = $conn->prepare($query_professor);
$stmt_professor->bind_param("i", $id_usuario);
$stmt_professor->execute();
$result_professor = $stmt_professor->get_result();

if ($result_professor->num_rows > 0) {
    $row_professor = $result_professor->fetch_assoc();
    $professor_id = $row_professor['id_professor'];
}

// Filtros
$ano_letivo = isset($_GET['ano_letivo']) ? intval($_GET['ano_letivo']) : date('Y');
$filtro_turma = isset($_GET['filtro_turma']) ? $_GET['filtro_turma'] : 'minhas';

// Buscar turmas do professor
$query_minhas_turmas = "SELECT t.id_turma, t.nome AS nome_turma, c.nome AS nome_curso,
                       COUNT(DISTINCT m.id_matricula) AS total_alunos
                       FROM turma t
                       JOIN curso c ON t.curso_id_curso = c.id_curso
                       JOIN professor_tem_turma pt ON pt.turma_id_turma = t.id_turma
                       LEFT JOIN matricula m ON m.turma_id_turma = t.id_turma AND m.ano_letivo = ?
                       WHERE pt.professor_id_professor = ?
                       GROUP BY t.id_turma, t.nome, c.nome
                       ORDER BY c.nome, t.nome";

$stmt_minhas_turmas = $conn->prepare($query_minhas_turmas);
$stmt_minhas_turmas->bind_param("ii", $ano_letivo, $professor_id);
$stmt_minhas_turmas->execute();
$minhas_turmas = $stmt_minhas_turmas->get_result()->fetch_all(MYSQLI_ASSOC);

// Buscar todas as turmas (para o filtro "todas")
$query_todas_turmas = "SELECT t.id_turma, t.nome AS nome_turma, c.nome AS nome_curso,
                      COUNT(DISTINCT m.id_matricula) AS total_alunos,
                      GROUP_CONCAT(DISTINCT u.nome SEPARATOR ', ') AS professores
                      FROM turma t
                      JOIN curso c ON t.curso_id_curso = c.id_curso
                      LEFT JOIN professor_tem_turma pt ON pt.turma_id_turma = t.id_turma
                      LEFT JOIN professor p ON pt.professor_id_professor = p.id_professor
                      LEFT JOIN usuario u ON p.usuario_id_usuario = u.id_usuario
                      LEFT JOIN matricula m ON m.turma_id_turma = t.id_turma AND m.ano_letivo = ?
                      GROUP BY t.id_turma, t.nome, c.nome
                      ORDER BY c.nome, t.nome";

$stmt_todas_turmas = $conn->prepare($query_todas_turmas);
$stmt_todas_turmas->bind_param("i", $ano_letivo);
$stmt_todas_turmas->execute();
$todas_turmas = $stmt_todas_turmas->get_result()->fetch_all(MYSQLI_ASSOC);

// Determinar quais turmas mostrar com base no filtro
$turmas = ($filtro_turma == 'todas') ? $todas_turmas : $minhas_turmas;
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
                                                    <h4>Gerenciamento de Turmas</h4>
                                                    <span>Visualize e gerencie suas turmas e alunos</span>
                                                </div>
                                            </div>
                                            <div class="col-lg-4">
                                                <div class="page-header-breadcrumb">
                                                    <ul class="breadcrumb-title">
                                                        <li class="breadcrumb-item">
                                                            <a href="index.php"><i class="feather icon-home"></i></a>
                                                        </li>
                                                        <li class="breadcrumb-item"><a href="#!">Professor</a></li>
                                                        <li class="breadcrumb-item"><a href="#!">Turmas</a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="page-body">
                                        <div class="row">
                                            <div class="col-12">
                                                <!-- Filtros -->
                                                <div class="card card-table mb-3">
                                                    <div class="card-header">
                                                        <h5>Filtrar Turmas</h5>
                                                    </div>
                                                    <div class="card-body">
                                                        <form method="GET" action="">
                                                            <div class="row">
                                                                <div class="col-md-4">
                                                                    <div class="form-group">
                                                                        <label>Tipo de Turmas</label>
                                                                        <select class="form-control" name="filtro_turma">
                                                                            <option value="minhas" <?= $filtro_turma == 'minhas' ? 'selected' : '' ?>>Minhas Turmas</option>
                                                                            <option value="todas" <?= $filtro_turma == 'todas' ? 'selected' : '' ?>>Todas as Turmas</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div class="form-group">
                                                                        <label>Ano Letivo</label>
                                                                        <select class="form-control" name="ano_letivo">
                                                                            <?php for ($i = date('Y') - 1; $i <= date('Y') + 1; $i++): ?>
                                                                                <option value="<?= $i ?>" <?= $ano_letivo == $i ? 'selected' : '' ?>><?= $i ?></option>
                                                                            <?php endfor; ?>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4 d-flex align-items-end">
                                                                    <button type="submit" class="btn btn-primary btn-block">
                                                                        <i class="feather icon-filter"></i> Aplicar Filtros
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>

                                                <!-- Tabela de Turmas -->
                                                <div class="card card-table">
                                                    <div class="card-header">
                                                        <h5><?= $filtro_turma == 'minhas' ? 'Minhas Turmas' : 'Todas as Turmas' ?></h5>
                                                    </div>
                                                    <div class="card-block">
                                                        <div class="table-responsive">
                                                            <table class="table table-hover">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Turma</th>
                                                                        <th>Curso</th>
                                                                        <th>Alunos</th>
                                                                        <?php if ($filtro_turma == 'todas'): ?>
                                                                            <th>Professores</th>
                                                                        <?php endif; ?>
                                                                        <th>Ações</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php if (empty($turmas)): ?>
                                                                        <tr>
                                                                            <td colspan="<?= $filtro_turma == 'todas' ? 5 : 4 ?>" class="text-center">Nenhuma turma encontrada</td>
                                                                        </tr>
                                                                    <?php else: ?>
                                                                        <?php foreach ($turmas as $turma): ?>
                                                                            <tr>
                                                                                <td><?= htmlspecialchars($turma['nome_turma']) ?></td>
                                                                                <td><?= htmlspecialchars($turma['nome_curso']) ?></td>
                                                                                <td><?= $turma['total_alunos'] ?></td>
                                                                                <?php if ($filtro_turma == 'todas'): ?>
                                                                                    <td><?= !empty($turma['professores']) ? htmlspecialchars($turma['professores']) : 'Nenhum' ?></td>
                                                                                <?php endif; ?>
                                                                                <td>
                                                                                    <a href="turma_detalhes.php?id=<?= $turma['id_turma'] ?>&ano=<?= $ano_letivo ?>" 
                                                                                       class="btn btn-info btn-sm" title="Ver detalhes">
                                                                                        <i class="feather icon-eye"></i>
                                                                                    </a>
                                                                                    <?php if ($filtro_turma == 'minhas'): ?>
                                                                                        <a href="notas.php?turma_id=<?= $turma['id_turma'] ?>&ano_letivo=<?= $ano_letivo ?>" 
                                                                                           class="btn btn-primary btn-sm" title="Lançar notas">
                                                                                            <i class="feather icon-edit"></i>
                                                                                        </a>
                                                                                        <a href="frequencia.php?turma_id=<?= $turma['id_turma'] ?>&ano_letivo=<?= $ano_letivo ?>" 
                                                                                           class="btn btn-warning btn-sm" title="Registrar frequência">
                                                                                            <i class="feather icon-check-square"></i>
                                                                                        </a>
                                                                                    <?php endif; ?>
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