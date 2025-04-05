<?php
// require_once "../auth/permissoes.php";
// verificarPermissao(['secretaria']);

require_once 'conexao.php';

// Filtros recebidos via GET
$id_curso = isset($_GET['id_curso']) ? intval($_GET['id_curso']) : null;

// Query base para professores
// Substitua a query atual por esta:
$query = "SELECT 
             p.id_professor,
             u.nome, 
             u.email,
             u.bi_numero,
             c.nome AS curso,
             c.id_curso,
             u.status,
             GROUP_CONCAT(d.nome SEPARATOR ', ') AS disciplinas
          FROM professor p
          JOIN usuario u ON p.usuario_id_usuario = u.id_usuario
          JOIN curso c ON p.curso_id_curso = c.id_curso
          LEFT JOIN disciplina d ON d.professor_id_professor = p.id_professor";

// Filtros
$where = [];
$params = [];
$types = ""; // Tipos para bind_param

if ($id_curso) {
    $where[] = "c.id_curso = ?";
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
$professores = $result->fetch_all(MYSQLI_ASSOC);

// Obter cursos
$result_cursos = $conn->query("SELECT id_curso, nome FROM curso ORDER BY nome");
$cursos = $result_cursos->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <title>SECRETARIA - Gestão de Professores | Alda Lara</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Sistema de Gestão Escolar - Escola Alda Lara">
    <meta name="keywords" content="Escola, Alda Lara, Angola, Luanda, Secretaria, Professores">
    <meta name="author" content="Escola Alda Lara">
    <link rel="icon" href="libraries/assets/images/favicon.ico" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="libraries/bower_components/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="libraries/assets/icon/feather/css/feather.css">
    <link rel="stylesheet" type="text/css" href="libraries/assets/css/style.css">
    <link rel="stylesheet" type="text/css" href="libraries/assets/css/jquery.mCustomScrollbar.css">
    <style>
        .bg-img {
            width: 100%;
            height: auto;
            background-image: url('../public/img/bg.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
        .table-custom {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(8px);
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
        }
        .table-custom th, .table-custom td {
            padding: 12px;
            color: #ffffff;
        }
        .table-custom thead {
            background: rgba(7, 200, 206, 0.55);
            color: white;
            font-weight: bold;
        }
        .table-custom tbody tr:hover {
            background: rgba(255, 255, 255, 0.3);
            transition: 0.3s;
        }
        .card-table {
            background: rgba(19, 125, 171, 0.082);
            backdrop-filter: blur(10px);
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            color: white !important;
        }
        .card-table .card-header {
            background: rgba(7, 200, 206, 0.836);
            color: white !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
        }
        .card-table .table {
            background: transparent;
        }
        .action-buttons .btn {
            margin: 0 3px;
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        .filtros-container {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .filtros-container label {
            color: white;
            font-weight: bold;
        }
        .btn-filtrar {
            margin-top: 28px;
        }
        .btn-limpar {
            margin-top: 28px;
            background-color: #6c757d;
            border-color: #6c757d;
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
            <nav class="navbar header-navbar pcoded-header">
                <div class="navbar-wrapper">
                    <div class="navbar-logo">
                        <a class="mobile-menu" id="mobile-collapse" href="#!"><i class="feather icon-menu"></i></a>
                        <a href="dashboard.htm">
                            <img class="img-fluid" src="libraries/assets/images/logo.png" height="50px" width="50px" alt="Theme-Logo">
                            <span class="font-italic font-weight-bold text-uppercase text-warning text-center">SECRETARIA|Alda Lara</span>
                        </a>
                        <a class="mobile-options"><i class="feather icon-more-horizontal"></i></a>
                    </div>
                    <div class="navbar-container container-fluid">
                        <ul class="nav-left">
                            <li class="header-search">
                                <div class="main-search morphsearch-search">
                                    <div class="input-group">
                                        <span class="input-group-addon search-close"><i class="feather icon-x"></i></span>
                                        <input type="text" class="form-control" placeholder="Pesquisar professor...">
                                        <span class="input-group-addon search-btn"><i class="feather icon-search"></i></span>
                                    </div>
                                </div>
                            </li>
                            <li><a href="#!" onclick="javascript:toggleFullScreen()"><i class="feather icon-maximize full-screen"></i></a></li>
                        </ul>
                        <ul class="nav-right">
                            <li class="header-notification">
                                <div class="dropdown-primary dropdown">
                                    <div class="dropdown-toggle" data-toggle="dropdown">
                                        <i class="feather icon-bell"></i>
                                        <span class="badge bg-c-pink">5</span>
                                    </div>
                                    <ul class="show-notification notification-view dropdown-menu" data-dropdown-in="fadeIn" data-dropdown-out="fadeOut">
                                        <li><h6>Notificações</h6><label class="label label-danger">Novo</label></li>
                                        <li>
                                            <div class="media">
                                                <img class="d-flex align-self-center img-radius" src="libraries/assets/images/avatar-4.jpg" alt="Generic placeholder image">
                                                <div class="media-body">
                                                    <h5 class="notification-user">Secretaria</h5>
                                                    <p class="notification-msg">Novos professores cadastrados</p>
                                                    <span class="notification-time">Hoje</span>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li class="user-profile header-notification">
                                <div class="dropdown-primary dropdown">
                                    <div class="dropdown-toggle" data-toggle="dropdown">
                                        <img src="libraries/assets/images/avatar-4.jpg" class="img-radius" alt="User-Profile-Image">
                                        <span>Usuário Secretaria</span>
                                        <i class="feather icon-chevron-down"></i>
                                    </div>
                                    <ul class="show-notification profile-notification dropdown-menu" data-dropdown-in="fadeIn" data-dropdown-out="fadeOut">
                                        <li><a href="user-profile.htm"><i class="feather icon-user"></i> Perfil</a></li>
                                        <li><a href="login.htm"><i class="feather icon-log-out"></i> Sair</a></li>
                                    </ul>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>

            <!-- Sidebar -->
            <div class="pcoded-main-container">
                <div class="pcoded-wrapper">
                    <nav class="pcoded-navbar">
                        <div class="pcoded-inner-navbar main-menu">
                            <div class="pcoded-navigatio-lavel">Navegação</div>
                            <ul class="pcoded-item pcoded-left-item">
                                <li class="pcoded-hasmenu active pcoded-trigger">
                                    <a href="javascript:void(0)"><span class="pcoded-micon"><i class="feather icon-home"></i></span>
                                    <span class="pcoded-mtext">Dashboard</span></a>
                                </li>
                                <li class="pcoded-hasmenu">
                                    <a href="/secretaria/matriculas.php"><span class="pcoded-micon"><i class="feather icon-user-plus"></i></span>
                                    <span class="pcoded-mtext">Matrículas</span></a>
                                </li>
                                <li class="pcoded-hasmenu">
                                    <a href="/secretaria/alunos.php"><span class="pcoded-micon"><i class="feather icon-users"></i></span>
                                    <span class="pcoded-mtext">Gerenciar Alunos</span></a>
                                </li>
                                <li class="pcoded-hasmenu">
                                    <a href="/secretaria/professores.php"><span class="pcoded-micon"><i class="feather icon-users"></i></span>
                                    <span class="pcoded-mtext">Gerenciar Professores</span></a>
                                </li>
                                <li class="pcoded-hasmenu">
                                    <a href="/secretaria/turmas.php"><span class="pcoded-micon"><i class="feather icon-layers"></i></span>
                                    <span class="pcoded-mtext">Gerenciar Turmas</span></a>
                                </li>
                                <li class="pcoded-hasmenu">
                                    <a href="/secretaria/documentos.php"><span class="pcoded-micon"><i class="feather icon-file-text"></i></span>
                                    <span class="pcoded-mtext">Documentos</span></a>
                                </li>
                                <li class="pcoded-hasmenu">
                                    <a href="/secretaria/relatorios.php"><span class="pcoded-micon"><i class="feather icon-bar-chart"></i></span>
                                    <span class="pcoded-mtext">Relatórios</span></a>
                                </li>
                            </ul>
                        </div>
                    </nav>

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
                                                
                                                <!-- Tabela de Professores -->
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
                                                                            <!-- Botão Editar -->
                                                                            <button class="btn btn-warning btn-sm" onclick="editarProfessor(<?= $professor['id_professor'] ?>)">
                                                                                <i class="feather icon-edit"></i>
                                                                            </button>
                                                                            
                                                                            <!-- Botão Ver Disciplinas -->
                                                                            <button class="btn btn-info btn-sm" onclick="verDisciplinas(<?= $professor['id_professor'] ?>)">
                                                                                <i class="feather icon-book"></i>
                                                                            </button>
                                                                            
                                                                            <!-- Botão Excluir -->
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

                                                <!-- Modal Professor -->
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
                                                                <form id="formProfessor" method="POST" action="salvar_professor.php">
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
                                                                    
                                                                    <!-- Campo de Disciplinas (Múltipla Seleção) -->
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
        function novoProfessor() {
            $('#formProfessor')[0].reset();
            $('#professorId').val('');
            $('#modalProfessorLabel').text('Novo Professor');
        }
        
        function editarProfessor(id) {
            $.ajax({
                url: 'getProfessor.php',
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
                    
                    // Não preenche a senha por questões de segurança
                    $('#senha').val('');
                    $('#senha').removeAttr('required');
                    
                    $('#modalProfessorLabel').text('Editar Professor: ' + professor.nome);
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
                    url: 'excluir_professor.php',
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
            window.open('disciplinas.php?id_professor=' + id, '_blank');
        }
        
        function exportarProfessores() {
            const id_curso = $('#filtro_curso').val() || '';
            window.open('exportar_professores.php?id_curso=' + id_curso, '_blank');
        }
        
        // Eventos
        $(document).ready(function() {
            // Validação do formulário de professor
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
        });
        
        function validarBI(bi) {
            const regex = /^[0-9]{9}[A-Z]{2}[0-9]{3}$/;
            return regex.test(bi);
        }


        // carregar disciplinas dinamicamente:
        $('#id_curso').change(function() {
            const cursoId = $(this).val();
            if (cursoId) {
                $.ajax({
                    url: 'get_disciplinas.php',
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

        // E na função editarProfessor, adicione:
        function editarProfessor(id) {
            $.ajax({
                url: 'getProfessor.php',
                method: 'GET',
                data: { id: id },
                dataType: 'json',
                success: function(professor) {
                    // ... código existente ...
                    
                    // Carrega disciplinas do professor
                    $.ajax({
                        url: 'get_disciplinas_professor.php',
                        method: 'GET',
                        data: { id_professor: id },
                        success: function(disciplinas) {
                            $('#disciplinas').val(disciplinas).trigger('change');
                        }
                    });
                }
            });
        }
    </script>

<!-- JavaScript para as Ações -->
<script>
    // Função para editar professor
    function editarProfessor(id) {
        $.ajax({
            url: 'getProfessor.php',
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
                
                // Carrega as disciplinas do professor
                $.ajax({
                    url: 'get_disciplinas_professor.php',
                    method: 'GET',
                    data: { id_professor: id },
                    success: function(disciplinas) {
                        $('#disciplinas').val(disciplinas);
                    }
                });
                
                $('#modalProfessorLabel').text('Editar Professor: ' + professor.nome);
                $('#modalProfessor').modal('show');
            }
        });
    }

    // Função para ver disciplinas (em página separada)
    function verDisciplinas(id) {
        window.location.href = 'disciplinas_professor.php?id=' + id;
    }

    // Função para confirmar exclusão
    function confirmarExclusao(id) {
        if(confirm('Tem certeza que deseja excluir este professor?')) {
            $.ajax({
                url: 'excluir_professor.php',
                method: 'POST',
                data: { id: id },
                success: function(response) {
                    if(response.success) {
                        alert('Professor excluído com sucesso');
                        location.reload();
                    } else {
                        alert('Erro ao excluir: ' + response.message);
                    }
                }
            });
        }
    }

    // Função para novo professor
    function novoProfessor() {
        $('#formProfessor')[0].reset();
        $('#professorId').val('');
        $('#modalProfessorLabel').text('Novo Professor');
        $('#disciplinas').val(null); // Limpa seleção de disciplinas
    }

    // Atualiza disciplinas quando o curso é alterado
    $('#id_curso').change(function() {
        const cursoId = $(this).val();
        if (cursoId) {
            $.ajax({
                url: 'get_disciplinas.php',
                method: 'GET',
                data: { id_curso: cursoId },
                success: function(data) {
                    $('#disciplinas').empty();
                    data.forEach(function(disciplina) {
                        $('#disciplinas').append(
                            $('<option>', {
                                value: disciplina.id_disciplina,
                                text: disciplina.nome
                            })
                        );
                    });
                }
            });
        }
    });
</script>
</body>
</html>