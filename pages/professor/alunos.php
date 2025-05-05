<?php
require_once '../../includes/common/permissoes.php';
// Verificar permissão do professor
verificarPermissao(['professor']);
require_once '../../process/verificar_sessao.php';
require_once '../../database/conexao.php';


// Configurações iniciais
$title = "Meus Alunos";
$currentYear = date('Y');
$alunos = [];
$turmasProfessor = [];

try {
    // Obter ID do professor logado
    $idUsuario = $_SESSION['id_usuario'];
    $queryProfessor = "SELECT id_professor FROM professor WHERE usuario_id_usuario = ?";
    $stmtProfessor = $conn->prepare($queryProfessor);
    $stmtProfessor->bind_param("i", $idUsuario);
    $stmtProfessor->execute();
    $resultProfessor = $stmtProfessor->get_result();

    if ($resultProfessor->num_rows === 0) {
        throw new Exception("Professor não encontrado.");
    }

    $professor = $resultProfessor->fetch_assoc();
    $professorId = $professor['id_professor'];

    // Obter filtros
    $anoLetivo = filter_input(INPUT_GET, 'ano_letivo', FILTER_VALIDATE_INT, [
        'options' => ['default' => $currentYear, 'min_range' => $currentYear - 1, 'max_range' => $currentYear + 1]
    ]);

    $turmaId = filter_input(INPUT_GET, 'turma_id', FILTER_VALIDATE_INT);

    // Buscar turmas do professor
    $queryTurmas = "SELECT t.id_turma, t.nome, c.nome AS curso_nome 
                   FROM turma t
                   JOIN curso c ON t.curso_id_curso = c.id_curso
                   JOIN professor_tem_turma pt ON pt.turma_id_turma = t.id_turma
                   WHERE pt.professor_id_professor = ?
                   ORDER BY c.nome, t.nome";
    
    $stmtTurmas = $conn->prepare($queryTurmas);
    $stmtTurmas->bind_param("i", $professorId);
    $stmtTurmas->execute();
    $turmasProfessor = $stmtTurmas->get_result()->fetch_all(MYSQLI_ASSOC);

    // Buscar alunos se uma turma foi selecionada
    if ($turmaId) {
        // Verificar se o professor tem acesso à turma selecionada
        $turmaValida = false;
        foreach ($turmasProfessor as $turma) {
            if ($turma['id_turma'] == $turmaId) {
                $turmaValida = true;
                break;
            }
        }

        if ($turmaValida) {
            $queryAlunos = "SELECT a.id_aluno, u.nome, u.email, u.foto_perfil, 
                           m.numero_matricula, t.classe, t.turno, m.status_matricula
                          FROM matricula m
                          JOIN aluno a ON m.aluno_id_aluno = a.id_aluno
                          JOIN usuario u ON a.usuario_id_usuario = u.id_usuario
                          JOIN turma t ON a.turma_id_turma = t.id_turma
                          WHERE m.turma_id_turma = ? AND m.ano_letivo = ?
                          ORDER BY u.nome";
            
            $stmtAlunos = $conn->prepare($queryAlunos);
            $stmtAlunos->bind_param("ii", $turmaId, $anoLetivo);
            $stmtAlunos->execute();
            $alunos = $stmtAlunos->get_result()->fetch_all(MYSQLI_ASSOC);
        }
    }
} catch (Exception $e) {
    $_SESSION['erro'] = "Erro ao carregar dados: " . $e->getMessage();
}

// Template HTML
require_once '../../includes/common/head.php';
?>

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
                                    <!-- Page Header -->
                                    <div class="page-header">
                                        <div class="row align-items-end">
                                            <div class="col-lg-8">
                                                <div class="page-header-title">
                                                    <h4>Meus Alunos</h4>
                                                    <span>Lista de alunos por turma</span>
                                                </div>
                                            </div>
                                            <div class="col-lg-4">
                                                <div class="page-header-breadcrumb">
                                                    <ul class="breadcrumb-title">
                                                        <li class="breadcrumb-item">
                                                            <a href="index.php"><i class="feather icon-home"></i></a>
                                                        </li>
                                                        <li class="breadcrumb-item"><a href="#!">Professor</a></li>
                                                        <li class="breadcrumb-item active">Alunos</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Page Body -->
                                    <div class="page-body">
                                        <!-- Mensagens de feedback -->
                                        <?php if (isset($_SESSION['erro'])): ?>
                                            <div class="alert alert-danger">
                                                <?= $_SESSION['erro']; unset($_SESSION['erro']); ?>
                                            </div>
                                        <?php endif; ?>

                                        <!-- Filtros -->
                                        <div class="card card-table mb-3">
                                            <div class="card-header">
                                                <h5>Filtrar Alunos</h5>
                                            </div>
                                            <div class="card-body">
                                                <form method="GET" action="">
                                                    <div class="row">
                                                        <div class="col-md-5">
                                                            <div class="form-group">
                                                                <label for="turma_id">Turma</label>
                                                                <select class="form-control" name="turma_id" id="turma_id" required>
                                                                    <option value="">Selecione uma turma</option>
                                                                    <?php foreach ($turmasProfessor as $turma): ?>
                                                                        <option value="<?= htmlspecialchars($turma['id_turma']) ?>" 
                                                                            <?= ($turmaId == $turma['id_turma']) ? 'selected' : '' ?>>
                                                                            <?= htmlspecialchars($turma['nome']) ?> - <?= htmlspecialchars($turma['curso_nome']) ?>
                                                                        </option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-5">
                                                            <div class="form-group">
                                                                <label for="ano_letivo">Ano Letivo</label>
                                                                <select class="form-control" name="ano_letivo" id="ano_letivo" required>
                                                                    <?php for ($i = $currentYear - 1; $i <= $currentYear + 1; $i++): ?>
                                                                        <option value="<?= $i ?>" <?= ($anoLetivo == $i) ? 'selected' : '' ?>>
                                                                            <?= $i ?>
                                                                        </option>
                                                                    <?php endfor; ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2 d-flex align-items-end">
                                                            <button type="submit" class="btn btn-primary btn-block">
                                                                <i class="feather icon-filter"></i> Filtrar
                                                            </button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>

                                        <!-- Lista de Alunos -->
                                        <div class="card card-table">
                                            <div class="card-header">
                                                <h5>Alunos da Turma</h5>
                                                <?php if ($turmaId): ?>
                                                    <div class="float-right">
                                                        <span class="badge badge-dark">
                                                            Total: <?= count($alunos) ?> aluno(s)
                                                        </span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="card-block">
                                                <?php if (empty($turmaId)): ?>
                                                    <div class="alert alert-info">
                                                        Selecione uma turma para visualizar os alunos.
                                                    </div>
                                                <?php elseif (empty($alunos)): ?>
                                                    <div class="alert alert-warning">
                                                        Nenhum aluno encontrado nesta turma para o ano letivo selecionado.
                                                    </div>
                                                <?php else: ?>
                                                    <div class="table-responsive">
                                                        <table class="table table-hover table-striped text-white">
                                                            <thead>
                                                                <tr>
                                                                    <th>#</th>
                                                                    <th>Foto</th>
                                                                    <th>Nome</th>
                                                                    <th>Matrícula</th>
                                                                    <th>Classe</th>
                                                                    <th>Turno</th>
                                                                    <th>Status</th>
                                                                    <th>Ações</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody class="text-white">
                                                                <?php foreach ($alunos as $index => $aluno): ?>
                                                                    <tr>
                                                                        <td><?= $index + 1 ?></td>
                                                                        <td class="text-center">
                                                                            <img src="../../public/uploads/perfil/<?= !empty($aluno['foto_perfil']) ? htmlspecialchars($aluno['foto_perfil']) : 'default.png' ?>" 
                                                                                 class="img-radius img-40" alt="Foto do aluno">
                                                                        </td>
                                                                        <td><?= htmlspecialchars($aluno['nome']) ?></td>
                                                                        <td><?= htmlspecialchars($aluno['numero_matricula']) ?></td>
                                                                        <td><?= htmlspecialchars($aluno['classe']) ?></td>
                                                                        <td><?= htmlspecialchars($aluno['turno']) ?></td>
                                                                        <td>
                                                                            <span class="badge badge-<?= $aluno['status_matricula'] == 'ativa' ? 'success' : 'danger' ?>">
                                                                                <?= ucfirst($aluno['status_matricula']) ?>
                                                                            </span>
                                                                        </td>
                                                                        <td>
                                                                            <a href="perfil_aluno.php?id=<?= $aluno['id_aluno'] ?>&turma_id=<?= $turmaId ?>" 
                                                                               class="btn btn-info btn-sm" title="Ver perfil">
                                                                                <i class="feather icon-user"></i> Perfil
                                                                            </a>
                                                                        </td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
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

    <?php require_once '../../includes/common/js_imports.php'; ?>
    <script>
        $(document).ready(function() {
            // Ativar tooltips
            $('[data-toggle="tooltip"]').tooltip();
            
            // Validação do formulário de filtro
            $('form').validate({
                errorClass: 'text-danger',
                rules: {
                    'turma_id': { required: true },
                    'ano_letivo': { required: true }
                },
                messages: {
                    'turma_id': 'Selecione uma turma',
                    'ano_letivo': 'Selecione um ano letivo'
                },
                highlight: function(element) {
                    $(element).addClass('is-invalid');
                },
                unhighlight: function(element) {
                    $(element).removeClass('is-invalid');
                }
            });
        });
    </script>
</body>
</html>
<?php
$conn->close();
?>