<?php
require_once __DIR__ . '/../../includes/common/permissoes.php';
verificarPermissao(['professor']);
require_once __DIR__ . '/../../process/verificar_sessao.php';
require_once __DIR__ . '/../../database/conexao.php';

if (!isset($_GET['disciplina_id']) || !isset($_GET['classe'])) {
    header("Location: /professor/lancar_notas.php");
    exit();
}

$disciplina_id = (int)$_GET['disciplina_id'];
$classe = $_GET['classe'];

// Verificar se o professor tem acesso a esta disciplina/classe
$query_verifica = "SELECT 1 FROM professor_tem_disciplina 
                  WHERE professor_id_professor = ? 
                  AND disciplina_id_disciplina = ? 
                  AND classe = ?";
$stmt_verifica = $conn->prepare($query_verifica);
$stmt_verifica->bind_param("iis", $_SESSION['id_professor'], $disciplina_id, $classe);
$stmt_verifica->execute();

if ($stmt_verifica->get_result()->num_rows === 0) {
    header("Location: /professor/lancar_notas.php");
    exit();
}

// Obter informações da disciplina
$query_disciplina = "SELECT nome FROM disciplina WHERE id_disciplina = ?";
$stmt_disciplina = $conn->prepare($query_disciplina);
$stmt_disciplina->bind_param("i", $disciplina_id);
$stmt_disciplina->execute();
$disciplina = $stmt_disciplina->get_result()->fetch_assoc();

// Obter turmas para esta classe
$query_turmas = "SELECT id_turma, nome, turno FROM turma WHERE classe = ?";
$stmt_turmas = $conn->prepare($query_turmas);
$stmt_turmas->bind_param("s", $classe);
$stmt_turmas->execute();
$turmas = $stmt_turmas->get_result();

// Obter notas se uma turma for selecionada
$notas = [];
if (isset($_GET['turma_id'])) {
    $turma_id = (int)$_GET['turma_id'];
    
    $query_notas = "SELECT u.nome as aluno_nome, n.nota, n.tipo_avaliacao, n.trimestre, 
                   n.descricao, n.data, n.peso
                   FROM nota n
                   JOIN aluno a ON n.aluno_id_aluno = a.id_aluno
                   JOIN usuario u ON a.usuario_id_usuario = u.id_usuario
                   WHERE n.disciplina_id_disciplina = ? AND a.turma_id_turma = ?
                   ORDER BY u.nome, n.trimestre, n.data";
    $stmt_notas = $conn->prepare($query_notas);
    $stmt_notas->bind_param("ii", $disciplina_id, $turma_id);
    $stmt_notas->execute();
    $notas = $stmt_notas->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <?php require_once __DIR__ . '/../../includes/common/head.php'; ?>
    <title>Notas - <?= htmlspecialchars($disciplina['nome']) ?> - Classe <?= htmlspecialchars($classe) ?></title>
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
                            <div class="main-body">
                                <div class="page-wrapper">
                                    <div class="page-body">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="card">
                                                    <div class="card-header">
                                                        <h5>
                                                            <i class="feather icon-list"></i> Notas - <?= htmlspecialchars($disciplina['nome']) ?>
                                                            <span class="badge badge-primary">Classe <?= htmlspecialchars($classe) ?></span>
                                                        </h5>
                                                    </div>
                                                    <div class="card-body">
                                                        <form method="GET" class="mb-4">
                                                            <input type="hidden" name="disciplina_id" value="<?= $disciplina_id ?>">
                                                            <input type="hidden" name="classe" value="<?= $classe ?>">
                                                            
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label for="turma_id">Selecione a Turma</label>
                                                                        <select class="form-control" id="turma_id" name="turma_id" onchange="this.form.submit()">
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
                                                        
                                                        <?php if (!empty($notas)): ?>
                                                            <div class="table-responsive">
                                                                <table class="table table-hover table-notas">
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
                                                                        <?php foreach ($notas as $nota): ?>
                                                                            <tr>
                                                                                <td><?= htmlspecialchars($nota['aluno_nome']) ?></td>
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
                                                                                    <span class="badge badge-trimestre">
                                                                                        <?= $nota['trimestre'] ?>º Trim
                                                                                    </span>
                                                                                </td>
                                                                                <td><?= htmlspecialchars($nota['descricao']) ?></td>
                                                                                <td><?= date('d/m/Y', strtotime($nota['data'])) ?></td>
                                                                                <td><?= number_format($nota['peso'], 1) ?></td>
                                                                            </tr>
                                                                        <?php endforeach; ?>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        <?php elseif (isset($_GET['turma_id'])): ?>
                                                            <div class="alert alert-info">
                                                                <i class="feather icon-info"></i> Nenhuma nota lançada para esta turma/disciplina.
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