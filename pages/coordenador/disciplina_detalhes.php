<?php
require_once '../../includes/common/permissoes.php';
verificarPermissao(['coordenador']);
require_once '../../process/verificar_sessao.php';
require_once '../../database/conexao.php';

// Verificar se o ID da disciplina foi fornecido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: disciplinas.php");
    exit();
}

$id_disciplina = $_GET['id'];

// Obter informações do coordenador e curso
$id_usuario = $_SESSION['id_usuario'];
$sql_coordenador = "SELECT c.curso_id_curso 
                   FROM coordenador c
                   WHERE c.usuario_id_usuario = ?";
$stmt = $conn->prepare($sql_coordenador);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
$coordenador = $result->fetch_assoc();

if (!$coordenador) {
    die("Acesso negado ou coordenador não encontrado.");
}

// Obter informações da disciplina
$sql_disciplina = "SELECT d.*, c.nome as nome_curso, 
                  u.nome as nome_professor, u.id_usuario as id_professor
                  FROM disciplina d
                  JOIN curso c ON d.curso_id_curso = c.id_curso
                  LEFT JOIN professor p ON d.professor_id_professor = p.id_professor
                  LEFT JOIN usuario u ON p.usuario_id_usuario = u.id_usuario
                  WHERE d.id_disciplina = ? AND d.curso_id_curso = ?";
$stmt = $conn->prepare($sql_disciplina);
$stmt->bind_param("ii", $id_disciplina, $coordenador['curso_id_curso']);
$stmt->execute();
$result = $stmt->get_result();
$disciplina = $result->fetch_assoc();

if (!$disciplina) {
    die("Disciplina não encontrada ou você não tem permissão para acessá-la.");
}

// Obter turmas que têm esta disciplina
$sql_turmas = "SELECT DISTINCT t.id_turma, t.nome
              FROM cronograma_aula ca
              JOIN turma t ON ca.turma_id_turma = t.id_turma
              WHERE ca.id_disciplina = ?";
$stmt = $conn->prepare($sql_turmas);
$stmt->bind_param("i", $id_disciplina);
$stmt->execute();
$turmas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Obter materiais de apoio
$sql_materiais = "SELECT * FROM materiais_apoio
                 WHERE id_disciplina = ?
                 ORDER BY id_material DESC";
$stmt = $conn->prepare($sql_materiais);
$stmt->bind_param("i", $id_disciplina);
$stmt->execute();
$materiais = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Obter estatísticas de notas
$sql_notas = "SELECT 
             COUNT(n.id_nota) as total_avaliacoes,
             AVG(n.nota) as media_geral,
             MIN(n.nota) as nota_minima,
             MAX(n.nota) as nota_maxima,
             COUNT(DISTINCT n.aluno_id_aluno) as total_alunos
             FROM nota n
             WHERE n.disciplina_id_disciplina = ?";
$stmt = $conn->prepare($sql_notas);
$stmt->bind_param("i", $id_disciplina);
$stmt->execute();
$estatisticas = $stmt->get_result()->fetch_assoc();

// Obter últimos lançamentos de notas
$sql_ultimas_notas = "SELECT n.*, u.nome as nome_aluno, t.nome as nome_turma
                     FROM nota n
                     JOIN aluno a ON n.aluno_id_aluno = a.id_aluno
                     JOIN usuario u ON a.usuario_id_usuario = u.id_usuario
                     JOIN turma t ON a.turma_id_turma = t.id_turma
                     WHERE n.disciplina_id_disciplina = ?
                     ORDER BY n.data DESC
                     LIMIT 5";
$stmt = $conn->prepare($sql_ultimas_notas);
$stmt->bind_param("i", $id_disciplina);
$stmt->execute();
$ultimas_notas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$title = "Detalhes da Disciplina - " . htmlspecialchars($disciplina['nome']);
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

                                    <div class="page-body">
                                        <div class="card mb-4 card-table">
                                            <div class="card-header">
                                                <h5>Informações da Disciplina</h5>
                                            </div>
                                            <div class="card-block text-dark">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <p><strong>Nome:</strong> <?= htmlspecialchars($disciplina['nome']) ?></p>
                                                        <p><strong>Curso:</strong> <?= htmlspecialchars($disciplina['nome_curso']) ?></p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <p><strong>Professor:</strong> 
                                                            <?= $disciplina['nome_professor'] ? 
                                                                '<a href="professor_detalhes.php?id='.$disciplina['id_professor'].'">'.htmlspecialchars($disciplina['nome_professor']).'</a>' : 
                                                                'Nenhum professor atribuído' ?>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="card bg-c-blue text-white mb-4">
                                                    <div class="card-block">
                                                        <div class="row align-items-center">
                                                            <div class="col-8">
                                                                <h4 class="text-white"><?= $estatisticas['total_alunos'] ?? 0 ?></h4>
                                                                <h6 class="text-white m-b-0">Alunos</h6>
                                                            </div>
                                                            <div class="col-4 text-right">
                                                                <i class="feather icon-users" style="font-size: 40px;"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="card bg-c-green text-white mb-4">
                                                    <div class="card-block">
                                                        <div class="row align-items-center">
                                                            <div class="col-8">
                                                                <h4 class="text-white"><?= round($estatisticas['media_geral'] ?? 0, 2) ?></h4>
                                                                <h6 class="text-white m-b-0">Média Geral</h6>
                                                            </div>
                                                            <div class="col-4 text-right">
                                                                <i class="feather icon-bar-chart-2" style="font-size: 40px;"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="card bg-c-yellow text-white mb-4">
                                                    <div class="card-block">
                                                        <div class="row align-items-center">
                                                            <div class="col-8">
                                                                <h4 class="text-white"><?= $estatisticas['total_avaliacoes'] ?? 0 ?></h4>
                                                                <h6 class="text-white m-b-0">Avaliações</h6>
                                                            </div>
                                                            <div class="col-4 text-right">
                                                                <i class="feather icon-clipboard" style="font-size: 40px;"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="card mb-4 card-table">
                                                    <div class="card-header">
                                                        <h5>Turmas com esta Disciplina</h5>
                                                    </div>
                                                    <div class="card-block">
                                                        <?php if (!empty($turmas)): ?>
                                                            <ul class="list-group">
                                                                <?php foreach ($turmas as $turma): ?>
                                                                    <li class="list-group-item d-flex justify-content-between align-items-center bg-success">
                                                                        <?= htmlspecialchars($turma['nome']) ?>
                                                                        <a href="turma_detalhes.php?id=<?= $turma['id_turma'] ?>" class="btn btn-sm btn-primary">
                                                                            <i class="feather icon-eye"></i> Ver Turma
                                                                        </a>
                                                                    </li>
                                                                <?php endforeach; ?>
                                                            </ul>
                                                        <?php else: ?>
                                                            <div class="alert alert-info">
                                                                Nenhuma turma encontrada com esta disciplina.
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>

                                                <div class="card card-table">
                                                    <div class="card-header">
                                                        <h5>Últimas Avaliações</h5>
                                                    </div>
                                                    <div class="card-block">
                                                        <?php if (!empty($ultimas_notas)): ?>
                                                            <div class="table-responsive">
                                                                <table class="table table-bordered table-striped">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>Aluno</th>
                                                                            <th>Turma</th>
                                                                            <th>Nota</th>
                                                                            <th>Data</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <?php foreach ($ultimas_notas as $nota): ?>
                                                                            <tr>
                                                                                <td><?= htmlspecialchars($nota['nome_aluno']) ?></td>
                                                                                <td><?= htmlspecialchars($nota['nome_turma']) ?></td>
                                                                                <td class="<?= $nota['nota'] < 10 ? 'text-danger' : 'text-success' ?>">
                                                                                    <strong><?= number_format($nota['nota'], 2) ?></strong>
                                                                                </td>
                                                                                <td><?= date('d/m/Y', strtotime($nota['data'])) ?></td>
                                                                            </tr>
                                                                        <?php endforeach; ?>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="alert alert-info">
                                                                Nenhuma avaliação registrada ainda.
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="card mb-4 card-table">
                                                    <div class="card-header">
                                                        <h5>Materiais de Apoio</h5>
                                                    </div>
                                                    <div class="card-block">
                                                        <?php if (!empty($materiais)): ?>
                                                            <div class="table-responsive">
                                                                <table class="table table-bordered">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>Nome</th>
                                                                            <th>Descrição</th>
                                                                            <th>Ações</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <?php foreach ($materiais as $material): ?>
                                                                            <tr>
                                                                                <td><?= htmlspecialchars($material['nome']) ?></td>
                                                                                <td><?= htmlspecialchars(substr($material['descricao'], 0, 50)) ?>...</td>
                                                                                <td>
                                                                                    <a href="../../uploads/<?= htmlspecialchars($material['caminho_arquivo']) ?>" 
                                                                                       class="btn btn-sm btn-primary" download>
                                                                                        <i class="feather icon-download"></i> Baixar
                                                                                    </a>
                                                                                </td>
                                                                            </tr>
                                                                        <?php endforeach; ?>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="alert alert-info">
                                                                Nenhum material de apoio cadastrado.
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>

                                                <div class="card">
                                                    <div class="card-header">
                                                        <h5>Estatísticas Detalhadas</h5>
                                                    </div>
                                                    <div class="card-block">
                                                        <canvas id="graficoDesempenho" height="200"></canvas>
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
        // Gráfico de desempenho
        document.addEventListener('DOMContentLoaded', function() {
            var ctx = document.getElementById('graficoDesempenho').getContext('2d');
            
            var chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Média Geral', 'Nota Mínima', 'Nota Máxima'],
                    datasets: [{
                        label: 'Desempenho',
                        data: [
                            <?= round($estatisticas['media_geral'] ?? 0, 2) ?>,
                            <?= $estatisticas['nota_minima'] ?? 0 ?>,
                            <?= $estatisticas['nota_maxima'] ?? 0 ?>
                        ],
                        backgroundColor: [
                            'rgba(54, 162, 235, 0.7)',
                            'rgba(255, 99, 132, 0.7)',
                            'rgba(75, 192, 192, 0.7)'
                        ],
                        borderColor: [
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 99, 132, 1)',
                            'rgba(75, 192, 192, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 20,
                            ticks: {
                                callback: function(value) {
                                    return value.toFixed(1);
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.parsed.y.toFixed(2);
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>