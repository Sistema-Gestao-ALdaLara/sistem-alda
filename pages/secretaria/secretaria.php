<?php
require_once '../../includes/common/permissoes.php';
verificarPermissao(['secretaria']);
require_once '../../process/verificar_sessao.php';
require_once '../../database/conexao.php';

// Verificar se a secretaria logada pode registrar outras
$podeRegistrar = false;
if (isset($_SESSION['id_usuario'])) {
    $sql = "SELECT s.pode_registrar 
            FROM secretaria s 
            JOIN usuario u ON s.usuario_id_usuario = u.id_usuario 
            WHERE u.id_usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_SESSION['id_usuario']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $podeRegistrar = (bool)$row['pode_registrar'];
    }
}

// Obter lista de secretarias
$query = "SELECT 
             s.id_secretaria,
             s.setor,
             s.pode_registrar,
             u.id_usuario,
             u.nome, 
             u.email,
             u.bi_numero,
             u.status,
             u.foto_perfil
          FROM secretaria s
          JOIN usuario u ON s.usuario_id_usuario = u.id_usuario
          ORDER BY u.nome";

$result = $conn->query($query);
$secretarias = $result->fetch_all(MYSQLI_ASSOC);
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
                                                <div class="card card-table">
                                                    <div class="card-header d-flex justify-content-between align-items-center">
                                                        <h5 class="text-white mb-0">Lista de Secretarias</h5>
                                                        <?php if ($podeRegistrar): ?>
                                                        <button class="btn btn-primary" onclick="novaSecretaria()" data-toggle="modal" data-target="#modalSecretaria">
                                                            <i class="feather icon-plus"></i> Nova Secretaria
                                                        </button>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="card-block">
                                                        <div class="table-responsive">
                                                            <table class="table table-custom">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Nome</th>
                                                                        <th>Setor</th>
                                                                        <th>BI</th>
                                                                        <th>Email</th>
                                                                        <th>Permissões</th>
                                                                        <th>Status</th>
                                                                        <th>Ações</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php if (empty($secretarias)): ?>
                                                                    <tr>
                                                                        <td colspan="7" class="text-center">Nenhuma secretaria encontrada</td>
                                                                    </tr>
                                                                    <?php else: ?>
                                                                    <?php foreach ($secretarias as $secretaria): 
                                                                        $ehUsuarioAtual = isset($_SESSION['id_usuario']) && $_SESSION['id_usuario'] == $secretaria['id_usuario'];
                                                                    ?>
                                                                    <tr>
                                                                        <td>
                                                                            <?= htmlspecialchars($secretaria['nome']) ?>
                                                                            <?php if ($ehUsuarioAtual): ?>
                                                                            <span class="badge badge-info">Você</span>
                                                                            <?php endif; ?>
                                                                        </td>
                                                                        <td><?= htmlspecialchars($secretaria['setor']) ?></td>
                                                                        <td><?= htmlspecialchars($secretaria['bi_numero']) ?></td>
                                                                        <td><?= htmlspecialchars($secretaria['email']) ?></td>
                                                                        <td>
                                                                            <?php if ($secretaria['pode_registrar']): ?>
                                                                            <span class="badge badge-registrador">Pode registrar</span>
                                                                            <?php endif; ?>
                                                                        </td>
                                                                        <td>
                                                                            <span class="badge <?= $secretaria['status'] == 'ativo' ? 'badge-success' : 'badge-danger' ?>">
                                                                                <?= ucfirst($secretaria['status']) ?>
                                                                            </span>
                                                                        </td>
                                                                        <td class="action-buttons">
                                                                            <button class="btn btn-warning btn-sm" onclick="editarSecretaria(<?= $secretaria['id_secretaria'] ?>, <?= (int)$ehUsuarioAtual ?>)">
                                                                                <i class="feather icon-edit"></i> Editar
                                                                            </button>
                                                                            <?php if (!$ehUsuarioAtual && $podeRegistrar): ?>
                                                                            <button class="btn btn-danger btn-sm" onclick="confirmarExclusao(<?= $secretaria['id_secretaria'] ?>)">
                                                                                <i class="feather icon-trash"></i> Excluir
                                                                            </button>
                                                                            <?php endif; ?>
                                                                        </td>
                                                                    </tr>
                                                                    <?php endforeach; ?>
                                                                    <?php endif; ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Modal Secretaria -->
                                                <div class="modal fade" id="modalSecretaria" tabindex="-1" role="dialog" aria-labelledby="modalSecretariaLabel" aria-hidden="true">
                                                    <div class="modal-dialog modal-lg" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header bg-primary text-white">
                                                                <h5 class="modal-title" id="modalSecretariaLabel">Nova Secretaria</h5>
                                                                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <form id="formSecretaria" method="POST" action="salvar_secretaria.php">
                                                                    <input type="hidden" id="secretariaId" name="secretariaId">
                                                                    <input type="hidden" id="usuarioId" name="usuarioId">
                                                                    <input type="hidden" name="tipo" value="secretaria">
                                                                    
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
                                                                                <label for="senha">Senha <span id="senhaObrigatoria">*</span></label>
                                                                                <input type="password" class="form-control" id="senha" name="senha">
                                                                                <small id="senhaHelp" class="form-text text-muted">Mínimo 8 caracteres</small>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <div class="row">
                                                                        <div class="col-md-6">
                                                                            <div class="form-group">
                                                                                <label for="setor">Setor *</label>
                                                                                <input type="text" class="form-control" id="setor" name="setor" required>
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
                                                                    
                                                                    <?php if ($podeRegistrar): ?>
                                                                    <div class="row">
                                                                        <div class="col-md-6">
                                                                            <div class="form-group">
                                                                                <div class="form-check">
                                                                                    <input class="form-check-input" type="checkbox" id="pode_registrar" name="pode_registrar">
                                                                                    <label class="form-check-label" for="pode_registrar">
                                                                                        Pode registrar outras secretarias
                                                                                    </label>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <?php endif; ?>
                                                                    
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
    function novaSecretaria() {
        $('#formSecretaria')[0].reset();
        $('#secretariaId').val('');
        $('#usuarioId').val('');
        $('#modalSecretariaLabel').text('Nova Secretaria');
        $('#senha').prop('required', true);
        $('#senhaObrigatoria').show();
        $('#modalSecretaria').modal('show');
    }
    
    function editarSecretaria(id, ehUsuarioAtual) {
        $.ajax({
            url: '../../../process/consultas/getSecretaria.php',
            method: 'GET',
            data: { id: id },
            dataType: 'json',
            success: function(secretaria) {
                $('#secretariaId').val(secretaria.id_secretaria);
                $('#usuarioId').val(secretaria.id_usuario);
                $('#nome').val(secretaria.nome);
                $('#bi_numero').val(secretaria.bi_numero);
                $('#email').val(secretaria.email);
                $('#setor').val(secretaria.setor);
                $('#status').val(secretaria.status);
                $('#pode_registrar').prop('checked', secretaria.pode_registrar == 1);
                
                // Não preenche a senha por questões de segurança
                $('#senha').val('');
                $('#senha').prop('required', false);
                $('#senhaObrigatoria').hide();
                
                if (ehUsuarioAtual) {
                    $('#pode_registrar').closest('.form-group').hide();
                    $('#modalSecretariaLabel').text('Editar Minha Conta');
                } else {
                    $('#modalSecretariaLabel').text('Editar Secretaria: ' + secretaria.nome);
                }
                
                $('#modalSecretaria').modal('show');
            },
            error: function() {
                alert('Erro ao carregar dados da secretaria');
            }
        });
    }
    
    function confirmarExclusao(id) {
        if(confirm('Tem certeza que deseja excluir esta secretaria?\nEsta ação não pode ser desfeita.')) {
            $.ajax({
                url: '../../../actions/secretaria/excluir_secretaria.php',
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

    // Validação do formulário
    $('#formSecretaria').submit(function(e) {
        e.preventDefault();
        
        if(!validarBI($('#bi_numero').val())) {
            alert('Número de BI inválido. Formato correto: 123456789LA123');
            return false;
        }
        
        // Se for novo, verifica senha
        if(!$('#secretariaId').val() && $('#senha').val().length < 8) {
            alert('A senha deve ter no mínimo 8 caracteres');
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