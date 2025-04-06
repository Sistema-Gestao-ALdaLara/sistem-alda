<?php
    require_once "permissoes.php";
    verificarPermissao(['aluno']);
    require_once 'verificar_sessao.php';
?>
<!DOCTYPE html>
<html lang="pt">

    <head>
        <title>PROFESSOR</title>
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
                                <img class="img-fluid" src="libraries\assets\images\logo.png" height="50px" width="50px" alt="Theme-Logo"> <span class="font-italic font-weight-bold text-uppercase text-warning text-center">PROFESSOR |Alda Lara</span>
                            </a>
                            <a class="mobile-options">
                                <i class="feather icon-more-horizontal"></i>
                            </a>
                        </div>

                        <div class="navbar-container container-fluid">
                            <ul class="nav-left">
                                <li class="header-search">
                                    <div class="main-search morphsearch-search">
                                        <div class="input-group">
                                            <span class="input-group-addon search-close"><i class="feather icon-x"></i></span>
                                            <input type="text" class="form-control">
                                            <span class="input-group-addon search-btn"><i class="feather icon-search"></i></span>
                                        </div>
                                    </div>
                                </li>
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
                                <li class="user-profile header-notification">
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
                                <div class="pcoded-navigatio-lavel">Navegacao</div>
                                <ul class="pcoded-item pcoded-left-item">
                                    <li class="pcoded-hasmenu active pcoded-trigger">
                                        <a href="javascript:void(0)">
                                            <span class="pcoded-micon"><i class="feather icon-home"></i></span>
                                            <span class="pcoded-mtext">Dashboard</span>
                                        </a>
                                    </li>

                                    <li class="pcoded-hasmenu">
                                        <a href="/professor/turmas.php">
                                            <span class="pcoded-micon"><i class="feather icon-layers"></i></span>
                                            <span class="pcoded-mtext">Minhas Turmas</span>
                                        </a>
                                    </li>
                                    <li class="pcoded-hasmenu">
                                        <a href="/professor/notas.php">
                                            <span class="pcoded-micon"><i class="feather icon-edit"></i></span>
                                            <span class="pcoded-mtext">Lançamento de Notas</span>
                                        </a>
                                    </li>
                                    <li class="pcoded-hasmenu">
                                        <a href="/professor/alunos.php">
                                            <span class="pcoded-micon"><i class="feather icon-users"></i></span>
                                            <span class="pcoded-mtext">Perfil dos Alunos</span>
                                        </a>
                                    </li>
                                    <li class="pcoded-hasmenu">
                                        <a href="/professor/notificacoes.php">
                                            <span class="pcoded-micon"><i class="feather icon-bell"></i></span>
                                            <span class="pcoded-mtext">Notificações e Avisos</span>
                                        </a>
                                    </li>
                                    <li class="pcoded-hasmenu">
                                        <a href="/professor/planos.php">
                                            <span class="pcoded-micon"><i class="feather icon-file-text"></i></span>
                                            <span class="pcoded-mtext">Planos de Aula</span>
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
                                                <!-- Minhas Turmas -->
                                                <a href="/professor/turmas.php" class="col-xl-3 col-md-6 d-block text-decoration-none text-reset">
                                                    <div class="card bg-c-yellow update-card">
                                                        <div class="card-block">
                                                            <div class="row align-items-end">
                                                                <div class="col-8">
                                                                    <h4 class="text-white">5</h4>
                                                                    <h6 class="text-white m-b-0">
                                                                        <i class="feather icon-layers"></i> Minhas Turmas
                                                                    </h6>
                                                                </div>
                                                                <div class="col-4 text-right">
                                                                    <i class="feather icon-layers text-white" style="font-size: 40px;"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="card-footer">
                                                            <p class="text-white m-b-0">
                                                                <i class="feather icon-clock text-white f-14 m-r-10"></i> Atualizado agora
                                                            </p>
                                                        </div>
                                                    </div>
                                                </a>
                                            
                                                <!-- Alunos Cadastrados -->
                                                <a href="/professor/alunos.php" class="col-xl-3 col-md-6 d-block text-decoration-none text-reset">
                                                    <div class="card bg-c-green update-card">
                                                        <div class="card-block">
                                                            <div class="row align-items-end">
                                                                <div class="col-8">
                                                                    <h4 class="text-white">120</h4>
                                                                    <h6 class="text-white m-b-0">
                                                                        <i class="feather icon-users"></i> Alunos Cadastrados
                                                                    </h6>
                                                                </div>
                                                                <div class="col-4 text-right">
                                                                    <i class="feather icon-users text-white" style="font-size: 40px;"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="card-footer">
                                                            <p class="text-white m-b-0">
                                                                <i class="feather icon-clock text-white f-14 m-r-10"></i> Atualizado agora
                                                            </p>
                                                        </div>
                                                    </div>
                                                </a>
                                            
                                                <!-- Notas Lançadas -->
                                                <a href="/professor/notas.php" class="col-xl-3 col-md-6 d-block text-decoration-none text-reset">
                                                    <div class="card bg-c-pink update-card">
                                                        <div class="card-block">
                                                            <div class="row align-items-end">
                                                                <div class="col-8">
                                                                    <h4 class="text-white">32</h4>
                                                                    <h6 class="text-white m-b-0">
                                                                        <i class="feather icon-edit"></i> Notas Lançadas
                                                                    </h6>
                                                                </div>
                                                                <div class="col-4 text-right">
                                                                    <i class="feather icon-edit text-white" style="font-size: 40px;"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="card-footer">
                                                            <p class="text-white m-b-0">
                                                                <i class="feather icon-clock text-white f-14 m-r-10"></i> Atualizado agora
                                                            </p>
                                                        </div>
                                                    </div>
                                                </a>
                                            
                                                <!-- Faltas Registradas -->
                                                <a href="/professor/notas.php" class="col-xl-3 col-md-6 d-block text-decoration-none text-reset">
                                                    <div class="card bg-c-lite-green update-card">
                                                        <div class="card-block">
                                                            <div class="row align-items-end">
                                                                <div class="col-8">
                                                                    <h4 class="text-white">10</h4>
                                                                    <h6 class="text-white m-b-0">
                                                                        <i class="feather icon-check-square"></i> Faltas Registradas
                                                                    </h6>
                                                                </div>
                                                                <div class="col-4 text-right">
                                                                    <i class="feather icon-check-square text-white" style="font-size: 40px;"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="card-footer">
                                                            <p class="text-white m-b-0">
                                                                <i class="feather icon-clock text-white f-14 m-r-10"></i> Atualizado agora
                                                            </p>
                                                        </div>
                                                    </div>
                                                </a>
                                            
                                                <!-- Avisos Enviados -->
                                                <a href="/professor/notificacoes.php" class="col-xl-3 col-md-6 d-block text-decoration-none text-reset">
                                                    <div class="card bg-c-blue update-card">
                                                        <div class="card-block">
                                                            <div class="row align-items-end">
                                                                <div class="col-8">
                                                                    <h4 class="text-white">8</h4>
                                                                    <h6 class="text-white m-b-0">
                                                                        <i class="feather icon-bell"></i> Avisos Enviados
                                                                    </h6>
                                                                </div>
                                                                <div class="col-4 text-right">
                                                                    <i class="feather icon-bell text-white" style="font-size: 40px;"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="card-footer">
                                                            <p class="text-white m-b-0">
                                                                <i class="feather icon-clock text-white f-14 m-r-10"></i> Atualizado agora
                                                            </p>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                            
                                            <div class="card mb-4 card-table">
                                                <div class="card-header">
                                                    <h5 class="text-white">Últimos Lançamentos de Notas</h5>
                                                </div>
                                                <div class="card-block table-border-style">
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered table-custom">
                                                            <thead>
                                                                <tr>
                                                                    <th>Aluno</th>
                                                                    <th>Turma</th>
                                                                    <th>Disciplina</th>
                                                                    <th>Nota</th>
                                                                    <th>Data</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td>João Silva</td>
                                                                    <td>I11AM</td>
                                                                    <td>Matemática</td>
                                                                    <td>12.5</td>
                                                                    <td>20/03/2025</td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Maria Santos</td>
                                                                    <td>I11BM</td>
                                                                    <td>Fisica</td>
                                                                    <td>13.0</td>
                                                                    <td>18/03/2025</td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Carlos Oliveira</td>
                                                                    <td>I11CM</td>
                                                                    <td>Física</td>
                                                                    <td>12.2</td>
                                                                    <td>17/03/2025</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="card mb-4 card-table">
                                                <div class="card-header">
                                                    <h5>Últimos Avisos Enviados</h5>
                                                </div>
                                                <div class="card-block">
                                                    <ul class="list-group">
                                                        <li class="list-group-item">
                                                            <i class="feather icon-bell"></i> Prova de Matemática adiada para 25/03.
                                                        </li>
                                                        <li class="list-group-item">
                                                            <i class="feather icon-bell"></i> Entrega do trabalho de História até sexta-feira.
                                                        </li>
                                                        <li class="list-group-item">
                                                            <i class="feather icon-bell"></i> Revisão para a avaliação de Ciências amanhã.
                                                        </li>
                                                    </ul>
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
    </body>

</html>
