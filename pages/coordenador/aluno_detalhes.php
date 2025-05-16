<?php
require_once '../../includes/common/permissoes.php';
verificarPermissao(['coordenador']);
require_once '../../process/verificar_sessao.php';
require_once '../../database/conexao.php';

// Verificar se o ID do aluno foi fornecido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: desempenho.php");
    exit();
}

$id_aluno = $_GET['id'];
$id_disciplina_filtro = isset($_GET['disciplina']) ? $_GET['disciplina'] : '';

// Obter informações do coordenador
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

// Obter informações básicas do aluno
$sql_aluno = "SELECT 
                a.*, 
                u.nome, 
                u.email, 
                u.bi_numero, 
                u.foto_perfil, 
                t.nome as nome_turma,
                t.classe,
                t.turno
              FROM aluno a
              JOIN usuario u ON a.usuario_id_usuario = u.id_usuario
              JOIN turma t ON a.turma_id_turma = t.id_turma
              WHERE a.id_aluno = ? AND t.curso_id_curso = ?";
$stmt = $conn->prepare($sql_aluno);
$stmt->bind_param("ii", $id_aluno, $id_curso);
$stmt->execute();
$result = $stmt->get_result();
$aluno = $result->fetch_assoc();

if (!$aluno) {
    die("Aluno não encontrado ou não pertence ao seu curso.");
}

// Obter disciplinas do aluno (do curso do coordenador)
$sql_disciplinas = "SELECT d.id_disciplina, d.nome 
                    FROM disciplina d
                    WHERE d.curso_id_curso = ?
                    ORDER BY d.nome";
$stmt = $conn->prepare($sql_disciplinas);
$stmt->bind_param("i", $id_curso);
$stmt->execute();
$disciplinas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Obter notas do aluno
$sql_notas = "SELECT 
                n.*, 
                d.nome as nome_disciplina,
                d.id_disciplina
              FROM nota n
              JOIN disciplina d ON n.disciplina_id_disciplina = d.id_disciplina
              WHERE n.aluno_id_aluno = ?
              AND d.curso_id_curso = ?";

if (!empty($id_disciplina_filtro)) {
    $sql_notas .= " AND n.disciplina_id_disciplina = ?";
    $stmt = $conn->prepare($sql_notas);
    $stmt->bind_param("ii", $id_aluno, $id_curso, $id_disciplina_filtro);
} else {
    $stmt = $conn->prepare($sql_notas);
    $stmt->bind_param("ii", $id_aluno, $id_curso);
}

$stmt->execute();
$notas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Calcular médias por disciplina
$medias_disciplinas = [];
foreach ($notas as $nota) {
    $id_disc = $nota['disciplina_id_disciplina'];
    if (!isset($medias_disciplinas[$id_disc])) {
        $medias_disciplinas[$id_disc] = [
            'nome' => $nota['nome_disciplina'],
            'soma' => 0,
            'contagem' => 0,
            'notas' => []
        ];
    }
    $medias_disciplinas[$id_disc]['soma'] += $nota['nota'];
    $medias_disciplinas[$id_disc]['contagem']++;
    $medias_disciplinas[$id_disc]['notas'][] = $nota;
}

// Calcular média geral
$media_geral = 0;
$total_notas = 0;
foreach ($medias_disciplinas as $id_disc => $dados) {
    $media_geral += $dados['soma'];
    $total_notas += $dados['contagem'];
}
$media_geral = $total_notas > 0 ? $media_geral / $total_notas : 0;

// Obter frequência do aluno
$sql_frequencia = "SELECT 
                    f.*, 
                    d.nome as nome_disciplina
                  FROM frequencia_aluno f
                  JOIN disciplina d ON f.disciplina_id_disciplina = d.id_disciplina
                  WHERE f.aluno_id_aluno = ?
                  AND d.curso_id_curso = ?
                  ORDER BY f.data_aula DESC";
$stmt = $conn->prepare($sql_frequencia);
$stmt->bind_param("ii", $id_aluno, $id_curso);
$stmt->execute();
$frequencias = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Calcular estatísticas de frequência
$total_presencas = 0;
$total_ausencias = 0;
$total_justificadas = 0;

foreach ($frequencias as $freq) {
    if ($freq['presenca'] == 'presente') {
        $total_presencas++;
    } elseif ($freq['presenca'] == 'ausente') {
        $total_ausencias++;
    } else {
        $total_justificadas++;
    }
}

$total_aulas = $total_presencas + $total_ausencias + $total_justificadas;
$percentual_presenca = $total_aulas > 0 ? round(($total_presencas / $total_aulas) * 100, 2) : 0;

$title = "Detalhes do Aluno - " . htmlspecialchars($aluno['nome']);
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
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="card card-table">
                                                    <div class="card-header">
                                                        <h5>Informações do Aluno</h5>
                                                        <div class="card-header-right">
                                                            <a href="desempenho.php" class="btn btn-sm btn-primary">
                                                                <i class="feather icon-arrow-left"></i> Voltar
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="card-block text-dark">
                                                        <div class="row">
                                                            <div class="col-md-3 text-center">
                                                                <?php if (!empty($aluno['foto_perfil'])): ?>
                                                                    <img src="<?= htmlspecialchars($aluno['foto_perfil']) ?>" class="img-fluid rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;" alt="Foto do Aluno">
                                                                <?php else: ?>
                                                                    <div class="avatar-default rounded-circle mb-3" style="width: 150px; height: 150px; display: flex; align-items: center; justify-content: center; background-color: #f1f1f1;">
                                                                        <i class="feather icon-user" style="font-size: 60px; color: #666;"></i>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="col-md-9">
                                                                <h3><?= htmlspecialchars($aluno['nome']) ?></h3>
                                                                <div class="row mt-3">
                                                                    <div class="col-md-6">
                                                                        <p><strong>BI/Nº:</strong> <?= htmlspecialchars($aluno['bi_numero']) ?></p>
                                                                        <p><strong>Email:</strong> <?= htmlspecialchars($aluno['email']) ?></p>
                                                                        <p><strong>Data Nascimento:</strong> <?= date('d/m/Y', strtotime($aluno['data_nascimento'])) ?></p>
                                                                        <p><strong>Gênero:</strong> <?= htmlspecialchars($aluno['genero']) ?></p>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <p><strong>Turma:</strong> <?= htmlspecialchars($aluno['nome_turma']) ?></p>
                                                                        <p><strong>Classe:</strong> <?= htmlspecialchars($aluno['classe']) ?></p>
                                                                        <p><strong>Turno:</strong> <?= htmlspecialchars($aluno['turno']) ?></p>
                                                                        <p><strong>Ano Letivo:</strong> <?= htmlspecialchars($aluno['ano_letivo']) ?></p>
                                                                    </div>
                                                                </div>
                                                                <div class="row mt-2">
                                                                    <div class="col-md-12">
                                                                        <p><strong>Enc. Educação:</strong> <?= htmlspecialchars($aluno['nome_encarregado']) ?> (<?= htmlspecialchars($aluno['contacto_encarregado']) ?>)</p>
                                                                        <p><strong>Naturalidade:</strong> <?= htmlspecialchars($aluno['naturalidade']) ?>, <?= htmlspecialchars($aluno['municipio']) ?></p>
                                                                        <p><strong>Nacionalidade:</strong> <?= htmlspecialchars($aluno['nacionalidade']) ?></p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mt-4">
                                            <div class="col-md-4">
                                                <div class="card bg-c-blue text-white">
                                                    <div class="card-block">
                                                        <div class="row align-items-center">
                                                            <div class="col-8">
                                                                <h4 class="text-white"><?= round($media_geral, 2) ?></h4>
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
                                                <div class="card bg-c-green text-white">
                                                    <div class="card-block">
                                                        <div class="row align-items-center">
                                                            <div class="col-8">
                                                                <h4 class="text-white"><?= count($notas) ?></h4>
                                                                <h6 class="text-white m-b-0">Avaliações</h6>
                                                            </div>
                                                            <div class="col-4 text-right">
                                                                <i class="feather icon-clipboard" style="font-size: 40px;"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="card bg-c-yellow text-white">
                                                    <div class="card-block">
                                                        <div class="row align-items-center">
                                                            <div class="col-8">
                                                                <h4 class="text-white"><?= $percentual_presenca ?>%</h4>
                                                                <h6 class="text-white m-b-0">Presença</h6>
                                                            </div>
                                                            <div class="col-4 text-right">
                                                                <i class="feather icon-check-circle" style="font-size: 40px;"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mt-4">
                                            <div class="col-md-12">
                                                <div class="card card-table">
                                                    <div class="card-header">
                                                        <h5>Filtrar Dados</h5>
                                                    </div>
                                                    <div class="card-block">
                                                        <form method="GET" action="">
                                                            <input type="hidden" name="id" value="<?= $id_aluno ?>">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label for="disciplina">Filtrar por Disciplina</label>
                                                                        <select class="form-control" id="disciplina" name="disciplina">
                                                                            <option value="">Todas as Disciplinas</option>
                                                                            <?php foreach ($disciplinas as $disciplina): ?>
                                                                                <option value="<?= $disciplina['id_disciplina'] ?>" <?= ($id_disciplina_filtro == $disciplina['id_disciplina']) ? 'selected' : '' ?>>
                                                                                    <?= htmlspecialchars($disciplina['nome']) ?>
                                                                                </option>
                                                                            <?php endforeach; ?>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6" style="display: flex; align-items: flex-end;">
                                                                    <button type="submit" class="btn btn-primary">
                                                                        <i class="feather icon-filter"></i> Aplicar Filtro
                                                                    </button>
                                                                    <?php if (!empty($id_disciplina_filtro)): ?>
                                                                        <a href="aluno_detalhes.php?id=<?= $id_aluno ?>" class="btn btn-secondary ml-2">
                                                                            <i class="feather icon-refresh-ccw"></i> Limpar Filtro
                                                                        </a>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mt-4">
                                            <div class="col-md-6">
                                                <div class="card card-table">
                                                    <div class="card-header">
                                                        <h5>Desempenho por Disciplina</h5>
                                                    </div>
                                                    <div class="card-block">
                                                        <div class="table-responsive">
                                                            <table class="table table-bordered">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Disciplina</th>
                                                                        <th>Média</th>
                                                                        <th>Avaliações</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php foreach ($medias_disciplinas as $id_disc => $dados): 
                                                                        $media_disciplina = $dados['contagem'] > 0 ? $dados['soma'] / $dados['contagem'] : 0;
                                                                        $classe = ($media_disciplina < 10) ? 'text-danger' : 'text-success';
                                                                    ?>
                                                                        <tr>
                                                                            <td><?= htmlspecialchars($dados['nome']) ?></td>
                                                                            <td class="<?= $classe ?>"><strong><?= round($media_disciplina, 2) ?></strong></td>
                                                                            <td><?= $dados['contagem'] ?></td>
                                                                        </tr>
                                                                    <?php endforeach; ?>
                                                                    <?php if (empty($medias_disciplinas)): ?>
                                                                        <tr>
                                                                            <td colspan="3" class="text-center">Nenhuma nota registrada para este aluno.</td>
                                                                        </tr>
                                                                    <?php endif; ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="card">
                                                    <div class="card-header">
                                                        <h5>Gráfico de Desempenho</h5>
                                                    </div>
                                                    <div class="card-block">
                                                        <canvas id="graficoDesempenho" height="250"></canvas>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mt-4">
                                            <div class="col-md-12">
                                                <div class="card card-table">
                                                    <div class="card-header">
                                                        <h5>Detalhes das Notas</h5>
                                                    </div>
                                                    <div class="card-block">
                                                        <div class="table-responsive">
                                                            <table class="table table-bordered table-striped">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Disciplina</th>
                                                                        <th>Nota</th>
                                                                        <th>Tipo</th>
                                                                        <th>Data</th>
                                                                        <th>Trimestre</th>
                                                                        <th>Peso</th>
                                                                        <th>Descrição</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php foreach ($notas as $nota): 
                                                                        $classe = ($nota['nota'] < 10) ? 'text-danger' : 'text-success';
                                                                    ?>
                                                                        <tr>
                                                                            <td><?= htmlspecialchars($nota['nome_disciplina']) ?></td>
                                                                            <td class="<?= $classe ?>"><strong><?= $nota['nota'] ?></strong></td>
                                                                            <td><?= ucfirst(str_replace('_', ' ', $nota['tipo_avaliacao'])) ?></td>
                                                                            <td><?= date('d/m/Y', strtotime($nota['data'])) ?></td>
                                                                            <td><?= $nota['trimestre'] ?? '-' ?></td>
                                                                            <td><?= $nota['peso'] ?></td>
                                                                            <td><?= htmlspecialchars($nota['descricao'] ?? '-') ?></td>
                                                                        </tr>
                                                                    <?php endforeach; ?>
                                                                    <?php if (empty($notas)): ?>
                                                                        <tr>
                                                                            <td colspan="7" class="text-center">Nenhuma nota registrada para este aluno.</td>
                                                                        </tr>
                                                                    <?php endif; ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mt-4">
                                            <div class="col-md-12">
                                                <div class="card card-table">
                                                    <div class="card-header mb-3">
                                                        <h5>Frequência</h5>
                                                    </div>
                                                    <div class="card-block">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="card bg-c-green text-white">
                                                                    <div class="card-block">
                                                                        <div class="row align-items-center">
                                                                            <div class="col-8">
                                                                                <h4 class="text-white"><?= $total_presencas ?></h4>
                                                                                <h6 class="text-white m-b-0">Presenças</h6>
                                                                            </div>
                                                                            <div class="col-4 text-right">
                                                                                <i class="feather icon-check" style="font-size: 40px;"></i>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="card bg-c-pink text-white">
                                                                    <div class="card-block">
                                                                        <div class="row align-items-center">
                                                                            <div class="col-8">
                                                                                <h4 class="text-white"><?= $total_ausencias ?></h4>
                                                                                <h6 class="text-white m-b-0">Ausências</h6>
                                                                            </div>
                                                                            <div class="col-4 text-right">
                                                                                <i class="feather icon-x" style="font-size: 40px;"></i>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="table-responsive mt-4">
                                                            <table class="table table-bordered table-striped">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Data</th>
                                                                        <th>Disciplina</th>
                                                                        <th>Status</th>
                                                                        <th>Tipo Aula</th>
                                                                        <th>Observação</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php foreach ($frequencias as $freq): 
                                                                        $classe = '';
                                                                        if ($freq['presenca'] == 'presente') {
                                                                            $classe = 'bg-success text-white';
                                                                        } elseif ($freq['presenca'] == 'ausente') {
                                                                            $classe = 'bg-danger text-white';
                                                                        } else {
                                                                            $classe = 'bg-warning text-dark';
                                                                        }
                                                                    ?>
                                                                        <tr class="<?= $classe ?>">
                                                                            <td><?= date('d/m/Y', strtotime($freq['data_aula'])) ?></td>
                                                                            <td><?= htmlspecialchars($freq['nome_disciplina']) ?></td>
                                                                            <td><?= ucfirst($freq['presenca']) ?></td>
                                                                            <td><?= ucfirst(str_replace('_', ' ', $freq['tipo_aula'])) ?></td>
                                                                            <td><?= htmlspecialchars($freq['observacao'] ?? '-') ?></td>
                                                                        </tr>
                                                                    <?php endforeach; ?>
                                                                    <?php if (empty($frequencias)): ?>
                                                                        <tr>
                                                                            <td colspan="5" class="text-center">Nenhum registro de frequência encontrado.</td>
                                                                        </tr>
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
    
    <script>
        // Gráfico de desempenho
        document.addEventListener('DOMContentLoaded', function() {
            var ctx = document.getElementById('graficoDesempenho').getContext('2d');
            
            // Preparar dados para o gráfico
            var disciplinas = [];
            var medias = [];
            var cores = [];
            
            <?php 
            foreach ($medias_disciplinas as $id_disc => $dados) {
                $media = $dados['contagem'] > 0 ? $dados['soma'] / $dados['contagem'] : 0;
                $cor = ($media < 10) ? '#ff6384' : '#36a2eb';
                
                echo "disciplinas.push('" . addslashes($dados['nome']) . "');";
                echo "medias.push(" . round($media, 2) . ");";
                echo "cores.push('$cor');";
            }
            ?>
            
            var chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: disciplinas,
                    datasets: [{
                        label: 'Média por Disciplina',
                        data: medias,
                        backgroundColor: cores,
                        borderColor: cores.map(cor => cor.replace('0.7', '1')),
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
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.parsed.y.toFixed(2);
                                }
                            }
                        },
                        legend: {
                            display: false
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>