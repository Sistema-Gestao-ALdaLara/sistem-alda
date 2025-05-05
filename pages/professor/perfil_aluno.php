<?php
require_once '../../includes/common/permissoes.php';
verificarPermissao(['professor']);
require_once '../../process/verificar_sessao.php';
require_once '../../database/conexao.php';

$title = "Perfil do Aluno";
$currentYear = date('Y');

try {
    // Validar parâmetros GET
    $alunoId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    $turmaId = filter_input(INPUT_GET, 'turma_id', FILTER_VALIDATE_INT);

    if (!$alunoId || !$turmaId) {
        throw new Exception("Parâmetros inválidos para visualização do aluno.");
    }

    // Obter ID do professor logado
    $idUsuario = $_SESSION['id_usuario'];
    $queryProfessor = "SELECT id_professor FROM professor WHERE usuario_id_usuario = ?";
    $stmtProfessor = $conn->prepare($queryProfessor);
    $stmtProfessor->bind_param("i", $idUsuario);
    $stmtProfessor->execute();
    $resultProfessor = $stmtProfessor->get_result();

    if ($resultProfessor->num_rows === 0) {
        throw new Exception("Professor não encontrado.");
    }

    $professor = $resultProfessor->fetch_assoc();
    $professorId = $professor['id_professor'];

    // Verificar se o professor tem acesso ao aluno na turma especificada
    $queryVerificacao = "SELECT 1 FROM professor_tem_turma 
                        WHERE professor_id_professor = ? AND turma_id_turma = ?";
    $stmtVerificacao = $conn->prepare($queryVerificacao);
    $stmtVerificacao->bind_param("ii", $professorId, $turmaId);
    $stmtVerificacao->execute();
    
    if ($stmtVerificacao->get_result()->num_rows === 0) {
        throw new Exception("Você não tem permissão para acessar este aluno.");
    }

    // Obter informações básicas do aluno
    $queryAluno = "SELECT 
                    u.id_usuario, u.nome, u.email, u.bi_numero, u.foto_perfil, u.status,
                    a.data_nascimento, a.genero, a.naturalidade, a.nacionalidade, a.municipio,
                    a.nome_encarregado, a.contacto_encarregado,
                    m.numero_matricula, t.classe, m.ano_letivo, m.status_matricula,
                    t.nome AS nome_turma, t.turno, c.nome AS nome_curso
                  FROM aluno a
                  JOIN usuario u ON a.usuario_id_usuario = u.id_usuario
                  JOIN matricula m ON m.aluno_id_aluno = a.id_aluno
                  JOIN turma t ON m.turma_id_turma = t.id_turma
                  JOIN curso c ON t.curso_id_curso = c.id_curso
                  WHERE a.id_aluno = ? AND m.turma_id_turma = ?
                  ORDER BY m.ano_letivo DESC
                  LIMIT 1";

    $stmtAluno = $conn->prepare($queryAluno);
    $stmtAluno->bind_param("ii", $alunoId, $turmaId);
    $stmtAluno->execute();
    $aluno = $stmtAluno->get_result()->fetch_assoc();

    if (!$aluno) {
        throw new Exception("Aluno não encontrado na turma especificada.");
    }

    // Obter histórico de matrículas
    $queryHistorico = "SELECT 
                        m.ano_letivo, t.nome AS nome_turma, c.nome AS nome_curso, 
                        t.classe, t.turno, m.status_matricula, m.data_matricula
                      FROM matricula m
                      JOIN turma t ON m.turma_id_turma = t.id_turma
                      JOIN curso c ON t.curso_id_curso = c.id_curso
                      WHERE m.aluno_id_aluno = ?
                      ORDER BY m.ano_letivo DESC";
    
    $stmtHistorico = $conn->prepare($queryHistorico);
    $stmtHistorico->bind_param("i", $alunoId);
    $stmtHistorico->execute();
    $historicoMatriculas = $stmtHistorico->get_result()->fetch_all(MYSQLI_ASSOC);

    // Obter notas do aluno para o ano letivo atual
    $queryNotas = "SELECT 
                    n.id_nota, n.nota, n.data, n.tipo_avaliacao, n.trimestre, 
                    n.descricao, n.peso, d.nome AS nome_disciplina
                  FROM nota n
                  JOIN disciplina d ON n.disciplina_id_disciplina = d.id_disciplina
                  WHERE n.aluno_id_aluno = ? 
                  AND EXISTS (
                      SELECT 1 FROM matricula m 
                      WHERE m.aluno_id_aluno = n.aluno_id_aluno 
                      AND m.ano_letivo = ?
                  )
                  ORDER BY d.nome, n.data DESC";
    
    $stmtNotas = $conn->prepare($queryNotas);
    $stmtNotas->bind_param("ii", $alunoId, $aluno['ano_letivo']);
    $stmtNotas->execute();
    $notas = $stmtNotas->get_result()->fetch_all(MYSQLI_ASSOC);

    // Calcular médias por disciplina
    $mediasDisciplinas = [];
    foreach ($notas as $nota) {
        $disciplina = $nota['nome_disciplina'];
        if (!isset($mediasDisciplinas[$disciplina])) {
            $mediasDisciplinas[$disciplina] = [
                'total' => 0,
                'peso_total' => 0,
                'notas' => []
            ];
        }
        $mediasDisciplinas[$disciplina]['total'] += $nota['nota'] * $nota['peso'];
        $mediasDisciplinas[$disciplina]['peso_total'] += $nota['peso'];
        $mediasDisciplinas[$disciplina]['notas'][] = $nota;
    }

    // Calcular média geral
    $mediaGeral = 0;
    $totalDisciplinas = count($mediasDisciplinas);
    foreach ($mediasDisciplinas as $disciplina => $dados) {
        if ($dados['peso_total'] > 0) {
            $mediaGeral += ($dados['total'] / $dados['peso_total']);
        }
    }
    $mediaGeral = $totalDisciplinas > 0 ? $mediaGeral / $totalDisciplinas : 0;

    // Obter frequência do aluno (últimos 30 dias)
    $queryFrequencia = "SELECT 
                        fa.id_frequencia_aluno, fa.data_aula, fa.presenca, 
                        fa.tipo_aula, fa.observacao, d.nome AS nome_disciplina
                       FROM frequencia_aluno fa
                       JOIN disciplina d ON fa.disciplina_id_disciplina = d.id_disciplina
                       WHERE fa.aluno_id_aluno = ?
                       AND fa.data_aula >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                       ORDER BY fa.data_aula DESC";
    
    $stmtFrequencia = $conn->prepare($queryFrequencia);
    $stmtFrequencia->bind_param("i", $alunoId);
    $stmtFrequencia->execute();
    $frequencia = $stmtFrequencia->get_result()->fetch_all(MYSQLI_ASSOC);

    // Calcular estatísticas de frequência
    $totalAulas = count($frequencia);
    $presencas = count(array_filter($frequencia, function($f) { return $f['presenca'] == 'presente'; }));
    $ausencias = count(array_filter($frequencia, function($f) { return $f['presenca'] == 'ausente'; }));
    $justificadas = count(array_filter($frequencia, function($f) { return $f['presenca'] == 'justificado'; }));
    $percentualPresenca = $totalAulas > 0 ? round(($presencas + $justificadas) / $totalAulas * 100, 2) : 0;

} catch (Exception $e) {
    $_SESSION['erro'] = "Erro ao carregar perfil do aluno: " . $e->getMessage();
    echo "erro";
    exit();
}

// Template HTML
require_once '../../includes/common/head.php';
?>

<body>
    <?php require_once '../../includes/common/preloader.php'; ?>

    <div id="pcoded" class="pcoded">
        <div class="pcoded-overlay-box"></div>
        <div class="pcoded-container navbar-wrapper">
            <?php require_once '../../includes/professor/navbar.php'; ?>

            <div class="pcoded-main-container">
                <div class="pcoded-wrapper">
                    <?php require_once '../../includes/professor/sidebar.php'; ?>
                    
                    <div class="pcoded-content">
                        <div class="pcoded-inner-content">
                            <div class="main-body bg-img">
                                <div class="page-wrapper">
                                    <!-- Page Header -->
                                    <div class="page-header">
                                        <div class="row align-items-end">
                                            <div class="col-lg-8">
                                                <div class="page-header-title">
                                                    <h4>Perfil do Aluno</h4>
                                                    <span>Informações detalhadas sobre o desempenho acadêmico</span>
                                                </div>
                                            </div>
                                            <div class="col-lg-4">
                                                <div class="page-header-breadcrumb">
                                                    <ul class="breadcrumb-title">
                                                        <li class="breadcrumb-item">
                                                            <a href="index.php"><i class="feather icon-home"></i></a>
                                                        </li>
                                                        <li class="breadcrumb-item"><a href="lista_alunos.php">Alunos</a></li>
                                                        <li class="breadcrumb-item active">Perfil</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Page Body -->
                                    <div class="page-body">
                                        <!-- Mensagens de feedback -->
                                        <?php if (isset($_SESSION['erro'])): ?>
                                            <div class="alert alert-danger">
                                                <?= $_SESSION['erro']; unset($_SESSION['erro']); ?>
                                            </div>
                                        <?php endif; ?>

                                        <!-- Cabeçalho do Perfil -->
                                        <div class="profile-header bg-primary text-dark rounded p-3 mb-4">
                                            <div class="row align-items-center">
                                                <div class="col-md-2 text-center">
                                                    <img src="../../public/uploads/perfil/<?= !empty($aluno['foto_perfil']) ? htmlspecialchars($aluno['foto_perfil']) : 'default.png' ?>" 
                                                         class="img-thumbnail rounded-circle" style="width: 120px; height: 120px;" alt="Foto do aluno">
                                                </div>
                                                <div class="col-md-7">
                                                    <h3 class="mb-2"><?= htmlspecialchars($aluno['nome']) ?></h3>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <p class="mb-1">
                                                                <i class="feather icon-bookmark"></i> 
                                                                <?= htmlspecialchars($aluno['nome_curso']) ?> - <?= htmlspecialchars($aluno['nome_turma']) ?>
                                                            </p>
                                                            <p class="mb-1">
                                                                <i class="feather icon-hash"></i> 
                                                                Matrícula: <?= htmlspecialchars($aluno['numero_matricula']) ?>
                                                            </p>
                                                            <p class="mb-1">
                                                                <i class="feather icon-user"></i> 
                                                                Encarregado: <?= htmlspecialchars($aluno['nome_encarregado']) ?>
                                                            </p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <p class="mb-1">
                                                                <i class="feather icon-calendar"></i> 
                                                                Ano Letivo: <?= $aluno['ano_letivo'] ?>
                                                            </p>
                                                            <p class="mb-1">
                                                                <i class="feather icon-award"></i> 
                                                                Classe: <?= htmlspecialchars($aluno['classe']) ?>
                                                            </p>
                                                            <p class="mb-1">
                                                                <i class="feather icon-clock"></i> 
                                                                Turno: <?= htmlspecialchars($aluno['turno']) ?>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-3 text-right">
                                                    <span class="badge badge-<?= $aluno['status'] == 'ativo' ? 'success' : 'danger' ?>">
                                                        <?= ucfirst($aluno['status']) ?>
                                                    </span>
                                                    <span class="badge badge-<?= $aluno['status_matricula'] == 'ativa' ? 'success' : 'danger' ?>">
                                                        Matrícula <?= ucfirst($aluno['status_matricula']) ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Estatísticas Rápidas -->
                                        <div class="row mb-4">
                                            <div class="col-md-3">
                                                <div class="card border-left-primary shadow-sm h-100 card-table card-estatistica">
                                                    <div class="card-body">
                                                        <div class="row no-gutters align-items-center">
                                                            <div class="col mr-2">
                                                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                                    Média Geral</div>
                                                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                                    <?= number_format($mediaGeral, 2, ',', '.') ?>
                                                                </div>
                                                            </div>
                                                            <div class="col-auto">
                                                                <i class="feather icon-bar-chart-2 fa-2x text-gray-300"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="card border-left-success shadow-sm h-100 card-avaliacoes card-estatistica">
                                                    <div class="card-body">
                                                        <div class="row no-gutters align-items-center">
                                                            <div class="col mr-2">
                                                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                                    Presença (30 dias)</div>
                                                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                                    <?= $percentualPresenca ?>%
                                                                </div>
                                                            </div>
                                                            <div class="col-auto">
                                                                <i class="feather icon-check-circle fa-2x text-gray-300"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="card border-left-info shadow-sm h-100 card-table card-estatistica">
                                                    <div class="card-body">
                                                        <div class="row no-gutters align-items-center">
                                                            <div class="col mr-2">
                                                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                                                    Avaliações</div>
                                                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                                    <?= count($notas) ?>
                                                                </div>
                                                            </div>
                                                            <div class="col-auto">
                                                                <i class="feather icon-edit fa-2x text-gray-300"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="card border-left-warning shadow-sm h-100 card-estatistica card-trabalhos">
                                                    <div class="card-body">
                                                        <div class="row no-gutters align-items-center">
                                                            <div class="col mr-2">
                                                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                                    Anos Letivos</div>
                                                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                                    <?= count($historicoMatriculas) ?>
                                                                </div>
                                                            </div>
                                                            <div class="col-auto">
                                                                <i class="feather icon-clock fa-2x text-gray-300"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Abas de Navegação -->
                                        <ul class="nav nav-tabs mb-4 bg-primary" id="alunoTabs" role="tablist">
                                            <li class="nav-item">
                                                <a class="nav-link active" id="dados-tab" data-toggle="tab" href="#dados" role="tab">
                                                    <i class="feather icon-info"></i> Dados Pessoais
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" id="desempenho-tab" data-toggle="tab" href="#desempenho" role="tab">
                                                    <i class="feather icon-bar-chart-2"></i> Desempenho
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" id="frequencia-tab" data-toggle="tab" href="#frequencia" role="tab">
                                                    <i class="feather icon-check-square"></i> Frequência
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" id="historico-tab" data-toggle="tab" href="#historico" role="tab">
                                                    <i class="feather icon-clock"></i> Histórico
                                                </a>
                                            </li>
                                        </ul>

                                        <!-- Conteúdo das Abas -->
                                        <div class="tab-content" id="alunoTabsContent">
                                            <!-- Tab Dados Pessoais -->
                                            <div class="tab-pane fade show active" id="dados" role="tabpanel">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="card shadow-sm card-table">
                                                            <div class="card-header ">
                                                                <h5 class="mb-0"><i class="feather icon-user"></i> Informações Pessoais</h5>
                                                            </div>
                                                            <div class="card-body">
                                                                <div class="table-responsive">
                                                                    <table class="table table-sm text-white">
                                                                        <tbody>
                                                                            <tr>
                                                                                <th width="40%">Nome Completo</th>
                                                                                <td><?= htmlspecialchars($aluno['nome']) ?></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <th>BI/Nº Documento</th>
                                                                                <td><?= htmlspecialchars($aluno['bi_numero']) ?></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <th>Data de Nascimento</th>
                                                                                <td><?= date('d/m/Y', strtotime($aluno['data_nascimento'])) ?></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <th>Gênero</th>
                                                                                <td><?= htmlspecialchars($aluno['genero']) ?></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <th>Naturalidade</th>
                                                                                <td><?= htmlspecialchars($aluno['naturalidade']) ?></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <th>Nacionalidade</th>
                                                                                <td><?= htmlspecialchars($aluno['nacionalidade']) ?></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <th>Município</th>
                                                                                <td><?= htmlspecialchars($aluno['municipio']) ?></td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="card shadow-sm">
                                                            <div class="card-header bg-primary">
                                                                <h5 class="mb-0"><i class="feather icon-book"></i> Informações Acadêmicas</h5>
                                                            </div>
                                                            <div class="card-body">
                                                                <div class="table-responsive">
                                                                    <table class="table table-sm">
                                                                        <tbody>
                                                                            <tr>
                                                                                <th width="40%">Curso</th>
                                                                                <td><?= htmlspecialchars($aluno['nome_curso']) ?></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <th>Turma</th>
                                                                                <td><?= htmlspecialchars($aluno['nome_turma']) ?></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <th>Classe</th>
                                                                                <td><?= htmlspecialchars($aluno['classe']) ?></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <th>Turno</th>
                                                                                <td><?= htmlspecialchars($aluno['turno']) ?></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <th>Ano Letivo</th>
                                                                                <td><?= $aluno['ano_letivo'] ?></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <th>Status</th>
                                                                                <td>
                                                                                    <span class="badge badge-<?= $aluno['status'] == 'ativo' ? 'success' : 'danger' ?>">
                                                                                        <?= ucfirst($aluno['status']) ?>
                                                                                    </span>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <th>Matrícula</th>
                                                                                <td>
                                                                                    <span class="badge badge-<?= $aluno['status_matricula'] == 'ativa' ? 'success' : 'danger' ?>">
                                                                                        <?= ucfirst($aluno['status_matricula']) ?>
                                                                                    </span>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <th>Encarregado</th>
                                                                                <td><?= htmlspecialchars($aluno['nome_encarregado']) ?></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <th>Contacto Encarregado</th>
                                                                                <td><?= htmlspecialchars($aluno['contacto_encarregado']) ?></td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Tab Desempenho -->
                                            <div class="tab-pane fade" id="desempenho" role="tabpanel">
                                                <div class="card shadow-sm card-table">
                                                    <div class="card-header">
                                                        <h5 class="mb-0"><i class="feather icon-bar-chart-2"></i> Desempenho Acadêmico</h5>
                                                    </div>
                                                    <div class="card-body text-white">
                                                        <?php if (empty($mediasDisciplinas)): ?>
                                                            <div class="alert alert-info">
                                                                Nenhuma nota registrada para este aluno no ano letivo atual.
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="row">
                                                                <?php foreach ($mediasDisciplinas as $disciplina => $dados): ?>
                                                                    <?php 
                                                                        $mediaDisciplina = $dados['peso_total'] > 0 ? $dados['total'] / $dados['peso_total'] : 0;
                                                                        $cor = ($mediaDisciplina >= 14) ? 'success' : 
                                                                               ($mediaDisciplina >= 10 ? 'warning' : 'danger');
                                                                    ?>
                                                                    <div class="col-md-4 mb-4 card-table">
                                                                        <div class="card border-<?= $cor ?> shadow-sm h-100 card-table">
                                                                            <div class="card-header bg-<?= $cor ?> text-white">
                                                                                <h6 class="mb-0"><?= htmlspecialchars($disciplina) ?></h6>
                                                                            </div>
                                                                            <div class="card-body text-center">
                                                                                <h2 class="text-<?= $cor ?>">
                                                                                    <?= number_format($mediaDisciplina, 2, ',', '.') ?>
                                                                                </h2>
                                                                                <p class="mb-3">Média Ponderada</p>
                                                                                
                                                                                <div class="progress mb-3">
                                                                                    <div class="progress-bar bg-<?= $cor ?>" 
                                                                                         role="progressbar" 
                                                                                         style="width: <?= $mediaDisciplina * 5 ?>%" 
                                                                                         aria-valuenow="<?= $mediaDisciplina * 5 ?>" 
                                                                                         aria-valuemin="0" 
                                                                                         aria-valuemax="100">
                                                                                    </div>
                                                                                </div>
                                                                                
                                                                                <button class="btn btn-sm btn-outline-<?= $cor ?> w-100" 
                                                                                        type="button" 
                                                                                        data-toggle="collapse" 
                                                                                        data-target="#notas-<?= md5($disciplina) ?>">
                                                                                    Ver Notas <i class="feather icon-chevron-down"></i>
                                                                                </button>
                                                                                
                                                                                <div class="collapse mt-3" id="notas-<?= md5($disciplina) ?>">
                                                                                    <div class="table-responsive">
                                                                                        <table class="table table-sm table-bordered text-white">
                                                                                            <thead class="thead-light">
                                                                                                <tr>
                                                                                                    <th>Data</th>
                                                                                                    <th>Tipo</th>
                                                                                                    <th>Nota</th>
                                                                                                    <th>Peso</th>
                                                                                                    <th>Trimestre</th>
                                                                                                </tr>
                                                                                            </thead>
                                                                                            <tbody>
                                                                                                <?php foreach ($dados['notas'] as $nota): ?>
                                                                                                    <tr>
                                                                                                        <td><?= date('d/m/Y', strtotime($nota['data'])) ?></td>
                                                                                                        <td><?= ucfirst(str_replace('_', ' ', $nota['tipo_avaliacao'])) ?></td>
                                                                                                        <td><?= number_format($nota['nota'], 2, ',', '.') ?></td>
                                                                                                        <td><?= number_format($nota['peso'], 1) ?></td>
                                                                                                        <td><?= $nota['trimestre'] ?? '-' ?></td>
                                                                                                    </tr>
                                                                                                <?php endforeach; ?>
                                                                                            </tbody>
                                                                                        </table>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Tab Frequência -->
                                            <div class="tab-pane fade" id="frequencia" role="tabpanel">
                                                <div class="card shadow-sm">
                                                    <div class="card-header bg-white">
                                                        <h5 class="mb-0"><i class="feather icon-check-square"></i> Registro de Frequência</h5>
                                                        <p class="mb-0 text-muted">Últimos 30 dias</p>
                                                    </div>
                                                    <div class="card-body">
                                                        <?php if (empty($frequencia)): ?>
                                                            <div class="alert alert-info">
                                                                Nenhum registro de frequência encontrado nos últimos 30 dias.
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="table-responsive">
                                                                <table class="table table-bordered table-hover">
                                                                    <thead class="thead-light">
                                                                        <tr>
                                                                            <th>Data</th>
                                                                            <th>Disciplina</th>
                                                                            <th>Status</th>
                                                                            <th>Tipo Aula</th>
                                                                            <th>Observação</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <?php foreach ($frequencia as $registro): ?>
                                                                            <tr>
                                                                                <td><?= date('d/m/Y', strtotime($registro['data_aula'])) ?></td>
                                                                                <td><?= htmlspecialchars($registro['nome_disciplina']) ?></td>
                                                                                <td>
                                                                                    <?php if ($registro['presenca'] == 'presente'): ?>
                                                                                        <span class="badge badge-success">Presente</span>
                                                                                    <?php elseif ($registro['presenca'] == 'ausente'): ?>
                                                                                        <span class="badge badge-danger">Ausente</span>
                                                                                    <?php else: ?>
                                                                                        <span class="badge badge-warning">Justificado</span>
                                                                                    <?php endif; ?>
                                                                                </td>
                                                                                <td><?= ucfirst(str_replace('_', ' ', $registro['tipo_aula'])) ?></td>
                                                                                <td><?= !empty($registro['observacao']) ? htmlspecialchars($registro['observacao']) : '-' ?></td>
                                                                            </tr>
                                                                        <?php endforeach; ?>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Tab Histórico -->
                                            <div class="tab-pane fade" id="historico" role="tabpanel">
                                                <div class="card shadow-sm">
                                                    <div class="card-header bg-white">
                                                        <h5 class="mb-0"><i class="feather icon-clock"></i> Histórico de Matrículas</h5>
                                                    </div>
                                                    <div class="card-body">
                                                        <?php if (empty($historicoMatriculas)): ?>
                                                            <div class="alert alert-info">
                                                                Nenhum histórico de matrículas encontrado.
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="table-responsive">
                                                                <table class="table table-bordered table-hover">
                                                                    <thead class="thead-light">
                                                                        <tr>
                                                                            <th>Ano Letivo</th>
                                                                            <th>Turma</th>
                                                                            <th>Curso</th>
                                                                            <th>Classe</th>
                                                                            <th>Turno</th>
                                                                            <th>Status</th>
                                                                            <th>Data Matrícula</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <?php foreach ($historicoMatriculas as $matricula): ?>
                                                                            <tr>
                                                                                <td><?= $matricula['ano_letivo'] ?></td>
                                                                                <td><?= htmlspecialchars($matricula['nome_turma']) ?></td>
                                                                                <td><?= htmlspecialchars($matricula['nome_curso']) ?></td>
                                                                                <td><?= htmlspecialchars($matricula['classe']) ?></td>
                                                                                <td><?= htmlspecialchars($matricula['turno']) ?></td>
                                                                                <td>
                                                                                    <span class="badge badge-<?= $matricula['status_matricula'] == 'ativa' ? 'success' : 'danger' ?>">
                                                                                        <?= ucfirst($matricula['status_matricula']) ?>
                                                                                    </span>
                                                                                </td>
                                                                                <td><?= date('d/m/Y', strtotime($matricula['data_matricula'])) ?></td>
                                                                            </tr>
                                                                        <?php endforeach; ?>
                                                                    </tbody>
                                                                </table>
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

    <?php require_once '../../includes/common/js_imports.php'; ?>
    <script>
        $(document).ready(function() {
            // Ativar tooltips
            $('[data-toggle="tooltip"]').tooltip();
            
            // Ativar popovers
            $('[data-toggle="popover"]').popover();
            
            // Inicializar gráficos (exemplo)
            if (typeof Chart !== 'undefined') {
                // Você pode adicionar gráficos aqui se necessário
            }
            
            // Alternar entre abas e manter estado
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                localStorage.setItem('lastTab', $(e.target).attr('href'));
            });
            
            var lastTab = localStorage.getItem('lastTab');
            if (lastTab) {
                $('[href="' + lastTab + '"]').tab('show');
            }
        });
    </script>
</body>
</html>
<?php
$conn->close();
?>