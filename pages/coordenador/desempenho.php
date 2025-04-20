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
    $filtro_turma = isset($_GET['turma']) ? $_GET['turma'] : '';
    $filtro_aluno = isset($_GET['aluno']) ? $_GET['aluno'] : '';
    $filtro_disciplina = isset($_GET['disciplina']) ? $_GET['disciplina'] : '';
    $filtro_trimestre = isset($_GET['trimestre']) ? $_GET['trimestre'] : '';

    // Obter turmas do curso para filtro
    $sql_turmas = "SELECT id_turma, nome FROM turma WHERE curso_id_curso = ? ORDER BY nome";
    $stmt = $conn->prepare($sql_turmas);
    $stmt->bind_param("i", $id_curso);
    $stmt->execute();
    $turmas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Obter alunos do curso para filtro
    $sql_alunos = "SELECT a.id_aluno, u.nome 
                FROM aluno a
                JOIN usuario u ON a.usuario_id_usuario = u.id_usuario
                JOIN turma t ON a.turma_id_turma = t.id_turma
                WHERE t.curso_id_curso = ?
                ORDER BY u.nome";
    $stmt = $conn->prepare($sql_alunos);
    $stmt->bind_param("i", $id_curso);
    $stmt->execute();
    $alunos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Obter disciplinas do curso para filtro
    $sql_disciplinas = "SELECT id_disciplina, nome FROM disciplina WHERE curso_id_curso = ? ORDER BY nome";
    $stmt = $conn->prepare($sql_disciplinas);
    $stmt->bind_param("i", $id_curso);
    $stmt->execute();
    $disciplinas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Consulta principal de desempenho
    $sql_desempenho = "SELECT 
                    a.id_aluno, 
                    u.nome as nome_aluno,
                    t.nome as nome_turma,
                    d.nome as nome_disciplina,
                    AVG(n.nota) as media_geral,
                    COUNT(n.id_nota) as total_avaliacoes,
                    SUM(CASE WHEN n.nota < 10 THEN 1 ELSE 0 END) as notas_vermelhas,
                    MAX(n.data) as ultima_avaliacao
                    FROM nota n
                    JOIN aluno a ON n.aluno_id_aluno = a.id_aluno
                    JOIN usuario u ON a.usuario_id_usuario = u.id_usuario
                    JOIN turma t ON a.turma_id_turma = t.id_turma
                    JOIN disciplina d ON n.disciplina_id_disciplina = d.id_disciplina
                    WHERE t.curso_id_curso = ?";

    $params = array($id_curso);
    $types = "i";

    // Aplicar filtros
    if (!empty($filtro_turma)) {
        $sql_desempenho .= " AND a.turma_id_turma = ?";
        $params[] = $filtro_turma;
        $types .= "i";
    }

    if (!empty($filtro_aluno)) {
        $sql_desempenho .= " AND a.id_aluno = ?";
        $params[] = $filtro_aluno;
        $types .= "i";
    }

    if (!empty($filtro_disciplina)) {
        $sql_desempenho .= " AND n.disciplina_id_disciplina = ?";
        $params[] = $filtro_disciplina;
        $types .= "i";
    }

    if (!empty($filtro_trimestre)) {
        $sql_desempenho .= " AND n.trimestre = ?";
        $params[] = $filtro_trimestre;
        $types .= "i";
    }

    $sql_desempenho .= " GROUP BY a.id_aluno, n.disciplina_id_disciplina
                        ORDER BY u.nome, d.nome";

    $stmt = $conn->prepare($sql_desempenho);

    if ($types !== "i") {
        $stmt->bind_param($types, ...$params);
    } else {
        $stmt->bind_param($types, $id_curso);
    }

    $stmt->execute();
    $resultado_desempenho = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Calcular estatísticas gerais
    $sql_estatisticas = "SELECT 
                        COUNT(DISTINCT a.id_aluno) as total_alunos,
                        AVG(n.nota) as media_curso,
                        COUNT(n.id_nota) as total_notas,
                        SUM(CASE WHEN n.nota < 10 THEN 1 ELSE 0 END) as total_notas_vermelhas
                        FROM nota n
                        JOIN aluno a ON n.aluno_id_aluno = a.id_aluno
                        JOIN turma t ON a.turma_id_turma = t.id_turma
                        WHERE t.curso_id_curso = ?";

    if (!empty($filtro_turma)) {
        $sql_estatisticas .= " AND a.turma_id_turma = ?";
    }

    if (!empty($filtro_trimestre)) {
        $sql_estatisticas .= " AND n.trimestre = ?";
    }

    $stmt = $conn->prepare($sql_estatisticas);

    if (!empty($filtro_turma) && !empty($filtro_trimestre)) {
        $stmt->bind_param("iii", $id_curso, $filtro_turma, $filtro_trimestre);
    } elseif (!empty($filtro_turma)) {
        $stmt->bind_param("ii", $id_curso, $filtro_turma);
    } elseif (!empty($filtro_trimestre)) {
        $stmt->bind_param("ii", $id_curso, $filtro_trimestre);
    } else {
        $stmt->bind_param("i", $id_curso);
    }

    $stmt->execute();
    $estatisticas = $stmt->get_result()->fetch_assoc();

    $title = "Supervisão de Desempenho - " . htmlspecialchars($nome_curso);
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
                                                <h5>Supervisão de Desempenho - <?= htmlspecialchars($nome_curso) ?></h5>
                                            </div>
                                            <div class="card-block">
                                                <form method="GET" action="">
                                                    <div class="row text-dark">
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label for="turma">Filtrar por Turma</label>
                                                                <select class="form-control" id="turma" name="turma">
                                                                    <option value="">Todas as Turmas</option>
                                                                    <?php foreach ($turmas as $turma): ?>
                                                                        <option value="<?= $turma['id_turma'] ?>" <?= ($filtro_turma == $turma['id_turma']) ? 'selected' : '' ?>>
                                                                            <?= htmlspecialchars($turma['nome']) ?>
                                                                        </option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label for="aluno">Filtrar por Aluno</label>
                                                                <select class="form-control" id="aluno" name="aluno">
                                                                    <option value="">Todos os Alunos</option>
                                                                    <?php foreach ($alunos as $aluno): ?>
                                                                        <option value="<?= $aluno['id_aluno'] ?>" <?= ($filtro_aluno == $aluno['id_aluno']) ? 'selected' : '' ?>>
                                                                            <?= htmlspecialchars($aluno['nome']) ?>
                                                                        </option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label for="disciplina">Filtrar por Disciplina</label>
                                                                <select class="form-control" id="disciplina" name="disciplina">
                                                                    <option value="">Todas as Disciplinas</option>
                                                                    <?php foreach ($disciplinas as $disciplina): ?>
                                                                        <option value="<?= $disciplina['id_disciplina'] ?>" <?= ($filtro_disciplina == $disciplina['id_disciplina']) ? 'selected' : '' ?>>
                                                                            <?= htmlspecialchars($disciplina['nome']) ?>
                                                                        </option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label for="trimestre">Filtrar por Trimestre</label>
                                                                <select class="form-control" id="trimestre" name="trimestre">
                                                                    <option value="">Todos</option>
                                                                    <option value="1" <?= ($filtro_trimestre == '1') ? 'selected' : '' ?>>1º Trimestre</option>
                                                                    <option value="2" <?= ($filtro_trimestre == '2') ? 'selected' : '' ?>>2º Trimestre</option>
                                                                    <option value="3" <?= ($filtro_trimestre == '3') ? 'selected' : '' ?>>3º Trimestre</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-12 text-right">
                                                            <button type="submit" class="btn btn-primary">
                                                                <i class="feather icon-filter"></i> Aplicar Filtros
                                                            </button>
                                                            <a href="desempenho.php" class="btn btn-secondary">
                                                                <i class="feather icon-refresh-ccw"></i> Limpar Filtros
                                                            </a>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>

                                        <div class="row card-deck">
                                            <div class="col-6 col-md-3 mb-3">
                                                <div class="card bg-c-green text-white">
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
                                            <div class="col-6 col-md-3 mb-3">
                                                <div class="card bg-c-blue text-white">
                                                    <div class="card-block">
                                                        <div class="row align-items-center">
                                                            <div class="col-8">
                                                                <h4 class="text-white"><?= round($estatisticas['media_curso'] ?? 0, 2) ?></h4>
                                                                <h6 class="text-white m-b-0">Média Geral</h6>
                                                            </div>
                                                            <div class="col-4 text-right">
                                                                <i class="feather icon-bar-chart-2" style="font-size: 40px;"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6 col-md-3 mb-3">
                                                <div class="card bg-c-yellow text-white">
                                                    <div class="card-block">
                                                        <div class="row align-items-center">
                                                            <div class="col-8">
                                                                <h4 class="text-white"><?= $estatisticas['total_notas'] ?? 0 ?></h4>
                                                                <h6 class="text-white m-b-0">Avaliações</h6>
                                                            </div>
                                                            <div class="col-4 text-right">
                                                                <i class="feather icon-clipboard" style="font-size: 40px;"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6 col-md-3 mb-3">
                                                <div class="card bg-c-pink text-white">
                                                    <div class="card-block">
                                                        <div class="row align-items-center">
                                                            <div class="col-8">
                                                                <h4 class="text-white"><?= $estatisticas['total_notas_vermelhas'] ?? 0 ?></h4>
                                                                <h6 class="text-white m-b-0">Notas < 10</h6>
                                                            </div>
                                                            <div class="col-4 text-right">
                                                                <i class="feather icon-alert-triangle" style="font-size: 40px;"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card card-table">
                                            <div class="card-header">
                                                <h5>Detalhes do Desempenho</h5>
                                            </div>
                                            <div class="card-block">
                                                <div class="table-responsive">
                                                    <table class="table table-bordered table-striped">
                                                        <thead>
                                                            <tr>
                                                                <th>Aluno</th>
                                                                <th>Turma</th>
                                                                <th>Disciplina</th>
                                                                <th>Média</th>
                                                                <th>Avaliações</th>
                                                                <th>Notas < 10</th>
                                                                <th>Última Avaliação</th>
                                                                <th>Ações</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($resultado_desempenho as $desempenho): ?>
                                                                <tr>
                                                                    <td><?= htmlspecialchars($desempenho['nome_aluno']) ?></td>
                                                                    <td><?= htmlspecialchars($desempenho['nome_turma']) ?></td>
                                                                    <td><?= htmlspecialchars($desempenho['nome_disciplina']) ?></td>
                                                                    <td>
                                                                        <?php 
                                                                            $media = round($desempenho['media_geral'], 2);
                                                                            $classe = ($media < 10) ? 'text-danger' : 'text-success';
                                                                            echo "<span class='$classe'><strong>$media</strong></span>";
                                                                        ?>
                                                                    </td>
                                                                    <td><?= $desempenho['total_avaliacoes'] ?></td>
                                                                    <td><?= $desempenho['notas_vermelhas'] ?></td>
                                                                    <td><?= date('d/m/Y', strtotime($desempenho['ultima_avaliacao'])) ?></td>
                                                                    <td>
                                                                        <a href="aluno_detalhes.php?id=<?= $desempenho['id_aluno'] ?>&disciplina=<?= $filtro_disciplina ?>" 
                                                                           class="btn btn-info btn-sm" title="Ver detalhes">
                                                                            <i class="feather icon-eye"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                            <?php if (empty($resultado_desempenho)): ?>
                                                                <tr>
                                                                    <td colspan="8" class="text-center">Nenhum dado de desempenho encontrado com os filtros aplicados.</td>
                                                                </tr>
                                                            <?php endif; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card mt-4">
                                            <div class="card-header">
                                                <h5>Gráfico de Desempenho por Disciplina</h5>
                                            </div>
                                            <div class="card-block">
                                                <canvas id="graficoDesempenho" height="150"></canvas>
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
            
            // Dados para o gráfico (seriam substituídos por dados reais do PHP)
            var disciplinas = [];
            var medias = [];
            
            <?php 
            // Preparar dados para o gráfico
            if (!empty($resultado_desempenho)) {
                $dados_grafico = [];
                foreach ($resultado_desempenho as $item) {
                    if (!isset($dados_grafico[$item['nome_disciplina']])) {
                        $dados_grafico[$item['nome_disciplina']] = [
                            'soma' => 0,
                            'contagem' => 0
                        ];
                    }
                    $dados_grafico[$item['nome_disciplina']]['soma'] += $item['media_geral'];
                    $dados_grafico[$item['nome_disciplina']]['contagem']++;
                }
                
                foreach ($dados_grafico as $disciplina => $dados) {
                    echo "disciplinas.push('" . addslashes($disciplina) . "');";
                    echo "medias.push(" . round($dados['soma'] / $dados['contagem'], 2) . ");";
                }
            }
            ?>
            
            var chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: disciplinas,
                    datasets: [{
                        label: 'Média por Disciplina',
                        data: medias,
                        backgroundColor: 'rgba(54, 162, 235, 0.7)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
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
                        }
                    }
                }
            });
        });
    </script>

    <script>
        // Ajustar gráfico em telas pequenas
        function resizeChart() {
            const canvas = document.getElementById('graficoDesempenho');
            const container = canvas.parentElement;
            
            if (window.innerWidth < 768) {
                canvas.style.height = '250px';
                if(chart) {
                    chart.options.maintainAspectRatio = false;
                    chart.update();
                }
            } else {
                canvas.style.height = '150px';
                if(chart) {
                    chart.options.maintainAspectRatio = true;
                    chart.update();
                }
            }
        }

        window.addEventListener('resize', resizeChart);
        document.addEventListener('DOMContentLoaded', resizeChart);
    </script>
</body>
</html>