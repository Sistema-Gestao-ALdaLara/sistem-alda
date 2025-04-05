<?php
// require_once "../auth/permissoes.php";
// verificarPermissao(['secretaria']);

require_once 'conexao.php';

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
<head>
    <title>SECRETARIA - Gestão de Diretores | Alda Lara</title>
    <!-- HTML5 Shim and Respond.js IE10 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 10]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
      <![endif]-->
    <!-- Meta -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="#">
    <meta name="keywords" content="Admin , Responsive, Landing, Bootstrap, App, Template, Mobile, iOS, Android, apple, creative app">
    <meta name="author" content="#">
    <!-- Favicon icon -->
    <link rel="icon" href="libraries\assets\images\favicon.ico" type="image/x-icon">
    <!-- Google font-->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600" rel="stylesheet">
    <!-- Required Fremwork -->
    <link rel="stylesheet" type="text/css" href="libraries\bower_components\bootstrap\css\bootstrap.min.css">
    <!-- feather Awesome -->
    <link rel="stylesheet" type="text/css" href="libraries\assets\icon\feather\css\feather.css">
    <!-- Style.css -->
    <link rel="stylesheet" type="text/css" href="libraries\assets\css\style.css">
    <link rel="stylesheet" type="text/css" href="libraries\assets\css\jquery.mCustomScrollbar.css">

    <style>
        .bg-img {
          width: 100%; /* Ou um valor específico, como 500px */
          height: auto; /* Defina a altura conforme necessário */
          background-image: url('../public/img/bg.jpg'); /* Caminho da imagem */
          background-size: cover; /* Faz com que a imagem cubra toda a div */
          background-position: center; /* Centraliza a imagem */
          background-repeat: no-repeat; /* Evita repetições da imagem */
        }

        .table-custom {
            background: rgba(255, 255, 255, 0.2); /* Branco bem transparente */
            backdrop-filter: blur(8px); /* Efeito vidro fosco */
            border-radius: 10px; /* Bordas arredondadas */
            border: 1px solid rgba(255, 255, 255, 0.3); /* Borda branca fraca */
            color: white; /* Texto branco para contraste */
        }

        .table-custom th,
        .table-custom td {
            padding: 12px;
            color: #ffffff; /* Texto branco */
        }

        .table-custom thead {
            background: rgba(7, 200, 206, 0.55); /* Azul mais transparente */
            color: white;
            font-weight: bold;
        }

        .table-custom tbody tr:hover {
            background: rgba(255, 255, 255, 0.3); /* Efeito ao passar o mouse */
            transition: 0.3s;
        }

        /* Estilo específico para os cards que contêm tabelas */
        .card-table {
            background: rgba(19, 125, 171, 0.082); /* Fundo branco com transparência */
            backdrop-filter: blur(10px); /* Efeito vidro fosco */
            border-radius: 10px; /* Bordas arredondadas */
            border: 1px solid rgba(255, 255, 255, 0.3); /* Borda sutil */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Sombra leve */
            color: white !important;
        }

        /* Ajuste no cabeçalho do card */
        .card-table .card-header {
            background: rgba(7, 200, 206, 0.836); /* Azul translúcido */
            color: white !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
        }

        /* Estilo da tabela dentro do card */
        .card-table .table {
            background: transparent; /* Mantém a tabela transparente dentro do card */
        }
    </style>
    <style>
        /* ... (estilos iguais ao anterior) ... */
        .badge-diretor-geral {
            background-color: #4e73df;
        }
        .badge-diretor-pedagogico {
            background-color: #1cc88a;
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
                                        <input type="text" class="form-control" placeholder="Pesquisar coordenador...">
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
                                                    <p class="notification-msg">Novos coordenadores cadastrados</p>
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
                                <li class="pcoded-hasmenu active">
                                    <a href="/secretaria/coordenadores.php"><span class="pcoded-micon"><i class="feather icon-users"></i></span>
                                    <span class="pcoded-mtext">Gerenciar Coordenadores</span></a>
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
            url: 'getDiretor.php',
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
                url: 'excluir_diretor.php',
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