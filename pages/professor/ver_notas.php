<?php
require_once __DIR__ . '/../../includes/common/permissoes.php';
verificarPermissao(['professor']);
require_once __DIR__ . '/../../process/verificar_sessao.php';
require_once __DIR__ . '/../../database/conexao.php';

if (!isset($_GET['disciplina_id']) || !isset($_GET['classe'])) {
    header("Location: notas.php");
    exit();
}

$disciplina_id = (int)$_GET['disciplina_id'];
$classe = $_GET['classe'];


// Obter informações da disciplina
$query_disciplina = "SELECT nome, curso_id_curso FROM disciplina WHERE id_disciplina = ?";
$stmt_disciplina = $conn->prepare($query_disciplina);
$stmt_disciplina->bind_param("i", $disciplina_id);
$stmt_disciplina->execute();
$disciplina = $stmt_disciplina->get_result()->fetch_assoc();

// Obter turmas para esta classe e curso
$query_turmas = "SELECT id_turma, nome, turno 
                 FROM turma 
                 WHERE classe = ? AND curso_id_curso = ?";
$stmt_turmas = $conn->prepare($query_turmas);
$stmt_turmas->bind_param("si", $classe, $disciplina['curso_id_curso']);
$stmt_turmas->execute();
$turmas = $stmt_turmas->get_result();

// Obter notas se uma turma for selecionada
$notas = [];
$alunos_notas = [];
if (isset($_GET['turma_id']) && is_numeric($_GET['turma_id'])) {
    $turma_id = (int)$_GET['turma_id'];
    
    // Verificar se a turma pertence ao curso correto
    $query_verifica_turma = "SELECT 1 FROM turma 
                            WHERE id_turma = ? AND curso_id_curso = ?";
    $stmt_verifica_turma = $conn->prepare($query_verifica_turma);
    $stmt_verifica_turma->bind_param("ii", $turma_id, $disciplina['curso_id_curso']);
    $stmt_verifica_turma->execute();
    
    if ($stmt_verifica_turma->get_result()->num_rows === 0) {
        header("Location: /professor/notas.php");
        exit();
    }
    
    // Obter alunos da turma
    $query_alunos = "SELECT a.id_aluno, u.nome as aluno_nome
                    FROM aluno a
                    JOIN usuario u ON a.usuario_id_usuario = u.id_usuario
                    WHERE a.turma_id_turma = ?
                    ORDER BY u.nome";
    $stmt_alunos = $conn->prepare($query_alunos);
    $stmt_alunos->bind_param("i", $turma_id);
    $stmt_alunos->execute();
    $alunos = $stmt_alunos->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Obter notas organizadas por aluno
    foreach ($alunos as $aluno) {
        $query_notas = "SELECT n.nota, n.tipo_avaliacao, n.trimestre, 
                       n.descricao, n.data, n.peso
                       FROM nota n
                       WHERE n.aluno_id_aluno = ? AND n.disciplina_id_disciplina = ?
                       ORDER BY n.trimestre, n.data";
        $stmt_notas = $conn->prepare($query_notas);
        $stmt_notas->bind_param("ii", $aluno['id_aluno'], $disciplina_id);
        $stmt_notas->execute();
        $notas_aluno = $stmt_notas->get_result()->fetch_all(MYSQLI_ASSOC);
        
        if (!empty($notas_aluno)) {
            $alunos_notas[] = [
                'id_aluno' => $aluno['id_aluno'],
                'nome' => $aluno['aluno_nome'],
                'notas' => $notas_aluno
            ];
        }
    }
}

$title = "Ver Notas";
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <?php require_once __DIR__ . '/../../includes/common/head.php'; ?>
    <style>
        .table-notas th {
            background-color: #4680ff;
            color: white;
        }
        .badge-trimestre {
            background-color: #6c757d;
            color: white;
        }
        .badge-prova {
            background-color: #28a745;
        }
        .badge-trabalho {
            background-color: #17a2b8;
        }
        .badge-continua {
            background-color: #ffc107;
            color: #212529;
        }
        .badge-recuperacao {
            background-color: #dc3545;
        }
        .badge-projeto {
            background-color: #6610f2;
        }
        .aluno-header {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .trimestre-header {
            background-color: #e9ecef;
            font-weight: bold;
        }
        .nota-row:hover {
            background-color: #f1f5ff;
        }
    </style>
</head>
<body>
    <?php require_once __DIR__ . '/../../includes/common/preloader.php'; ?>

    <div id="pcoded" class="pcoded">
        <div class="pcoded-overlay-box"></div>
        <div class="pcoded-container navbar-wrapper">
            <?php require_once __DIR__ . '/../../includes/professor/navbar.php'; ?>

            <div class="pcoded-main-container">
                <div class="pcoded-wrapper">
                    <?php require_once __DIR__ . '/../../includes/professor/sidebar.php'; ?>
                    
                    <div class="pcoded-content">
                        <div class="pcoded-inner-content">
                            <div class="main-body bg-img">
                                <div class="page-wrapper">
                                    <div class="page-body">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="card card-table">
                                                    <div class="card-header">
                                                        <h5>
                                                            <i class="feather icon-list"></i> Notas - <?= htmlspecialchars($disciplina['nome']) ?>
                                                            <span class="badge badge-primary">Classe <?= htmlspecialchars($classe) ?></span>
                                                        </h5>
                                                        <div class="card-header-right">
                                                            <a href="notas.php" class="btn btn-sm btn-primary">
                                                                <i class="feather icon-plus"></i> Voltar
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="card-body">
                                                        <form method="GET" class="mb-4">
                                                            <input type="hidden" name="disciplina_id" value="<?= $disciplina_id ?>">
                                                            <input type="hidden" name="classe" value="<?= $classe ?>">
                                                            
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label for="turma_id">Selecione a Turma</label>
                                                                        <select class="form-control" id="turma_id" name="turma_id" onchange="this.form.submit()" required>
                                                                            <option value="">Selecione uma turma</option>
                                                                            <?php while ($turma = $turmas->fetch_assoc()): ?>
                                                                                <option value="<?= $turma['id_turma'] ?>" <?= isset($_GET['turma_id']) && $_GET['turma_id'] == $turma['id_turma'] ? 'selected' : '' ?>>
                                                                                    <?= htmlspecialchars($turma['nome']) ?> (<?= htmlspecialchars($turma['turno']) ?>)
                                                                                </option>
                                                                            <?php endwhile; ?>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </form>
                                                        
                                                        <?php if (!empty($alunos_notas)): ?>
                                                            <div class="table-responsive">
                                                                <table class="table table-hover table-notas card-table">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>Aluno</th>
                                                                            <th>Nota</th>
                                                                            <th>Tipo</th>
                                                                            <th>Trimestre</th>
                                                                            <th>Descrição</th>
                                                                            <th>Data</th>
                                                                            <th>Peso</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <?php foreach ($alunos_notas as $aluno): ?>
                                                                            <tr class="">
                                                                                <td colspan="7">
                                                                                    <i class="feather icon-user"></i> <?= htmlspecialchars($aluno['nome']) ?>
                                                                                </td>
                                                                            </tr>
                                                                            <?php 
                                                                                // Agrupar notas por trimestre
                                                                                $notas_por_trimestre = [];
                                                                                foreach ($aluno['notas'] as $nota) {
                                                                                    $trimestre = $nota['trimestre'];
                                                                                    if (!isset($notas_por_trimestre[$trimestre])) {
                                                                                        $notas_por_trimestre[$trimestre] = [];
                                                                                    }
                                                                                    $notas_por_trimestre[$trimestre][] = $nota;
                                                                                }
                                                                                
                                                                                foreach ($notas_por_trimestre as $trimestre => $notas_trimestre): 
                                                                            ?>
                                                                                <?php foreach ($notas_trimestre as $nota): ?>
                                                                                    <tr class="nota-row">
                                                                                        <td></td>
                                                                                        <td><strong><?= number_format($nota['nota'], 2) ?></strong></td>
                                                                                        <td>
                                                                                            <?php 
                                                                                                $badge_class = '';
                                                                                                switch ($nota['tipo_avaliacao']) {
                                                                                                    case 'prova': $badge_class = 'badge-prova'; break;
                                                                                                    case 'trabalho': $badge_class = 'badge-trabalho'; break;
                                                                                                    case 'avaliacao_continua': $badge_class = 'badge-continua'; break;
                                                                                                    case 'recuperacao': $badge_class = 'badge-recuperacao'; break;
                                                                                                    case 'projeto': $badge_class = 'badge-projeto'; break;
                                                                                                }
                                                                                            ?>
                                                                                            <span class="badge <?= $badge_class ?>">
                                                                                                <?= ucfirst(str_replace('_', ' ', $nota['tipo_avaliacao'])) ?>
                                                                                            </span>
                                                                                        </td>
                                                                                        <td>
                                                                                            <i class="feather icon-calendar">
                                                                                            <span class="badge badge-trimestre">
                                                                                                <?= $nota['trimestre'] ?>º Trim
                                                                                            </span>
                                                                                        </td>
                                                                                        <td><?= htmlspecialchars($nota['descricao']) ?></td>
                                                                                        <td><?= date('d/m/Y', strtotime($nota['data'])) ?></td>
                                                                                        <td><?= number_format($nota['peso'], 1) ?></td>
                                                                                    </tr>
                                                                                <?php endforeach; ?>
                                                                            <?php endforeach; ?>
                                                                        <?php endforeach; ?>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        <?php elseif (isset($_GET['turma_id'])): ?>
                                                            <div class="alert alert-info">
                                                                <i class="feather icon-info"></i> Nenhuma nota lançada para esta turma/disciplina.
                                                                <a href="adicionar_nota.php?disciplina_id=<?= $disciplina_id ?>&classe=<?= $classe ?>&turma_id=<?= $_GET['turma_id'] ?>" 
                                                                   class="btn btn-sm btn-primary ml-3">
                                                                    <i class="feather icon-plus"></i> Adicionar Nota
                                                                </a>
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="alert alert-secondary">
                                                                <i class="feather icon-info"></i> Selecione uma turma para visualizar as notas.
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
</body>
</html>