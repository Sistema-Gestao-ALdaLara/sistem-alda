<?php
require_once __DIR__ . '/../../includes/common/permissoes.php';
verificarPermissao(['aluno']);
require_once __DIR__ . '/../../process/verificar_sessao.php';
require_once __DIR__ . '/../../database/conexao.php';

// Verificação de sessão
if (!isset($_SESSION['id_usuario'])) {
    header("Location: /login.php");
    exit();
}

// 1. Obter informações do aluno
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
    header("Location: /aluno/dashboard.php");
    exit();
}

// 2. Obter todas as disciplinas do aluno com notas
$query_disciplinas = "SELECT d.id_disciplina, d.nome as nome_disciplina,
                             u.nome as nome_professor, u.foto_perfil
                      FROM disciplina d
                      JOIN curso c ON d.curso_id_curso = c.id_curso
                      LEFT JOIN professor p ON d.professor_id_professor = p.id_professor
                      LEFT JOIN usuario u ON p.usuario_id_usuario = u.id_usuario
                      WHERE d.curso_id_curso = ?
                      ORDER BY d.nome";
$stmt_disciplinas = $conn->prepare($query_disciplinas);
$stmt_disciplinas->bind_param("i", $aluno['curso_id_curso']);
$stmt_disciplinas->execute();
$disciplinas = $stmt_disciplinas->get_result();

// 3. Obter notas agrupadas por disciplina e trimestre
$notas_por_disciplina = [];
$query_notas = "SELECT n.*, d.nome as nome_disciplina
                FROM nota n
                JOIN disciplina d ON n.disciplina_id_disciplina = d.id_disciplina
                WHERE n.aluno_id_aluno = ?
                ORDER BY n.disciplina_id_disciplina, n.trimestre, n.data DESC";
$stmt_notas = $conn->prepare($query_notas);
$stmt_notas->bind_param("i", $aluno['id_aluno']);
$stmt_notas->execute();
$result_notas = $stmt_notas->get_result();

while ($nota = $result_notas->fetch_assoc()) {
    $id_disciplina = $nota['disciplina_id_disciplina'];
    $trimestre = $nota['trimestre'];
    
    if (!isset($notas_por_disciplina[$id_disciplina])) {
        $notas_por_disciplina[$id_disciplina] = [
            'nome' => $nota['nome_disciplina'],
            'trimestres' => []
        ];
    }
    
    if (!isset($notas_por_disciplina[$id_disciplina]['trimestres'][$trimestre])) {
        $notas_por_disciplina[$id_disciplina]['trimestres'][$trimestre] = [
            'notas' => [],
            'soma' => 0,
            'peso_total' => 0
        ];
    }
    
    $notas_por_disciplina[$id_disciplina]['trimestres'][$trimestre]['notas'][] = $nota;
    $notas_por_disciplina[$id_disciplina]['trimestres'][$trimestre]['soma'] += $nota['nota'] * $nota['peso'];
    $notas_por_disciplina[$id_disciplina]['trimestres'][$trimestre]['peso_total'] += $nota['peso'];
}

// Calcular médias ponderadas
foreach ($notas_por_disciplina as &$disciplina) {
    foreach ($disciplina['trimestres'] as &$trimestre) {
        $trimestre['media'] = $trimestre['peso_total'] > 0 
            ? $trimestre['soma'] / $trimestre['peso_total'] 
            : 0;
    }
}
unset($disciplina, $trimestre);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <?php require_once __DIR__ . '/../../includes/common/head.php'; ?>
    <title>Notas - Área do Aluno</title>
    <style>
        .card-nota {
            border-left: 4px solid #4680ff;
            margin-bottom: 20px;
        }
        .table-notes th {
            background-color: #f8f9fa;
        }
        .badge-trimestre {
            font-size: 1rem;
            padding: 5px 10px;
        }
        .media-trimestre {
            font-weight: bold;
            color: #4680ff;
        }
        .disciplina-header {
            cursor: pointer;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        .disciplina-header:hover {
            background-color: #e9ecef;
        }
        .collapse-content {
            padding: 15px;
            border: 1px solid #dee2e6;
            border-radius: 0 0 5px 5px;
            border-top: none;
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
                            <div class="main-body">
                                <div class="page-wrapper">
                                    <div class="page-body">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="card">
                                                    <div class="card-header">
                                                        <h5>
                                                            <i class="feather icon-file-text"></i> Notas
                                                            <span class="float-right">
                                                                Turma: <?= htmlspecialchars($aluno['nome_turma']) ?>
                                                            </span>
                                                        </h5>
                                                    </div>
                                                    <div class="card-body">
                                                        <?php if (empty($notas_por_disciplina)): ?>
                                                            <div class="alert alert-info">
                                                                <i class="feather icon-info"></i> 
                                                                Nenhuma nota registrada até o momento.
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="accordion" id="accordionDisciplinas">
                                                                <?php foreach ($notas_por_disciplina as $id_disciplina => $disciplina): ?>
                                                                    <div class="card card-nota">
                                                                        <div class="disciplina-header" id="heading<?= $id_disciplina ?>" data-toggle="collapse" data-target="#collapse<?= $id_disciplina ?>" aria-expanded="true" aria-controls="collapse<?= $id_disciplina ?>">
                                                                            <h5 class="mb-0">
                                                                                <?= htmlspecialchars($disciplina['nome']) ?>
                                                                                <span class="float-right">
                                                                                    <i class="feather icon-chevron-down"></i>
                                                                                </span>
                                                                            </h5>
                                                                        </div>

                                                                        <div id="collapse<?= $id_disciplina ?>" class="collapse show" aria-labelledby="heading<?= $id_disciplina ?>" data-parent="#accordionDisciplinas">
                                                                            <div class="collapse-content">
                                                                                <?php foreach ($disciplina['trimestres'] as $trimestre => $dados): ?>
                                                                                    <div class="mb-4">
                                                                                        <h6>
                                                                                            <span class="badge badge-primary badge-trimestre"><?= $trimestre ?>º Trimestre</span>
                                                                                            <span class="media-trimestre ml-3">
                                                                                                Média: <?= number_format($dados['media'], 2) ?>
                                                                                            </span>
                                                                                        </h6>
                                                                                        <div class="table-responsive">
                                                                                            <table class="table table-notes table-hover">
                                                                                                <thead>
                                                                                                    <tr>
                                                                                                        <th>Data</th>
                                                                                                        <th>Tipo</th>
                                                                                                        <th>Nota</th>
                                                                                                        <th>Peso</th>
                                                                                                        <th>Descrição</th>
                                                                                                    </tr>
                                                                                                </thead>
                                                                                                <tbody>
                                                                                                    <?php foreach ($dados['notas'] as $nota): ?>
                                                                                                        <tr>
                                                                                                            <td><?= date('d/m/Y', strtotime($nota['data'])) ?></td>
                                                                                                            <td><?= ucfirst(str_replace('_', ' ', $nota['tipo_avaliacao'])) ?></td>
                                                                                                            <td><strong><?= number_format($nota['nota'], 2) ?></strong></td>
                                                                                                            <td><?= number_format($nota['peso'], 2) ?></td>
                                                                                                            <td><?= htmlspecialchars($nota['descricao']) ?></td>
                                                                                                        </tr>
                                                                                                    <?php endforeach; ?>
                                                                                                </tbody>
                                                                                            </table>
                                                                                        </div>
                                                                                    </div>
                                                                                <?php endforeach; ?>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                <?php endforeach; ?>
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

    <?php require_once __DIR__ . '/../../includes/common/js_imports.php'; ?>
    <script>
        // Inicializar tooltips
        $(function () {
            $('[data-toggle="tooltip"]').tooltip();
            
            // Fechar todos os accordions exceto o primeiro
            $('.collapse').on('show.bs.collapse', function () {
                $('.collapse').not(this).collapse('hide');
            });
        });
    </script>
</body>
</html>