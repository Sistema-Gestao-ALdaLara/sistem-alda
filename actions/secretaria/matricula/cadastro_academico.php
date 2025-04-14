<?php
// Inclui a conexão com o banco de dados
require_once '../../database/conexao.php';

// Verifica se o usuário está logado e tem permissão (diretor ou coordenador)
#session_start();
#if (!isset($_SESSION['id_usuario']) || ($_SESSION['tipo'] != 'diretor_geral' && $_SESSION['tipo'] != 'diretor_pedagogico' && $_SESSION['tipo'] != 'coordenador')) {
    #header("Location: login.php");
    #exit();
#}

// Processamento do formulário de cursos
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cadastrar_curso'])) {
    $nome_curso = $conn->real_escape_string($_POST['nome_curso']);
    
    $sql = "INSERT INTO curso (nome) VALUES ('$nome_curso')";
    if ($conn->query($sql) === TRUE) {
        $msg_curso = "Curso cadastrado com sucesso!";
    } else {
        $erro_curso = "Erro ao cadastrar curso: " . $conn->error;
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Processamento do formulário de turmas
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cadastrar_turma'])) {
    $nome_turma = $conn->real_escape_string($_POST['nome_turma']);
    $id_curso = $conn->real_escape_string($_POST['id_curso']);
    
    $sql = "INSERT INTO turma (nome, curso_id_curso) VALUES ('$nome_turma', '$id_curso')";
    if ($conn->query($sql) === TRUE) {
        $msg_turma = "Turma cadastrada com sucesso!";
    } else {
        $erro_turma = "Erro ao cadastrar turma: " . $conn->error;
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Processamento do formulário de disciplinas
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cadastrar_disciplina'])) {
    $nome_disciplina = $conn->real_escape_string($_POST['nome_disciplina']);
    $id_curso = $conn->real_escape_string($_POST['id_curso_disciplina']);
    
    $sql = "INSERT INTO disciplina (nome, curso_id_curso) VALUES ('$nome_disciplina', '$id_curso')";
    if ($conn->query($sql) === TRUE) {
        $msg_disciplina = "Disciplina cadastrada com sucesso!";
    } else {
        $erro_disciplina = "Erro ao cadastrar disciplina: " . $conn->error;
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Busca cursos para os selects
$cursos = $conn->query("SELECT id_curso, nome FROM curso ORDER BY nome");
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <title>Cadastro Acadêmico - Sistema Escolar Alda Lara</title>
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
    <meta name="description" content="Sistema Escolar Alda Lara">
    <meta name="keywords" content="admin, escola, sistema, alda lara">
    <meta name="author" content="Escola Alda Lara">
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
        <?php include 'navbar.php'; ?>

        <div class="pcoded-main-container">
            <div class="pcoded-wrapper">
                <?php include 'sidebar.php'; ?>
                
                <div class="pcoded-content">
                    <div class="pcoded-inner-content">
                        <!-- Main-body start -->
                        <div class="main-body">
                            <div class="page-wrapper">
                                <!-- Page-header start -->
                                <div class="page-header">
                                    <div class="row align-items-end">
                                        <div class="col-lg-8">
                                            <div class="page-header-title">
                                                <div class="d-inline">
                                                    <h4>Cadastro Acadêmico</h4>
                                                    <span>Gerenciamento de cursos, turmas e disciplinas</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="page-header-breadcrumb">
                                                <ul class="breadcrumb-title">
                                                    <li class="breadcrumb-item">
                                                        <a href="index.php"> <i class="feather icon-home"></i> </a>
                                                    </li>
                                                    <li class="breadcrumb-item"><a href="#!">Acadêmico</a></li>
                                                    <li class="breadcrumb-item"><a href="#!">Cadastros</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Page-header end -->
                                    
                                <!-- Page body start -->
                                <div class="page-body">
                                    <div class="row">
                                        <!-- Cadastro de Cursos -->
                                        <div class="col-md-6">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h5>Cadastro de Cursos</h5>
                                                    <span>Adicione novos cursos oferecidos pela instituição</span>
                                                </div>
                                                <div class="card-block">
                                                    <?php if(isset($msg_curso)): ?>
                                                        <div class="alert alert-success"><?php echo $msg_curso; ?></div>
                                                    <?php endif; ?>
                                                    <?php if(isset($erro_curso)): ?>
                                                        <div class="alert alert-danger"><?php echo $erro_curso; ?></div>
                                                    <?php endif; ?>
                                                    
                                                    <form method="POST">
                                                        <div class="form-group">
                                                            <label for="nome_curso">Nome do Curso</label>
                                                            <input type="text" class="form-control" id="nome_curso" name="nome_curso" required>
                                                        </div>
                                                        <button type="submit" name="cadastrar_curso" class="btn btn-primary">Cadastrar Curso</button>
                                                    </form>
                                                    
                                                    <hr>
                                                    <h5>Cursos Cadastrados</h5>
                                                    <div class="table-responsive">
                                                        <table class="table table-striped">
                                                            <thead>
                                                                <tr>
                                                                    <th>ID</th>
                                                                    <th>Nome</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php 
                                                                $result = $conn->query("SELECT id_curso, nome FROM curso ORDER BY nome");
                                                                while($row = $result->fetch_assoc()): ?>
                                                                    <tr>
                                                                        <td><?php echo $row['id_curso']; ?></td>
                                                                        <td><?php echo $row['nome']; ?></td>
                                                                    </tr>
                                                                <?php endwhile; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Cadastro de Turmas -->
                                        <div class="col-md-6">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h5>Cadastro de Turmas</h5>
                                                    <span>Adicione novas turmas para os cursos</span>
                                                </div>
                                                <div class="card-block">
                                                    <?php if(isset($msg_turma)): ?>
                                                        <div class="alert alert-success"><?php echo $msg_turma; ?></div>
                                                    <?php endif; ?>
                                                    <?php if(isset($erro_turma)): ?>
                                                        <div class="alert alert-danger"><?php echo $erro_turma; ?></div>
                                                    <?php endif; ?>
                                                    
                                                    <form method="POST">
                                                        <div class="form-group">
                                                            <label for="nome_turma">Nome da Turma</label>
                                                            <input type="text" class="form-control" id="nome_turma" name="nome_turma" required>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="id_curso">Curso</label>
                                                            <select class="form-control" id="id_curso" name="id_curso" required>
                                                                <option value="">Selecione um curso</option>
                                                                <?php while($curso = $cursos->fetch_assoc()): ?>
                                                                    <option value="<?php echo $curso['id_curso']; ?>"><?php echo $curso['nome']; ?></option>
                                                                <?php endwhile; ?>
                                                            </select>
                                                        </div>
                                                        <button type="submit" name="cadastrar_turma" class="btn btn-primary">Cadastrar Turma</button>
                                                    </form>
                                                    
                                                    <hr>
                                                    <h5>Turmas Cadastradas</h5>
                                                    <div class="table-responsive">
                                                        <table class="table table-striped">
                                                            <thead>
                                                                <tr>
                                                                    <th>ID</th>
                                                                    <th>Nome</th>
                                                                    <th>Curso</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php 
                                                                $result = $conn->query("
                                                                    SELECT t.id_turma, t.nome AS turma, c.nome AS curso 
                                                                    FROM turma t 
                                                                    JOIN curso c ON t.curso_id_curso = c.id_curso 
                                                                    ORDER BY c.nome, t.nome
                                                                ");

                                                                while ($row = $result->fetch_assoc()): ?>
                                                                    <tr>
                                                                        <td><?php echo $row['id_turma']; ?></td>
                                                                        <td><?php echo htmlspecialchars($row['turma']); ?></td>
                                                                        <td><?php echo htmlspecialchars($row['curso']); ?></td>
                                                                    </tr>
                                                                <?php endwhile; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Cadastro de Disciplinas -->
                                        <div class="col-md-12">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h5>Cadastro de Disciplinas</h5>
                                                    <span>Adicione novas disciplinas para os cursos</span>
                                                </div>
                                                <div class="card-block">
                                                    <?php if(isset($msg_disciplina)): ?>
                                                        <div class="alert alert-success"><?php echo $msg_disciplina; ?></div>
                                                    <?php endif; ?>
                                                    <?php if(isset($erro_disciplina)): ?>
                                                        <div class="alert alert-danger"><?php echo $erro_disciplina; ?></div>
                                                    <?php endif; ?>
                                                    
                                                    <form method="POST">
                                                        <div class="form-group">
                                                            <label for="nome_disciplina">Nome da Disciplina</label>
                                                            <input type="text" class="form-control" id="nome_disciplina" name="nome_disciplina" required>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="id_curso_disciplina">Curso</label>
                                                            <select class="form-control" id="id_curso_disciplina" name="id_curso_disciplina" required>
                                                                <option value="">Selecione um curso</option>
                                                                <?php 
                                                                $cursos->data_seek(0); // Reinicia o ponteiro do resultado
                                                                while($curso = $cursos->fetch_assoc()): ?>
                                                                    <option value="<?php echo $curso['id_curso']; ?>"><?php echo $curso['nome']; ?></option>
                                                                <?php endwhile; ?>
                                                            </select>
                                                        </div>
                                                        <button type="submit" name="cadastrar_disciplina" class="btn btn-primary">Cadastrar Disciplina</button>
                                                    </form>
                                                    
                                                    <hr>
                                                    <h5>Disciplinas Cadastradas</h5>
                                                    <div class="table-responsive">
                                                        <table class="table table-striped">
                                                            <thead>
                                                                <tr>
                                                                    <th>ID</th>
                                                                    <th>Nome</th>
                                                                    <th>Curso</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php 
                                                                $result = $conn->query("
                                                                    SELECT d.id_disciplina, d.nome as disciplina, c.nome as curso 
                                                                    FROM disciplina d 
                                                                    JOIN curso c ON d.curso_id_curso = c.id_curso 
                                                                    ORDER BY c.nome, d.nome
                                                                ");
                                                                while($row = $result->fetch_assoc()): ?>
                                                                    <tr>
                                                                        <td><?php echo $row['id_disciplina']; ?></td>
                                                                        <td><?php echo $row['disciplina']; ?></td>
                                                                        <td><?php echo $row['curso']; ?></td>
                                                                    </tr>
                                                                <?php endwhile; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Page body end -->
                            </div>
                        </div>
                        <!-- Main-body end -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Required Jquery -->
<script type="text/javascript" src="libraries/bower_components/jquery/js/jquery.min.js"></script>
<script type="text/javascript" src="libraries/bower_components/jquery-ui/js/jquery-ui.min.js"></script>
<script type="text/javascript" src="libraries/bower_components/popper.js/js/popper.min.js"></script>
<script type="text/javascript" src="libraries/bower_components/bootstrap/js/bootstrap.min.js"></script>
<!-- jquery slimscroll js -->
<script type="text/javascript" src="libraries/bower_components/jquery-slimscroll/js/jquery.slimscroll.js"></script>
<!-- modernizr js -->
<script type="text/javascript" src="libraries/bower_components/modernizr/js/modernizr.js"></script>
<script type="text/javascript" src="libraries/bower_components/modernizr/js/css-scrollbars.js"></script>
<!-- sweet alert js -->
<script type="text/javascript" src="libraries/bower_components/sweetalert/js/sweetalert.min.js"></script>
<!-- Custom js -->
<script type="text/javascript" src="libraries/assets/js/script.js"></script>
<script src="libraries/assets/js/pcoded.min.js"></script>
<script src="libraries/assets/js/vartical-layout.min.js"></script>
<script src="libraries/assets/js/jquery.mCustomScrollbar.concat.min.js"></script>




<!-- epah-->
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
    <script data-cfasync="false" src="..\..\..\cdn-cgi\scripts\5c5dd728\cloudflare-static\email-decode.min.js"></script><script type="text/javascript" src="libraries\bower_components\jquery\js\jquery.min.js"></script>
    <script type="text/javascript" src="libraries\bower_components\jquery-ui\js\jquery-ui.min.js"></script>
    <script type="text/javascript" src="libraries\bower_components\popper.js\js\popper.min.js"></script>
    <script type="text/javascript" src="libraries\bower_components\bootstrap\js\bootstrap.min.js"></script>
    <!-- jquery slimscroll js -->
    <script type="text/javascript" src="libraries\bower_components\jquery-slimscroll\js\jquery.slimscroll.js"></script>
    <!-- modernizr js -->
    <script type="text/javascript" src="libraries\bower_components\modernizr\js\modernizr.js"></script>

    <!-- epah-->

<script>
// Função para exibir mensagens de sucesso/erro
$(document).ready(function() {
    // Verifica se há mensagens para exibir
    <?php if(isset($msg_curso) || isset($erro_curso) || isset($msg_turma) || isset($erro_turma) || isset($msg_disciplina) || isset($erro_disciplina)): ?>
        $('html, body').animate({
            scrollTop: $(".page-body").offset().top
        }, 1000);
    <?php endif; ?>
    
    // Atualiza a lista de cursos quando um novo curso é adicionado
    $('form').on('submit', function(e) {
        // Você pode adicionar validações adicionais aqui se necessário
    });
});
</script>
</body>
</html>