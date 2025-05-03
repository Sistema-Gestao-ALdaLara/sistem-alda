<?php
    require_once '../../includes/common/permissoes.php';
    verificarPermissao(['diretor_geral']);
    require_once '../../process/verificar_sessao.php';
    require_once '../../database/conexao.php';
    
    $title = "D. Geral - Cursos";

// Query para cursos
$query = "SELECT 
             id_curso,
             nome
          FROM curso";

// Ordenação
$query .= " ORDER BY nome ASC";

// Preparar e executar
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();
$cursos = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt">

<?php require_once '../../includes/common/head.php'; ?>

<body>
    <?php require_once '../../includes/common/preloader.php'; ?>

    <div id="pcoded" class="pcoded">
        <div class="pcoded-overlay-box"></div>
        <div class="pcoded-container navbar-wrapper">

            <?php require_once '../../includes/diretor_geral/navbar.php'; ?>

            <!--sidebar-->
            <div class="pcoded-main-container">
                <div class="pcoded-wrapper">
                    <?php require_once '../../includes/diretor_geral/sidebar.php'; ?>

                    <!-- Conteúdo Principal -->
                    <div class="pcoded-content">
                        <div class="pcoded-inner-content">
                            <div class="main-body bg-img">
                                <div class="page-wrapper">
                                    <div class="page-body">
                                        <div class="row">
                                            <div class="col-12 mt-4">
                                                <!-- Tabela de Cursos -->
                                                <div class="card card-table">
                                                    <div class="card-header d-flex justify-content-between align-items-center">
                                                        <h5 class="text-white mb-0">Lista de Cursos</h5>
                                                        <div>
                                                            <button class="btn btn-primary mr-2" onclick="novoCurso()" data-toggle="modal" data-target="#modalCurso">
                                                                <i class="feather icon-plus"></i> Novo Curso
                                                            </button>
                                                            <button class="btn btn-info" onclick="exportarCursos()">
                                                                <i class="feather icon-download"></i> Exportar
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="card-block">
                                                        <div class="table-responsive">
                                                            <table class="table table-custom" id="tabelaCursos">
                                                                <thead>
                                                                    <tr>
                                                                        <th>#</th>
                                                                        <th>Nome do Curso</th>
                                                                        <th>Ações</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php if (empty($cursos)): ?>
                                                                    <tr>
                                                                        <td colspan="3" class="text-center">Nenhum curso cadastrado</td>
                                                                    </tr>
                                                                    <?php else: ?>
                                                                    <?php foreach ($cursos as $index => $curso): ?>
                                                                    <tr>
                                                                        <td><?= $index + 1 ?></td>
                                                                        <td><?= htmlspecialchars($curso['nome']) ?></td>
                                                                        <td class="action-buttons">
                                                                            <!-- Botão Editar -->
                                                                            <button class="btn btn-warning btn-sm" onclick="editarCurso(<?= $curso['id_curso'] ?>)">
                                                                                <i class="feather icon-edit"></i>
                                                                            </button>
                                                                            
                                                                            <!-- Botão Excluir -->
                                                                            <button class="btn btn-danger btn-sm" onclick="confirmarExclusao(<?= $curso['id_curso'] ?>)">
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

                                                <!-- Modal Curso -->
                                                <div class="modal fade" id="modalCurso" tabindex="-1" role="dialog" aria-labelledby="modalCursoLabel" aria-hidden="true">
                                                    <div class="modal-dialog" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header bg-primary text-white">
                                                                <h5 class="modal-title" id="modalCursoLabel">Novo Curso</h5>
                                                                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <form id="formCurso" method="POST" action="../../actions/diretor_geral/salvar_curso.php">
                                                                    <input type="hidden" id="cursoId" name="cursoId">
                                                                    
                                                                    <div class="form-group">
                                                                        <label for="nome">Nome do Curso *</label>
                                                                        <input type="text" class="form-control" id="nome" name="nome" required>
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

    <!-- Scripts -->
    <?php require_once '../../includes/common/js_imports.php'; ?>

    <script>
        // Funções do Sistema
        function novoCurso() {
            $('#formCurso')[0].reset();
            $('#cursoId').val('');
            $('#modalCursoLabel').text('Novo Curso');
        }
        
        function editarCurso(id) {
            $.ajax({
                url: '../../process/consultas/getCurso.php',
                method: 'GET',
                data: { id: id },
                dataType: 'json',
                success: function(curso) {
                    $('#cursoId').val(curso.id_curso);
                    $('#nome').val(curso.nome);
                    
                    $('#modalCursoLabel').text('Editar Curso: ' + curso.nome);
                    $('#modalCurso').modal('show');
                },
                error: function() {
                    alert('Erro ao carregar dados do curso');
                }
            });
        }
        
        function confirmarExclusao(id) {
            if(confirm('Tem certeza que deseja excluir este curso?\n\nATENÇÃO: Esta ação também excluirá todas as turmas, disciplinas e registros associados a este curso.')) {
                $.ajax({
                    url: '../../actions/diretor_geral/excluir_curso.php',
                    method: 'POST',
                    data: { id: id },
                    dataType: 'json',
                    success: function(response) {
                        if(response.success) {
                            alert('Curso excluído com sucesso');
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
        
        function exportarCursos() {
            window.open('../../process/diretor_geral/exportar_cursos.php', '_blank');
        }
        
        // Validação do formulário de curso
        $('#formCurso').submit(function(e) {
            e.preventDefault();
            
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
    </script>
</body>
</html>