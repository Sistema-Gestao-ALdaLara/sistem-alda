<?php
require_once "../auth/permissoes.php";
verificarPermissao(['secretaria']);

require_once '../config/conexao.php';

// Filtros
$status = isset($_GET['status']) ? $_GET['status'] : 'pendente';
$id_curso = isset($_GET['id_curso']) ? intval($_GET['id_curso']) : null;
$ano_letivo = isset($_GET['ano_letivo']) ? intval($_GET['ano_letivo']) : date('Y');

// Obter matrículas com filtros
$query = "SELECT m.id_matricula, a.id_aluno, u.nome, u.bi_numero, 
          c.nome AS curso, t.nome AS turma, m.data_matricula, 
          m.status_matricula, m.tipo_matricula, m.ano_letivo
          FROM matricula m
          JOIN aluno a ON m.id_aluno = a.id_aluno
          JOIN usuario u ON a.id_usuario = u.id_usuario
          LEFT JOIN curso c ON a.id_curso = c.id_curso
          LEFT JOIN turma t ON a.id_turma = t.id_turma
          WHERE m.ano_letivo = ?";

$params = [$ano_letivo];

if ($status !== 'todos') {
    $query .= " AND m.status_matricula = ?";
    $params[] = $status;
}

if ($id_curso) {
    $query .= " AND a.id_curso = ?";
    $params[] = $id_curso;
}

$query .= " ORDER BY m.data_matricula DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$matriculas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obter cursos para filtros
$cursos = $pdo->query("SELECT id_curso, nome FROM curso ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);

// Obter alunos não matriculados no ano letivo
$alunos_nao_matriculados = $pdo->prepare("SELECT a.id_aluno, u.nome 
                                         FROM aluno a
                                         JOIN usuario u ON a.id_usuario = u.id_usuario
                                         WHERE a.id_aluno NOT IN 
                                         (SELECT id_aluno FROM matricula WHERE ano_letivo = ?)");
$alunos_nao_matriculados->execute([$ano_letivo]);
$alunos_nao_matriculados = $alunos_nao_matriculados->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <title>SECRETARIA - Gestão de Matrículas | Alda Lara</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Sistema de Gestão Escolar - Escola Alda Lara">
    <meta name="keywords" content="Escola, Alda Lara, Angola, Luanda, Secretaria, Matrículas">
    <meta name="author" content="Escola Alda Lara">
    <link rel="icon" href="../libraries/assets/images/favicon.ico" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="../libraries/bower_components/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="../libraries/assets/icon/feather/css/feather.css">
    <link rel="stylesheet" type="text/css" href="../libraries/assets/css/style.css">
    <link rel="stylesheet" type="text/css" href="../libraries/assets/css/jquery.mCustomScrollbar.css">
    <style>
        .card-matricula {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .badge-pendente { background-color: #ffc107; color: #000; }
        .badge-aprovada { background-color: #28a745; color: #fff; }
        .badge-rejeitada { background-color: #dc3545; color: #fff; }
        .badge-transferencia { background-color: #17a2b8; color: #fff; }
        .filtros-container {
            background: rgba(255, 255, 255, 0.85);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .table-responsive {
            overflow-x: auto;
        }
        @media (max-width: 768px) {
            .btn-action {
                margin-bottom: 5px;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Pre-loader -->
    <div class="theme-loader">
        <div class="ball-scale">
            <div class='contain'>
                <div class="ring"><div class="frame"></div></div>
                <div class="ring"><div class="frame"></div></div>
                <div class="ring"><div class="frame"></div></div>
                <div class="ring"><div class="frame"></div></div>
                <div class="ring"><div class="frame"></div></div>
                <div class="ring"><div class="frame"></div></div>
                <div class="ring"><div class="frame"></div></div>
                <div class="ring"><div class="frame"></div></div>
                <div class="ring"><div class="frame"></div></div>
                <div class="ring"><div class="frame"></div></div>
            </div>
        </div>
    </div>

    <div id="pcoded" class="pcoded">
        <div class="pcoded-overlay-box"></div>
        <div class="pcoded-container navbar-wrapper">
            <!-- Cabeçalho (igual ao da página alunos) -->
            <!-- Sidebar (igual ao da página alunos) -->

            <!-- Conteúdo Principal -->
            <div class="pcoded-content">
                <div class="pcoded-inner-content">
                    <div class="main-body">
                        <div class="page-wrapper">
                            <div class="page-body">
                                <div class="row">
                                    <div class="col-12 mt-4">
                                        <!-- Filtros -->
                                        <div class="card card-matricula mb-3">
                                            <div class="card-header">
                                                <h5>Filtrar Matrículas</h5>
                                            </div>
                                            <div class="card-body">
                                                <form id="formFiltros" method="GET" action="">
                                                    <div class="row">
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label for="status">Status</label>
                                                                <select class="form-control" id="status" name="status">
                                                                    <option value="pendente" <?= $status == 'pendente' ? 'selected' : '' ?>>Pendentes</option>
                                                                    <option value="aprovada" <?= $status == 'aprovada' ? 'selected' : '' ?>>Aprovadas</option>
                                                                    <option value="rejeitada" <?= $status == 'rejeitada' ? 'selected' : '' ?>>Rejeitadas</option>
                                                                    <option value="todos" <?= $status == 'todos' ? 'selected' : '' ?>>Todos</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label for="id_curso">Curso</label>
                                                                <select class="form-control" id="id_curso" name="id_curso">
                                                                    <option value="">Todos os cursos</option>
                                                                    <?php foreach ($cursos as $curso): ?>
                                                                    <option value="<?= $curso['id_curso'] ?>" <?= $id_curso == $curso['id_curso'] ? 'selected' : '' ?>>
                                                                        <?= htmlspecialchars($curso['nome']) ?>
                                                                    </option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label for="ano_letivo">Ano Letivo</label>
                                                                <select class="form-control" id="ano_letivo" name="ano_letivo">
                                                                    <option value="<?= date('Y')-1 ?>"><?= date('Y')-1 ?></option>
                                                                    <option value="<?= date('Y') ?>" selected><?= date('Y') ?></option>
                                                                    <option value="<?= date('Y')+1 ?>"><?= date('Y')+1 ?></option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3 d-flex align-items-end">
                                                            <button type="submit" class="btn btn-primary btn-block">
                                                                <i class="feather icon-filter"></i> Filtrar
                                                            </button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>

                                        <!-- Tabela de Matrículas -->
                                        <div class="card card-matricula">
                                            <div class="card-header d-flex justify-content-between align-items-center">
                                                <h5 class="mb-0">Gestão de Matrículas</h5>
                                                <div>
                                                    <button class="btn btn-primary" data-toggle="modal" data-target="#modalNovaMatricula">
                                                        <i class="feather icon-plus"></i> Nova Matrícula
                                                    </button>
                                                    <button class="btn btn-success" data-toggle="modal" data-target="#modalTransferencia">
                                                        <i class="feather icon-repeat"></i> Transferência
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="card-block">
                                                <div class="table-responsive">
                                                    <table class="table table-hover">
                                                        <thead>
                                                            <tr>
                                                                <th>Aluno</th>
                                                                <th>BI</th>
                                                                <th>Curso</th>
                                                                <th>Turma</th>
                                                                <th>Tipo</th>
                                                                <th>Data</th>
                                                                <th>Status</th>
                                                                <th>Ações</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php if (empty($matriculas)): ?>
                                                            <tr>
                                                                <td colspan="8" class="text-center">Nenhuma matrícula encontrada com os filtros selecionados</td>
                                                            </tr>
                                                            <?php else: ?>
                                                            <?php foreach ($matriculas as $matricula): ?>
                                                            <tr>
                                                                <td><?= htmlspecialchars($matricula['nome']) ?></td>
                                                                <td><?= htmlspecialchars($matricula['bi_numero']) ?></td>
                                                                <td><?= htmlspecialchars($matricula['curso'] ?? 'N/D') ?></td>
                                                                <td><?= htmlspecialchars($matricula['turma'] ?? 'N/D') ?></td>
                                                                <td>
                                                                    <?= $matricula['tipo_matricula'] == 'regular' ? 'Regular' : 'Transferência' ?>
                                                                </td>
                                                                <td><?= date('d/m/Y', strtotime($matricula['data_matricula'])) ?></td>
                                                                <td>
                                                                    <?php 
                                                                    $badge_class = '';
                                                                    if ($matricula['status_matricula'] == 'pendente') $badge_class = 'badge-pendente';
                                                                    elseif ($matricula['status_matricula'] == 'aprovada') $badge_class = 'badge-aprovada';
                                                                    else $badge_class = 'badge-rejeitada';
                                                                    ?>
                                                                    <span class="badge <?= $badge_class ?>">
                                                                        <?= ucfirst($matricula['status_matricula']) ?>
                                                                    </span>
                                                                </td>
                                                                <td>
                                                                    <div class="btn-group btn-group-sm">
                                                                        <?php if ($matricula['status_matricula'] == 'pendente'): ?>
                                                                        <button class="btn btn-success btn-action" onclick="aprovarMatricula(<?= $matricula['id_matricula'] ?>)">
                                                                            <i class="feather icon-check"></i>
                                                                        </button>
                                                                        <button class="btn btn-danger btn-action" onclick="rejeitarMatricula(<?= $matricula['id_matricula'] ?>)">
                                                                            <i class="feather icon-x"></i>
                                                                        </button>
                                                                        <?php endif; ?>
                                                                        <button class="btn btn-info btn-action" onclick="editarMatricula(<?= $matricula['id_matricula'] ?>)">
                                                                            <i class="feather icon-edit"></i>
                                                                        </button>
                                                                        <button class="btn btn-secondary btn-action" onclick="emitirComprovante(<?= $matricula['id_matricula'] ?>)">
                                                                            <i class="feather icon-printer"></i>
                                                                        </button>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            <?php endforeach; ?>
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

    <!-- Modal Nova Matrícula -->
    <div class="modal fade" id="modalNovaMatricula" tabindex="-1" role="dialog" aria-labelledby="modalNovaMatriculaLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalNovaMatriculaLabel">Nova Matrícula</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="formMatricula" method="POST" action="salvar_matricula.php">
                        <input type="hidden" name="tipo_matricula" value="regular">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="id_aluno">Aluno *</label>
                                    <select class="form-control" id="id_aluno" name="id_aluno" required>
                                        <option value="">Selecione um aluno</option>
                                        <?php foreach ($alunos_nao_matriculados as $aluno): ?>
                                        <option value="<?= $aluno['id_aluno'] ?>"><?= htmlspecialchars($aluno['nome']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="ano_letivo_matricula">Ano Letivo *</label>
                                    <select class="form-control" id="ano_letivo_matricula" name="ano_letivo" required>
                                        <option value="<?= date('Y') ?>" selected><?= date('Y') ?></option>
                                        <option value="<?= date('Y')+1 ?>"><?= date('Y')+1 ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="curso_matricula">Curso *</label>
                                    <select class="form-control" id="curso_matricula" name="id_curso" required>
                                        <option value="">Selecione um curso</option>
                                        <?php foreach ($cursos as $curso): ?>
                                        <option value="<?= $curso['id_curso'] ?>"><?= htmlspecialchars($curso['nome']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="turma_matricula">Turma</label>
                                    <select class="form-control" id="turma_matricula" name="id_turma">
                                        <option value="">Selecione um curso primeiro</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="observacoes">Observações</label>
                            <textarea class="form-control" id="observacoes" name="observacoes" rows="3"></textarea>
                        </div>
                        
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Salvar Matrícula</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Transferência -->
    <div class="modal fade" id="modalTransferencia" tabindex="-1" role="dialog" aria-labelledby="modalTransferenciaLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="modalTransferenciaLabel">Registrar Transferência</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="formTransferencia" method="POST" action="salvar_matricula.php">
                        <input type="hidden" name="tipo_matricula" value="transferencia">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="aluno_transferencia">Aluno *</label>
                                    <select class="form-control" id="aluno_transferencia" name="id_aluno" required>
                                        <option value="">Selecione um aluno</option>
                                        <!-- Lista de alunos já matriculados -->
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="ano_letivo_transferencia">Ano Letivo *</label>
                                    <select class="form-control" id="ano_letivo_transferencia" name="ano_letivo" required>
                                        <option value="<?= date('Y') ?>" selected><?= date('Y') ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="curso_origem">Curso de Origem</label>
                                    <input type="text" class="form-control" id="curso_origem" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="turma_origem">Turma de Origem</label>
                                    <input type="text" class="form-control" id="turma_origem" readonly>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="curso_destino">Novo Curso *</label>
                                    <select class="form-control" id="curso_destino" name="id_curso" required>
                                        <option value="">Selecione o novo curso</option>
                                        <?php foreach ($cursos as $curso): ?>
                                        <option value="<?= $curso['id_curso'] ?>"><?= htmlspecialchars($curso['nome']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="turma_destino">Nova Turma</label>
                                    <select class="form-control" id="turma_destino" name="id_turma">
                                        <option value="">Selecione um curso primeiro</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="motivo_transferencia">Motivo da Transferência *</label>
                            <textarea class="form-control" id="motivo_transferencia" name="observacoes" rows="3" required></textarea>
                        </div>
                        
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-info">Registrar Transferência</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="../libraries/bower_components/jquery/js/jquery.min.js"></script>
    <script src="../libraries/bower_components/jquery-ui/js/jquery-ui.min.js"></script>
    <script src="../libraries/bower_components/popper.js/js/popper.min.js"></script>
    <script src="../libraries/bower_components/bootstrap/js/bootstrap.min.js"></script>
    <script src="../libraries/bower_components/jquery-slimscroll/js/jquery.slimscroll.js"></script>
    <script src="../libraries/bower_components/modernizr/js/modernizr.js"></script>
    <script src="../libraries/assets/js/jquery.mCustomScrollbar.concat.min.js"></script>
    <script src="../libraries/assets/js/pcoded.min.js"></script>
    <script src="../libraries/assets/js/vartical-layout.min.js"></script>
    <script src="../libraries/assets/js/script.min.js"></script>

    <script>
    // Funções do Sistema
    function carregarTurmas(id_curso, elemento) {
        if(id_curso) {
            $.ajax({
                url: '../secretaria/getTurmas.php',
                method: 'GET',
                data: { id_curso: id_curso },
                success: function(response) {
                    $(elemento).html(response);
                },
                error: function() {
                    $(elemento).html('<option value="">Erro ao carregar turmas</option>');
                }
            });
        } else {
            $(elemento).html('<option value="">Selecione um curso primeiro</option>');
        }
    }
    
    function aprovarMatricula(id) {
        if(confirm('Deseja aprovar esta matrícula?')) {
            $.ajax({
                url: 'alterar_status_matricula.php',
                method: 'POST',
                data: { id: id, status: 'aprovada' },
                dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        alert('Matrícula aprovada com sucesso');
                        location.reload();
                    } else {
                        alert('Erro: ' + response.message);
                    }
                },
                error: function() {
                    alert('Erro na comunicação com o servidor');
                }
            });
        }
    }
    
    function rejeitarMatricula(id) {
        if(confirm('Deseja rejeitar esta matrícula?')) {
            $.ajax({
                url: 'alterar_status_matricula.php',
                method: 'POST',
                data: { id: id, status: 'rejeitada' },
                dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        alert('Matrícula rejeitada com sucesso');
                        location.reload();
                    } else {
                        alert('Erro: ' + response.message);
                    }
                },
                error: function() {
                    alert('Erro na comunicação com o servidor');
                }
            });
        }
    }
    
    function editarMatricula(id) {
        // Implementar lógica para edição
        alert('Editar matrícula ID: ' + id);
    }
    
    function emitirComprovante(id) {
        window.open('comprovante_matricula.php?id=' + id, '_blank');
    }
    
    // Carregar turmas quando um curso é selecionado
    $(document).ready(function() {
        $('#curso_matricula').change(function() {
            carregarTurmas($(this).val(), '#turma_matricula');
        });
        
        $('#curso_destino').change(function() {
            carregarTurmas($(this).val(), '#turma_destino');
        });
        
        // Carregar alunos matriculados para transferência
        $.ajax({
            url: 'get_alunos_matriculados.php',
            method: 'GET',
            data: { ano_letivo: $('#ano_letivo_transferencia').val() },
            success: function(response) {
                $('#aluno_transferencia').html(response);
            }
        });
        
        // Carregar dados do aluno selecionado para transferência
        $('#aluno_transferencia').change(function() {
            const id_aluno = $(this).val();
            if(id_aluno) {
                $.ajax({
                    url: 'get_dados_aluno.php',
                    method: 'GET',
                    data: { id_aluno: id_aluno },
                    dataType: 'json',
                    success: function(aluno) {
                        $('#curso_origem').val(aluno.curso || 'N/D');
                        $('#turma_origem').val(aluno.turma || 'N/D');
                    }
                });
            }
        });
    });
    </script>
</body>
</html>