<?php
require_once '../../includes/common/permissoes.php';
verificarPermissao(['coordenador']);
require_once '../../process/verificar_sessao.php';
require_once '../../database/conexao.php';

// Verificar ID do professor
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: supervisao_professores.php");
    exit();
}

$id_professor = $_GET['id'];

// Verificar se o professor pertence ao curso do coordenador
$id_usuario = $_SESSION['id_usuario'];
$sql = "SELECT p.id_professor, u.nome, u.email, u.status, u.foto_perfil, 
       c.nome as nome_curso, p.curso_id_curso, u.bi_numero
       FROM professor p
       JOIN usuario u ON p.usuario_id_usuario = u.id_usuario
       JOIN curso c ON p.curso_id_curso = c.id_curso
       JOIN coordenador co ON co.curso_id_curso = c.id_curso
       WHERE p.id_professor = ? AND co.usuario_id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id_professor, $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
$professor = $result->fetch_assoc();

if (!$professor) {
    die("Professor não encontrado ou você não tem permissão para acessar.");
}

// Obter disciplinas ministradas (atualizada para usar professor_tem_disciplina)
$sql_disciplinas = "SELECT d.id_disciplina, d.nome, d.classe,
                   COUNT(DISTINCT n.id_nota) as total_avaliacoes,
                   AVG(n.nota) as media_notas
                   FROM disciplina d
                   JOIN professor_tem_disciplina ptd ON d.id_disciplina = ptd.disciplina_id_disciplina
                   LEFT JOIN nota n ON d.id_disciplina = n.disciplina_id_disciplina
                   WHERE ptd.professor_id_professor = ?
                   GROUP BY d.id_disciplina";
$stmt = $conn->prepare($sql_disciplinas);
$stmt->bind_param("i", $id_professor);
$stmt->execute();
$disciplinas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Obter turmas associadas
$sql_turmas = "SELECT t.id_turma, t.nome, t.classe
              FROM professor_tem_turma pt
              JOIN turma t ON pt.turma_id_turma = t.id_turma
              WHERE pt.professor_id_professor = ?";
$stmt = $conn->prepare($sql_turmas);
$stmt->bind_param("i", $id_professor);
$stmt->execute();
$turmas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Obter histórico de frequência (atualizada para usar professor_tem_disciplina)
$sql_frequencia = "SELECT 
                  DATE(fa.data_aula) as data,
                  COUNT(DISTINCT fa.id_frequencia_aluno) as aulas_registradas,
                  d.nome as disciplina,
                  t.nome as turma,
                  d.classe
                  FROM frequencia_aluno fa
                  JOIN disciplina d ON fa.disciplina_id_disciplina = d.id_disciplina
                  JOIN professor_tem_disciplina ptd ON d.id_disciplina = ptd.disciplina_id_disciplina
                  JOIN turma t ON fa.turma_id_turma = t.id_turma
                  WHERE ptd.professor_id_professor = ?
                  GROUP BY DATE(fa.data_aula), fa.disciplina_id_disciplina, fa.turma_id_turma
                  ORDER BY DATE(fa.data_aula) DESC
                  LIMIT 10";
$stmt = $conn->prepare($sql_frequencia);
$stmt->bind_param("i", $id_professor);
$stmt->execute();
$frequencia = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Obter materiais de apoio (atualizada para usar professor_tem_disciplina)
$sql_materiais = "SELECT ma.*, d.nome as disciplina, d.classe
                 FROM materiais_apoio ma
                 JOIN disciplina d ON ma.id_disciplina = d.id_disciplina
                 JOIN professor_tem_disciplina ptd ON d.id_disciplina = ptd.disciplina_id_disciplina
                 WHERE ptd.professor_id_professor = ?
                 ORDER BY ma.data_upload DESC
                 LIMIT 5";
$stmt = $conn->prepare($sql_materiais);
$stmt->bind_param("i", $id_professor);
$stmt->execute();
$materiais = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Obter cronograma de aulas
$sql_cronograma = "SELECT ca.*, d.nome as disciplina_nome, t.nome as turma_nome
                  FROM cronograma_aula ca
                  JOIN disciplina d ON ca.id_disciplina = d.id_disciplina
                  JOIN turma t ON ca.turma_id_turma = t.id_turma
                  JOIN professor_tem_disciplina ptd ON d.id_disciplina = ptd.disciplina_id_disciplina
                  WHERE ptd.professor_id_professor = ?
                  ORDER BY FIELD(ca.dia_semana, 'segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado'), 
                  ca.horario_inicio";
$stmt = $conn->prepare($sql_cronograma);
$stmt->bind_param("i", $id_professor);
$stmt->execute();
$cronograma = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$title = "Detalhes do Professor - " . htmlspecialchars($professor['nome']);
?>

<!DOCTYPE html>
<html lang="pt">
    
    <!-- Incluir head comum -->
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

                    <div class="page-body">
                        <div class="profile-header">
                            <div class="row align-items-center">
                                <div class="col-md-2 text-center">
                                    <img src="<?= $professor['foto_perfil'] ? '../../uploads/' . htmlspecialchars($professor['foto_perfil']) : '../../assets/images/default-profile.png' ?>" 
                                         alt="Foto do Professor" class="profile-img rounded-circle">
                                </div>
                                <div class="col-md-6">
                                    <h3><?= htmlspecialchars($professor['nome']) ?></h3>
                                    <p class="mb-1"><i class="feather icon-mail"></i> <?= htmlspecialchars($professor['email']) ?></p>
                                    <p class="mb-1"><i class="feather icon-book"></i> <?= htmlspecialchars($professor['nome_curso']) ?></p>
                                    <p class="mb-1"><i class="feather icon-credit-card"></i> BI: <?= htmlspecialchars($professor['bi_numero']) ?></p>
                                    <span class="badge badge-<?= $professor['status'] == 'ativo' ? 'success' : 'danger' ?> badge-status">
                                        <?= ucfirst($professor['status']) ?>
                                    </span>
                                </div>
                                <div class="col-md-4 text-right">
                                    <a href="professores.php" class="btn btn-secondary">
                                        <i class="feather icon-arrow-left"></i> Voltar
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-4">
                                <div class="card bg-c-pink">
                                    <div class="card-header">
                                        <h5>Estatísticas</h5>
                                    </div>
                                    <div class="card-block">
                                        <div class="row">
                                            <div class="col-6 stat-card">
                                                <h6>Disciplinas</h6>
                                                <h3><?= count($disciplinas) ?></h3>
                                            </div>
                                            <div class="col-6 stat-card">
                                                <h6>Turmas</h6>
                                                <h3><?= count($turmas) ?></h3>
                                            </div>
                                            <div class="col-6 stat-card">
                                                <h6>Média Geral</h6>
                                                <h3>
                                                    <?php
                                                        $total = 0;
                                                        $count = 0;
                                                        foreach ($disciplinas as $d) {
                                                            if ($d['media_notas']) {
                                                                $total += $d['media_notas'];
                                                                $count++;
                                                            }
                                                        }
                                                        echo $count > 0 ? round($total/$count, 2) : 'N/A';
                                                    ?>
                                                </h3>
                                            </div>
                                            <div class="col-6 stat-card">
                                                <h6>Horário</h6>
                                                <h3><?= count($cronograma) ?> aulas</h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card mt-4 card-table">
                                    <div class="card-header">
                                        <h5>Turmas Atribuídas</h5>
                                    </div>
                                    <div class="card-block">
                                        <?php if (!empty($turmas)): ?>
                                            <div class="table-responsive">
                                                <table class="table table-hover">
                                                    <tbody>
                                                        <?php foreach ($turmas as $turma): ?>
                                                            <tr>
                                                                <td>
                                                                    <strong><?= htmlspecialchars($turma['nome']) ?></strong><br>
                                                                    <small>Classe: <?= htmlspecialchars($turma['classe']) ?></small>
                                                                </td>
                                                                <td class="text-right">
                                                                    <a href="turma_detalhes.php?id=<?= $turma['id_turma'] ?>" class="btn btn-sm btn-primary">
                                                                        <i class="feather icon-eye"></i>
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php else: ?>
                                            <p class="text-muted">Nenhuma turma atribuída.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="card mt-4 card-table">
                                    <div class="card-header">
                                        <h5>Horário Semanal</h5>
                                    </div>
                                    <div class="card-block">
                                        <?php if (!empty($cronograma)): ?>
                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>Dia</th>
                                                            <th>Horário</th>
                                                            <th>Disciplina</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($cronograma as $aula): ?>
                                                            <tr>
                                                                <td><?= ucfirst(htmlspecialchars($aula['dia_semana'])) ?></td>
                                                                <td><?= date('H:i', strtotime($aula['horario_inicio'])) ?>-<?= date('H:i', strtotime($aula['horario_fim'])) ?></td>
                                                                <td><?= htmlspecialchars($aula['disciplina_nome']) ?></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php else: ?>
                                            <p class="text-muted">Horário não definido.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-8">
                                <div class="card card-table">
                                    <div class="card-header">
                                        <h5>Disciplinas Ministradas</h5>
                                    </div>
                                    <div class="card-block">
                                        <?php if (!empty($disciplinas)): ?>
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th>Disciplina</th>
                                                            <th>Classe</th>
                                                            <th>Média</th>
                                                            <th>Avaliações</th>
                                                            <th>Detalhes</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($disciplinas as $disciplina): ?>
                                                            <tr>
                                                                <td><?= htmlspecialchars($disciplina['nome']) ?></td>
                                                                <td><?= htmlspecialchars($disciplina['classe']) ?></td>
                                                                <td class="<?= $disciplina['media_notas'] && $disciplina['media_notas'] < 10 ? 'text-danger' : 'text-success' ?>">
                                                                    <?= $disciplina['media_notas'] ? round($disciplina['media_notas'], 2) : 'N/A' ?>
                                                                </td>
                                                                <td><?= $disciplina['total_avaliacoes'] ?></td>
                                                                <td>
                                                                    <a href="disciplina_detalhes.php?id=<?= $disciplina['id_disciplina'] ?>" 
                                                                       class="btn btn-sm btn-primary">
                                                                        <i class="feather icon-bar-chart-2"></i>
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php else: ?>
                                            <p class="text-dark">Nenhuma disciplina atribuída.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-md-6">
                                        <div class="card card-table">
                                            <div class="card-header">
                                                <h5>Últimas Aulas Registradas</h5>
                                            </div>
                                            <div class="card-block">
                                                <?php if (!empty($frequencia)): ?>
                                                    <ul class="list-group">
                                                        <?php foreach ($frequencia as $aula): ?>
                                                            <li class="list-group-item">
                                                                <div class="d-flex justify-content-between">
                                                                    <div>
                                                                        <strong><?= htmlspecialchars($aula['disciplina']) ?></strong><br>
                                                                        <small>Turma: <?= htmlspecialchars($aula['turma']) ?> (<?= htmlspecialchars($aula['classe']) ?>)</small>
                                                                    </div>
                                                                    <div class="text-right">
                                                                        <?= date('d/m/Y', strtotime($aula['data'])) ?><br>
                                                                        <span class="badge badge-primary"><?= $aula['aulas_registradas'] ?> reg.</span>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                <?php else: ?>
                                                    <p class="text-muted">Nenhuma aula registrada recentemente.</p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="card card-table">
                                            <div class="card-header">
                                                <h5>Materiais Recentes</h5>
                                            </div>
                                            <div class="card-block">
                                                <?php if (!empty($materiais)): ?>
                                                    <ul class="list-group">
                                                        <?php foreach ($materiais as $material): ?>
                                                            <li class="list-group-item">
                                                                <div class="d-flex justify-content-between align-items-center">
                                                                    <div>
                                                                        <strong><?= htmlspecialchars($material['nome']) ?></strong><br>
                                                                        <small><?= htmlspecialchars($material['disciplina']) ?> (<?= htmlspecialchars($material['classe']) ?>)</small>
                                                                    </div>
                                                                    <div class="text-right">
                                                                        <small><?= date('d/m/Y', strtotime($material['data_upload'])) ?></small><br>
                                                                        <a href="../../uploads/<?= htmlspecialchars($material['caminho_arquivo']) ?>" 
                                                                           class="btn btn-sm btn-primary" download>
                                                                            <i class="feather icon-download"></i>
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                <?php else: ?>
                                                    <p class="text-muted">Nenhum material enviado recentemente.</p>
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