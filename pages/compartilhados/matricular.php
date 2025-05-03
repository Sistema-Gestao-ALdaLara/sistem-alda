<?php
require_once '../../includes/common/permissoes.php';
verificarPermissao(['secretaria', 'diretor_geral', 'diretor_pedagogico']);
require_once '../../process/verificar_sessao.php';
require_once '../../database/conexao.php';

$title = "Secretaria - Matrículas";

// Filtros
$status = isset($_GET['status']) ? $_GET['status'] : 'ativa';
$id_curso = isset($_GET['id_curso']) ? intval($_GET['id_curso']) : null;
$ano_letivo = isset($_GET['ano_letivo']) ? intval($_GET['ano_letivo']) : date('Y');

// Obter matrículas com filtros
$query = "SELECT m.id_matricula, a.id_aluno, u.nome, u.bi_numero, 
        c.nome AS curso, t.nome AS turma, t.turno, m.data_matricula, 
        m.ano_letivo, t.classe, m.numero_matricula,
        m.status_matricula
        FROM matricula m
        JOIN aluno a ON m.aluno_id_aluno = a.id_aluno
        JOIN usuario u ON a.usuario_id_usuario = u.id_usuario
        LEFT JOIN turma t ON m.turma_id_turma = t.id_turma
        LEFT JOIN curso c ON t.curso_id_curso = c.id_curso
        WHERE m.ano_letivo = ?";

$params = [$ano_letivo];
$types = "i";

if ($status != 'todos') {
    $query .= " AND m.status_matricula = ?";
    $params[] = $status;
    $types .= "s";
}

if ($id_curso) {
    $query .= " AND m.curso_id_curso = ?";
    $params[] = $id_curso;
    $types .= "i";
}

$query .= " ORDER BY m.data_matricula DESC";

$stmt = $conn->prepare($query);

if ($params) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$matriculas = $result->fetch_all(MYSQLI_ASSOC);

// Obter cursos para filtros
$result_cursos = $conn->query("SELECT id_curso, nome FROM curso ORDER BY nome");
$cursos = $result_cursos->fetch_all(MYSQLI_ASSOC);

// Obter turmas para select
$turmas = [];
if ($result_turmas = $conn->query("SELECT id_turma, nome, classe, turno, curso_id_curso FROM turma")) {
    $turmas = $result_turmas->fetch_all(MYSQLI_ASSOC);
}

$tipo = $_SESSION['tipo_usuario'];
?>

<!DOCTYPE html>
<html lang="pt">

<?php require_once '../../includes/common/head.php'; ?>

<body>
    <?php require_once '../../includes/common/preloader.php'; ?>

    <div id="pcoded" class="pcoded">
        <div class="pcoded-overlay-box"></div>
        <div class="pcoded-container navbar-wrapper">

            <?php require_once "../../includes/$tipo/navbar.php"; ?>

            <!--sidebar-->
            <div class="pcoded-main-container">
                <div class="pcoded-wrapper">
                    <?php require_once "../../includes/$tipo/sidebar.php"; ?>

                    <!-- Conteúdo Principal -->
                    <div class="pcoded-content">
                        <div class="pcoded-inner-content">
                            <div class="main-body bg-img">
                                <div class="page-wrapper">
                                    <div class="page-body">
                                        <!-- Mensagens de feedback -->
                                        <?php if (isset($_SESSION['sucesso'])): ?>
                                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                                            <?= $_SESSION['sucesso'] ?>
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <?php unset($_SESSION['sucesso']); endif; ?>
                                        
                                        <?php if (isset($_SESSION['erro'])): ?>
                                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                            <?= $_SESSION['erro'] ?>
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <?php unset($_SESSION['erro']); endif; ?>
                                        
                                        <div class="row">
                                            <div class="col-12 mt-4">
                                                <!-- Filtros -->
                                                <div class="card card-table mb-3">
                                                    <div class="card-header">
                                                        <h5 class="text-white mb-0">Filtrar Matrículas</h5>
                                                    </div>
                                                    <div class="card-body">
                                                        <form id="formFiltros" method="GET" action="">
                                                            <div class="row">
                                                                <div class="col-md-4">
                                                                    <div class="form-group">
                                                                        <label for="status">Status</label>
                                                                        <select class="form-control" id="status" name="status">
                                                                            <option value="ativa" <?= $status == 'ativa' ? 'selected' : '' ?>>Ativas</option>
                                                                            <option value="cancelada" <?= $status == 'cancelada' ? 'selected' : '' ?>>Canceladas</option>
                                                                            <option value="trancada" <?= $status == 'trancada' ? 'selected' : '' ?>>Trancadas</option>
                                                                            <option value="todos" <?= $status == 'todos' ? 'selected' : '' ?>>Todos</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div class="form-group">
                                                                        <label for="ano_letivo">Ano Letivo</label>
                                                                        <select class="form-control" id="ano_letivo" name="ano_letivo">
                                                                            <option value="<?= date('Y')-1 ?>"><?= date('Y')-1 ?></option>
                                                                            <option value="<?= date('Y') ?>" selected><?= date('Y') ?></option>
                                                                            <option value="<?= date('Y')+1 ?>"><?= date('Y')+1 ?></option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-12 text-right">
                                                                    <button type="submit" class="btn btn-primary">
                                                                        <i class="feather icon-filter"></i> Filtrar
                                                                    </button>
                                                                    <a href="matricula.php" class="btn btn-secondary">
                                                                        <i class="feather icon-refresh-ccw"></i> Limpar
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                                
                                                <!-- Tabela de Matrículas -->
                                                <div class="card card-table">
                                                    <div class="card-header d-flex justify-content-between align-items-center">
                                                        <h5 class="text-white mb-0">Lista de Matrículas</h5>
                                                        <div>
                                                            <button class="btn btn-primary" data-toggle="modal" data-target="#modalMatricula">
                                                                <i class="feather icon-plus"></i> Nova Matrícula
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="card-block">
                                                        <div class="table-responsive">
                                                            <table class="table table-custom" id="tabelaMatriculas">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Nº Matrícula</th>
                                                                        <th>Aluno</th>
                                                                        <th>BI</th>
                                                                        <th>Curso</th>
                                                                        <th>Turma</th>
                                                                        <th>Classe</th>
                                                                        <th>Data</th>
                                                                        <th>Status</th>
                                                                        <th>Ações</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php if (empty($matriculas)): ?>
                                                                    <tr>
                                                                        <td colspan="9" class="text-center">Nenhuma matrícula encontrada</td>
                                                                    </tr>
                                                                    <?php else: ?>
                                                                    <?php foreach ($matriculas as $matricula): ?>
                                                                    <tr>
                                                                        <td><?= htmlspecialchars($matricula['numero_matricula']) ?></td>
                                                                        <td><?= htmlspecialchars($matricula['nome']) ?></td>
                                                                        <td><?= htmlspecialchars($matricula['bi_numero']) ?></td>
                                                                        <td><?= htmlspecialchars($matricula['curso'] ?? 'N/D') ?></td>
                                                                        <td><?= htmlspecialchars($matricula['turma'] ?? 'N/D') ?></td>
                                                                        <td><?= htmlspecialchars($matricula['classe'] ?? 'N/D') ?></td>
                                                                        <td><?= date('d/m/Y', strtotime($matricula['data_matricula'])) ?></td>
                                                                        <td>
                                                                            <span class="badge <?= $matricula['status_matricula'] == 'ativa' ? 'badge-success' : ($matricula['status_matricula'] == 'cancelada' ? 'badge-danger' : 'badge-warning') ?>">
                                                                                <?= ucfirst($matricula['status_matricula']) ?>
                                                                            </span>
                                                                        </td>
                                                                        <td>
                                                                            <button class="btn btn-warning btn-sm" onclick="editarMatricula(<?= $matricula['id_matricula'] ?>)">
                                                                                <i class="feather icon-edit-2"></i>
                                                                            </button>
                                                                            <button class="btn btn-info btn-sm" onclick="emitirComprovante(<?= $matricula['id_matricula'] ?>)">
                                                                                <i class="feather icon-printer"></i>
                                                                            </button>
                                                                            <?php if ($matricula['status_matricula'] == 'ativa'): ?>
                                                                            <button class="btn btn-danger btn-sm" onclick="cancelarMatricula(<?= $matricula['id_matricula'] ?>)">
                                                                                <i class="feather icon-x"></i>
                                                                            </button>
                                                                            <?php else: ?>
                                                                            <button class="btn btn-success btn-sm" onclick="reativarMatricula(<?= $matricula['id_matricula'] ?>)">
                                                                                <i class="feather icon-check"></i>
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

        <!-- Modal Matrícula -->
        <div class="modal fade" id="modalMatricula" tabindex="-1" role="dialog" aria-labelledby="modalMatriculaLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="modalMatriculaLabel">Nova Matrícula</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="formMatricula" method="POST" action="../../actions/processar_matricula.php" enctype="multipart/form-data">
                            <h4 class="sub-title">Dados Pessoais</h4>
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
                                        <small class="form-text text-muted">Mínimo 6 caracteres</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="data_nascimento">Data de Nascimento *</label>
                                        <input type="date" class="form-control" id="data_nascimento" name="data_nascimento" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="genero">Gênero *</label>
                                        <select class="form-control" id="genero" name="genero" required>
                                            <option value="">Selecione...</option>
                                            <option value="Masculino">Masculino</option>
                                            <option value="Feminino">Feminino</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="naturalidade">Naturalidade *</label>
                                        <input type="text" class="form-control" id="naturalidade" name="naturalidade" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="nacionalidade">Nacionalidade *</label>
                                        <input type="text" class="form-control" id="nacionalidade" name="nacionalidade" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="municipio">Município *</label>
                                        <input type="text" class="form-control" id="municipio" name="municipio" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="foto_perfil">Foto de Perfil</label>
                                        <input type="file" class="form-control-file" id="foto_perfil" name="foto_perfil" accept="image/*">
                                        <small class="form-text text-muted">Tamanho máximo: 2MB (JPEG, PNG)</small>
                                    </div>
                                </div>
                            </div>
                            
                            <h4 class="sub-title mt-4">Dados do Encarregado</h4>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="nome_encarregado">Nome do Encarregado *</label>
                                        <input type="text" class="form-control" id="nome_encarregado" name="nome_encarregado" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="contacto_encarregado">Contacto do Encarregado *</label>
                                        <input type="text" class="form-control" id="contacto_encarregado" name="contacto_encarregado" required>
                                    </div>
                                </div>
                            </div>
                            
                            <h4 class="sub-title mt-4">Dados da Matrícula</h4>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="ano_letivo">Ano Letivo *</label>
                                        <select class="form-control" id="ano_letivo" name="ano_letivo" required>
                                            <?php for ($i = date('Y')-1; $i <= date('Y')+1; $i++): ?>
                                            <option value="<?= $i ?>" <?= $i == date('Y') ? 'selected' : '' ?>><?= $i ?></option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="id_curso">Curso *</label>
                                        <select class="form-control" id="id_curso" name="id_curso" required>
                                            <option value="">Selecione um curso...</option>
                                            <?php foreach ($cursos as $curso): ?>
                                            <option value="<?= $curso['id_curso'] ?>"><?= htmlspecialchars($curso['nome']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="id_turma">Turma *</label>
                                        <select class="form-control" id="id_turma" name="id_turma" required disabled>
                                            <option value=""></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="classe">Classe *</label>
                                        <select class="form-control" id="classe" name="classe" required disabled>
                                            <option value="">Selecione uma turma primeiro</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="comprovativo">Comprovativo de Pagamento *</label>
                                        <input type="file" class="form-control-file" id="comprovativo" name="comprovativo" accept=".pdf,.jpg,.jpeg,.png" required>
                                        <small class="form-text text-muted">Formatos aceites: PDF, JPG, PNG</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                    <i class="feather icon-x"></i> Cancelar
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="feather icon-save"></i> Salvar Matrícula
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
            // Carregar turmas quando um curso é selecionado
            $('#id_curso').change(function() {
                const id_curso = $(this).val();
                const $turmaSelect = $('#id_turma');
                const $classeSelect = $('#classe');
                
                if (id_curso) {
                    $.ajax({
                        url: '../../process/consultas/getTurmasByCurso.php',
                        method: 'GET',
                        data: { id_curso: id_curso },
                        dataType: 'html',
                        success: function(response) {
                            $turmaSelect.html(response);
                            $turmaSelect.prop('disabled', false);
                            
                            if ($turmaSelect.find('option').length === 2) {
                                $turmaSelect.val($turmaSelect.find('option:last').val()).trigger('change');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Erro ao carregar turmas:', error);
                            $turmaSelect.html('<option value="">Erro ao carregar turmas</option>');
                        }
                    });
                } else {
                    $turmaSelect.html('<option value="">Selecione um curso primeiro</option>');
                    $classeSelect.html('<option value="">Selecione uma turma primeiro</option>').prop('disabled', true);
                }
            });

            // Atualizar classe quando uma turma é selecionada
            $('#id_turma').change(function() {
                const selectedOption = $(this).find('option:selected');
                const $classeSelect = $('#classe');
                
                if (selectedOption.val()) {
                    const classe = selectedOption.data('classe');
                    $classeSelect.empty().append(`<option value="${classe}" selected>${classe}</option>`);
                    $classeSelect.prop('disabled', false);
                } else {
                    $classeSelect.empty().append('<option value="">Selecione uma turma primeiro</option>');
                    $classeSelect.prop('disabled', true);
                }
            });
            
            function emitirComprovante(id) {
                window.open('../../process/secretaria/comprovante_matricula.php?id=' + id, '_blank');
            }
            
            function cancelarMatricula(id) {
                if (confirm('Tem certeza que deseja cancelar esta matrícula?')) {
                    $.ajax({
                        url: '../../actions/secretaria/cancelar_matricula.php',
                        method: 'POST',
                        data: { id: id },
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
                }
            }
            
            function reativarMatricula(id) {
                if (confirm('Tem certeza que deseja reativar esta matrícula?')) {
                    $.ajax({
                        url: '../../actions/secretaria/reativar_matricula.php',
                        method: 'POST',
                        data: { id: id },
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
                }
            }
            
            // Validação do formulário
           /* $('#formMatricula').submit(function(e) {
                e.preventDefault();
                
                // Validar BI
                if (!validarBI($('#bi_numero').val())) {
                    alert('Número de BI inválido. Formato correto: 123456789LA123');
                    return false;
                }
                
                // Validar senha
                if ($('#senha').val().length < 6) {
                    alert('A senha deve ter no mínimo 6 caracteres');
                    return false;
                }
                
                // Validar data de nascimento
                const dataNasc = new Date($('#data_nascimento').val());
                const hoje = new Date();
                let idade = hoje.getFullYear() - dataNasc.getFullYear();
                const mes = hoje.getMonth() - dataNasc.getMonth();
                
                if (mes < 0 || (mes === 0 && hoje.getDate() < dataNasc.getDate())) {
                    idade--;
                }
                
                if (idade < 15) {
                    alert('O aluno deve ter pelo menos 15 anos de idade');
                    return false;
                }
                
                // Criar FormData para enviar arquivos
                var formData = new FormData(this);
                
                $.ajax({
                    url: $(this).attr('action'),
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            alert(response.message);
                            $('#modalMatricula').modal('hide');
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
            }*/
        </script>

        <script>
            function editarMatricula(id) {
                // Carregar dados da matrícula via AJAX
                $.ajax({
                    url: '../../process/secretaria/obter_dados_matricula.php',
                    method: 'GET',
                    data: { id: id },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            // Preencher o formulário com os dados obtidos
                            const dados = response.dados;
                            
                            // Dados pessoais
                            $('#nome').val(dados.nome);
                            $('#bi_numero').val(dados.bi_numero);
                            $('#email').val(dados.email);
                            $('#data_nascimento').val(dados.data_nascimento);
                            $('#genero').val(dados.genero);
                            $('#naturalidade').val(dados.naturalidade);
                            $('#nacionalidade').val(dados.nacionalidade);
                            $('#municipio').val(dados.municipio);
                            
                            // Dados do encarregado
                            $('#nome_encarregado').val(dados.nome_encarregado);
                            $('#contacto_encarregado').val(dados.contacto_encarregado);
                            
                            // Dados da matrícula
                            $('#ano_letivo').val(dados.ano_letivo);
                            $('#id_curso').val(dados.id_curso).trigger('change');
                            
                            // Adicionar campo oculto com ID da matrícula para atualização
                            if (!$('#id_matricula').length) {
                                $('<input>').attr({
                                    type: 'hidden',
                                    id: 'id_matricula',
                                    name: 'id_matricula',
                                    value: id
                                }).appendTo('#formMatricula');
                            } else {
                                $('#id_matricula').val(id);
                            }
                            
                            // Modificar o título e o botão do modal
                            $('#modalMatriculaLabel').text('Editar Matrícula');
                            $('#formMatricula button[type="submit"]').html('<i class="feather icon-save"></i> Atualizar Matrícula');
                            
                            // Definir o campo de senha como opcional na edição
                            $('#senha').removeAttr('required');
                            $('<small class="form-text text-muted">Deixe em branco para manter a senha atual</small>').insertAfter('#senha');
                            
                            // Definir o campo de comprovativo como opcional na edição
                            $('#comprovativo').removeAttr('required');
                            
                            // Abrir o modal
                            $('#modalMatricula').modal('show');
                            
                            // Após carregar o curso, selecionar a turma correta
                            setTimeout(function() {
                                $('#id_turma').val(dados.id_turma).trigger('change');
                            }, 500);
                        } else {
                            alert('Erro ao carregar dados: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('Erro na comunicação com o servidor');
                    }
                });
            }

            // Modificar a parte do submit do formulário
            $('#formMatricula').submit(function(e) {
                e.preventDefault();
                
                // Verificar se é uma edição ou nova matrícula
                const isEdicao = $('#id_matricula').length > 0;
                
                // Validações específicas para nova matrícula
                if (!isEdicao) {
                    // Validar BI
                    if (!validarBI($('#bi_numero').val())) {
                        alert('Número de BI inválido. Formato correto: 123456789LA123');
                        return false;
                    }
                    
                    // Validar senha
                    if ($('#senha').val().length < 6) {
                        alert('A senha deve ter no mínimo 6 caracteres');
                        return false;
                    }
                    
                    // Validar data de nascimento
                    const dataNasc = new Date($('#data_nascimento').val());
                    const hoje = new Date();
                    let idade = hoje.getFullYear() - dataNasc.getFullYear();
                    const mes = hoje.getMonth() - dataNasc.getMonth();
                    
                    if (mes < 0 || (mes === 0 && hoje.getDate() < dataNasc.getDate())) {
                        idade--;
                    }
                    
                    if (idade < 15) {
                        alert('O aluno deve ter pelo menos 15 anos de idade');
                        return false;
                    }
                }
                
                // Criar FormData para enviar arquivos
                var formData = new FormData(this);
                
                // Definir a URL correta com base em se é edição ou nova matrícula
                const url = isEdicao ? '../../actions/secretaria/atualizar_matricula.php' : '../../actions/processar_matricula.php';
                
                $.ajax({
                    url: url,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            alert(response.message);
                            $('#modalMatricula').modal('hide');
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

            // Função para validar o número de BI
            function validarBI(bi) {
                const regex = /^[0-9]{9}[A-Z]{2}[0-9]{3}$/;
                return regex.test(bi);
            }

            // Resetar o formulário quando o modal for fechado
            $('#modalMatricula').on('hidden.bs.modal', function () {
                $('#formMatricula')[0].reset();
                $('#modalMatriculaLabel').text('Nova Matrícula');
                $('#formMatricula button[type="submit"]').html('<i class="feather icon-save"></i> Salvar Matrícula');
                $('#senha').attr('required', 'required');
                $('#comprovativo').attr('required', 'required');
                $('#id_matricula').remove();
                $('#senha').next('small.form-text.text-muted:not(:first-of-type)').remove();
            });

        </script>
    </body>
</html>