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

// Obter alunos se uma turma for selecionada
$alunos = [];
if (isset($_GET['turma_id'])) {
    $turma_id = (int)$_GET['turma_id'];
    
    $query_alunos = "SELECT a.id_aluno, u.nome, u.email, u.bi_numero, 
                     (SELECT AVG(nota) FROM nota WHERE aluno_id_aluno = a.id_aluno AND disciplina_id_disciplina = ?) as media
                     FROM aluno a
                     JOIN usuario u ON a.usuario_id_usuario = u.id_usuario
                     WHERE a.turma_id_turma = ?
                     ORDER BY u.nome";
    $stmt_alunos = $conn->prepare($query_alunos);
    $stmt_alunos->bind_param("ii", $disciplina_id, $turma_id);
    $stmt_alunos->execute();
    $alunos = $stmt_alunos->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <?php require_once __DIR__ . '/../../includes/common/head.php'; ?>
    <title>Alunos - <?= htmlspecialchars($disciplina['nome']) ?> - Classe <?= htmlspecialchars($classe) ?></title>
    <style>
        .table-alunos th {
            background-color: #4680ff;
            color: white;
        }
        .media-aluno {
            font-weight: bold;
        }
        .media-baixa {
            color: #ff5252;
        }
        .media-media {
            color: #FFC107;
        }
        .media-alta {
            color: #4CAF50;
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
                                                            <i class="feather icon-users"></i> Alunos - <?= htmlspecialchars($disciplina['nome']) ?>
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
                                                        
                                                        <?php if (!empty($alunos)): ?>
                                                            <div class="table-responsive">
                                                                <table class="table table-hover table-alunos">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>#</th>
                                                                            <th>Nome</th>
                                                                            <th>BI</th>
                                                                            <th>Email</th>
                                                                            <th>Média</th>
                                                                            <th>Ações</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <?php foreach ($alunos as $index => $aluno): ?>
                                                                            <tr>
                                                                                <td><?= $index + 1 ?></td>
                                                                                <td><?= htmlspecialchars($aluno['nome']) ?></td>
                                                                                <td><?= htmlspecialchars($aluno['bi_numero']) ?></td>
                                                                                <td><?= htmlspecialchars($aluno['email']) ?></td>
                                                                                <td>
                                                                                    <?php if ($aluno['media'] !== null): ?>
                                                                                        <?php 
                                                                                            $media_class = '';
                                                                                            if ($aluno['media'] < 10) $media_class = 'media-baixa';
                                                                                            elseif ($aluno['media'] < 14) $media_class = 'media-media';
                                                                                            else $media_class = 'media-alta';
                                                                                        ?>
                                                                                        <span class="media-aluno <?= $media_class ?>">
                                                                                            <?= number_format($aluno['media'], 2) ?>
                                                                                        </span>
                                                                                    <?php else: ?>
                                                                                        <span class="text-muted">N/A</span>
                                                                                    <?php endif; ?>
                                                                                </td>
                                                                                <td>
                                                                                    <a href="ver_notas_aluno.php?aluno_id=<?= $aluno['id_aluno'] ?>&disciplina_id=<?= $disciplina_id ?>" 
                                                                                       class="btn btn-sm btn-outline-primary">
                                                                                        <i class="feather icon-list"></i> Notas
                                                                                    </a>
                                                                                </td>
                                                                            </tr>
                                                                        <?php endforeach; ?>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        <?php elseif (isset($_GET['turma_id'])): ?>
                                                            <div class="alert alert-info">
                                                                <i class="feather icon-info"></i> Nenhum aluno encontrado nesta turma.
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="alert alert-secondary">
                                                                <i class="feather icon-info"></i> Selecione uma turma para visualizar os alunos.
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