<?php
    require_once '../../includes/common/permissoes.php';
    verificarPermissao(['diretor_geral']);
    require_once '../../process/verificar_sessao.php';
    require_once '../../database/conexao.php';
    
    $title = "D. Geral";

// Filtros recebidos via GET
$id_curso = isset($_GET['id_curso']) ? intval($_GET['id_curso']) : null;

// Query para coordenadores
$query = "SELECT 
             c.id_coordenador,
             u.nome, 
             u.email,
             u.bi_numero,
             cr.nome AS curso,
             cr.id_curso,
             u.status
          FROM coordenador c
          JOIN usuario u ON c.usuario_id_usuario = u.id_usuario
          JOIN curso cr ON c.curso_id_curso = cr.id_curso";

// Filtros
$where = [];
$params = [];
$types = "";

if ($id_curso) {
    $where[] = "cr.id_curso = ?";
    $params[] = $id_curso;
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
$coordenadores = $result->fetch_all(MYSQLI_ASSOC);

// Obter cursos
$result_cursos = $conn->query("SELECT id_curso, nome FROM curso ORDER BY nome");
$cursos = $result_cursos->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt">

<?php require_once '../../includes/common//head.php'; ?>

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
                                                <!-- Filtros -->
                                                <div class="card card-table mb-3">
                                                    <div class="card-header">
                                                        <h5 class="text-white mb-0">Filtrar Coordenadores</h5>
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
                                                                    <a href="coordenadores.php" class="btn btn-limpar btn-secondary">
                                                                        <i class="feather icon-refresh-ccw"></i> Limpar
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                                
                                                <!-- Tabela de Coordenadores -->
                                                <div class="card card-table">
                                                    <div class="card-header d-flex justify-content-between align-items-center">
                                                        <h5 class="text-white mb-0">Lista de Coordenadores</h5>
                                                        <div>
                                                            <button class="btn btn-primary mr-2" onclick="novoCoordenador()" data-toggle="modal" data-target="#modalCoordenador">
                                                                <i class="feather icon-plus"></i> Novo Coordenador
                                                            </button>
                                                            <button class="btn btn-info" onclick="exportarCoordenadores()">
                                                                <i class="feather icon-download"></i> Exportar
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="card-block">
                                                        <div class="table-responsive">
                                                            <table class="table table-custom" id="tabelaCoordenadores">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Nome</th>
                                                                        <th>BI</th>
                                                                        <th>Email</th>
                                                                        <th>Curso</th>
                                                                        <th>Status</th>
                                                                        <th>Ações</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php if (empty($coordenadores)): ?>
                                                                    <tr>
                                                                        <td colspan="6" class="text-center">Nenhum coordenador encontrado</td>
                                                                    </tr>
                                                                    <?php else: ?>
                                                                    <?php foreach ($coordenadores as $coordenador): ?>
                                                                    <tr>
                                                                        <td><?= htmlspecialchars($coordenador['nome']) ?></td>
                                                                        <td><?= htmlspecialchars($coordenador['bi_numero']) ?></td>
                                                                        <td><?= htmlspecialchars($coordenador['email']) ?></td>
                                                                        <td><?= htmlspecialchars($coordenador['curso']) ?></td>
                                                                        <td>
                                                                            <span class="badge <?= $coordenador['status'] == 'ativo' ? 'badge-success' : 'badge-danger' ?>">
                                                                                <?= ucfirst($coordenador['status']) ?>
                                                                            </span>
                                                                        </td>
                                                                        <td class="action-buttons">
                                                                            <!-- Botão Editar -->
                                                                            <button class="btn btn-warning btn-sm" onclick="editarCoordenador(<?= $coordenador['id_coordenador'] ?>)">
                                                                                <i class="feather icon-edit"></i>
                                                                            </button>
                                                                            
                                                                            <!-- Botão Excluir -->
                                                                            <button class="btn btn-danger btn-sm" onclick="confirmarExclusao(<?= $coordenador['id_coordenador'] ?>)">
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

                                                <!-- Modal Coordenador -->
                                                <div class="modal fade" id="modalCoordenador" tabindex="-1" role="dialog" aria-labelledby="modalCoordenadorLabel" aria-hidden="true">
                                                    <div class="modal-dialog modal-lg" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header bg-primary text-white">
                                                                <h5 class="modal-title" id="modalCoordenadorLabel">Novo Coordenador</h5>
                                                                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <form id="formCoordenador" method="POST" action="../../actions/diretor_geral/salvar_coordenador.php">
                                                                    <input type="hidden" id="coordenadorId" name="coordenadorId">
                                                                    <input type="hidden" name="tipo" value="coordenador">

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
        function novoCoordenador() {
            $('#formCoordenador')[0].reset();
            $('#coordenadorId').val('');
            $('#modalCoordenadorLabel').text('Novo Coordenador');
        }
        
        function editarCoordenador(id) {
            $.ajax({
                url: '../../process/consultas/getCoordenador.php',
                method: 'GET',
                data: { id: id },
                dataType: 'json',
                success: function(coordenador) {
                    $('#coordenadorId').val(coordenador.id_coordenador);
                    $('#nome').val(coordenador.nome);
                    $('#bi_numero').val(coordenador.bi_numero);
                    $('#email').val(coordenador.email);
                    $('#id_curso').val(coordenador.id_curso);
                    $('#status').val(coordenador.status);
                    
                    // Não preenche a senha por questões de segurança
                    $('#senha').val('');
                    $('#senha').removeAttr('required');
                    
                    $('#modalCoordenadorLabel').text('Editar Coordenador: ' + coordenador.nome);
                    $('#modalCoordenador').modal('show');
                },
                error: function() {
                    alert('Erro ao carregar dados do coordenador');
                }
            });
        }
        
        function confirmarExclusao(id) {
            if(confirm('Tem certeza que deseja excluir este coordenador?')) {
                $.ajax({
                    url: '../../action/diretor_geral/excluir_coordenador.php',
                    method: 'POST',
                    data: { id: id },
                    success: function(response) {
                        if(response.success) {
                            alert('Coordenador excluído com sucesso');
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
        
        function exportarCoordenadores() {
            const id_curso = $('#filtro_curso').val() || '';
            window.open('../../process/diretor_geral/exportar_coordenadores.php?id_curso=' + id_curso, '_blank');
        }
        
        // Validação do formulário de coordenador
        $('#formCoordenador').submit(function(e) {
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
        
        function validarBI(bi) {
            const regex = /^[0-9]{9}[A-Z]{2}[0-9]{3}$/;
            return regex.test(bi);
        }
    </script>
</body>
</html>