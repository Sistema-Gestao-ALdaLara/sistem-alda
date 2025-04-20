<?php
require_once '../../includes/common/permissoes.php';
verificarPermissao(['aluno']);
require_once '../../process/verificar_sessao.php';
require_once '../../database/conexao.php';

// Obter ID da turma do aluno logado
$id_usuario = $_SESSION['id_usuario'];
$query_turma = "SELECT turma_id_turma FROM aluno WHERE usuario_id_usuario = ?";
$stmt_turma = $conn->prepare($query_turma);
$stmt_turma->bind_param("i", $id_usuario);
$stmt_turma->execute();
$result_turma = $stmt_turma->get_result();
$turma_aluno = $result_turma->fetch_assoc();
$id_turma = $turma_aluno['turma_id_turma'];

// Obter horários de aula da turma
$query_horarios = "SELECT 
                    ca.dia_semana, 
                    ca.horario_inicio, 
                    ca.horario_fim, 
                    ca.sala,
                    d.nome as disciplina
                   FROM cronograma_aula ca
                   JOIN disciplina d ON ca.id_disciplina = d.id_disciplina
                   WHERE ca.turma_id_turma = ?
                   ORDER BY FIELD(ca.dia_semana, 'segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado'), 
                            ca.horario_inicio";
$stmt_horarios = $conn->prepare($query_horarios);
$stmt_horarios->bind_param("i", $id_turma);
$stmt_horarios->execute();
$horarios = $stmt_horarios->get_result();

// Organizar horários por dia da semana
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
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <title>Horários Escolares | Alda Lara</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <!-- CSS local -->
    <link rel="stylesheet" href="../../public/libraries/bower_components/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../public/libraries/assets/icon/feather/css/feather.css">
    <link rel="stylesheet" href="../../public/libraries/assets/css/style.css">
    
    <style>
        .bg-img {
            background-image: url('../../public/img/bg.jpg');
            background-size: cover;
            background-position: center;
            min-height: 100vh;
        }
        
        .card-horario {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .card-header-horario {
            background: #07c8ce;
            color: white;
            border-radius: 10px 10px 0 0 !important;
        }
        
        .dia-semana {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            font-weight: bold;
        }
        
        .aula-item {
            border-left: 4px solid #07c8ce;
            margin-bottom: 10px;
            padding: 10px;
            background: white;
            border-radius: 5px;
        }
        
        .hora-aula {
            font-weight: bold;
            color: #07c8ce;
        }
        
        .nome-disciplina {
            font-weight: bold;
        }
        
        .sala-aula {
            font-style: italic;
        }
    </style>
</head>
<body>
    <?php require_once '../../includes/common/preloader.php'; ?>

    <div id="pcoded" class="pcoded">
        <div class="pcoded-overlay-box"></div>
        <div class="pcoded-container navbar-wrapper">
            <?php require_once '../../includes/aluno/navbar.php'; ?>

            <div class="pcoded-main-container">
                <div class="pcoded-wrapper">
                    <?php require_once '../../includes/aluno/sidebar.php'; ?>
                    
                    <div class="pcoded-content">
                        <div class="pcoded-inner-content">
                            <div class="main-body bg-img">
                                <div class="page-wrapper">
                                    <div class="page-body">
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <div class="card card-horario">
                                                    <div class="card-header card-header-horario">
                                                        <h5><i class="feather icon-clock"></i> Grade de Horários</h5>
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
                                                                    <div class="aula-item">
                                                                        <div class="hora-aula">
                                                                            <?php echo date('H:i', strtotime($aula['horario_inicio'])) . ' - ' . date('H:i', strtotime($aula['horario_fim'])); ?>
                                                                        </div>
                                                                        <div class="nome-disciplina">
                                                                            <?php echo htmlspecialchars($aula['disciplina']); ?>
                                                                        </div>
                                                                        <div class="sala-aula">
                                                                            Sala: <?php echo htmlspecialchars($aula['sala']); ?>
                                                                        </div>
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

    <!-- JavaScript local -->
    <script src="../../public/libraries/bower_components/jquery/js/jquery.min.js"></script>
    <script src="../../public/libraries/bower_components/jquery-ui/js/jquery-ui.min.js"></script>
    <script src="../../public/libraries/bower_components/popper.js/js/popper.min.js"></script>
    <script src="../../public/libraries/bower_components/bootstrap/js/bootstrap.min.js"></script>
    <script src="../../public/libraries/assets/js/pcoded.min.js"></script>
    <script src="../../public/libraries/assets/js/vartical-layout.min.js"></script>
    <script src="../../public/libraries/assets/js/script.min.js"></script>
</body>
</html>