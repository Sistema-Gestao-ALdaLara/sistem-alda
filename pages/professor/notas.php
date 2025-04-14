<?php
    require_once '../../includes/common/permissoes.php';
    verificarPermissao(['professor']);
    require_once '../../process/verificar_sessao.php';
    
    $title = "Lançamento de Notas";
    require_once '../../database/conexao.php';

    // Obter o ID do professor logado
    $id_usuario = $_SESSION['id_usuario'];
    $professor_id = 0;

    // Buscar o ID do professor
    $query_professor = "SELECT id_professor FROM professor WHERE usuario_id_usuario = ?";
    $stmt_professor = $conn->prepare($query_professor);
    $stmt_professor->bind_param("i", $id_usuario);
    $stmt_professor->execute();
    $result_professor = $stmt_professor->get_result();
    
    if ($result_professor->num_rows > 0) {
        $row_professor = $result_professor->fetch_assoc();
        $professor_id = $row_professor['id_professor'];
    }

    // Buscar turmas e disciplinas do professor
    $turmas = [];
    $disciplinas = [];
    if ($professor_id > 0) {
        // Turmas do professor
        $query_turmas = "SELECT t.id_turma, t.nome, c.nome as curso_nome 
                        FROM turma t
                        JOIN curso c ON t.curso_id_curso = c.id_curso
                        JOIN professor_tem_turma pt ON pt.turma_id_turma = t.id_turma
                        WHERE pt.professor_id_professor = ?";
        $stmt_turmas = $conn->prepare($query_turmas);
        $stmt_turmas->bind_param("i", $professor_id);
        $stmt_turmas->execute();
        $result_turmas = $stmt_turmas->get_result();
        $turmas = $result_turmas->fetch_all(MYSQLI_ASSOC);

        // Disciplinas do professor
        $query_disciplinas = "SELECT d.id_disciplina, d.nome, c.nome as curso_nome
                            FROM disciplina d
                            JOIN curso c ON d.curso_id_curso = c.id_curso
                            WHERE d.professor_id_professor = ?";
        $stmt_disciplinas = $conn->prepare($query_disciplinas);
        $stmt_disciplinas->bind_param("i", $professor_id);
        $stmt_disciplinas->execute();
        $result_disciplinas = $stmt_disciplinas->get_result();
        $disciplinas = $result_disciplinas->fetch_all(MYSQLI_ASSOC);
    }

    // Processar o formulário de lançamento de notas
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['lancar_notas'])) {
        $turma_id = intval($_POST['turma_id']);
        $disciplina_id = intval($_POST['disciplina_id']);
        $ano_letivo = intval($_POST['ano_letivo']);
        $data_nota = $_POST['data_nota'];
        $tipo_avaliacao = $_POST['tipo_avaliacao'];
        $bimestre = isset($_POST['bimestre']) ? intval($_POST['bimestre']) : null;
        $descricao = $conn->real_escape_string($_POST['descricao'] ?? '');
        $peso = isset($_POST['peso']) ? floatval($_POST['peso']) : 1.0;
        
        // Verificar permissão na disciplina
        $disciplina_valida = false;
        foreach ($disciplinas as $disciplina) {
            if ($disciplina['id_disciplina'] == $disciplina_id) {
                $disciplina_valida = true;
                break;
            }
        }
        
        if ($disciplina_valida) {
            $conn->begin_transaction();
            try {
                foreach ($_POST['notas'] as $matricula_id => $valor_nota) {
                    $matricula_id = intval($matricula_id);
                    $valor_nota = floatval(str_replace(',', '.', $valor_nota));
                    
                    if ($valor_nota >= 0 && $valor_nota <= 20) {
                        // Verificar se o aluno pertence à turma
                        $query_verifica = "SELECT a.id_aluno FROM matricula m
                                         JOIN aluno a ON m.aluno_id_aluno = a.id_aluno
                                         WHERE m.id_matricula = ? AND m.turma_id_turma = ?";
                        $stmt_verifica = $conn->prepare($query_verifica);
                        $stmt_verifica->bind_param("ii", $matricula_id, $turma_id);
                        $stmt_verifica->execute();
                        $result_verifica = $stmt_verifica->get_result();
                        
                        if ($result_verifica->num_rows > 0) {
                            $aluno_id = $result_verifica->fetch_assoc()['id_aluno'];
                            
                            // Inserir a nota com todos os campos
                            $query_insere = "INSERT INTO nota 
                                (nota, tipo_avaliacao, bimestre, descricao, peso, data, aluno_id_aluno, disciplina_id_disciplina)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                            $stmt_insere = $conn->prepare($query_insere);
                            $stmt_insere->bind_param(
                                "ssisdiii", 
                                $valor_nota, 
                                $tipo_avaliacao,
                                $bimestre,
                                $descricao,
                                $peso,
                                $data_nota, 
                                $aluno_id, 
                                $disciplina_id
                            );
                            $stmt_insere->execute();
                        }
                    }
                }
                $conn->commit();
                $_SESSION['mensagem_sucesso'] = "Notas lançadas com sucesso!";
            } catch (Exception $e) {
                $conn->rollback();
                $_SESSION['mensagem_erro'] = "Erro ao lançar notas: " . $e->getMessage();
            }
            header("Location: notas.php?turma_id=$turma_id&disciplina_id=$disciplina_id&ano_letivo=$ano_letivo");
            exit();
        } else {
            $_SESSION['mensagem_erro'] = "Você não tem permissão para lançar notas nesta disciplina.";
        }
    }
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <?php require_once '../../includes/common/head.php'; ?>

</head>
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
                                    <div class="page-header">
                                        <div class="row align-items-end">
                                            <div class="col-lg-8">
                                                <div class="page-header-title">
                                                    <h4>Lançamento de Notas</h4>
                                                    <span> Registre as notas dos alunos por disciplina</span>
                                                </div>
                                            </div>
                                            <div class="col-lg-4">
                                                <div class="page-header-breadcrumb">
                                                    <ul class="breadcrumb-title">
                                                        <li class="breadcrumb-item">
                                                            <a href="index.php"><i class="feather icon-home"></i></a>
                                                        </li>
                                                        <li class="breadcrumb-item"><a href="#!">Professor</a></li>
                                                        <li class="breadcrumb-item"><a href="#!">Notas</a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="page-body">
                                        <?php if (isset($_SESSION['mensagem_sucesso'])): ?>
                                            <div class="alert alert-success alert-dismissible fade show">
                                                <?= $_SESSION['mensagem_sucesso'] ?>
                                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                            </div>
                                            <?php unset($_SESSION['mensagem_sucesso']); ?>
                                        <?php endif; ?>
                                        
                                        <?php if (isset($_SESSION['mensagem_erro'])): ?>
                                            <div class="alert alert-danger alert-dismissible fade show">
                                                <?= $_SESSION['mensagem_erro'] ?>
                                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                            </div>
                                            <?php unset($_SESSION['mensagem_erro']); ?>
                                        <?php endif; ?>

                                        <div class="row">
                                            <!-- Cards de Estatísticas -->
                                            <div class="col-md-4">
                                                <div class="card card-estatistica card-provas card-table">
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div class="col-8">
                                                                <h3 class="mb-1" id="total-alunos">0</h3>
                                                                <p class="mb-0">Alunos na Turma</p>
                                                            </div>
                                                            <div class="col-4 text-right">
                                                                <i class="feather icon-users f-40"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="card card-estatistica card-avaliacoes card-table">
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div class="col-8">
                                                                <h3 class="mb-1" id="media-turma">0.00</h3>
                                                                <p class="mb-0">Média da Turma</p>
                                                            </div>
                                                            <div class="col-4 text-right">
                                                                <i class="feather icon-bar-chart f-40"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="card card-estatistica card-trabalhos card-table">
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div class="col-8">
                                                                <h3 class="mb-1" id="notas-lancadas">0</h3>
                                                                <p class="mb-0">Notas Lançadas</p>
                                                            </div>
                                                            <div class="col-4 text-right">
                                                                <i class="feather icon-edit f-40"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card card-table">
                                            <div class="card-header">
                                                <h5>Selecionar Turma e Disciplina</h5>
                                            </div>
                                            <div class="card-body">
                                                <form id="formSelecao" method="GET" action="notas.php">
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label>Turma</label>
                                                                <select class="form-control" name="turma_id" required>
                                                                    <option value="">Selecione...</option>
                                                                    <?php foreach ($turmas as $turma): ?>
                                                                        <option value="<?= $turma['id_turma'] ?>"
                                                                            <?= isset($_GET['turma_id']) && $_GET['turma_id'] == $turma['id_turma'] ? 'selected' : '' ?>>
                                                                            <?= $turma['nome'] ?> - <?= $turma['curso_nome'] ?>
                                                                        </option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label>Disciplina</label>
                                                                <select class="form-control" name="disciplina_id" required>
                                                                    <option value="">Selecione...</option>
                                                                    <?php foreach ($disciplinas as $disciplina): ?>
                                                                        <option value="<?= $disciplina['id_disciplina'] ?>"
                                                                            <?= isset($_GET['disciplina_id']) && $_GET['disciplina_id'] == $disciplina['id_disciplina'] ? 'selected' : '' ?>>
                                                                            <?= $disciplina['nome'] ?> - <?= $disciplina['curso_nome'] ?>
                                                                        </option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <div class="form-group">
                                                                <label>Ano Letivo</label>
                                                                <select class="form-control" name="ano_letivo" required>
                                                                    <?php $ano_atual = date('Y'); ?>
                                                                    <?php for ($i = $ano_atual - 1; $i <= $ano_atual + 1; $i++): ?>
                                                                        <option value="<?= $i ?>"
                                                                            <?= (isset($_GET['ano_letivo']) && $_GET['ano_letivo'] == $i) || (!isset($_GET['ano_letivo']) && $i == $ano_atual) ? 'selected' : '' ?>>
                                                                            <?= $i ?>
                                                                        </option>
                                                                    <?php endfor; ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2 d-flex align-items-end">
                                                            <button type="submit" class="btn btn-primary btn-block">
                                                                <i class="feather icon-search"></i> Buscar
                                                            </button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>

                                        <?php if (isset($_GET['turma_id']) && isset($_GET['disciplina_id']) && isset($_GET['ano_letivo'])): ?>
                                            <?php
                                                $turma_id = intval($_GET['turma_id']);
                                                $disciplina_id = intval($_GET['disciplina_id']);
                                                $ano_letivo = intval($_GET['ano_letivo']);
                                                
                                                // Verificar permissão na disciplina
                                                $disciplina_valida = false;
                                                foreach ($disciplinas as $disciplina) {
                                                    if ($disciplina['id_disciplina'] == $disciplina_id) {
                                                        $disciplina_valida = true;
                                                        break;
                                                    }
                                                }
                                                
                                                if ($disciplina_valida) {
                                                    // Buscar alunos da turma
                                                    $query_alunos = "SELECT m.id_matricula, u.nome, u.foto_perfil, 
                                                                    m.numero_matricula, m.classe, m.turno
                                                                  FROM matricula m
                                                                  JOIN aluno a ON m.aluno_id_aluno = a.id_aluno
                                                                  JOIN usuario u ON a.usuario_id_usuario = u.id_usuario
                                                                  WHERE m.turma_id_turma = ? AND m.ano_letivo = ?
                                                                  ORDER BY u.nome";
                                                    $stmt_alunos = $conn->prepare($query_alunos);
                                                    $stmt_alunos->bind_param("ii", $turma_id, $ano_letivo);
                                                    $stmt_alunos->execute();
                                                    $alunos = $stmt_alunos->get_result()->fetch_all(MYSQLI_ASSOC);
                                                    
                                                    // Buscar informações da turma e disciplina
                                                    $stmt_turma = $conn->prepare("SELECT nome FROM turma WHERE id_turma = ?");
                                                    $stmt_turma->bind_param("i", $turma_id);
                                                    $stmt_turma->execute();
                                                    $turma_nome = $stmt_turma->get_result()->fetch_assoc()['nome'];
                                                    
                                                    $stmt_disciplina = $conn->prepare("SELECT nome FROM disciplina WHERE id_disciplina = ?");
                                                    $stmt_disciplina->bind_param("i", $disciplina_id);
                                                    $stmt_disciplina->execute();
                                                    $disciplina_nome = $stmt_disciplina->get_result()->fetch_assoc()['nome'];
                                                    
                                                    // Buscar estatísticas
                                                    $query_stats = "SELECT 
                                                                    COUNT(DISTINCT n.aluno_id_aluno) as total_notas,
                                                                    AVG(n.nota) as media,
                                                                    COUNT(DISTINCT CASE WHEN n.tipo_avaliacao = 'prova' THEN n.id_nota END) as total_provas,
                                                                    COUNT(DISTINCT CASE WHEN n.tipo_avaliacao = 'avaliacao_continua' THEN n.id_nota END) as total_avaliacoes,
                                                                    COUNT(DISTINCT CASE WHEN n.tipo_avaliacao = 'trabalho' THEN n.id_nota END) as total_trabalhos
                                                                  FROM nota n
                                                                  JOIN matricula m ON m.aluno_id_aluno = n.aluno_id_aluno
                                                                  WHERE n.disciplina_id_disciplina = ? 
                                                                  AND m.turma_id_turma = ? 
                                                                  AND m.ano_letivo = ?";
                                                    $stmt_stats = $conn->prepare($query_stats);
                                                    $stmt_stats->bind_param("iii", $disciplina_id, $turma_id, $ano_letivo);
                                                    $stmt_stats->execute();
                                                    $stats = $stmt_stats->get_result()->fetch_assoc();
                                                    
                                                    // Buscar notas para o histórico
                                                    $query_notas = "SELECT n.id_nota, n.nota, n.data, n.tipo_avaliacao, 
                                                                  n.bimestre, n.descricao, n.peso,
                                                                  u.nome as aluno_nome, m.numero_matricula
                                                                  FROM nota n
                                                                  JOIN aluno a ON n.aluno_id_aluno = a.id_aluno
                                                                  JOIN usuario u ON a.usuario_id_usuario = u.id_usuario
                                                                  JOIN matricula m ON m.aluno_id_aluno = a.id_aluno
                                                                  WHERE n.disciplina_id_disciplina = ? 
                                                                  AND m.turma_id_turma = ? 
                                                                  AND m.ano_letivo = ?
                                                                  ORDER BY n.data DESC, u.nome";
                                                    $stmt_notas = $conn->prepare($query_notas);
                                                    $stmt_notas->bind_param("iii", $disciplina_id, $turma_id, $ano_letivo);
                                                    $stmt_notas->execute();
                                                    $notas = $stmt_notas->get_result()->fetch_all(MYSQLI_ASSOC);
                                            ?>
                                            
                                            <div class="card card-table">
                                                <div class="card-header">
                                                    <h5>Lançar Notas - <?= $disciplina_nome ?> | <?= $turma_nome ?> (<?= $ano_letivo ?>)</h5>
                                                </div>
                                                <div class="card-body">
                                                    <form id="formLancarNotas" method="POST" action="notas.php">
                                                        <input type="hidden" name="turma_id" value="<?= $turma_id ?>">
                                                        <input type="hidden" name="disciplina_id" value="<?= $disciplina_id ?>">
                                                        <input type="hidden" name="ano_letivo" value="<?= $ano_letivo ?>">
                                                        
                                                        <div class="row mb-4">
                                                            <div class="col-md-3">
                                                                <div class="form-group">
                                                                    <label>Data da Avaliação *</label>
                                                                    <input type="date" class="form-control" name="data_nota" required value="<?= date('Y-m-d') ?>">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <div class="form-group">
                                                                    <label>Tipo de Avaliação *</label>
                                                                    <select class="form-control" name="tipo_avaliacao" required>
                                                                        <option value="prova">Prova</option>
                                                                        <option value="avaliacao_continua">Avaliação Contínua</option>
                                                                        <option value="trabalho">Trabalho</option>
                                                                        <option value="recuperacao">Recuperação</option>
                                                                        <option value="projeto">Projeto</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-2">
                                                                <div class="form-group">
                                                                    <label>Bimestre</label>
                                                                    <select class="form-control" name="bimestre">
                                                                        <option value="">N/A</option>
                                                                        <option value="1">1º Bimestre</option>
                                                                        <option value="2">2º Bimestre</option>
                                                                        <option value="3">3º Bimestre</option>
                                                                        <option value="4">4º Bimestre</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-2">
                                                                <div class="form-group">
                                                                    <label>Peso</label>
                                                                    <input type="number" class="form-control" name="peso" min="0.1" max="5" step="0.1" value="1.0">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-2">
                                                                <div class="form-group">
                                                                    <label>Descrição</label>
                                                                    <input type="text" class="form-control" name="descricao" placeholder="Ex: Prova Bimestral 1">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="table-responsive">
                                                            <table class="table table-bordered table-hover">
                                                                <thead>
                                                                    <tr>
                                                                        <th width="5%">#</th>
                                                                        <th width="15%">Foto</th>
                                                                        <th width="25%">Aluno</th>
                                                                        <th width="15%">Matrícula</th>
                                                                        <th width="10%">Classe</th>
                                                                        <th width="10%">Turno</th>
                                                                        <th width="20%">Nota (0-20)</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php if (!empty($alunos)): ?>
                                                                        <?php foreach ($alunos as $i => $aluno): ?>
                                                                            <tr>
                                                                                <td><?= $i + 1 ?></td>
                                                                                <td class="text-center">
                                                                                    <img src="../../public/uploads/perfil/<?= !empty($aluno['foto_perfil']) ? $aluno['foto_perfil'] : 'default.png' ?>" 
                                                                                         class="img-radius img-40" alt="Foto">
                                                                                </td>
                                                                                <td><?= htmlspecialchars($aluno['nome']) ?></td>
                                                                                <td><?= htmlspecialchars($aluno['numero_matricula']) ?></td>
                                                                                <td><?= htmlspecialchars($aluno['classe']) ?></td>
                                                                                <td><?= htmlspecialchars($aluno['turno']) ?></td>
                                                                                <td>
                                                                                    <input type="number" class="form-control nota-input" 
                                                                                           name="notas[<?= $aluno['id_matricula'] ?>]" 
                                                                                           min="0" max="20" step="0.1" required>
                                                                                </td>
                                                                            </tr>
                                                                        <?php endforeach; ?>
                                                                    <?php else: ?>
                                                                        <tr>
                                                                            <td colspan="7" class="text-center">Nenhum aluno encontrado</td>
                                                                        </tr>
                                                                    <?php endif; ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                        
                                                        <?php if (!empty($alunos)): ?>
                                                            <div class="text-right mt-3">
                                                                <button type="submit" name="lancar_notas" class="btn btn-primary">
                                                                    <i class="feather icon-save"></i> Lançar Notas
                                                                </button>
                                                            </div>
                                                        <?php endif; ?>
                                                    </form>
                                                </div>
                                            </div>
                                            
                                            <div class="card card-table">
                                                <div class="card-header">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <h5>Histórico de Notas</h5>
                                                        <div>
                                                            <select class="form-control form-control-sm" id="filtroTipo" style="width: 180px;">
                                                                <option value="">Todos os tipos</option>
                                                                <option value="prova">Provas</option>
                                                                <option value="avaliacao_continua">Aval. Contínuas</option>
                                                                <option value="trabalho">Trabalhos</option>
                                                                <option value="recuperacao">Recuperações</option>
                                                                <option value="projeto">Projetos</option>
                                                            </select>
                                                            <select class="form-control form-control-sm" id="filtroBimestre" style="width: 120px;">
                                                                <option value="">Todos bimestres</option>
                                                                <option value="1">1º Bimestre</option>
                                                                <option value="2">2º Bimestre</option>
                                                                <option value="3">3º Bimestre</option>
                                                                <option value="4">4º Bimestre</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card-body">
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered table-hover" id="tabelaHistorico">
                                                            <thead>
                                                                <tr>
                                                                    <th>Data</th>
                                                                    <th>Tipo</th>
                                                                    <th>Bimestre</th>
                                                                    <th>Descrição</th>
                                                                    <th>Aluno</th>
                                                                    <th>Nota</th>
                                                                    <th>Peso</th>
                                                                    <th>Ações</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php if (!empty($notas)): ?>
                                                                    <?php foreach ($notas as $nota): ?>
                                                                        <tr data-tipo="<?= $nota['tipo_avaliacao'] ?>" data-bimestre="<?= $nota['bimestre'] ?>">
                                                                            <td><?= date('d/m/Y', strtotime($nota['data'])) ?></td>
                                                                            <td>
                                                                                <?php
                                                                                    $badge_class = '';
                                                                                    $tipo_text = '';
                                                                                    switch($nota['tipo_avaliacao']) {
                                                                                        case 'prova': $badge_class = 'badge-prova'; $tipo_text = 'Prova'; break;
                                                                                        case 'avaliacao_continua': $badge_class = 'badge-avaliacao_continua'; $tipo_text = 'Aval. Cont.'; break;
                                                                                        case 'trabalho': $badge_class = 'badge-trabalho'; $tipo_text = 'Trabalho'; break;
                                                                                        case 'recuperacao': $badge_class = 'badge-recuperacao'; $tipo_text = 'Recup.'; break;
                                                                                        case 'projeto': $badge_class = 'badge-projeto'; $tipo_text = 'Projeto'; break;
                                                                                    }
                                                                                ?>
                                                                                <span class="badge <?= $badge_class ?>"><?= $tipo_text ?></span>
                                                                            </td>
                                                                            <td><?= $nota['bimestre'] ? $nota['bimestre'] . 'º' : '-' ?></td>
                                                                            <td><?= htmlspecialchars($nota['descricao'] ?? '') ?></td>
                                                                            <td><?= htmlspecialchars($nota['aluno_nome']) ?></td>
                                                                            <td><?= number_format($nota['nota'], 2, ',', '.') ?></td>
                                                                            <td><?= number_format($nota['peso'], 1) ?></td>
                                                                            <td>
                                                                                <button class="btn btn-sm btn-danger btn-excluir" data-id="<?= $nota['id_nota'] ?>">
                                                                                    <i class="feather icon-trash-2"></i>
                                                                                </button>
                                                                            </td>
                                                                        </tr>
                                                                    <?php endforeach; ?>
                                                                <?php else: ?>
                                                                    <tr>
                                                                        <td colspan="8" class="text-center">Nenhuma nota lançada</td>
                                                                    </tr>
                                                                <?php endif; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <?php } else { ?>
                                                <div class="alert alert-danger">
                                                    Você não tem permissão para acessar esta disciplina.
                                                </div>
                                            <?php } ?>
                                        <?php endif; ?>
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
            // Atualizar estatísticas
            document.getElementById('total-alunos').textContent = '<?= count($alunos) ?>';
            document.getElementById('notas-lancadas').textContent = '<?= $stats['total_notas'] ?? 0 ?>';
            document.getElementById('media-turma').textContent = '<?= isset($stats['media']) ? number_format($stats['media'], 2, ',', '.') : '0.00' ?>';
            
            // Filtros do histórico
            $(document).ready(function() {
                $('#filtroTipo, #filtroBimestre').change(function() {
                    const tipo = $('#filtroTipo').val();
                    const bimestre = $('#filtroBimestre').val();
                    
                    $('#tabelaHistorico tbody tr').each(function() {
                        const rowTipo = $(this).data('tipo');
                        const rowBimestre = $(this).data('bimestre');
                        
                        const showTipo = tipo === '' || rowTipo === tipo;
                        const showBimestre = bimestre === '' || rowBimestre == bimestre;
                        
                        $(this).toggle(showTipo && showBimestre);
                    });
                });
                
                // Excluir nota
                $('.btn-excluir').click(function() {
                    const id = $(this).data('id');
                    const linha = $(this).closest('tr');
                    
                    if (confirm('Tem certeza que deseja excluir esta nota?')) {
                        $.ajax({
                            url: '../../process/professor/excluir_nota.php',
                            method: 'POST',
                            data: { id_nota: id },
                            dataType: 'json',
                            success: function(response) {
                                if (response.success) {
                                    linha.fadeOut(function() {
                                        linha.remove();
                                        alert('Nota excluída com sucesso!');
                                    });
                                } else {
                                    alert('Erro: ' + response.message);
                                }
                            },
                            error: function() {
                                alert('Erro ao conectar com o servidor');
                            }
                        });
                    }
                });
                
                // Validação do formulário
                $('#formLancarNotas').validate({
                    errorClass: 'text-danger',
                    rules: {
                        'data_nota': { required: true },
                        'tipo_avaliacao': { required: true }
                    },
                    messages: {
                        'data_nota': 'Informe a data',
                        'tipo_avaliacao': 'Selecione o tipo'
                    },
                    highlight: function(element) {
                        $(element).addClass('is-invalid');
                    },
                    unhighlight: function(element) {
                        $(element).removeClass('is-invalid');
                    }
                });
                
                $('.nota-input').each(function() {
                    $(this).rules('add', {
                        required: true,
                        min: 0,
                        max: 20,
                        messages: {
                            required: "Informe a nota",
                            min: "Mínimo 0",
                            max: "Máximo 20"
                        }
                    });
                });
            });
        </script>
    </body>
</html>
<?php $conn->close(); ?>