<?php
    require_once "permissoes.php";
    verificarPermissao(['aluno']);
    require_once 'verificar_sessao.php';
    require_once '../../database/conexao.php';

    // Obter informações do aluno logado
    $id_usuario = $_SESSION['id_usuario'];
    $query_aluno = "SELECT a.id_aluno, t.nome as turma, c.nome as curso 
                    FROM aluno a
                    JOIN turma t ON a.turma_id_turma = t.id_turma
                    JOIN curso c ON a.curso_id_curso = c.id_curso
                    WHERE a.usuario_id_usuario = ?";
    $stmt_aluno = $conn->prepare($query_aluno);
    $stmt_aluno->bind_param("i", $id_usuario);
    $stmt_aluno->execute();
    $result_aluno = $stmt_aluno->get_result();
    $aluno = $result_aluno->fetch_assoc();

    // Obter disciplinas do aluno
    $query_disciplinas = "SELECT d.id_disciplina, d.nome as disciplina, 
                                 u.nome as professor, u.foto_perfil,
                                 p.id_professor
                          FROM disciplina d
                          JOIN professor p ON d.professor_id_professor = p.id_professor
                          JOIN usuario u ON p.usuario_id_usuario = u.id_usuario
                          JOIN curso c ON d.curso_id_curso = c.id_curso
                          WHERE c.id_curso = ?
                          ORDER BY d.nome";
    $stmt_disciplinas = $conn->prepare($query_disciplinas);
    $stmt_disciplinas->bind_param("i", $aluno['curso_id_curso']);
    $stmt_disciplinas->execute();
    $disciplinas = $stmt_disciplinas->get_result();
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <title>Minhas Disciplinas</title>
    <!-- Meta -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
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
          width: 100%;
          height: auto;
          background-image: url('../public/img/bg.jpg');
          background-size: cover;
          background-position: center;
          background-repeat: no-repeat;
        }

        .card-disciplina {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(8px);
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            transition: transform 0.3s;
            margin-bottom: 20px;
        }

        .card-disciplina:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .card-disciplina .card-header {
            background: rgba(7, 200, 206, 0.55);
            color: white;
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
            font-weight: bold;
        }

        .professor-img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid rgba(255, 255, 255, 0.3);
        }

        .disciplina-info {
            padding: 15px;
        }

        .disciplina-nome {
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .professor-nome {
            font-size: 1rem;
            color: #07c8ce;
        }

        .empty-message {
            text-align: center;
            padding: 50px;
            color: #ccc;
            font-size: 1.2rem;
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
                            <div class="main-body bg-img">
                                <div class="page-wrapper">
                                    <div class="page-body">
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <div class="card">
                                                    <div class="card-header">
                                                        <h5><i class="feather icon-book"></i> Minhas Disciplinas</h5>
                                                        <div class="card-header-right">
                                                            <span class="badge badge-primary">Curso: <?php echo htmlspecialchars($aluno['curso']); ?></span>
                                                            <span class="badge badge-info ml-2">Turma: <?php echo htmlspecialchars($aluno['turma']); ?></span>
                                                        </div>
                                                    </div>
                                                    <div class="card-body">
                                                        <?php if ($disciplinas->num_rows > 0): ?>
                                                            <div class="row">
                                                                <?php while ($disciplina = $disciplinas->fetch_assoc()): ?>
                                                                    <div class="col-md-6 col-lg-4">
                                                                        <div class="card card-disciplina">
                                                                            <div class="card-header">
                                                                                <?php echo htmlspecialchars($disciplina['disciplina']); ?>
                                                                            </div>
                                                                            <div class="card-body text-center">
                                                                                <div class="disciplina-info">
                                                                                    <img src="<?php echo !empty($disciplina['foto_perfil']) ? htmlspecialchars($disciplina['foto_perfil']) : 'libraries/assets/images/avatar-2.jpg'; ?>" 
                                                                                         alt="Professor" class="professor-img mb-3">
                                                                                    <div class="professor-nome">
                                                                                        <i class="feather icon-user"></i> 
                                                                                        <?php echo htmlspecialchars($disciplina['professor']); ?>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="card-footer text-center">
                                                                                <a href="disciplina_detalhes.php?id=<?php echo $disciplina['id_disciplina']; ?>" 
                                                                                   class="btn btn-sm btn-primary">
                                                                                    <i class="feather icon-info"></i> Detalhes
                                                                                </a>
                                                                                <a href="materiais.php?disciplina=<?php echo $disciplina['id_disciplina']; ?>" 
                                                                                   class="btn btn-sm btn-success ml-2">
                                                                                    <i class="feather icon-folder"></i> Materiais
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                <?php endwhile; ?>
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="empty-message">
                                                                <i class="feather icon-book-open" style="font-size: 3rem;"></i>
                                                                <p class="mt-3">Nenhuma disciplina encontrada para o seu curso.</p>
                                                            </div>
                                                        <?php endif; ?>
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

    <!-- Required Jquery -->
    <script type="text/javascript" src="libraries\bower_components\jquery\js\jquery.min.js"></script>
    <script type="text/javascript" src="libraries\bower_components\jquery-ui\js\jquery-ui.min.js"></script>
    <script type="text/javascript" src="libraries\bower_components\popper.js\js\popper.min.js"></script>
    <script type="text/javascript" src="libraries\bower_components\bootstrap\js\bootstrap.min.js"></script>
    <!-- jquery slimscroll js -->
    <script type="text/javascript" src="libraries\bower_components\jquery-slimscroll\js\jquery.slimscroll.js"></script>
    <!-- modernizr js -->
    <script type="text/javascript" src="libraries\bower_components\modernizr\js\modernizr.js"></script>
    <!-- amchart js -->
    <script src="libraries\assets\pages\widget\amchart\amcharts.js"></script>
    <script src="libraries\assets\pages\widget\amchart\serial.js"></script>
    <script src="libraries\assets\pages\widget\amchart\light.js"></script>
    <script src="libraries\assets\js\jquery.mCustomScrollbar.concat.min.js"></script>
    <script type="text/javascript" src="libraries\assets\js\SmoothScroll.js"></script>
    <script src="libraries\assets\js\pcoded.min.js"></script>
    <!-- custom js -->
    <script src="libraries\assets\js\vartical-layout.min.js"></script>
    <script type="text/javascript" src="libraries\assets\js\script.min.js"></script>
</body>
</html>