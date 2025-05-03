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
$filtro_status = isset($_GET['status']) ? $_GET['status'] : 'ativos';

// Obter professores do curso
$sql_professores = "SELECT p.id_professor, u.nome, u.email, u.status,
                   (SELECT COUNT(*) FROM professor_tem_disciplina dp WHERE dp.professor_id_professor = p.id_professor) as total_disciplinas,
                   (SELECT COUNT(DISTINCT pt.turma_id_turma) FROM professor_tem_turma pt WHERE pt.professor_id_professor = p.id_professor) as total_turmas
                   FROM professor p
                   JOIN usuario u ON p.usuario_id_usuario = u.id_usuario
                   WHERE p.curso_id_curso = ? 
                   AND u.status = ?
                   ORDER BY u.nome";

$stmt = $conn->prepare($sql_professores);
$status_filter = ($filtro_status == 'ativos') ? 'ativo' : 'inativo';
$stmt->bind_param("is", $id_curso, $status_filter);
$stmt->execute();
$professores = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Obter estatísticas de desempenho dos professores
$professores_com_desempenho = [];
foreach ($professores as $professor) {
    $id_professor = $professor['id_professor'];
    
    // Média das notas das disciplinas do professor
    $sql_desempenho = "SELECT AVG(n.nota) as media_notas, 
                       COUNT(DISTINCT n.aluno_id_aluno) as total_alunos,
                       COUNT(n.id_nota) as total_avaliacoes,
                       d.nome as disciplina_nome
                       FROM nota n
                       JOIN professor_tem_disciplina dp ON n.disciplina_id_disciplina = dp.disciplina_id_disciplina
                       JOIN disciplina d ON n.disciplina_id_disciplina = d.id_disciplina
                       WHERE dp.professor_id_professor = ?
                       GROUP BY n.disciplina_id_disciplina";
    
    $stmt = $conn->prepare($sql_desempenho);
    $stmt->bind_param("i", $id_professor);
    $stmt->execute();
    $disciplinas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Frequência de aulas (baseada no cronograma vs. frequência registrada)
    $sql_frequencia = "SELECT 
                      COUNT(DISTINCT ca.id_cronograma_aula) as total_aulas,
                      COUNT(DISTINCT fa.id_frequencia_aluno) as aulas_registradas
                      FROM cronograma_aula ca
                      LEFT JOIN frequencia_aluno fa ON ca.id_disciplina = fa.disciplina_id_disciplina 
                      AND ca.turma_id_turma = fa.turma_id_turma
                      AND DATE(fa.data_aula) = CURRENT_DATE()
                      WHERE ca.id_professor = ?";
    
    $stmt = $conn->prepare($sql_frequencia);
    $stmt->bind_param("i", $id_professor);
    $stmt->execute();
    $frequencia = $stmt->get_result()->fetch_assoc();
    
    // Materiais de apoio recentes
    $sql_materiais = "SELECT ma.nome, ma.data_upload 
                     FROM materiais_apoio ma
                     WHERE ma.id_disciplina IN (
                         SELECT id_disciplina FROM professor_tem_disciplina WHERE professor_id_professor = ?
                     )
                     ORDER BY ma.data_upload DESC
                     LIMIT 3";
    
    $stmt = $conn->prepare($sql_materiais);
    $stmt->bind_param("i", $id_professor);
    $stmt->execute();
    $materiais = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    $professor['disciplinas'] = $disciplinas;
    $professor['frequencia'] = $frequencia;
    $professor['materiais'] = $materiais;
    
    $professores_com_desempenho[] = $professor;
}

$title = "Supervisão de Professores - " . htmlspecialchars($nome_curso);
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <?php require_once '../../includes/common/head.php'; ?>
    <style>
        .teacher-card {
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .teacher-info p {
            margin-bottom: 8px;
        }
        .teacher-info i {
            width: 20px;
            text-align: center;
            margin-right: 5px;
        }
        @media (max-width: 768px) {
            .teacher-card .row > div {
                margin-bottom: 15px;
            }
        }
    </style>
</head>

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

                                    <div class="page-body">
                                        <div class="card mb-4 card-table">
                                            <div class="card-header">
                                                <h5>Supervisão de Professores - <?= htmlspecialchars($nome_curso) ?></h5>
                                            </div>
                                            <div class="card-block">
                                                <form method="GET" action="" class="row">
                                                    <div class="col-md-3 col-6 mb-2">
                                                        <select class="form-control" name="status">
                                                            <option value="ativos" <?= $filtro_status == 'ativos' ? 'selected' : '' ?>>Professores Ativos</option>
                                                            <option value="inativos" <?= $filtro_status == 'inativos' ? 'selected' : '' ?>>Professores Inativos</option>
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

                                        <div class="row">
                                            <?php foreach ($professores_com_desempenho as $professor): ?>
                                                <div class="col-xl-6 col-md-12">
                                                    <div class="card teacher-card card-table">
                                                        <div class="card-header">
                                                            <h5><?= htmlspecialchars($professor['nome']) ?></h5>
                                                            <div class="card-header-right">
                                                                <span class="text-dark badge badge-<?= $professor['status'] == 'ativo' ? 'success' : 'danger' ?>">
                                                                    <?= ucfirst($professor['status']) ?>
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="card-block">
                                                            <div class="row">
                                                                <div class="col-md-4">
                                                                    <div class="teacher-info">
                                                                        <p><i class="feather icon-mail"></i> <?= htmlspecialchars($professor['email']) ?></p>
                                                                        <p><i class="feather icon-book"></i> <?= $professor['total_disciplinas'] ?> Disciplina(s)</p>
                                                                        <p><i class="feather icon-users"></i> <?= $professor['total_turmas'] ?> Turma(s)</p>
                                                                        
                                                                        <?php if (!empty($professor['frequencia'])): ?>
                                                                            <p><i class="feather icon-calendar"></i> 
                                                                                <?= $professor['frequencia']['aulas_registradas'] ?>/<?= $professor['frequencia']['total_aulas'] ?> aulas registradas
                                                                            </p>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                </div>
                                                                
                                                                <div class="col-md-8 ">
                                                                    <h6>Desempenho nas Disciplinas:</h6>
                                                                    <?php if (!empty($professor['disciplinas'])): ?>
                                                                        <div class="table-responsive ">
                                                                            <table class="table table-sm table-bordered">
                                                                                <thead>
                                                                                    <tr>
                                                                                        <th>Disciplina</th>
                                                                                        <th>Média</th>
                                                                                        <th>Alunos</th>
                                                                                        <th>Avaliações</th>
                                                                                    </tr>
                                                                                </thead>
                                                                                <tbody>
                                                                                    <?php foreach ($professor['disciplinas'] as $disciplina): ?>
                                                                                        <tr>
                                                                                            <td><?= htmlspecialchars($disciplina['disciplina_nome']) ?></td>
                                                                                            <td class="<?= $disciplina['media_notas'] < 10 ? 'text-danger' : 'text-success' ?>">
                                                                                                <?= round($disciplina['media_notas'], 2) ?>
                                                                                            </td>
                                                                                            <td><?= $disciplina['total_alunos'] ?></td>
                                                                                            <td><?= $disciplina['total_avaliacoes'] ?></td>
                                                                                        </tr>
                                                                                    <?php endforeach; ?>
                                                                                </tbody>
                                                                            </table>
                                                                        </div>
                                                                    <?php else: ?>
                                                                        <p class="text-muted">Nenhuma disciplina com avaliações registradas.</p>
                                                                    <?php endif; ?>
                                                                    
                                                                    <?php if (!empty($professor['materiais'])): ?>
                                                                        <h6 class="mt-3">Materiais Recentes:</h6>
                                                                        <ul class="list-group list-group-sm">
                                                                            <?php foreach ($professor['materiais'] as $material): ?>
                                                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                                                    <?= htmlspecialchars($material['nome']) ?>
                                                                                    <small class="text-muted"><?= date('d/m/Y', strtotime($material['data_upload'])) ?></small>
                                                                                </li>
                                                                            <?php endforeach; ?>
                                                                        </ul>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="card-footer text-right">
                                                            <a href="professor_detalhes.php?id=<?= $professor['id_professor'] ?>" class="btn btn-primary btn-sm">
                                                                <i class="feather icon-eye"></i> Detalhes Completos
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                            
                                            <?php if (empty($professores_com_desempenho)): ?>
                                                <div class="col-12">
                                                    <div class="alert alert-info">
                                                        Nenhum professor <?= $filtro_status == 'ativos' ? 'ativo' : 'inativo' ?> encontrado.
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

    <?php require_once '../../includes/common/js_imports.php'; ?>
    
</body>
</html>