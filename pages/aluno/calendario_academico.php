<?php
    require_once '../../includes/common/permissoes.php';
    verificarPermissao(['aluno']);
    require_once '../../process/verificar_sessao.php';
    require_once '../../database/conexao.php';

    // Obter informações do aluno logado
    $id_usuario = $_SESSION['id_usuario'];
    $query_aluno = "SELECT a.id_aluno, t.id_turma, t.nome as turma, c.id_curso, c.nome as curso 
                    FROM aluno a
                    JOIN turma t ON a.turma_id_turma = t.id_turma
                    JOIN curso c ON a.curso_id_curso = c.id_curso
                    WHERE a.usuario_id_usuario = ?";
    $stmt_aluno = $conn->prepare($query_aluno);
    $stmt_aluno->bind_param("i", $id_usuario);
    $stmt_aluno->execute();
    $result_aluno = $stmt_aluno->get_result();
    $aluno = $result_aluno->fetch_assoc();

    // Obter horários de aula do aluno
    $query_horarios = "SELECT ca.dia_semana, ca.horario_inicio, ca.horario_fim, ca.sala,
                              d.nome as disciplina, u.nome as professor
                       FROM cronograma_aula ca
                       JOIN disciplina d ON ca.id_disciplina = d.id_disciplina
                       JOIN professor p ON ca.id_professor = p.id_professor
                       JOIN usuario u ON p.usuario_id_usuario = u.id_usuario
                       WHERE ca.turma_id_turma = ?
                       ORDER BY FIELD(ca.dia_semana, 'segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado'), 
                                ca.horario_inicio";
    $stmt_horarios = $conn->prepare($query_horarios);
    $stmt_horarios->bind_param("i", $aluno['id_turma']);
    $stmt_horarios->execute();
    $horarios = $stmt_horarios->get_result();

    // Organizar horários por dia da semana para exibição
    $horarios_por_dia = [
        'segunda' => [],
        'terca' => [],
        'quarta' => [],
        'quinta' => [],
        'sexta' => [],
        'sabado' => []
    ];

    while ($horario = $horarios->fetch_assoc()) {
        $horarios_por_dia[$horario['dia_semana']][] = $horario;
    }

    // Obter eventos acadêmicos (poderia ser de outra tabela, mas como não temos, usaremos exemplos)
    $eventos_academicos = [
        ['data' => date('Y-m-d', strtotime('+1 week')), 'titulo' => 'Início das Avaliações Bimestrais', 'descricao' => 'Período de avaliações do 1º bimestre'],
        ['data' => date('Y-m-d', strtotime('+3 weeks')), 'titulo' => 'Reunião de Pais e Mestres', 'descricao' => 'Das 14h às 17h'],
        ['data' => date('Y-m-d', strtotime('+5 weeks')), 'titulo' => 'Feriado Escolar', 'descricao' => 'Não haverá aula']
    ];
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <title>Calendário Acadêmico</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="icon" href="libraries\assets\images\favicon.ico" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="libraries\bower_components\bootstrap\css\bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="libraries\assets\icon\feather\css\feather.css">
    <link rel="stylesheet" type="text/css" href="libraries\assets\css\style.css">
    <link rel="stylesheet" type="text/css" href="libraries\assets\css\jquery.mCustomScrollbar.css">
    <!-- FullCalendar CSS -->
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    
    <style>
        .bg-img {
          width: 100%;
          height: auto;
          background-image: url('../public/img/bg.jpg');
          background-size: cover;
          background-position: center;
          background-repeat: no-repeat;
        }

        .card-calendario {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .card-header {
            background: rgba(7, 200, 206, 0.8);
            color: white;
            border-radius: 10px 10px 0 0 !important;
        }

        .horario-card {
            background: white;
            border-left: 4px solid #07c8ce;
            border-radius: 5px;
            margin-bottom: 10px;
            padding: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .horario-card h6 {
            color: #07c8ce;
            font-weight: bold;
        }

        .dia-semana {
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 2px solid #07c8ce;
        }

        .fc-event {
            cursor: pointer;
        }

        .badge-curso {
            background-color: #6c757d;
        }

        .badge-turma {
            background-color: #17a2b8;
        }

        #calendar {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
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
                                                <div class="card card-calendario">
                                                    <div class="card-header">
                                                        <h5><i class="feather icon-calendar"></i> Calendário Acadêmico e Horários</h5>
                                                        <div class="card-header-right">
                                                            <span class="badge badge-curso">Curso: <?php echo htmlspecialchars($aluno['curso']); ?></span>
                                                            <span class="badge badge-turma ml-2">Turma: <?php echo htmlspecialchars($aluno['turma']); ?></span>
                                                        </div>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div class="col-md-8">
                                                                <div id="calendar"></div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="card">
                                                                    <div class="card-header">
                                                                        <h5><i class="feather icon-clock"></i> Grade Horária</h5>
                                                                    </div>
                                                                    <div class="card-body">
                                                                        <?php 
                                                                        $dias_semana = [
                                                                            'segunda' => 'Segunda-feira',
                                                                            'terca' => 'Terça-feira',
                                                                            'quarta' => 'Quarta-feira',
                                                                            'quinta' => 'Quinta-feira',
                                                                            'sexta' => 'Sexta-feira',
                                                                            'sabado' => 'Sábado'
                                                                        ];
                                                                        
                                                                        $horarios_existem = false;
                                                                        
                                                                        foreach ($dias_semana as $dia => $dia_nome): 
                                                                            if (!empty($horarios_por_dia[$dia])): 
                                                                                $horarios_existem = true; ?>
                                                                                <div class="dia-semana"><?php echo $dia_nome; ?></div>
                                                                                <?php foreach ($horarios_por_dia[$dia] as $aula): ?>
                                                                                    <div class="horario-card">
                                                                                        <h6><?php echo htmlspecialchars($aula['disciplina']); ?></h6>
                                                                                        <p>
                                                                                            <i class="feather icon-clock"></i> 
                                                                                            <?php echo date('H:i', strtotime($aula['horario_inicio'])) . ' - ' . date('H:i', strtotime($aula['horario_fim'])); ?>
                                                                                        </p>
                                                                                        <p>
                                                                                            <i class="feather icon-user"></i> 
                                                                                            <?php echo htmlspecialchars($aula['professor']); ?>
                                                                                        </p>
                                                                                        <p>
                                                                                            <i class="feather icon-map-pin"></i> 
                                                                                            Sala <?php echo htmlspecialchars($aula['sala']); ?>
                                                                                        </p>
                                                                                    </div>
                                                                                <?php endforeach; ?>
                                                                            <?php endif; 
                                                                        endforeach; 
                                                                        
                                                                        if (!$horarios_existem): ?>
                                                                            <div class="alert alert-info">
                                                                                <i class="feather icon-info"></i> Nenhum horário cadastrado para sua turma.
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
                </div>
            </div>
        </div>
    </div>

    <!-- Required Jquery -->
    <script type="text/javascript" src="libraries\bower_components\jquery\js\jquery.min.js"></script>
    <script type="text/javascript" src="libraries\bower_components\jquery-ui\js\jquery-ui.min.js"></script>
    <script type="text/javascript" src="libraries\bower_components\popper.js\js\popper.min.js"></script>
    <script type="text/javascript" src="libraries\bower_components\bootstrap\js\bootstrap.min.js"></script>
    <!-- FullCalendar JS -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/pt-br.js'></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar calendário
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'pt-br',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                events: [
                    <?php foreach ($eventos_academicos as $evento): ?>
                    {
                        title: '<?php echo addslashes($evento['titulo']); ?>',
                        start: '<?php echo $evento['data']; ?>',
                        description: '<?php echo addslashes($evento['descricao']); ?>',
                        color: '#07c8ce'
                    },
                    <?php endforeach; ?>
                    
                    <?php 
                    // Adicionar aulas recorrentes ao calendário
                    foreach ($horarios_por_dia as $dia => $aulas): 
                        if (!empty($aulas)): 
                            $dias_semana_fc = [
                                'segunda' => 1,
                                'terca' => 2,
                                'quarta' => 3,
                                'quinta' => 4,
                                'sexta' => 5,
                                'sabado' => 6
                            ];
                            
                            foreach ($aulas as $aula): ?>
                            {
                                title: '<?php echo addslashes($aula['disciplina']); ?>',
                                daysOfWeek: [<?php echo $dias_semana_fc[$dia]; ?>],
                                startTime: '<?php echo $aula['horario_inicio']; ?>',
                                endTime: '<?php echo $aula['horario_fim']; ?>',
                                description: 'Professor: <?php echo addslashes($aula['professor']); ?>\nSala: <?php echo $aula['sala']; ?>',
                                color: '#17a2b8',
                                display: 'background',
                                textColor: 'white'
                            },
                            <?php endforeach; 
                        endif; 
                    endforeach; ?>
                ],
                eventClick: function(info) {
                    var eventObj = info.event;
                    
                    if (eventObj.extendedProps.description) {
                        alert(eventObj.title + "\n\n" + eventObj.extendedProps.description);
                    } else {
                        alert(eventObj.title);
                    }
                }
            });
            
            calendar.render();
        });
    </script>
    
    <!-- Outros scripts -->
    <script type="text/javascript" src="libraries\bower_components\jquery-slimscroll\js\jquery.slimscroll.js"></script>
    <script type="text/javascript" src="libraries\bower_components\modernizr\js\modernizr.js"></script>
    <script src="libraries\assets\js\jquery.mCustomScrollbar.concat.min.js"></script>
    <script type="text/javascript" src="libraries\assets\js\SmoothScroll.js"></script>
    <script src="libraries\assets\js\pcoded.min.js"></script>
    <script src="libraries\assets\js\vartical-layout.min.js"></script>
    <script type="text/javascript" src="libraries\assets\js\script.min.js"></script>
</body>
</html>