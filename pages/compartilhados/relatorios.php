<?php
require_once '../../includes/common/permissoes.php';
verificarPermissao(['secretaria', 'diretor_geral', 'diretor_pedagogico']);
require_once '../../process/verificar_sessao.php';
require_once '../../database/conexao.php';

$title = "Relatórios Acadêmicos";

// Processar solicitação de relatório
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gerar_relatorio'])) {
    $tipo_relatorio = $conn->real_escape_string($_POST['tipo_relatorio']);
    $filtro_curso = isset($_POST['curso']) ? intval($_POST['curso']) : null;
    $filtro_turma = isset($_POST['turma']) ? intval($_POST['turma']) : null;
    $filtro_classe = isset($_POST['classe']) ? $conn->real_escape_string($_POST['classe']) : null;
    $trimestre = isset($_POST['trimestre']) ? intval($_POST['trimestre']) : null;
    $ano_letivo = isset($_POST['ano_letivo']) ? $conn->real_escape_string($_POST['ano_letivo']) : date('Y');
    $destinatarios = isset($_POST['destinatarios']) ? $_POST['destinatarios'] : [];
    
    // Validar dados
    if (empty($tipo_relatorio)) {
        $_SESSION['erro'] = "Selecione o tipo de relatório.";
    } elseif (empty($destinatarios)) {
        $_SESSION['erro'] = "Selecione pelo menos um destinatário.";
    } else {
        // Armazenar parâmetros em sessão para a geração do relatório
        $_SESSION['relatorio_params'] = [
            'tipo' => $tipo_relatorio,
            'curso' => $filtro_curso,
            'turma' => $filtro_turma,
            'classe' => $filtro_classe,
            'trimestre' => $trimestre,
            'ano_letivo' => $ano_letivo,
            'destinatarios' => $destinatarios
        ];
        
        // Redirecionar para a página de visualização do relatório
        header('Location: visualizar_relatorio.php');
        exit();
    }
}

// Obter cursos para filtros
$query_cursos = "SELECT id_curso, nome FROM curso ORDER BY nome";
$result_cursos = $conn->query($query_cursos);
$cursos = $result_cursos->fetch_all(MYSQLI_ASSOC);

// Obter turmas para filtros
$query_turmas = "SELECT id_turma, nome, classe FROM turma ORDER BY nome";
$result_turmas = $conn->query($query_turmas);
$turmas = $result_turmas->fetch_all(MYSQLI_ASSOC);

// Obter classes distintas
$query_classes = "SELECT DISTINCT classe FROM turma ORDER BY classe";
$result_classes = $conn->query($query_classes);
$classes = $result_classes->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <title><?= $title ?> | Sistema Escolar</title>
    <?php require_once '../../includes/common/head.php'; ?>
    
    <style>
        .card-relatorio {
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .card-header-relatorio {
            background-color: #4680ff;
            color: white;
            border-radius: 8px 8px 0 0 !important;
            padding: 15px 20px;
        }
        .filter-section {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .destinatarios-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .destinatario-checkbox {
            background-color: #f1f1f1;
            padding: 8px 15px;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .destinatario-checkbox:hover {
            background-color: #e0e0e0;
        }
        .destinatario-checkbox input[type="checkbox"] {
            margin-right: 8px;
        }
        .btn-gerar {
            background-color: #28a745;
            color: white;
            font-weight: bold;
            padding: 10px 25px;
            border-radius: 5px;
            border: none;
            transition: all 0.3s;
        }
        .btn-gerar:hover {
            background-color: #218838;
        }
    </style>
</head>

<body>
    <?php require_once '../../includes/common/preloader.php'; ?>

    <div id="pcoded" class="pcoded">
        <div class="pcoded-overlay-box"></div>
        <div class="pcoded-container navbar-wrapper">
            <?php require_once '../../includes/secretaria/navbar.php'; ?>

            <div class="pcoded-main-container">
                <div class="pcoded-wrapper">
                    <?php require_once '../../includes/secretaria/sidebar.php'; ?>
                    
                    <div class="pcoded-content">
                        <div class="pcoded-inner-content">
                            <div class="main-body bg-img">
                                <div class="page-wrapper">
                                    <div class="page-body">
                                        <!-- Mensagens de feedback -->
                                        <?php if (isset($_SESSION['erro'])): ?>
                                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                            <?= $_SESSION['erro'] ?>
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <?php unset($_SESSION['erro']); endif; ?>
                                        
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="card card-relatorio">
                                                    <div class="card-header card-header-relatorio">
                                                        <h5>Gerar Relatório Acadêmico</h5>
                                                    </div>
                                                    <div class="card-body">
                                                        <form method="POST" action="">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label for="tipo_relatorio">Tipo de Relatório *</label>
                                                                        <select class="form-control" id="tipo_relatorio" name="tipo_relatorio" required>
                                                                            <option value="">Selecione o tipo...</option>
                                                                            <option value="matriculas">Lista de Matrículas</option>
                                                                            <option value="notas">Boletim de Notas</option>
                                                                            <option value="frequencia">Relatório de Frequência</option>
                                                                            <option value="desempenho">Desempenho por Disciplina</option>
                                                                            <option value="planos_ensino">Status dos Planos de Ensino</option>
                                                                            <option value="transferencias">Transferências e Cancelamentos</option>
                                                                        </select>
                                                                    </div>
                                                                    
                                                                    <div id="filtros-container">
                                                                        <!-- Filtros dinâmicos serão carregados aqui via JavaScript -->
                                                                    </div>
                                                                </div>
                                                                
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label>Destinatários *</label>
                                                                        <div class="destinatarios-container">
                                                                            <label class="destinatario-checkbox">
                                                                                <input type="checkbox" name="destinatarios[]" value="diretor_geral"> Diretor Geral
                                                                            </label>
                                                                            <label class="destinatario-checkbox">
                                                                                <input type="checkbox" name="destinatarios[]" value="diretor_pedagogico"> Diretor Pedagógico
                                                                            </label>
                                                                            <label class="destinatario-checkbox">
                                                                                <input type="checkbox" name="destinatarios[]" value="coordenadores"> Coordenadores de Curso
                                                                            </label>
                                                                            <label class="destinatario-checkbox">
                                                                                <input type="checkbox" name="destinatarios[]" value="professores"> Professores
                                                                            </label>
                                                                            <label class="destinatario-checkbox">
                                                                                <input type="checkbox" name="destinatarios[]" value="alunos"> Alunos
                                                                            </label>
                                                                            <label class="destinatario-checkbox">
                                                                                <input type="checkbox" name="destinatarios[]" value="secretaria"> Secretaria
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <div class="form-group mt-4">
                                                                        <label for="ano_letivo">Ano Letivo</label>
                                                                        <select class="form-control" id="ano_letivo" name="ano_letivo">
                                                                            <?php
                                                                            $current_year = date('Y');
                                                                            for ($i = $current_year - 2; $i <= $current_year + 1; $i++) {
                                                                                echo "<option value=\"$i\"" . ($i == $current_year ? ' selected' : '') . ">$i</option>";
                                                                            }
                                                                            ?>
                                                                        </select>
                                                                    </div>
                                                                    
                                                                    <div class="form-group" id="trimestre-container" style="display: none;">
                                                                        <label for="trimestre">Trimestre</label>
                                                                        <select class="form-control" id="trimestre" name="trimestre">
                                                                            <option value="1">1º Trimestre</option>
                                                                            <option value="2">2º Trimestre</option>
                                                                            <option value="3">3º Trimestre</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="text-center mt-4">
                                                                <button type="submit" name="gerar_relatorio" class="btn btn-gerar">
                                                                    <i class="feather icon-file-text"></i> Gerar Relatório
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Histórico de Relatórios Gerados -->
                                            <div class="col-md-12">
                                                <div class="card card-relatorio">
                                                    <div class="card-header card-header-relatorio">
                                                        <h5>Histórico de Relatórios</h5>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="table-responsive">
                                                            <table class="table table-hover">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Tipo</th>
                                                                        <th>Parâmetros</th>
                                                                        <th>Data</th>
                                                                        <th>Destinatários</th>
                                                                        <th>Ações</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <tr>
                                                                        <td>Boletim de Notas</td>
                                                                        <td>Turma: I11AM, 1º Trimestre 2025</td>
                                                                        <td>15/03/2025</td>
                                                                        <td>Diretor Pedagógico, Professores</td>
                                                                        <td>
                                                                            <button class="btn btn-info btn-sm">
                                                                                <i class="feather icon-download"></i> Baixar
                                                                            </button>
                                                                            <button class="btn btn-secondary btn-sm">
                                                                                <i class="feather icon-send"></i> Reenviar
                                                                            </button>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Lista de Matrículas</td>
                                                                        <td>Curso: Informática, Ano 2025</td>
                                                                        <td>10/03/2025</td>
                                                                        <td>Diretor Geral, Secretaria</td>
                                                                        <td>
                                                                            <button class="btn btn-info btn-sm">
                                                                                <i class="feather icon-download"></i> Baixar
                                                                            </button>
                                                                            <button class="btn btn-secondary btn-sm">
                                                                                <i class="feather icon-send"></i> Reenviar
                                                                            </button>
                                                                        </td>
                                                                    </tr>
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
    </div>

    <?php require_once '../../includes/common/js_imports.php'; ?>
    
    <script>
        $(document).ready(function() {
            // Mostrar/ocultar filtros com base no tipo de relatório
            $('#tipo_relatorio').change(function() {
                const tipo = $(this).val();
                let html = '';
                
                // Mostrar campo de trimestre apenas para relatórios de notas
                if (tipo === 'notas' || tipo === 'frequencia' || tipo === 'desempenho') {
                    $('#trimestre-container').show();
                } else {
                    $('#trimestre-container').hide();
                }
                
                // Gerar filtros específicos
                switch(tipo) {
                    case 'matriculas':
                        html = `
                            <div class="filter-section">
                                <h6>Filtrar por:</h6>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="curso">Curso</label>
                                            <select class="form-control" id="curso" name="curso">
                                                <option value="">Todos</option>
                                                <?php foreach ($cursos as $curso): ?>
                                                <option value="<?= $curso['id_curso'] ?>"><?= htmlspecialchars($curso['nome']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="turma">Turma</label>
                                            <select class="form-control" id="turma" name="turma">
                                                <option value="">Todas</option>
                                                <?php foreach ($turmas as $turma): ?>
                                                <option value="<?= $turma['id_turma'] ?>"><?= htmlspecialchars($turma['nome']) ?> (<?= $turma['classe'] ?>)</option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="classe">Classe</label>
                                            <select class="form-control" id="classe" name="classe">
                                                <option value="">Todas</option>
                                                <?php foreach ($classes as $classe): ?>
                                                <option value="<?= $classe['classe'] ?>"><?= $classe['classe'] ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                        break;
                        
                    case 'notas':
                    case 'frequencia':
                    case 'desempenho':
                        html = `
                            <div class="filter-section">
                                <h6>Filtrar por:</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="turma">Turma</label>
                                            <select class="form-control" id="turma" name="turma" required>
                                                <option value="">Selecione...</option>
                                                <?php foreach ($turmas as $turma): ?>
                                                <option value="<?= $turma['id_turma'] ?>"><?= htmlspecialchars($turma['nome']) ?> (<?= $turma['classe'] ?>)</option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="classe">Classe</label>
                                            <select class="form-control" id="classe" name="classe">
                                                <option value="">Todas</option>
                                                <?php foreach ($classes as $classe): ?>
                                                <option value="<?= $classe['classe'] ?>"><?= $classe['classe'] ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                        break;
                        
                    case 'planos_ensino':
                        html = `
                            <div class="filter-section">
                                <h6>Filtrar por:</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="curso">Curso</label>
                                            <select class="form-control" id="curso" name="curso">
                                                <option value="">Todos</option>
                                                <?php foreach ($cursos as $curso): ?>
                                                <option value="<?= $curso['id_curso'] ?>"><?= htmlspecialchars($curso['nome']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="status">Status</label>
                                            <select class="form-control" id="status" name="status">
                                                <option value="">Todos</option>
                                                <option value="rascunho">Rascunho</option>
                                                <option value="submetido">Submetido</option>
                                                <option value="aprovado">Aprovado</option>
                                                <option value="rejeitado">Rejeitado</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                        break;
                }
                
                $('#filtros-container').html(html);
            });
            
            // Validação do formulário
            $('form').submit(function(e) {
                const tipoRelatorio = $('#tipo_relatorio').val();
                const destinatarios = $('input[name="destinatarios[]"]:checked').length;
                
                if (!tipoRelatorio) {
                    alert('Selecione o tipo de relatório.');
                    e.preventDefault();
                    return false;
                }
                
                if (destinatarios === 0) {
                    alert('Selecione pelo menos um destinatário.');
                    e.preventDefault();
                    return false;
                }
                
                // Validação adicional para relatórios que requerem turma
                if ((tipoRelatorio === 'notas' || tipoRelatorio === 'frequencia' || tipoRelatorio === 'desempenho') && !$('#turma').val()) {
                    alert('Para este relatório, selecione uma turma.');
                    e.preventDefault();
                    return false;
                }
                
                return true;
            });
        });
    </script>
</body>
</html>