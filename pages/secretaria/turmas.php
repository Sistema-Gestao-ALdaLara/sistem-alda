<?php
require_once '../../includes/common/permissoes.php';
verificarPermissao(['diretor_pedagogico', 'coordenador', 'secretaria']);
require_once '../../process/verificar_sessao.php';
require_once '../../database/conexao.php';

// Filtros recebidos via GET
$id_curso = isset($_GET['id_curso']) ? intval($_GET['id_curso']) : null;
$ano_letivo = isset($_GET['ano_letivo']) ? intval($_GET['ano_letivo']) : date('Y');
$classe = isset($_GET['classe']) ? $_GET['classe'] : '';
$turno = isset($_GET['turno']) ? $_GET['turno'] : '';

// Query para listar turmas
$query = "SELECT 
             t.id_turma,
             t.nome AS nome_turma,
             t.classe,
             t.turno,
             c.nome AS nome_curso,
             c.id_curso,
             COUNT(DISTINCT m.id_matricula) AS total_alunos,
             GROUP_CONCAT(DISTINCT p.id_professor) AS professores_ids,
             GROUP_CONCAT(DISTINCT u.nome SEPARATOR ', ') AS professores_nomes
          FROM turma t
          JOIN curso c ON t.curso_id_curso = c.id_curso
          LEFT JOIN matricula m ON m.turma_id_turma = t.id_turma AND m.ano_letivo = ?
          LEFT JOIN professor_tem_turma pt ON pt.turma_id_turma = t.id_turma
          LEFT JOIN professor p ON pt.professor_id_professor = p.id_professor
          LEFT JOIN usuario u ON p.usuario_id_usuario = u.id_usuario";

$where = [];
$params = [$ano_letivo];
$types = "i";

if ($id_curso) {
    $where[] = "c.id_curso = ?";
    $params[] = $id_curso;
    $types .= "i";
}

if (!empty($classe)) {
    $where[] = "t.classe = ?";
    $params[] = $classe;
    $types .= "s";
}

if (!empty($turno)) {
    $where[] = "t.turno = ?";
    $params[] = $turno;
    $types .= "s";
}

if (!empty($where)) {
    $query .= " WHERE " . implode(" AND ", $where);
}

$query .= " GROUP BY t.id_turma, t.nome, t.classe, t.turno, c.nome, c.id_curso";
$query .= " ORDER BY c.nome, t.nome";

$stmt = $conn->prepare($query);

if ($params) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$turmas = $result->fetch_all(MYSQLI_ASSOC);

// Obter cursos para o filtro
$result_cursos = $conn->query("SELECT id_curso, nome FROM curso ORDER BY nome");
$cursos = $result_cursos->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt">

<?php require_once '../../includes/common/head.php'; ?>

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
                                            <div class="col-12 mt-4">
                                                <!-- Filtros -->
                                                <div class="card card-table mb-3">
                                                    <div class="card-header bg-primary text-white">
                                                        <h5 class="mb-0"><i class="feather icon-filter"></i> Filtrar Turmas</h5>
                                                    </div>
                                                    <div class="card-body">
                                                        <form id="formFiltros" method="GET" action="">
                                                            <div class="row">
                                                                <!-- Curso -->
                                                                <div class="col-md-3 mb-3">
                                                                    <label for="id_curso" class="form-label">Curso</label>
                                                                    <select class="form-control" id="id_curso" name="id_curso">
                                                                        <option value="">Todos os cursos</option>
                                                                        <?php foreach ($cursos as $curso): ?>
                                                                        <option value="<?= $curso['id_curso'] ?>" <?= $id_curso == $curso['id_curso'] ? 'selected' : '' ?>>
                                                                            <?= htmlspecialchars($curso['nome']) ?>
                                                                        </option>
                                                                        <?php endforeach; ?>
                                                                    </select>
                                                                </div>
                                                                
                                                                <!-- Ano Letivo -->
                                                                <div class="col-md-3 mb-3">
                                                                    <label for="ano_letivo" class="form-label">Ano Letivo</label>
                                                                    <input type="number" class="form-control" id="ano_letivo" name="ano_letivo" 
                                                                           min="2000" max="2050" value="<?= $ano_letivo ?>">
                                                                </div>
                                                                
                                                                <!-- Classe -->
                                                                <div class="col-md-3 mb-3">
                                                                    <label for="classe" class="form-label">Classe</label>
                                                                    <select class="form-control" id="classe" name="classe">
                                                                        <option value="">Todas as Classes</option>
                                                                        <option value="10ª" <?= $classe == '10ª' ? 'selected' : '' ?>>10ª Classe</option>
                                                                        <option value="11ª" <?= $classe == '11ª' ? 'selected' : '' ?>>11ª Classe</option>
                                                                        <option value="12ª" <?= $classe == '12ª' ? 'selected' : '' ?>>12ª Classe</option>
                                                                        <option value="13ª" <?= $classe == '13ª' ? 'selected' : '' ?>>13ª Classe</option>
                                                                    </select>
                                                                </div>
                                                                
                                                                <!-- Turno -->
                                                                <div class="col-md-3 mb-3">
                                                                    <label for="turno" class="form-label">Turno</label>
                                                                    <select class="form-control" id="turno" name="turno">
                                                                        <option value="">Todos os Turnos</option>
                                                                        <option value="Manha" <?= $turno == 'Manha' ? 'selected' : '' ?>>Manhã</option>
                                                                        <option value="Tarde" <?= $turno == 'Tarde' ? 'selected' : '' ?>>Tarde</option>
                                                                        <option value="Noite" <?= $turno == 'Noite' ? 'selected' : '' ?>>Noite</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="row">
                                                                <div class="col-md-12 text-right">
                                                                    <button type="submit" class="btn btn-primary">
                                                                        <i class="feather icon-filter"></i> Aplicar Filtros
                                                                    </button>
                                                                    <a href="turmas.php" class="btn btn-secondary">
                                                                        <i class="feather icon-refresh-ccw"></i> Limpar
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                                
                                                <!-- Tabela de Turmas -->
                                                <div class="card card-table">
                                                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                                        <h5 class="mb-0">Lista de Turmas - Ano Letivo <?= $ano_letivo ?></h5>
                                                        <div>
                                                            <button class="btn btn-light" onclick="novaTurma()" data-toggle="modal" data-target="#modalTurma">
                                                                <i class="feather icon-plus"></i> Nova Turma
                                                            </button>
                                                            <button class="btn btn-info ml-2" onclick="exportarTurmas()">
                                                                <i class="feather icon-download"></i> Exportar
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="table-responsive">
                                                            <table class="table table-hover">
                                                                <thead class="table-dark">
                                                                    <tr>
                                                                        <th>Nome</th>
                                                                        <th>Curso</th>
                                                                        <th>Classe</th>
                                                                        <th>Turno</th>
                                                                        <th>Alunos</th>
                                                                        <th>Professores</th>
                                                                        <th class="text-right">Ações</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php if (empty($turmas)): ?>
                                                                    <tr>
                                                                        <td colspan="7" class="text-center py-4">
                                                                            <div class="d-flex flex-column align-items-center">
                                                                                <i class="feather icon-search mb-2" style="font-size: 2rem;"></i>
                                                                                <p class="mb-0">Nenhuma turma encontrada com os filtros selecionados</p>
                                                                                <a href="turmas.php" class="btn btn-sm btn-link mt-2">Limpar filtros</a>
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                    <?php else: ?>
                                                                    <?php foreach ($turmas as $turma): ?>
                                                                    <tr>
                                                                        <td><?= htmlspecialchars($turma['nome_turma']) ?></td>
                                                                        <td><?= htmlspecialchars($turma['nome_curso']) ?></td>
                                                                        <td><?= htmlspecialchars($turma['classe']) ?></td>
                                                                        <td><?= htmlspecialchars($turma['turno']) ?></td>
                                                                        <td><?= $turma['total_alunos'] ?></td>
                                                                        <td><?= $turma['professores_nomes'] ? htmlspecialchars($turma['professores_nomes']) : 'Nenhum' ?></td>
                                                                        <td class="text-right">
                                                                            <div class="btn-group btn-group-sm" role="group">
                                                                                <button class="btn btn-outline-warning" onclick="editarTurma(<?= $turma['id_turma'] ?>)">
                                                                                    <i class="feather icon-edit"></i>
                                                                                </button>
                                                                                <button class="btn btn-outline-info" onclick="gerenciarProfessores(<?= $turma['id_turma'] ?>)">
                                                                                    <i class="feather icon-users"></i>
                                                                                </button>
                                                                                <button class="btn btn-outline-primary" onclick="gerenciarAlunos(<?= $turma['id_turma'] ?>)">
                                                                                    <i class="feather icon-list"></i>
                                                                                </button>
                                                                                <button class="btn btn-outline-danger" onclick="confirmarExclusao(<?= $turma['id_turma'] ?>)">
                                                                                    <i class="feather icon-trash"></i>
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

                                                <!-- Modal Turma -->
                                                <div class="modal fade" id="modalTurma" tabindex="-1" role="dialog" aria-labelledby="modalTurmaLabel" aria-hidden="true">
                                                    <div class="modal-dialog modal-lg" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header bg-primary text-white">
                                                                <h5 class="modal-title" id="modalTurmaLabel">Nova Turma</h5>
                                                                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fechar">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <form id="formTurma" method="POST" action="../../actions/secretaria/salvar_turma.php">
                                                                    <input type="hidden" id="turmaId" name="turmaId">
                                                                    
                                                                    <div class="row">
                                                                        <div class="col-md-6">
                                                                            <div class="form-group">
                                                                                <label for="nome">Nome da Turma *</label>
                                                                                <input type="text" class="form-control" id="nome" name="nome" required>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <div class="form-group">
                                                                                <label for="id_curso">Curso *</label>
                                                                                <select class="form-control" id="id_curso" name="id_curso" required>
                                                                                    <option value="">Selecione...</option>
                                                                                    <?php foreach ($cursos as $curso): ?>
                                                                                    <option value="<?= $curso['id_curso'] ?>"><?= htmlspecialchars($curso['nome']) ?></option>
                                                                                    <?php endforeach; ?>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <div class="row">
                                                                        <div class="col-md-6">
                                                                            <div class="form-group">
                                                                                <label for="classe">Classe *</label>
                                                                                <select class="form-control" id="classe" name="classe" required>
                                                                                    <option value="">Selecione...</option>
                                                                                    <option value="10ª">10ª Classe</option>
                                                                                    <option value="11ª">11ª Classe</option>
                                                                                    <option value="12ª">12ª Classe</option>
                                                                                    <option value="13ª">13ª Classe</option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <div class="form-group">
                                                                                <label for="turno">Turno *</label>
                                                                                <select class="form-control" id="turno" name="turno" required>
                                                                                    <option value="">Selecione...</option>
                                                                                    <option value="Manha">Manhã</option>
                                                                                    <option value="Tarde">Tarde</option>
                                                                                    <option value="Noite">Noite</option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                                                            <i class="feather icon-x"></i> Cancelar
                                                                        </button>
                                                                        <button type="submit" class="btn btn-primary">
                                                                            <i class="feather icon-save"></i> Salvar
                                                                        </button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Modal Professores da Turma -->
                                                <div class="modal fade" id="modalProfessoresTurma" tabindex="-1" role="dialog" aria-labelledby="modalProfessoresTurmaLabel" aria-hidden="true">
                                                    <div class="modal-dialog modal-lg" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header bg-primary text-white">
                                                                <h5 class="modal-title" id="modalProfessoresTurmaLabel">Gerenciar Professores</h5>
                                                                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fechar">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <form id="formProfessoresTurma" method="POST" action="../../actions/secretaria/salvar_professores_turma.php">
                                                                    <input type="hidden" id="turmaIdProfessores" name="turmaId">
                                                                    
                                                                    <div class="form-group">
                                                                        <label>Professores Disponíveis</label>
                                                                        <div id="listaProfessores" class="row"></div>
                                                                    </div>
                                                                    
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                                                            <i class="feather icon-x"></i> Cancelar
                                                                        </button>
                                                                        <button type="submit" class="btn btn-primary">
                                                                            <i class="feather icon-save"></i> Salvar
                                                                        </button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Modal Alunos da Turma -->
                                                <div class="modal fade" id="modalAlunosTurma" tabindex="-1" role="dialog" aria-labelledby="modalAlunosTurmaLabel" aria-hidden="true">
                                                    <div class="modal-dialog modal-xl" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header bg-primary text-white">
                                                                <h5 class="modal-title" id="modalAlunosTurmaLabel">Gerenciar Alunos</h5>
                                                                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fechar">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="row">
                                                                    <div class="col-md-6">
                                                                        <h6>Alunos na Turma</h6>
                                                                        <div class="table-responsive">
                                                                            <table class="table table-sm" id="tabelaAlunosTurma">
                                                                                <thead>
                                                                                    <tr>
                                                                                        <th>Nome</th>
                                                                                        <th>Matrícula</th>
                                                                                        <th>Ações</th>
                                                                                    </tr>
                                                                                </thead>
                                                                                <tbody id="listaAlunosTurma"></tbody>
                                                                            </table>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <h6>Adicionar Alunos</h6>
                                                                        <div class="form-group">
                                                                            <label for="filtroAluno">Filtrar Alunos</label>
                                                                            <input type="text" class="form-control" id="filtroAluno" placeholder="Digite para filtrar...">
                                                                        </div>
                                                                        <div class="table-responsive">
                                                                            <table class="table table-sm" id="tabelaAlunosDisponiveis">
                                                                                <thead>
                                                                                    <tr>
                                                                                        <th>Nome</th>
                                                                                        <th>Matrícula</th>
                                                                                        <th>Ações</th>
                                                                                    </tr>
                                                                                </thead>
                                                                                <tbody id="listaAlunosDisponiveis"></tbody>
                                                                            </table>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                                                    <i class="feather icon-x"></i> Fechar
                                                                </button>
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
    </div>

    <?php require_once '../../includes/common/js_imports.php'; ?>

    <script>
        // Funções do Sistema
        function novaTurma() {
            $('#formTurma')[0].reset();
            $('#turmaId').val('');
            $('#modalTurmaLabel').text('Nova Turma');
        }
        
        function editarTurma(id) {
            $.ajax({
                url: '../../process/consultas/get_turma.php',
                method: 'GET',
                data: { id: id },
                dataType: 'json',
                success: function(turma) {
                    $('#turmaId').val(turma.id_turma);
                    $('#nome').val(turma.nome);
                    $('#id_curso').val(turma.curso_id_curso);
                    $('#classe').val(turma.classe);
                    $('#turno').val(turma.turno);
                    
                    $('#modalTurmaLabel').text('Editar Turma: ' + turma.nome);
                    $('#modalTurma').modal('show');
                },
                error: function() {
                    alert('Erro ao carregar dados da turma');
                }
            });
        }
        
        function gerenciarProfessores(id) {
            $('#turmaIdProfessores').val(id);
            
            $.ajax({
                url: '../../process/consultas/get_professores_turma.php',
                method: 'GET',
                data: { id_turma: id },
                dataType: 'json',
                success: function(data) {
                    let html = '';
                    
                    data.professores.forEach(function(professor) {
                        html += `
                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" 
                                           id="prof_${professor.id_professor}" 
                                           name="professores[]" 
                                           value="${professor.id_professor}"
                                           ${professor.na_turma ? 'checked' : ''}>
                                    <label class="form-check-label" for="prof_${professor.id_professor}">
                                        ${professor.nome} (${professor.disciplinas || 'Sem disciplina'})
                                    </label>
                                </div>
                            </div>
                        `;
                    });
                    
                    $('#listaProfessores').html(html);
                    
                    $('#modalProfessoresTurmaLabel').text('Professores da Turma');
                    $('#modalProfessoresTurma').modal('show');
                },
                error: function() {
                    alert('Erro ao carregar professores');
                }
            });
        }
        
        function gerenciarAlunos(id) {
            const ano_letivo = $('#ano_letivo').val();
            
            // Carrega alunos na turma
            $.ajax({
                url: '../../process/consultas/get_alunos_turma.php',
                method: 'GET',
                data: { id_turma: id, ano_letivo: ano_letivo },
                dataType: 'json',
                success: function(alunos) {
                    let html = '';
                    
                    if (alunos.length > 0) {
                        alunos.forEach(function(aluno) {
                            html += `
                                <tr>
                                    <td>${aluno.nome}</td>
                                    <td>${aluno.numero_matricula}</td>
                                    <td>
                                        <button class="btn btn-danger btn-sm" onclick="removerAlunoTurma(${aluno.id_matricula})">
                                            <i class="feather icon-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            `;
                        });
                    } else {
                        html = '<tr><td colspan="3">Nenhum aluno nesta turma</td></tr>';
                    }
                    
                    $('#listaAlunosTurma').html(html);
                }
            });
            
            // Carrega alunos disponíveis (não na turma)
            $.ajax({
                url: '../../process/consultas/get_alunos_disponiveis.php',
                method: 'GET',
                data: { id_turma: id, ano_letivo: ano_letivo },
                dataType: 'json',
                success: function(alunos) {
                    let html = '';
                    
                    if (alunos.length > 0) {
                        alunos.forEach(function(aluno) {
                            html += `
                                <tr>
                                    <td>${aluno.nome}</td>
                                    <td>${aluno.numero_matricula}</td>
                                    <td>
                                        <button class="btn btn-success btn-sm" onclick="adicionarAlunoTurma(${aluno.id_matricula}, ${id})">
                                            <i class="feather icon-plus"></i>
                                        </button>
                                    </td>
                                </tr>
                            `;
                        });
                    } else {
                        html = '<tr><td colspan="3">Nenhum aluno disponível</td></tr>';
                    }
                    
                    $('#listaAlunosDisponiveis').html(html);
                }
            });
            
            $('#modalAlunosTurmaLabel').text('Alunos da Turma');
            $('#modalAlunosTurma').modal('show');
        }
        
        function adicionarAlunoTurma(id_matricula, id_turma) {
            const ano_letivo = $('#ano_letivo').val();
            
            $.ajax({
                url: '../../actions/secretaria/adicionar_aluno_turma.php',
                method: 'POST',
                data: { 
                    id_matricula: id_matricula,
                    id_turma: id_turma,
                    ano_letivo: ano_letivo
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        gerenciarAlunos(id_turma); // Recarrega a lista
                    } else {
                        alert('Erro: ' + response.message);
                    }
                }
            });
        }
        
        function removerAlunoTurma(id_matricula) {
            if (confirm('Tem certeza que deseja remover este aluno da turma?')) {
                $.ajax({
                    url: '../../actions/secretaria/remover_aluno_turma.php',
                    method: 'POST',
                    data: { id_matricula: id_matricula },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            const id_turma = $('#turmaIdProfessores').val();
                            gerenciarAlunos(id_turma); // Recarrega a lista
                        } else {
                            alert('Erro: ' + response.message);
                        }
                    }
                });
            }
        }
        
        function confirmarExclusao(id) {
            if (confirm('Tem certeza que deseja excluir esta turma?\nEsta ação não pode ser desfeita.')) {
                $.ajax({
                    url: '../../actions/secretaria/excluir_turma.php',
                    method: 'POST',
                    data: { id: id },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            alert('Turma excluída com sucesso');
                            location.reload();
                        } else {
                            alert('Erro ao excluir: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('Erro na comunicação com o servidor');
                    }
                });
            }
        }
        
        function exportarTurmas() {
            const id_curso = $('#id_curso').val() || '';
            const ano_letivo = $('#ano_letivo').val() || '';
            const classe = $('#classe').val() || '';
            const turno = $('#turno').val() || '';
            
            window.open(`../../process/secretaria/exportar_turmas.php?id_curso=${id_curso}&ano_letivo=${ano_letivo}&classe=${classe}&turno=${turno}`, '_blank');
        }

        // Filtro de alunos no modal
        $('#filtroAluno').on('keyup', function() {
            const value = $(this).val().toLowerCase();
            $('#tabelaAlunosDisponiveis tbody tr').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });

        // Validação do formulário
        $('#formTurma').submit(function(e) {
            e.preventDefault();
            
            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        location.reload();
                    } else {
                        alert('Erro: ' + response.message);
                    }
                },
                error: function() {
                    alert('Erro na comunicação com o servidor');
                }
            });
        });

        // Validação do formulário de professores
        $('#formProfessoresTurma').submit(function(e) {
            e.preventDefault();
            
            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        $('#modalProfessoresTurma').modal('hide');
                        location.reload();
                    } else {
                        alert('Erro: ' + response.message);
                    }
                },
                error: function() {
                    alert('Erro na comunicação com o servidor');
                }
            });
        });
    </script>
</body>
</html>