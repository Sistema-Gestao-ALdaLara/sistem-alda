<?php
//require_once "../auth/permissoes.php";
//verificarPermissao(['secretaria']);
require_once '../../database/conexao.php';

#if (!$pdo) {
    die("Erro na conexão com o banco de dados");
#}

$tipo = $_GET['tipo'] ?? 'matriculas_por_curso';
$data_inicio = $_GET['data_inicio'] ?? null;
$data_fim = $_GET['data_fim'] ?? null;

// Construir query baseada no tipo de relatório
switch ($tipo) {
    case 'matriculas_por_curso':
        $query = "SELECT c.nome AS curso, COUNT(m.id_matricula) AS total,
                 SUM(CASE WHEN m.status_matricula = 'aprovada' THEN 1 ELSE 0 END) AS aprovadas,
                 SUM(CASE WHEN m.status_matricula = 'pendente' THEN 1 ELSE 0 END) AS pendentes,
                 SUM(CASE WHEN m.status_matricula = 'rejeitada' THEN 1 ELSE 0 END) AS rejeitadas
                 FROM matricula m
                 JOIN curso c ON m.id_curso = c.id_curso
                 GROUP BY c.nome
                 ORDER BY total DESC";
        $titulo = "Matrículas por Curso";
        $colunas = ['curso', 'total', 'aprovadas', 'pendentes', 'rejeitadas'];
        break;
        
    case 'matriculas_por_periodo':
        $query = "SELECT DATE_FORMAT(m.data_matricula, '%m/%Y') AS periodo, 
                 COUNT(m.id_matricula) AS total,
                 SUM(CASE WHEN m.tipo_matricula = 'regular' THEN 1 ELSE 0 END) AS regulares,
                 SUM(CASE WHEN m.tipo_matricula = 'transferencia' THEN 1 ELSE 0 END) AS transferencias
                 FROM matricula m
                 WHERE m.data_matricula BETWEEN ? AND ?
                 GROUP BY periodo
                 ORDER BY m.data_matricula";
        $titulo = "Matrículas por Período";
        $colunas = ['periodo', 'total', 'regulares', 'transferencias'];
        break;
        
    case 'transferencias':
        $query = "SELECT a.numero_matricula, u.nome, c_orig.nome AS curso_origem,
                 c_dest.nome AS curso_destino, m.observacoes, 
                 DATE_FORMAT(m.data_matricula, '%d/%m/%Y') AS data
                 FROM matricula m
                 JOIN aluno a ON m.id_aluno = a.id_aluno
                 JOIN usuario u ON a.id_usuario = u.id_usuario
                 JOIN curso c_dest ON m.id_curso = c_dest.id_curso
                 JOIN curso c_orig ON a.id_curso = c_orig.id_curso
                 WHERE m.tipo_matricula = 'transferencia'
                 AND m.data_matricula BETWEEN ? AND ?
                 ORDER BY m.data_matricula DESC";
        $titulo = "Transferências Realizadas";
        $colunas = ['numero_matricula', 'nome', 'curso_origem', 'curso_destino', 'observacoes', 'data'];
        break;
        
    case 'status_matriculas':
    default:
        $query = "SELECT m.status_matricula AS status, COUNT(*) AS total,
                 GROUP_CONCAT(DISTINCT c.nome SEPARATOR ', ') AS cursos
                 FROM matricula m
                 JOIN curso c ON m.id_curso = c.id_curso
                 GROUP BY m.status_matricula
                 ORDER BY total DESC";
        $titulo = "Status das Matrículas";
        $colunas = ['status', 'total', 'cursos'];
}

// Executar query com parâmetros se necessário
$stmt = $pdo->prepare($query);

if ($tipo === 'matriculas_por_periodo' || $tipo === 'transferencias') {
    $data_inicio = $data_inicio ?: date('Y-m-01');
    $data_fim = $data_fim ?: date('Y-m-d');
    $stmt->execute([$data_inicio, $data_fim]);
} else {
    $stmt->execute();
}

$dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <title>SECRETARIA - <?= htmlspecialchars($titulo) ?></title>
    <!-- Meta -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Relatório de Matrículas - Escola Alda Lara">
    <meta name="keywords" content="escola, alda lara, matrículas, relatórios">
    <meta name="author" content="Escola Alda Lara">
    
    <!-- Favicon -->
    <link rel="icon" href="libraries/assets/images/favicon.ico" type="image/x-icon">
    
    <!-- Google font-->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600" rel="stylesheet">
    
    <!-- Bootstrap local -->
    <link rel="stylesheet" type="text/css" href="libraries/bower_components/bootstrap/css/bootstrap.min.css">
    
    <!-- Feather icons -->
    <link rel="stylesheet" type="text/css" href="libraries/assets/icon/feather/css/feather.css">
    
    <!-- Estilos customizados -->
    <link rel="stylesheet" type="text/css" href="libraries/assets/css/style.css">
    <link rel="stylesheet" type="text/css" href="libraries/assets/css/jquery.mCustomScrollbar.css">
    
    <style>
        .report-header {
            background-color: rgba(7, 200, 206, 0.2);
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .table-report {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(8px);
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .table-report th {
            background: rgba(7, 200, 206, 0.55);
            color: white;
        }
        .table-report td {
            color: #333;
        }
        .badge-status {
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: 500;
        }
        .badge-aprovada {
            background-color: #28a745;
            color: white;
        }
        .badge-pendente {
            background-color: #ffc107;
            color: #212529;
        }
        .badge-rejeitada {
            background-color: #dc3545;
            color: white;
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
            <!-- [Cabeçalho existente] -->
            <!-- [Sidebar existente] -->

            <div class="pcoded-content">
                <div class="pcoded-inner-content">
                    <div class="main-body">
                        <div class="page-wrapper">
                            <div class="page-body">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="card card-table">
                                            <div class="card-header">
                                                <h5><?= htmlspecialchars($titulo) ?></h5>
                                                <div class="float-right">
                                                    <a href="javascript:window.print()" class="btn btn-sm btn-primary">
                                                        <i class="feather icon-printer"></i> Imprimir
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="card-block">
                                                <?php if ($data_inicio): ?>
                                                <div class="alert alert-info">
                                                    Período: <?= date('d/m/Y', strtotime($data_inicio)) ?> a <?= date('d/m/Y', strtotime($data_fim)) ?>
                                                </div>
                                                <?php endif; ?>
                                                
                                                <div class="table-responsive">
                                                    <table class="table table-report table-hover">
                                                        <thead>
                                                            <tr>
                                                                <?php foreach ($colunas as $col): ?>
                                                                <th><?= ucfirst(str_replace('_', ' ', $col)) ?></th>
                                                                <?php endforeach; ?>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($dados as $linha): ?>
                                                            <tr>
                                                                <?php foreach ($colunas as $col): ?>
                                                                <td>
                                                                    <?php if ($col === 'status'): ?>
                                                                        <span class="badge-status badge-<?= 
                                                                            $linha[$col] === 'aprovada' ? 'aprovada' : 
                                                                            ($linha[$col] === 'pendente' ? 'pendente' : 'rejeitada') 
                                                                        ?>">
                                                                            <?= ucfirst($linha[$col]) ?>
                                                                        </span>
                                                                    <?php else: ?>
                                                                        <?= htmlspecialchars($linha[$col] ?? 'N/D') ?>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <?php endforeach; ?>
                                                            </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                                
                                                <div class="text-right mt-3">
                                                    <small class="text-muted">
                                                        <i class="feather icon-clock"></i> Emitido em: <?= date('d/m/Y H:i:s') ?>
                                                    </small>
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

    <!-- Scripts locais -->
    <script type="text/javascript" src="libraries/bower_components/jquery/js/jquery.min.js"></script>
    <script type="text/javascript" src="libraries/bower_components/jquery-ui/js/jquery-ui.min.js"></script>
    <script type="text/javascript" src="libraries/bower_components/popper.js/js/popper.min.js"></script>
    <script type="text/javascript" src="libraries/bower_components/bootstrap/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="libraries/bower_components/jquery-slimscroll/js/jquery.slimscroll.js"></script>
    <script type="text/javascript" src="libraries/bower_components/modernizr/js/modernizr.js"></script>
    <script src="libraries/assets/js/jquery.mCustomScrollbar.concat.min.js"></script>
    <script src="libraries/assets/js/pcoded.min.js"></script>
    <script src="libraries/assets/js/vartical-layout.min.js"></script>
    <script type="text/javascript" src="libraries/assets/js/script.min.js"></script>
</body>
</html>