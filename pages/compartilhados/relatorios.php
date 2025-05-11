<?php
require_once '../../includes/common/permissoes.php';
verificarPermissao(['secretaria', 'diretor_geral', 'diretor_pedagogico', 'coordenador']);
require_once '../../process/verificar_sessao.php';
require_once '../../database/conexao.php';

$title = "Relatórios";

// Tipos de relatórios disponíveis com descrições
$tipos_relatorios = [
    'matriculas' => [
        'nome' => 'Matrículas',
        'descricao' => 'Relatório de matrículas por curso, turma ou período'
    ],
    'notas' => [
        'nome' => 'Notas e Resultados',
        'descricao' => 'Relatório de desempenho acadêmico dos alunos'
    ],
    'frequencia' => [
        'nome' => 'Frequência',
        'descricao' => 'Relatório de presenças e ausências dos alunos'
    ],
    'desempenho' => [
        'nome' => 'Desempenho Acadêmico',
        'descricao' => 'Análise comparativa de desempenho por turma/disciplina'
    ],
    'planos_ensino' => [
        'nome' => 'Planos de Ensino',
        'descricao' => 'Status dos planos de ensino por disciplina'
    ],
    'professores' => [
        'nome' => 'Professores',
        'descricao' => 'Relatório de professores e seu histórico'
    ]
];

// Obter anos letivos disponíveis
$query_anos = "SELECT DISTINCT ano_letivo FROM matricula ORDER BY ano_letivo DESC";
$result_anos = $conn->query($query_anos);
$anos_letivos = $result_anos->fetch_all(MYSQLI_ASSOC);

// Obter cursos para filtros
$query_cursos = "SELECT id_curso, nome FROM curso ORDER BY nome";
$result_cursos = $conn->query($query_cursos);
$cursos = $result_cursos->fetch_all(MYSQLI_ASSOC);

// Processar solicitação de novo relatório
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gerar_relatorio'])) {
    $tipo = $_POST['tipo'];
    $parametros = http_build_query($_POST);
    
    // Redirecionar para a página de visualização correspondente
    header("Location: ../visualizacoes/$tipo.php?$parametros");
    exit();
}



// Consulta para listar relatórios
$query = "SELECT * FROM relatorio WHERE usuario_id_gerador = ? ORDER BY data_geracao DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $_SESSION['id_usuario']);
$stmt->execute();
$relatorios = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);


$tipo_usuario = $_SESSION['tipo_usuario'];
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <?php require_once '../../includes/common/head.php'; ?>
    <style>
        .card-body {
            padding: 20px;
        }
        .tipo-relatorio {
            cursor: pointer;
            transition: all 0.2s;
        }
        .tipo-relatorio:hover {
            background-color: #f1f1f1;
        }
        .tipo-relatorio.active {
            border-left: 4px solid #007bff;
            background-color: #f8f9fa;
        }
        .filtro-tipo {
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div id="pcoded" class="pcoded">
        <div class="pcoded-overlay-box"></div>
        <div class="pcoded-container navbar-wrapper">
            <?php require_once "../../includes/$tipo_usuario/navbar.php"; ?>

            <div class="pcoded-main-container">
                <div class="pcoded-wrapper">
                    <?php require_once "../../includes/$tipo_usuario/sidebar.php"; ?>
                    
                    <div class="pcoded-content">
                        <div class="pcoded-inner-content">
                            <div class="main-body bg-img">
                                <div class="page-wrapper">
                                    <div class="page-body">
                                    <!-- Mensagens de feedback -->
                                    <?php if (isset($_SESSION['sucesso'])): ?>
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        <?= $_SESSION['sucesso'] ?>
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <?php unset($_SESSION['sucesso']); endif; ?>
                                    
                                    <?php if (isset($_SESSION['erro'])): ?>
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <?= $_SESSION['erro'] ?>
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <?php unset($_SESSION['erro']); endif; ?>
    
                                        <div class="row">
                                            <!-- Seleção de Tipo de Relatório -->
                                            <div class="col-md-4">
                                                <div class="card">
                                                    <div class="card-header">
                                                        <h5>Tipos de Relatório</h5>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="list-group">
                                                            <?php foreach ($tipos_relatorios as $key => $tipo_rel): ?>
                                                            <div class="list-group-item tipo-relatorio <?= isset($_GET['tipo']) && $_GET['tipo'] == $key ? 'active' : '' ?>" 
                                                                 onclick="selecionarTipoRelatorio('<?= $key ?>')">
                                                                <h6><?= $tipo_rel['nome'] ?></h6>
                                                                <small class="text-muted"><?= $tipo_rel['descricao'] ?></small>
                                                            </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Formulário de Geração -->
                                            <div class="col-md-8">
                                                <div class="card">
                                                    <div class="card-header">
                                                        <h5>Configurar Relatório</h5>
                                                    </div>
                                                    <div class="card-body">
                                                        <form method="POST" action="">
                                                            <input type="hidden" id="tipo_relatorio" name="tipo" value="<?= $_GET['tipo'] ?? 'matriculas' ?>">
                                                            
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label for="ano_letivo">Ano Letivo *</label>
                                                                        <select class="form-control" id="ano_letivo" name="ano_letivo" required>
                                                                            <?php foreach ($anos_letivos as $ano): ?>
                                                                            <option value="<?= $ano['ano_letivo'] ?>"><?= $ano['ano_letivo'] ?></option>
                                                                            <?php endforeach; ?>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label for="trimestre">Trimestre</label>
                                                                        <select class="form-control" id="trimestre" name="trimestre">
                                                                            <option value="">Todos</option>
                                                                            <option value="1">1º Trimestre</option>
                                                                            <option value="2">2º Trimestre</option>
                                                                            <option value="3">3º Trimestre</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label for="id_curso">Curso</label>
                                                                        <select class="form-control" id="id_curso" name="id_curso">
                                                                            <option value="">Todos</option>
                                                                            <?php foreach ($cursos as $curso): ?>
                                                                            <option value="<?= $curso['id_curso'] ?>"><?= htmlspecialchars($curso['nome']) ?></option>
                                                                            <?php endforeach; ?>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label for="id_turma">Turma</label>
                                                                        <select class="form-control" id="id_turma" name="id_turma" disabled>
                                                                            <option value="">Selecione um curso primeiro</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            
                                                            <!-- Filtros específicos para cada tipo de relatório -->
                                                            <div id="filtros-dinamicos" class="filtro-tipo">
                                                                <div class="form-group">
                                                                    <label for="ordenacao">Ordenar por</label>
                                                                    <select class="form-control" id="ordenacao" name="ordenacao">
                                                                        <option value="nome_asc">Nome (A-Z)</option>
                                                                        <option value="nome_desc">Nome (Z-A)</option>
                                                                        <option value="data_asc">Data mais antiga</option>
                                                                        <option value="data_desc">Data mais recente</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            
                                                            <button type="submit" name="gerar_relatorio" class="btn btn-primary mt-3">
                                                                <i class="feather icon-eye"></i> Visualizar Relatório
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Tabela com lista de relatórios -->
                                        <di class="card card-table">
                                            <div class="card-block">
                                                <div class="table-responsive rounded">
                                                    <table class="table table-custom ">
                                                        <thead>
                                                            <tr>
                                                                <th>Relatorios</th>
                                                                <th>Acoes</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($relatorios as $relatorio): ?>
                                                                <tr>
                                                                    <td><?= htmlspecialchars($relatorio['titulo']) ?></td>
                                                                    <td class="btn btn-warning btn-sm action-buttons">
                                                                        <a href="/<?= $relatorio['caminho_arquivo'] ?>" download>
                                                                            Baixar PDF
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; ?>
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

    <?php require_once '../../includes/common/js_imports.php'; ?>
    
    <script>
        // Função para selecionar o tipo de relatório
        function selecionarTipoRelatorio(tipo) {
            // Atualizar o tipo no formulário
            $('#tipo_relatorio').val(tipo);
            
            // Atualizar a seleção visual
            $('.tipo-relatorio').removeClass('active');
            $(`.tipo-relatorio[onclick*="${tipo}"]`).addClass('active');
        }
        
        // Carregar turmas quando um curso é selecionado
        $('#id_curso').change(function() {
            const id_curso = $(this).val();
            const $turmaSelect = $('#id_turma');
            
            if (id_curso) {
                $.ajax({
                    url: '../../process/consultas/getTurmasByCurso.php',
                    method: 'GET',
                    data: { id_curso: id_curso },
                    dataType: 'html',
                    success: function(response) {
                        $turmaSelect.html(response).prop('disabled', false);
                    },
                    error: function(xhr, status, error) {
                        console.error('Erro ao carregar turmas:', error);
                        $turmaSelect.html('<option value="">Erro ao carregar turmas</option>');
                    }
                });
            } else {
                $turmaSelect.html('<option value="">Selecione um curso primeiro</option>').prop('disabled', true);
            }
        });
        
        // Inicializar ao carregar a página
        $(document).ready(function() {
            // Selecionar o primeiro tipo de relatório se nenhum estiver selecionado
            if ($('#tipo_relatorio').val() === '') {
                const primeiroTipo = Object.keys(<?= json_encode($tipos_relatorios) ?>)[0];
                selecionarTipoRelatorio(primeiroTipo);
            }
        });
    </script>
</body>
</html>