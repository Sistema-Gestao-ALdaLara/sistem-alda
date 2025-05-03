<?php
require_once '../../includes/common/permissoes.php';
verificarPermissao(['secretaria', 'diretor_geral', 'diretor_pedagogico']);
require_once '../../process/verificar_sessao.php';
require_once '../../database/conexao.php';

$title = "Secretaria";

// Filtros recebidos via GET
$id_curso = isset($_GET['id_curso']) ? intval($_GET['id_curso']) : null;

// Query base para professores (atualizada para usar professor_tem_disciplina)
$query = "SELECT 
             p.id_professor,
             u.nome, 
             u.email,
             u.bi_numero,
             c.nome AS curso,
             c.id_curso,
             u.status,
             GROUP_CONCAT(DISTINCT d.nome SEPARATOR ', ') AS disciplinas
          FROM professor p
          JOIN usuario u ON p.usuario_id_usuario = u.id_usuario
          JOIN curso c ON p.curso_id_curso = c.id_curso
          LEFT JOIN professor_tem_disciplina ptd ON p.id_professor = ptd.professor_id_professor
          LEFT JOIN disciplina d ON ptd.disciplina_id_disciplina = d.id_disciplina";

// Filtros
$where = [];
$params = [];
$types = "";

if ($id_curso) {
    $where[] = "c.id_curso = ?";
    $params[] = $id_curso;
    $types .= "i";
}

if (!empty($where)) {
    $query .= " WHERE " . implode(" AND ", $where);
}

$query .= " GROUP BY p.id_professor, u.nome, u.email, u.bi_numero, c.nome, c.id_curso, u.status";
$query .= " ORDER BY u.nome ASC";

// Preparar e executar
$stmt = $conn->prepare($query);

if ($params) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$professores = $result->fetch_all(MYSQLI_ASSOC);

// Obter cursos
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
                                                <!-- Filtros (mantido igual) -->
                                                <div class="card card-table mb-3">
                                                    <div class="card-header">
                                                        <h5 class="text-white mb-0">Filtrar Professores</h5>
                                                    </div>
                                                    <div class="card-body">
                                                        <form id="formFiltros" method="GET" action="">
                                                            <div class="row">
                                                                <div class="col-md-10">
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
                                                                <div class="col-md-2">
                                                                    <button type="submit" class="btn btn-primary btn-filtrar">
                                                                        <i class="feather icon-filter"></i> Filtrar
                                                                    </button>
                                                                    <a href="professores.php" class="btn btn-limpar btn-secondary">
                                                                        <i class="feather icon-refresh-ccw"></i> Limpar
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                                
                                                <!-- Tabela de Professores (mantida igual) -->
                                                <div class="card card-table">
                                                    <div class="card-header d-flex justify-content-between align-items-center">
                                                        <h5 class="text-white mb-0">Lista de Professores</h5>
                                                        <div>
                                                            <button class="btn btn-primary mr-2" onclick="novoProfessor()" data-toggle="modal" data-target="#modalProfessor">
                                                                <i class="feather icon-plus"></i> Novo Professor
                                                            </button>
                                                            <button class="btn btn-info" onclick="exportarProfessores()">
                                                                <i class="feather icon-download"></i> Exportar
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="card-block">
                                                        <div class="table-responsive">
                                                            <table class="table table-custom" id="tabelaProfessores">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Nome</th>
                                                                        <th>BI</th>
                                                                        <th>Email</th>
                                                                        <th>Curso</th>
                                                                        <th>Disciplinas</th>
                                                                        <th>Status</th>
                                                                        <th>Ações</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php if (empty($professores)): ?>
                                                                    <tr>
                                                                        <td colspan="7" class="text-center">Nenhum professor encontrado</td>
                                                                    </tr>
                                                                    <?php else: ?>
                                                                    <?php foreach ($professores as $professor): ?>
                                                                    <tr>
                                                                        <td><?= htmlspecialchars($professor['nome']) ?></td>
                                                                        <td><?= htmlspecialchars($professor['bi_numero']) ?></td>
                                                                        <td><?= htmlspecialchars($professor['email']) ?></td>
                                                                        <td><?= htmlspecialchars($professor['curso']) ?></td>
                                                                        <td><?= htmlspecialchars($professor['disciplinas'] ?? 'Nenhuma disciplina') ?></td>
                                                                        <td>
                                                                            <span class="badge <?= $professor['status'] == 'ativo' ? 'badge-success' : 'badge-danger' ?>">
                                                                                <?= ucfirst($professor['status']) ?>
                                                                            </span>
                                                                        </td>
                                                                        <td class="action-buttons">
                                                                            <button class="btn btn-warning btn-sm" onclick="editarProfessor(<?= $professor['id_professor'] ?>)">
                                                                                <i class="feather icon-edit"></i>
                                                                            </button>
                                                                            <button class="btn btn-info btn-sm" onclick="verDisciplinas(<?= $professor['id_professor'] ?>)">
                                                                                <i class="feather icon-book"></i>
                                                                            </button>
                                                                            <button class="btn btn-danger btn-sm" onclick="confirmarExclusao(<?= $professor['id_professor'] ?>)">
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

                                                <!-- Modal Professor (mantido exatamente igual) -->
                                                <div class="modal fade" id="modalProfessor" tabindex="-1" role="dialog" aria-labelledby="modalProfessorLabel" aria-hidden="true">
                                                    <div class="modal-dialog modal-lg" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header bg-primary text-white">
                                                                <h5 class="modal-title" id="modalProfessorLabel">Novo Professor</h5>
                                                                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <form id="formProfessor" method="POST" action="../../actions/secretaria/salvar_professor.php">
                                                                    <input type="hidden" id="professorId" name="professorId">
                                                                    <input type="hidden" name="tipo" value="professor">

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
                                                                                <label for="senha">Senha *</label>
                                                                                <input type="password" class="form-control" id="senha" name="senha" required>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <div class="row">
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
                                                                        <div class="col-md-6">
                                                                            <div class="form-group">
                                                                                <label for="status">Status *</label>
                                                                                <select class="form-control" id="status" name="status" required>
                                                                                    <option value="ativo" selected>Ativo</option>
                                                                                    <option value="inativo">Inativo</option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <div class="row">
                                                                        <div class="col-md-12">
                                                                            <div class="form-group">
                                                                                <label for="disciplinas">Disciplinas</label>
                                                                                <select class="form-control" id="disciplinas" name="disciplinas[]">
                                                                                    <?php 
                                                                                    $disciplinas = $conn->query("SELECT id_disciplina, nome FROM disciplina ORDER BY nome");
                                                                                    while ($d = $disciplinas->fetch_assoc()): 
                                                                                    ?>
                                                                                    <option value="<?= $d['id_disciplina'] ?>"><?= htmlspecialchars($d['nome']) ?></option>
                                                                                    <?php endwhile; ?>
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
        // Funções do Sistema (mantidas iguais)
        function novoProfessor() {
            $('#formProfessor')[0].reset();
            $('#professorId').val('');
            $('#modalProfessorLabel').text('Novo Professor');
            $('#disciplinas').val(null);
        }
        
        function editarProfessor(id) {
            $.ajax({
                url: '../../process/consultas/getProfessor.php',
                method: 'GET',
                data: { id: id },
                dataType: 'json',
                success: function(professor) {
                    $('#professorId').val(professor.id_professor);
                    $('#nome').val(professor.nome);
                    $('#bi_numero').val(professor.bi_numero);
                    $('#email').val(professor.email);
                    $('#id_curso').val(professor.id_curso);
                    $('#status').val(professor.status);
                    
                    $('#senha').val('').removeAttr('required');
                    $('#modalProfessorLabel').text('Editar Professor: ' + professor.nome);
                    
                    // Carrega disciplinas do professor
                    $.ajax({
                        url: '../../process/consultas/get_disciplinas_professor.php',
                        method: 'GET',
                        data: { id_professor: id },
                        success: function(disciplinas) {
                            $('#disciplinas').val(disciplinas).trigger('change');
                        }
                    });
                    
                    $('#modalProfessor').modal('show');
                },
                error: function() {
                    alert('Erro ao carregar dados do professor');
                }
            });
        }
        
        function confirmarExclusao(id) {
            if(confirm('Tem certeza que deseja excluir este professor?')) {
                $.ajax({
                    url: '../../actions/secretaria/excluir_professor.php',
                    method: 'POST',
                    data: { id: id },
                    success: function(response) {
                        if(response.success) {
                            alert('Professor excluído com sucesso');
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
        
        function verDisciplinas(id) {
            window.open('../../process/consultas/disciplinas.php?id_professor=' + id, '_blank');
        }
        
        function exportarProfessores() {
            const id_curso = $('#filtro_curso').val() || '';
            window.open('../../process/secretaria/exportar_professores.php?id_curso=' + id_curso, '_blank');
        }
        
        $(document).ready(function() {
            // Validação do formulário
            $('#formProfessor').submit(function(e) {
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
            
            // Carregar disciplinas dinamicamente
            $('#id_curso').change(function() {
                const cursoId = $(this).val();
                if (cursoId) {
                    $.ajax({
                        url: '../../process/consultas/get_disciplinas.php',
                        method: 'GET',
                        data: { id_curso: cursoId },
                        success: function(data) {
                            $('#disciplinas').empty();
                            data.forEach(function(disciplina) {
                                $('#disciplinas').append(
                                    `<option value="${disciplina.id_disciplina}">${disciplina.nome}</option>`
                                );
                            });
                        }
                    });
                }
            });
        });
        
        function validarBI(bi) {
            const regex = /^[0-9]{9}[A-Z]{2}[0-9]{3}$/;
            return regex.test(bi);
        }
    </script>
</body>
</html>