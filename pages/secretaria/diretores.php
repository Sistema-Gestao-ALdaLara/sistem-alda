<?php
    require_once '../../includes/common/permissoes.php';
    verificarPermissao(['secretaria']);
    require_once '../../process/verificar_sessao.php';

require_once '../../database/conexao.php';

// Verificar se já existe diretor geral e pedagógico
$sql = "SELECT id_usuario, nome, tipo FROM usuario WHERE tipo IN ('diretor_geral', 'diretor_pedagogico')";
$result = $conn->query($sql);
$diretoresExistentes = $result->fetch_all(MYSQLI_ASSOC);

$diretorGeralExistente = false;
$diretorPedagogicoExistente = false;

foreach ($diretoresExistentes as $diretor) {
    if ($diretor['tipo'] === 'diretor_geral') {
        $diretorGeralExistente = true;
        $idDiretorGeral = $diretor['id_usuario'];
    } elseif ($diretor['tipo'] === 'diretor_pedagogico') {
        $diretorPedagogicoExistente = true;
        $idDiretorPedagogico = $diretor['id_usuario'];
    }
}

// Query para listar diretores
$query = "SELECT 
             u.id_usuario,
             u.nome, 
             u.email,
             u.bi_numero,
             u.tipo,
             u.status
          FROM usuario u
          WHERE u.tipo IN ('diretor_geral', 'diretor_pedagogico')
          ORDER BY FIELD(u.tipo, 'diretor_geral', 'diretor_pedagogico'), u.nome";

$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();
$diretores = $result->fetch_all(MYSQLI_ASSOC);
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
                                                <!-- Tabela de Diretores -->
                                                <div class="card card-table">
                                                    <div class="card-header d-flex justify-content-between align-items-center">
                                                        <h5 class="text-white mb-0">Lista de Diretores</h5>
                                                        <div>
                                                            <button class="btn btn-primary mr-2" onclick="novoDiretor()" data-toggle="modal" data-target="#modalDiretor">
                                                                <i class="feather icon-plus"></i> Novo Diretor
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="card-block">
                                                        <div class="table-responsive">
                                                            <table class="table table-custom" id="tabelaDiretores">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Tipo</th>
                                                                        <th>Nome</th>
                                                                        <th>BI</th>
                                                                        <th>Email</th>
                                                                        <th>Status</th>
                                                                        <th>Ações</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php if (empty($diretores)): ?>
                                                                    <tr>
                                                                        <td colspan="6" class="text-center">Nenhum diretor encontrado</td>
                                                                    </tr>
                                                                    <?php else: ?>
                                                                    <?php foreach ($diretores as $diretor): ?>
                                                                    <tr>
                                                                        <td>
                                                                            <span class="badge badge-<?= $diretor['tipo'] === 'diretor_geral' ? 'diretor-geral' : 'diretor-pedagogico' ?>">
                                                                                <?= $diretor['tipo'] === 'diretor_geral' ? 'Diretor Geral' : 'Diretor Pedagógico' ?>
                                                                            </span>
                                                                        </td>
                                                                        <td><?= htmlspecialchars($diretor['nome']) ?></td>
                                                                        <td><?= htmlspecialchars($diretor['bi_numero']) ?></td>
                                                                        <td><?= htmlspecialchars($diretor['email']) ?></td>
                                                                        <td>
                                                                            <span class="badge <?= $diretor['status'] == 'ativo' ? 'badge-success' : 'badge-danger' ?>">
                                                                                <?= ucfirst($diretor['status']) ?>
                                                                            </span>
                                                                        </td>
                                                                        <td class="action-buttons">
                                                                            <button class="btn btn-warning btn-sm" onclick="editarDiretor(<?= $diretor['id_usuario'] ?>, '<?= $diretor['tipo'] ?>')">
                                                                                <i class="feather icon-edit"></i> Editar
                                                                            </button>
                                                                            <button class="btn btn-danger btn-sm" onclick="confirmarExclusao(<?= $diretor['id_usuario'] ?>, '<?= $diretor['tipo'] === 'diretor_geral' ? 'Diretor Geral' : 'Diretor Pedagógico' ?>')">
                                                                                <i class="feather icon-trash"></i> Excluir
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
                                                <!-- Modal Diretor -->
                                                <div class="modal fade" id="modalDiretor" tabindex="-1" role="dialog" aria-labelledby="modalDiretorLabel" aria-hidden="true">
                                                    <div class="modal-dialog modal-lg" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header bg-primary text-white">
                                                                <h5 class="modal-title" id="modalDiretorLabel">Novo Diretor</h5>
                                                                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <form id="formDiretor" method="POST" action="salvar_diretor.php">
                                                                    <input type="hidden" id="diretorId" name="diretorId">
                                                                    
                                                                    <div class="row">
                                                                        <div class="col-md-6">
                                                                            <div class="form-group">
                                                                                <label for="tipo">Tipo de Diretor *</label>
                                                                                <select class="form-control" id="tipo" name="tipo" required>
                                                                                    <option value="">Selecione...</option>
                                                                                    <option value="diretor_geral" <?= $diretorGeralExistente ? 'disabled' : '' ?>>Diretor Geral</option>
                                                                                    <option value="diretor_pedagogico" <?= $diretorPedagogicoExistente ? 'disabled' : '' ?>>Diretor Pedagógico</option>
                                                                                </select>
                                                                                <?php if ($diretorGeralExistente || $diretorPedagogicoExistente): ?>
                                                                                <small class="form-text text-warning">
                                                                                    <?= $diretorGeralExistente ? 'Já existe um Diretor Geral cadastrado.<br>' : '' ?>
                                                                                    <?= $diretorPedagogicoExistente ? 'Já existe um Diretor Pedagógico cadastrado.<br>' : '' ?>
                                                                                    Para cadastrar um novo, primeiro exclua o existente.
                                                                                </small>
                                                                                <?php endif; ?>
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
                                                                                <label for="senha">Senha <?= isset($diretorId) ? '(Deixe em branco para manter a atual)' : '*' ?></label>
                                                                                <input type="password" class="form-control" id="senha" name="senha" <?= !isset($diretorId) ? 'required' : '' ?>>
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
    <script src="libraries/bower_components/jquery/js/jquery.min.js"></script>
    <script src="libraries/bower_components/jquery-ui/js/jquery-ui.min.js"></script>
    <script src="libraries/bower_components/popper.js/js/popper.min.js"></script>
    <script src="libraries/bower_components/bootstrap/js/bootstrap.min.js"></script>
    <script src="libraries/bower_components/jquery-slimscroll/js/jquery.slimscroll.js"></script>
    <script src="libraries/bower_components/modernizr/js/modernizr.js"></script>
    <script src="libraries/assets/js/jquery.mCustomScrollbar.concat.min.js"></script>
    <script src="libraries/assets/js/pcoded.min.js"></script>
    <script src="libraries/assets/js/vartical-layout.min.js"></script>
    <script src="libraries/assets/js/script.min.js"></script>

    <script>
        // Funções do Sistema
        function novoDiretor() {
            $('#formDiretor')[0].reset();
            $('#diretorId').val('');
            $('#modalDiretorLabel').text('Novo Diretor');
            $('#tipo').prop('disabled', false);
        }
        
        function editarDiretor(id, tipo) {
            $.ajax({
                url: '../../../process/consultas/getDiretor.php',
                method: 'GET',
                data: { id: id },
                dataType: 'json',
                success: function(diretor) {
                    $('#diretorId').val(diretor.id_usuario);
                    $('#nome').val(diretor.nome);
                    $('#bi_numero').val(diretor.bi_numero);
                    $('#email').val(diretor.email);
                    $('#tipo').val(diretor.tipo);
                    $('#status').val(diretor.status);
                    
                    // Não preenche a senha por questões de segurança
                    $('#senha').val('');
                    $('#senha').removeAttr('required');
                    
                    // Desabilita o campo tipo na edição
                    $('#tipo').prop('disabled', true);
                    
                    $('#modalDiretorLabel').text('Editar Diretor: ' + diretor.nome);
                    $('#modalDiretor').modal('show');
                },
                error: function() {
                    alert('Erro ao carregar dados do diretor');
                }
            });
        }
        
        function confirmarExclusao(id) {
            if(confirm('Tem certeza que deseja excluir este diretor?\nEsta ação não pode ser desfeita.')) {
                $.ajax({
                    url: '../../../actions/secretaria/excluir_diretor.php',
                    method: 'POST',
                    data: { id: id },
                    dataType: 'json',
                    success: function(response) {
                        if(response.success) {
                            alert(response.message);
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
        
        // Validação do formulário de diretor
        $('#formDiretor').submit(function(e) {
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