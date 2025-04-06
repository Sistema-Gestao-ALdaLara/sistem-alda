<?php
    require_once "permissoes.php";
    verificarPermissao(['aluno']);
    require_once 'verificar_sessao.php';
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <title>ALUNO</title>
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
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.3); /* Borda branca fraca */
            color: white; /* Texto branco para contraste */
        }

        .table-custom th,
        .table-custom td {
            padding: 12px;
            color: #ffffff;
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
            background: rgba(19, 125, 171, 0.082);
            backdrop-filter: blur(10px); /* Efeito vidro fosco */
            border-radius: 10px;
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
      
</head>

<body>
    <!-- Pre-loader start -->
    <div class="theme-loader">
        <div class="ball-scale">
            <div class='contain'>
                <div class="ring">
                    <div class="frame"></div>
                </div>
                <div class="ring">
                    <div class="frame"></div>
                </div>
                <div class="ring">
                    <div class="frame"></div>
                </div>
                <div class="ring">
                    <div class="frame"></div>
                </div>
                <div class="ring">
                    <div class="frame"></div>
                </div>
                <div class="ring">
                    <div class="frame"></div>
                </div>
                <div class="ring">
                    <div class="frame"></div>
                </div>
                <div class="ring">
                    <div class="frame"></div>
                </div>
                <div class="ring">
                    <div class="frame"></div>
                </div>
                <div class="ring">
                    <div class="frame"></div>
                </div>
            </div>
        </div>
    </div>
    <!-- Pre-loader end -->
    <div id="pcoded" class="pcoded">
        <div class="pcoded-overlay-box"></div>
        <div class="pcoded-container navbar-wrapper">

            <nav class="navbar header-navbar pcoded-header">
                <div class="navbar-wrapper">

                    <div class="navbar-logo">
                        <a class="mobile-menu" id="mobile-collapse" href="#!">
                            <i class="feather icon-menu"></i>
                        </a>
                        <a href="dashboard.htm">
                            <img class="img-fluid" src="libraries\assets\images\logo.png" height="50px" width="50px" alt="Theme-Logo"> <span class="font-italic font-weight-bold text-uppercase text-warning text-center">ALUNO |Alda Lara</span>
                        </a>
                        <a class="mobile-options">
                            <i class="feather icon-more-horizontal"></i>
                        </a>
                    </div>

                    <div class="navbar-container container-fluid">
                        <ul class="nav-left">
                            <li>
                                <a href="#!" onclick="javascript:toggleFullScreen()">
                                    <i class="feather icon-maximize full-screen"></i>
                                </a>
                            </li>
                        </ul>
                        <ul class="nav-right">
                            <li class="header-notification">
                                <div class="dropdown-primary dropdown">
                                    <div class="dropdown-toggle" data-toggle="dropdown">
                                        <i class="feather icon-bell"></i>
                                        <span class="badge bg-c-pink">5</span>
                                    </div>
                                    <ul class="show-notification notification-view dropdown-menu" data-dropdown-in="fadeIn" data-dropdown-out="fadeOut">
                                        <li>
                                            <h6>Notifications</h6>
                                            <label class="label label-danger">New</label>
                                        </li>
                                        <li>
                                            <div class="media">
                                                <img class="d-flex align-self-center img-radius" src="libraries\assets\images\avatar-4.jpg" alt="Generic placeholder image">
                                                <div class="media-body">
                                                    <h5 class="notification-user">Flavio Garcia</h5>
                                                    <p class="notification-msg">Lorem ipsum dolor sit amet, consectetuer elit.</p>
                                                    <span class="notification-time">30 minutes ago</span>
                                                </div>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="media">
                                                <img class="d-flex align-self-center img-radius" src="libraries\assets\images\avatar-3.jpg" alt="Generic placeholder image">
                                                <div class="media-body">
                                                    <h5 class="notification-user">Jucelmo Pereira</h5>
                                                    <p class="notification-msg">Lorem ipsum dolor sit amet, consectetuer elit.</p>
                                                    <span class="notification-time">30 minutes ago</span>
                                                </div>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="media">
                                                <img class="d-flex align-self-center img-radius" src="libraries\assets\images\avatar-4.jpg" alt="Generic placeholder image">
                                                <div class="media-body">
                                                    <h5 class="notification-user">Ariel Patricio</h5>
                                                    <p class="notification-msg">Lorem ipsum dolor sit amet, consectetuer elit.</p>
                                                    <span class="notification-time">30 minutes ago</span>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <!--<li class="user-profile header-notification">
                                <div class="dropdown-primary dropdown">
                                    <div class="dropdown-toggle" data-toggle="dropdown">
                                        <img src="libraries\assets\images\avatar-4.jpg" class="img-radius" alt="User-Profile-Image">
                                        <span>Flavio Garcia</span>
                                        <i class="feather icon-chevron-down"></i>
                                    </div>
                                    <ul class="show-notification profile-notification dropdown-menu" data-dropdown-in="fadeIn" data-dropdown-out="fadeOut">
                                        <li>
                                            <a href="#!">
                                                <i class="feather icon-settings"></i> Settings
                                            </a>
                                        </li>
                                        <li>
                                            <a href="user-profile.htm">
                                                <i class="feather icon-user"></i> Profile
                                            </a>
                                        </li>
                                        <li>
                                            <a href="email-inbox.htm">
                                                <i class="feather icon-mail"></i> My Messages
                                            </a>
                                        </li>
                                        <li>
                                            <a href="auth-lock-screen.htm">
                                                <i class="feather icon-lock"></i> Lock Screen
                                            </a>
                                        </li>
                                        <li>
                                            <a href="login.htm">
                                                <i class="feather icon-log-out"></i> Logout
                                            </a>
                                        </li>
                                    </ul>

                                </div>
                            </li>-->
                            <li class="user-profile header-notification">
                                <div class="dropdown-primary dropdown">
                                    <div class="dropdown-toggle" data-toggle="dropdown">
                                        <img src="libraries/assets/images/avatar-4.jpg" class="img-radius" alt="User-Profile-Image">
                                        <span><?php echo htmlspecialchars($_SESSION['nome_usuario']); ?></span>
                                        <i class="feather icon-chevron-down"></i>
                                    </div>
                                    <ul class="show-notification profile-notification dropdown-menu" data-dropdown-in="fadeIn" data-dropdown-out="fadeOut">
                                        <li>
                                            <a href="user-profile.htm">
                                                <i class="feather icon-user"></i> Perfil
                                            </a>
                                        </li>
                                        <li>
                                            <a href="logout.php">
                                                <i class="feather icon-log-out"></i> Sair
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </li>                        
                        </ul>
                    </div>
                </div>
            </nav>

            <!--sidebar-->
            <div class="pcoded-main-container">
                <div class="pcoded-wrapper">
                    <nav class="pcoded-navbar">
                        <div class="pcoded-inner-navbar main-menu">
                            <div class="pcoded-navigatio-lavel">Navegação</div>
                            <ul class="pcoded-item pcoded-left-item">
                    
                                <!-- Dashboard -->
                                <li class="pcoded-hasmenu active pcoded-trigger">
                                    <a href="/aluno/dashboard.php">
                                        <span class="pcoded-micon"><i class="feather icon-home"></i></span>
                                        <span class="pcoded-mtext">Dashboard</span>
                                    </a>
                                </li>
                    
                                <!-- Minhas Disciplinas -->
                                <li>
                                    <a href="/aluno/disciplinas.php">
                                        <span class="pcoded-micon"><i class="feather icon-book"></i></span>
                                        <span class="pcoded-mtext">Minhas Disciplinas</span>
                                    </a>
                                </li>
                    
                                <!-- Minhas Notas -->
                                <li>
                                    <a href="/aluno/notas.php">
                                        <span class="pcoded-micon"><i class="feather icon-bar-chart"></i></span>
                                        <span class="pcoded-mtext">Minhas Notas</span>
                                    </a>
                                </li>
                    
                                <!-- Horários e Calendário -->
                                <li>
                                    <a href="/aluno/calendario.php">
                                        <span class="pcoded-micon"><i class="feather icon-calendar"></i></span>
                                        <span class="pcoded-mtext">Horários e Calendário</span>
                                    </a>
                                </li>
                    
                                <!-- Materiais de Apoio -->
                                <li>
                                    <a href="/aluno/materiais.php">
                                        <span class="pcoded-micon"><i class="feather icon-folder"></i></span>
                                        <span class="pcoded-mtext">Materiais de Apoio</span>
                                    </a>
                                </li>
                    
                                <!-- Notificações -->
                                <li>
                                    <a href="/aluno/notificacoes.php">
                                        <span class="pcoded-micon"><i class="feather icon-bell"></i></span>
                                        <span class="pcoded-mtext">Notificações</span>
                                    </a>
                                </li>
                    
                                <!-- Atualizar Perfil -->
                                <li>
                                    <a href="/aluno/perfil.php">
                                        <span class="pcoded-micon"><i class="feather icon-user"></i></span>
                                        <span class="pcoded-mtext">Atualizar Perfil</span>
                                    </a>
                                </li>
                    
                                <!-- Contato com a Escola -->
                                <li>
                                    <a href="/aluno/contato.php">
                                        <span class="pcoded-micon"><i class="feather icon-mail"></i></span>
                                        <span class="pcoded-mtext">Contato com a Escola</span>
                                    </a>
                                </li>
                    
                            </ul>
                        </div>
                    </nav>
                    
                    <div class="pcoded-content">
                        <div class="pcoded-inner-content">
                            <div class="main-body bg-img">
                                <div class="page-wrapper">

                                    <div class="page-body">
                                        <div class="row">
                                            <!-- Card de Perfil -->
                                            <div class="col-xl-4 col-md-6">
                                                <div class="card user-card">
                                                    <div class="card-block text-center">
                                                        <div class="user-image">
                                                            <img src="libraries/assets/images/avatar-4.jpg" class="img-radius" id="profile-pic" width="100" height="100" alt="Foto do Aluno">
                                                        </div>
                                                        <h4 class="m-t-15 text-uppercase">João Silva</h4>
                                                        <p class="text-muted">Curso: Tecnico Informatica</p>
                                                        <p class="text-muted">Turma: I10AM</p>
                                                        <button class="btn btn-light btn-sm" onclick="document.getElementById('file-input').click();">
                                                            <i class="feather icon-camera"></i> Alterar Foto
                                                        </button>
                                                        <input type="file" id="file-input" class="d-none" accept="image/*" onchange="validateImage()">
                                                    </div>
                                                    <div class="card-footer">
                                                        <p class="text-muted m-b-0">
                                                            <i class="feather icon-clock f-14 m-r-10"></i> Última atualização: Hoje
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        
                                            <!-- Acesso Rápido -->
                                            <div class="col-xl-8 col-md-6">
                                                <div class="row">
                                                    <a href="/aluno/disciplinas.php" class="col-6 col-md-4 d-block text-decoration-none text-reset">
                                                        <div class="card bg-c-yellow">
                                                            <div class="card-block text-center">
                                                                <i class="feather icon-book f-30 text-white"></i>
                                                                <h6 class="text-white m-t-10">Minhas Disciplinas</h6>
                                                            </div>
                                                            <div class="card-footer bg-c-blue">
                                                                <p class="text-white m-b-0">
                                                                    <i class="feather icon-arrow-right f-14 m-r-10"></i> Acessar Disciplinas
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </a>
                                                    <a href="/aluno/notas.php" class="col-6 col-md-4 d-block text-decoration-none text-reset">
                                                        <div class="card bg-c-green">
                                                            <div class="card-block text-center">
                                                                <i class="feather icon-bar-chart f-30 text-white"></i>
                                                                <h6 class="text-white m-t-10">Minhas Notas</h6>
                                                            </div>
                                                            <div class="card-footer bg-c-yellow">
                                                                <p class="text-white m-b-0">
                                                                    <i class="feather icon-arrow-right f-14 m-r-10"></i> Ver Notas Recentes
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </a>
                                                    <a href="/aluno/calendario.php" class="col-6 col-md-4 d-block text-decoration-none text-reset">
                                                        <div class="card bg-c-blue">
                                                            <div class="card-block text-center">
                                                                <i class="feather icon-calendar f-30 text-white"></i>
                                                                <h6 class="text-white m-t-10">Calendário Escolar</h6>
                                                            </div>
                                                            <div class="card-footer bg-c-green">
                                                                <p class="text-white m-b-0">
                                                                    <i class="feather icon-arrow-right f-14 m-r-10"></i> Ver Eventos
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </a>
                                                </div>
                                            </div>
                                        
                                            <!-- Notas e Frequência -->
                                            <div class="col-xl-3 col-md-6">
                                                <div class="card bg-c-yellow update-card">
                                                    <div class="card-block">
                                                        <div class="row align-items-end">
                                                            <div class="col-8">
                                                                <h4 class="text-white">85%</h4>
                                                                <h6 class="text-white m-b-0">
                                                                    <i class="feather icon-star"></i> Média Geral
                                                                </h6>
                                                            </div>
                                                            <div class="col-4 text-right">
                                                                <i class="feather icon-star text-white" style="font-size: 40px;"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card-footer">
                                                        <p class="text-white m-b-0">
                                                            <i class="feather icon-clock text-white f-14 m-r-10"></i> Atualizado hoje
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        
                                            <div class="col-xl-3 col-md-6">
                                                <div class="card bg-c-green update-card">
                                                    <div class="card-block">
                                                        <div class="row align-items-end">
                                                            <div class="col-8">
                                                                <h4 class="text-white">92%</h4>
                                                                <h6 class="text-white m-b-0">
                                                                    <i class="feather icon-check-circle"></i> Presença
                                                                </h6>
                                                            </div>
                                                            <div class="col-4 text-right">
                                                                <i class="feather icon-check-circle text-white" style="font-size: 40px;"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card-footer">
                                                        <p class="text-white m-b-0">
                                                            <i class="feather icon-clock text-white f-14 m-r-10"></i> Atualizado ontem
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        
                                            <!-- Últimas Notas -->
                                            <div class="col-xl-6">
                                                <div class="card card-table">
                                                    <div class="card-header">
                                                        <h5><i class="feather icon-award"></i> Últimas Notas</h5>
                                                    </div>
                                                    <div class="card-body">
                                                        <table class="table table-hover">
                                                            <thead>
                                                                <tr>
                                                                    <th>Disciplina</th>
                                                                    <th>Nota</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td>Matemática</td>
                                                                    <td><span class="badge badge-success">15.0</span></td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Português</td>
                                                                    <td><span class="badge badge-warning">17.5</span></td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Fisica</td>
                                                                    <td><span class="badge badge-danger">13.0</span></td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <div class="card-footer">
                                                        <p class="text-muted m-b-0">
                                                            <i class="feather icon-clock f-14 m-r-10"></i> Atualizado recentemente
                                                        </p>
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


    <!-- Warning Section Ends -->
    <!-- Required Jquery -->
    <script data-cfasync="false" src="..\..\..\cdn-cgi\scripts\5c5dd728\cloudflare-static\email-decode.min.js"></script><script type="text/javascript" src="libraries\bower_components\jquery\js\jquery.min.js"></script>
    <script type="text/javascript" src="libraries\bower_components\jquery-ui\js\jquery-ui.min.js"></script>
    <script type="text/javascript" src="libraries\bower_components\popper.js\js\popper.min.js"></script>
    <script type="text/javascript" src="libraries\bower_components\bootstrap\js\bootstrap.min.js"></script>
    <!-- jquery slimscroll js -->
    <script type="text/javascript" src="libraries\bower_components\jquery-slimscroll\js\jquery.slimscroll.js"></script>
    <!-- modernizr js -->
    <script type="text/javascript" src="libraries\bower_components\modernizr\js\modernizr.js"></script>
    <!-- Chart js -->
    <script type="text/javascript" src="libraries\bower_components\chart.js\js\Chart.js"></script>
    <!-- amchart js -->
    <script src="libraries\assets\pages\widget\amchart\amcharts.js"></script>
    <script src="libraries\assets\pages\widget\amchart\serial.js"></script>
    <script src="libraries\assets\pages\widget\amchart\light.js"></script>
    <script src="libraries\assets\js\jquery.mCustomScrollbar.concat.min.js"></script>
    <script type="text/javascript" src="libraries\assets\js\SmoothScroll.js"></script>
    <script src="libraries\assets\js\pcoded.min.js"></script>
    <!-- custom js -->
    <script src="libraries\assets\js\vartical-layout.min.js"></script>
    <script type="text/javascript" src="libraries\assets\pages\dashboard\custom-dashboard.js"></script>
    <script type="text/javascript" src="libraries\assets\js\script.min.js"></script>
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async="" src="https://www.googletagmanager.com/gtag/js?id=UA-23581568-13"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'UA-23581568-13');
</script>


<script>
    function validateImage() {
        let fileInput = document.getElementById('file-input');
        let profilePic = document.getElementById('profile-pic');
    
        if (fileInput.files.length > 0) {
            let file = fileInput.files[0];
    
            // Verifica o tipo de arquivo
            if (!file.type.startsWith('image/')) {
                alert("Por favor, envie uma imagem válida.");
                return;
            }
    
            // Simulando uma IA que valida rostos (apenas uma verificação básica)
            let fileName = file.name.toLowerCase();
            if (!fileName.includes("face") && !fileName.includes("selfie")) {
                alert("A foto precisa ser do seu rosto!");
                return;
            }
    
            // Atualiza a imagem do perfil (simulação)
            let reader = new FileReader();
            reader.onload = function(e) {
                profilePic.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    }
    </script>

    <!-- Script para Validação da Foto -->
    <script>
        function validateImage() {
            let fileInput = document.getElementById('file-input');
            let profilePic = document.getElementById('profile-pic');
        
            if (fileInput.files.length > 0) {
                let file = fileInput.files[0];
        
                if (!file.type.startsWith('image/')) {
                    alert("Por favor, envie uma imagem válida.");
                    return;
                }
        
                let reader = new FileReader();
                reader.onload = function(e) {
                    profilePic.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        }
    </script>
</body>

</html>
