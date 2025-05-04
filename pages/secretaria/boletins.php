<?php
require_once '../../includes/common/permissoes.php';
verificarPermissao(['secretaria', 'diretor_geral', 'diretor_pedagogico']);
require_once '../../process/verificar_sessao.php';
require_once '../../database/conexao.php';

$title = "Selecionar Aluno para Boletim";

// Obter lista de cursos
$query_cursos = "SELECT id_curso, nome FROM curso ORDER BY nome";
$result_cursos = $conn->query($query_cursos);
$cursos = $result_cursos->fetch_all(MYSQLI_ASSOC);

// Obter turmas se um curso foi selecionado
$turmas = [];
if (isset($_GET['curso_id'])) {
    $curso_id = intval($_GET['curso_id']);
    $query_turmas = "SELECT id_turma, nome, classe 
                     FROM turma 
                     WHERE curso_id_curso = ? 
                     ORDER BY classe, nome";
    $stmt = $conn->prepare($query_turmas);
    $stmt->bind_param('i', $curso_id);
    $stmt->execute();
    $result_turmas = $stmt->get_result();
    $turmas = $result_turmas->fetch_all(MYSQLI_ASSOC);
}

// Obter alunos se uma turma foi selecionada
$alunos = [];
if (isset($_GET['turma_id'])) {
    $turma_id = intval($_GET['turma_id']);
    $query_alunos = "SELECT a.id_aluno, u.nome as aluno_nome, u.bi_numero 
                     FROM aluno a 
                     JOIN usuario u ON a.usuario_id_usuario = u.id_usuario 
                     WHERE a.turma_id_turma = ? 
                     ORDER BY u.nome";
    $stmt = $conn->prepare($query_alunos);
    $stmt->bind_param('i', $turma_id);
    $stmt->execute();
    $result_alunos = $stmt->get_result();
    $alunos = $result_alunos->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <title><?= $title ?> | Sistema Escolar</title>
    <?php require_once '../../includes/common/head.php'; ?>
    
    <style>
        .card-selecao {
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .card-header-selecao {
            background-color: #4680ff;
            color: white;
            border-radius: 8px 8px 0 0 !important;
            padding: 15px 20px;
        }
        .filter-section {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .btn-gerar {
            background-color: #28a745;
            color: white;
            padding: 8px 15px;
            border-radius: 4px;
            text-decoration: none;
            display: inline-block;
        }
        .btn-gerar:hover {
            background-color: #218838;
            color: blue;
        }
        .badge-classe {
            background-color: #6c757d;
            color: white;
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 0.8em;
        }
    </style>
</head>

<body>
    <?php require_once '../../includes/common/preloader.php'; ?>

    <div id="pcoded" class="pcoded">
        <div class="pcoded-overlay-box"></div>
        <div class="pcoded-container navbar-wrapper">
            <?php require_once '../../includes/secretaria/navbar.php'; ?>

            <div class="pcoded-main-container">
                <div class="pcoded-wrapper">
                    <?php require_once '../../includes/secretaria/sidebar.php'; ?>
                    
                    <div class="pcoded-content">
                        <div class="pcoded-inner-content">
                            <div class="main-body bg-img">
                                <div class="page-wrapper">
                                    <div class="page-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="card card-selecao card-table">
                                                    <div class="card-header card-header-selecao">
                                                        <h5>Selecionar Aluno para Boletim</h5>
                                                    </div>
                                                    <div class="card-body">
                                                        <form method="GET" action="">
                                                            <div class="row">
                                                                <div class="col-md-4">
                                                                    <div class="form-group">
                                                                        <label for="curso_id">Curso</label>
                                                                        <select class="form-control" id="curso_id" name="curso_id" required>
                                                                            <option value="">Selecione um curso...</option>
                                                                            <?php foreach ($cursos as $curso): ?>
                                                                            <option value="<?= $curso['id_curso'] ?>" 
                                                                                <?= isset($_GET['curso_id']) && $_GET['curso_id'] == $curso['id_curso'] ? 'selected' : '' ?>>
                                                                                <?= htmlspecialchars($curso['nome']) ?>
                                                                            </option>
                                                                            <?php endforeach; ?>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                
                                                                <div class="col-md-4">
                                                                    <div class="form-group">
                                                                        <label for="turma_id">Turma</label>
                                                                        <select class="form-control" id="turma_id" name="turma_id" <?= empty($turmas) ? 'disabled' : '' ?> required>
                                                                            <option value="">Selecione uma turma...</option>
                                                                            <?php foreach ($turmas as $turma): ?>
                                                                            <option value="<?= $turma['id_turma'] ?>" 
                                                                                <?= isset($_GET['turma_id']) && $_GET['turma_id'] == $turma['id_turma'] ? 'selected' : '' ?>>
                                                                                <?= htmlspecialchars($turma['nome']) ?> 
                                                                                <span class="badge-classe"><?= $turma['classe'] ?></span>
                                                                            </option>
                                                                            <?php endforeach; ?>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                
                                                                <div class="col-md-2">
                                                                    <button type="submit" class="btn btn-primary mt-4">Buscar Alunos</button>
                                                                </div>
                                                            </div>
                                                        </form>
                                                        
                                                        
                                                    </div>
                                                </div>
                                            </div>
                                                <div class="col-md-12">
                                                    <div>
                                                        <?php if (!empty($alunos)): ?>
                                                            <div class="table-responsive mt-4 ">
                                                                <table class="table table-alunos card-table">
                                                                    <thead class="text-dark bg-primary">
                                                                        <tr>
                                                                            <th>Nº</th>
                                                                            <th>Nome do Aluno</th>
                                                                            <th>BI/Nº</th>
                                                                            <th>Ação</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <?php foreach ($alunos as $index => $aluno): ?>
                                                                        <tr>
                                                                            <td><?= $index + 1 ?></td>
                                                                            <td><?= htmlspecialchars($aluno['aluno_nome']) ?></td>
                                                                            <td><?= htmlspecialchars($aluno['bi_numero']) ?></td>
                                                                            <td>
                                                                                <a href="gerar_boletim.php?aluno_id=<?= $aluno['id_aluno'] ?>" target="_blank"
                                                                                class="btn-gerar">
                                                                                    <i class="feather icon-file-text"></i> Gerar Boletim
                                                                                </a>
                                                                            </td>
                                                                        </tr>
                                                                        <?php endforeach; ?>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                            <?php elseif (isset($_GET['turma_id'])): ?>
                                                            <div class="alert alert-info mt-4">Nenhum aluno encontrado nesta turma.</div>
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

    <?php require_once '../../includes/common/js_imports.php'; ?>
    
    <script>
        $(document).ready(function() {
            // Atualizar dropdown de turmas quando o curso for selecionado
            $('#curso_id').change(function() {
                const cursoId = $(this).val();
                if (cursoId) {
                    window.location.href = 'boletins.php?curso_id=' + cursoId;
                }
            });
            
            // Habilitar/desabilitar dropdown de turmas
            if ($('#turma_id option').length > 1) {
                $('#turma_id').prop('disabled', false);
            }
        });
    </script>
</body>
</html>