<?php
    require_once '../../includes/common/permissoes.php';
    verificarPermissao(['secretaria']);
    require_once '../../process/verificar_sessao.php';
    require_once '../../database/conexao.php';

    // Filtros recebidos via GET
    $id_curso = isset($_GET['id_curso']) ? intval($_GET['id_curso']) : null;
    $id_turma = isset($_GET['id_turma']) ? intval($_GET['id_turma']) : null;

    // Query base
    $query = "SELECT 
                a.id_aluno,
                u.nome, 
                u.email,
                u.bi_numero,
                a.numero_matricula,
                t.nome AS turma,
                t.id_turma,
                c.nome AS curso,
                c.id_curso,
                a.ano_letivo,
                m.status_matricula
            FROM aluno a
            JOIN usuario u ON a.usuario_id_usuario = u.id_usuario
            LEFT JOIN turma t ON a.turma_id_turma = t.id_turma
            LEFT JOIN curso c ON a.curso_id_curso = c.id_curso
            LEFT JOIN matricula m ON m.aluno_id_aluno = a.id_aluno AND m.ano_letivo = a.ano_letivo";

    // Filtros
    $where = [];
    $params = [];
    $types = ""; // Tipos para bind_param

    if ($id_curso) {
        $where[] = "c.id_curso = ?";
        $params[] = $id_curso;
        $types .= "i";
    }

    if ($id_turma) {
        $where[] = "t.id_turma = ?";
        $params[] = $id_turma;
        $types .= "i";
    }

    if (!empty($where)) {
        $query .= " WHERE " . implode(" AND ", $where);
    }

    $query .= " ORDER BY u.nome ASC";

    // Preparar e executar
    $stmt = $conn->prepare($query);

    if ($params) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $alunos = $result->fetch_all(MYSQLI_ASSOC);

    // Obter cursos
    $result_cursos = $conn->query("SELECT id_curso, nome FROM curso ORDER BY nome");
    $cursos = $result_cursos->fetch_all(MYSQLI_ASSOC);

    // Obter turmas do curso (se selecionado)
    $turmas = [];
    if ($id_curso) {
        $stmt_turmas = $conn->prepare("SELECT id_turma, nome FROM turma WHERE curso_id_curso = ? ORDER BY nome");
        $stmt_turmas->bind_param("i", $id_curso);
        $stmt_turmas->execute();
        $result_turmas = $stmt_turmas->get_result();
        
        // Geração do HTML para as opções de turmas
        while ($turma = $result_turmas->fetch_assoc()) {
            echo "<option value='" . $turma['id_turma'] . "'>" . $turma['nome'] . "</option>";
        }
    }

    $title = "Secretaria";
?>


<!DOCTYPE html>
<html lang="pt">

<?php require_once '../../includes/common//head.php'; ?>

<body>
    <?php require_once '../../includes/common/preloader.php'; ?>

    <div id="pcoded" class="pcoded">
        <div class="pcoded-overlay-box"></div>
        <div class="pcoded-container navbar-wrapper">

            <?php require_once '../../includes/secretaria/navbar.php'; ?>

            <!--sidebar-->
            <div class="pcoded-main-container">
                <div class="pcoded-wrapper">
                    <?php require_once '../../includes/secretaria/sidebar.php'; ?>

                    <!-- Conteúdo Principal -->
                    <div class="pcoded-content">
                        <div class="pcoded-inner-content">
                            <div class="main-body bg-img">
                                <div class="page-wrapper">
                                    <div class="page-body">
                                        <div class="row">
                                            <div class="col-12 mt-4">
                                                <!-- Filtros -->
                                                <div class="card card-table mb-3">
                                                    <div class="card-header">
                                                        <h5 class="text-white mb-0">Filtrar Alunos</h5>
                                                    </div>
                                                    <div class="card-body">
                                                        <form id="formFiltros" method="GET" action="">
                                                            <div class="row">
                                                                <div class="col-md-5">
                                                                    <div class="form-group">
                                                                        <label for="filtro_curso">Curso</label>
                                                                        <select class="form-control" id="filtro_curso" name="id_curso">
                                                                            <option value="">Todos os cursos</option>
                                                                            <?php foreach ($cursos as $curso): ?>
                                                                            <option value="<?= $curso['id_curso'] ?>" <?= ($id_curso == $curso['id_curso']) ? 'selected' : '' ?>>
                                                                                <?= htmlspecialchars($curso['nome']) ?>
                                                                            </option>
                                                                            <?php endforeach; ?>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-5">
                                                                    <div class="form-group">
                                                                        <label for="filtro_turma">Turma</label>
                                                                        <select class="form-control" id="filtro_turma" name="id_turma" <?= empty($turmas) ? 'disabled' : '' ?>>
                                                                            <option value="">Todas as turmas</option>
                                                                            <?php if (!empty($turmas)): ?>
                                                                                <?php foreach ($turmas as $turma): ?>
                                                                                <option value="<?= $turma['id_turma'] ?>" <?= ($id_turma == $turma['id_turma']) ? 'selected' : '' ?>>
                                                                                    <?= htmlspecialchars($turma['nome']) ?>
                                                                                </option>
                                                                                <?php endforeach; ?>
                                                                            <?php endif; ?>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-2">
                                                                    <button type="submit" class="btn btn-primary btn-filtrar">
                                                                        <i class="feather icon-filter"></i> Filtrar
                                                                    </button>
                                                                    <a href="alunos.php" class="btn btn-limpar btn-secondary">
                                                                        <i class="feather icon-refresh-ccw"></i> Limpar
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                                
                                                <!-- Tabela de Alunos -->
                                                <div class="card card-table">
                                                    <div class="card-header d-flex justify-content-between align-items-center">
                                                        <h5 class="text-white mb-0">Lista de Alunos</h5>
                                                        <div>
                                                            <button class="btn btn-primary mr-2" onclick="novoAluno()" data-toggle="modal" data-target="#modalAluno">
                                                                <i class="feather icon-plus"></i> Novo Aluno
                                                            </button>
                                                            <button class="btn btn-info" onclick="exportarAlunos()">
                                                                <i class="feather icon-download"></i> Exportar
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="card-block">
                                                        <div class="table-responsive">
                                                            <table class="table table-custom" id="tabelaAlunos">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Matrícula</th>
                                                                        <th>Nome</th>
                                                                        <th>BI</th>
                                                                        <th>Turma</th>
                                                                        <th>Curso</th>
                                                                        <th>Ano Letivo</th>
                                                                        <th>Ações</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php if (empty($alunos)): ?>
                                                                    <tr>
                                                                        <td colspan="7" class="text-center">Nenhum aluno encontrado com os filtros selecionados</td>
                                                                    </tr>
                                                                    <?php else: ?>
                                                                    <?php foreach ($alunos as $aluno): ?>
                                                                    <tr>
                                                                        <td><?= htmlspecialchars($aluno['numero_matricula']) ?></td>   
                                                                        <td><?= htmlspecialchars($aluno['nome']) ?></td>
                                                                        <td><?= htmlspecialchars($aluno['bi_numero']) ?></td>
                                                                        <td><?= htmlspecialchars($aluno['turma'] ?? 'N/D') ?></td>
                                                                        <td><?= htmlspecialchars($aluno['curso'] ?? 'N/D') ?></td>
                                                                        <td><?= htmlspecialchars($aluno['ano_letivo']) ?></td>
                                                                        <td class="action-buttons">
                                                                            <button class="btn btn-warning btn-sm" onclick="editarAluno(<?= $aluno['id_aluno'] ?>)">
                                                                                <i class="feather icon-edit"></i>
                                                                            </button>
                                                                            <button class="btn btn-info btn-sm" onclick="verDocumentos(<?= $aluno['id_aluno'] ?>)">
                                                                                <i class="feather icon-file-text"></i>
                                                                            </button>
                                                                            <button class="btn btn-danger btn-sm" onclick="confirmarExclusao(<?= $aluno['id_aluno'] ?>)">
                                                                                <i class="feather icon-trash"></i>
                                                                            </button>
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
        </div>
    </div>

    <!-- Modal Aluno -->
    <div class="modal fade" id="modalAluno" tabindex="-1" role="dialog" aria-labelledby="modalAlunoLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalAlunoLabel">Novo Aluno</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="formAluno" method="POST" action="salvar_aluno.php">
                        <input type="hidden" id="alunoId" name="alunoId">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nome">Nome Completo *</label>
                                    <input type="text" class="form-control" id="nome" name="nome" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="bi_numero">Nº do BI *</label>
                                    <input type="text" class="form-control" id="bi_numero" name="bi_numero" 
                                           pattern="[0-9]{9}[A-Z]{2}[0-9]{3}" required>
                                    <small class="form-text text-muted">Formato: 123456789LA123</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">Email *</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="numero_matricula">Nº Matrícula *</label>
                                    <input type="text" class="form-control" id="numero_matricula" name="numero_matricula" 
                                           pattern="AL-/d{4}-/d{4}" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
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
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="id_turma">Turma</label>
                                    <select class="form-control" id="id_turma" name="id_turma">
                                        <option value="">Selecione um curso primeiro</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="ano_letivo">Ano Letivo *</label>
                                    <select class="form-control" id="ano_letivo" name="ano_letivo" required>
                                        <option value="<?= date('Y') ?>"><?= date('Y') ?></option>
                                        <option value="<?= date('Y')-1 ?>"><?= date('Y')-1 ?></option>
                                        <option value="<?= date('Y')+1 ?>"><?= date('Y')+1 ?></option>
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

    <!-- Scripts -->
    <?php require_once '../../includes/common/js_imports.php'; ?>

    <script>
        // Funções do Sistema
        function carregarTurmas(id_curso, turma_selecionada = null) {
            if(id_curso) {
                $.ajax({
                    url: '../../process/consultas/getTurma.php',
                    method: 'GET',
                    data: { id_curso: id_curso },
                    success: function(response) {
                        $('#id_turma').html(response);
                        $('#filtro_turma').html(response.replace('Selecione um curso primeiro', 'Todas as turmas'));
                        $('#filtro_turma').prop('disabled', false);
                        
                        if(turma_selecionada) {
                            setTimeout(function() {
                                $('#id_turma').val(turma_selecionada);
                            }, 500);
                        }
                    },
                    error: function() {
                        $('#id_turma').html('<option value="">Erro ao carregar turmas</option>');
                        $('#filtro_turma').html('<option value="">Erro ao carregar turmas</option>');
                    }
                });
            } else {
                $('#id_turma').html('<option value="">Selecione um curso primeiro</option>');
                $('#filtro_turma').html('<option value="">Todas as turmas</option>');
                $('#filtro_turma').prop('disabled', true);
            }
        }
        
        function novoAluno() {
            $('#formAluno')[0].reset();
            $('#alunoId').val('');
            $('#modalAlunoLabel').text('Novo Aluno');
            $('#numero_matricula').val('AL-' + new Date().getFullYear() + '-' + Math.floor(1000 + Math.random() * 9000));
            $('#id_curso').val('');
            $('#id_turma').html('<option value="">Selecione um curso primeiro</option>');
        }
        
        function editarAluno(id) {
            $.ajax({
                url: '../../process/consultas/getAluno.php',
                method: 'GET',
                data: { id: id },
                dataType: 'json',
                success: function(aluno) {
                    $('#alunoId').val(aluno.id_aluno);
                    $('#nome').val(aluno.nome);
                    $('#bi_numero').val(aluno.bi_numero);
                    $('#email').val(aluno.email);
                    $('#numero_matricula').val(aluno.numero_matricula);
                    $('#id_curso').val(aluno.id_curso).trigger('change');
                    $('#ano_letivo').val(aluno.ano_letivo);
                    
                    setTimeout(function() {
                        $('#id_turma').val(aluno.id_turma);
                    }, 500);
                    
                    $('#modalAlunoLabel').text('Editar Aluno: ' + aluno.nome);
                    $('#modalAluno').modal('show');
                },
                error: function() {
                    alert('Erro ao carregar dados do aluno');
                }
            });
        }
        
        function confirmarExclusao(id) {
            if(confirm('Tem certeza que deseja excluir este aluno?')) {
                $.ajax({
                    url: '../../actions/secretaria/excluir_aluno.php',
                    method: 'POST',
                    data: { id: id },
                    success: function(response) {
                        if(response.success) {
                            alert('Aluno excluído com sucesso');
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
        
        function verDocumentos(id) {
            window.open('../../process/consultas/documentos.php?id_aluno=' + id, '_blank');
        }
        
        function exportarAlunos() {
            // Passa os parâmetros de filtro para a exportação
            const id_curso = $('#filtro_curso').val() || '';
            const id_turma = $('#filtro_turma').val() || '';
            window.open('../../process/secretaria/exportar_alunos.php?id_curso=' + id_curso + '&id_turma=' + id_turma, '_blank');
        }
        
        // Eventos
        $(document).ready(function() {
            // Carrega turmas quando um curso é selecionado no modal
            $('#id_curso').change(function() {
                carregarTurmas($(this).val());
            });
            
            // Carrega turmas quando um curso é selecionado nos filtros
            $('#filtro_curso').change(function() {
                carregarTurmas($(this).val());
            });
            
            // Validação do formulário de aluno
            $('#formAluno').submit(function(e) {
                e.preventDefault();
                
                if(!validarBI($('#bi_numero').val())) {
                    alert('Número de BI inválido. Formato correto: 123456789LA123');
                    return false;
                }
                
                $.ajax({
                    url: $(this).attr('action'),
                    method: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if(response.success) {
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
        });
        
        function validarBI(bi) {
            const regex = /^[0-9]{9}[A-Z]{2}[0-9]{3}$/;
            return regex.test(bi);
        }
    </script>
</body>
</html>