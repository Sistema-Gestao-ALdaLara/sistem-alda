<?php
    require_once '../../includes/common/permissoes.php';
    verificarPermissao(['professor']);
    require_once '../../process/verificar_sessao.php';
    
    $title = "Lançamento de Frequência";
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

        // Disciplinas do professor (através da tabela professor_tem_disciplina)
        $query_disciplinas = "SELECT d.id_disciplina, d.nome, c.nome as curso_nome
                            FROM disciplina d
                            JOIN curso c ON d.curso_id_curso = c.id_curso
                            JOIN professor_tem_disciplina pd ON pd.disciplina_id_disciplina = d.id_disciplina
                            WHERE pd.professor_id_professor = ?";
        $stmt_disciplinas = $conn->prepare($query_disciplinas);
        $stmt_disciplinas->bind_param("i", $professor_id);
        $stmt_disciplinas->execute();
        $result_disciplinas = $stmt_disciplinas->get_result();
        $disciplinas = $result_disciplinas->fetch_all(MYSQLI_ASSOC);
    }

    // Processar o formulário de lançamento de frequência
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['lancar_frequencia'])) {
        $turma_id = intval($_POST['id_turma']);
        $disciplina_id = intval($_POST['id_disciplina']);
        $ano_letivo = intval($_POST['ano_letivo']);
        $data_aula = $_POST['data_aula'];
        $tipo_aula = $_POST['tipo_aula'];
        $observacao = $conn->real_escape_string($_POST['observacao'] ?? '');
        
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
                foreach ($_POST['frequencias'] as $aluno_id => $presenca) {
                    $aluno_id = intval($aluno_id);
                    $presenca = $conn->real_escape_string($presenca);
                    
                    // Verificar se o aluno pertence à turma
                    $query_verifica = "SELECT m.id_matricula FROM matricula m
                                     JOIN aluno a ON m.aluno_id_aluno = a.id_aluno
                                     WHERE a.id_aluno = ? AND m.turma_id_turma = ? AND m.ano_letivo = ?";
                    $stmt_verifica = $conn->prepare($query_verifica);
                    $stmt_verifica->bind_param("iii", $aluno_id, $turma_id, $ano_letivo);
                    $stmt_verifica->execute();
                    $result_verifica = $stmt_verifica->get_result();
                    
                    if ($result_verifica->num_rows > 0) {
                        // Inserir a frequência
                        $query_insere = "INSERT INTO frequencia_aluno 
                            (data_aula, presenca, tipo_aula, observacao, aluno_id_aluno, disciplina_id_disciplina, turma_id_turma)
                            VALUES (?, ?, ?, ?, ?, ?, ?)";
                        $stmt_insere = $conn->prepare($query_insere);
                        $stmt_insere->bind_param(
                            "ssssiii", 
                            $data_aula, 
                            $presenca,
                            $tipo_aula,
                            $observacao,
                            $aluno_id, 
                            $disciplina_id,
                            $turma_id
                        );
                        $stmt_insere->execute();
                    }
                }
                $conn->commit();
                $_SESSION['mensagem_sucesso'] = "Frequência lançada com sucesso!";
            } catch (Exception $e) {
                $conn->rollback();
                $_SESSION['mensagem_erro'] = "Erro ao lançar frequência: " . $e->getMessage();
            }
            header("Location: frequencia.php?id_turma=$turma_id&id_disciplina=$disciplina_id&ano_letivo=$ano_letivo");
            exit();
        } else {
            $_SESSION['mensagem_erro'] = "Você não tem permissão para lançar frequência nesta disciplina.";
        }
    }
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <?php require_once '../../includes/common/head.php'; ?>
    <style>
        .badge-presente {
            background-color: #28a745;
        }
        .badge-ausente {
            background-color: #dc3545;
        }
        .badge-justificado {
            background-color: #ffc107;
            color: #212529;
        }
        .badge-normal {
            background-color: #17a2b8;
        }
        .badge-reposicao {
            background-color: #6c757d;
        }
        .badge-atividade_externa {
            background-color: #6610f2;
        }
        .presenca-select {
            min-width: 120px;
        }
    </style>
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
                                                    <h4>Lançamento de Frequência</h4>
                                                    <span> Registre a frequência dos alunos por disciplina</span>
                                                </div>
                                            </div>
                                            <div class="col-lg-4">
                                                <div class="page-header-breadcrumb">
                                                    <ul class="breadcrumb-title">
                                                        <li class="breadcrumb-item">
                                                            <a href="index.php"><i class="feather icon-home"></i></a>
                                                        </li>
                                                        <li class="breadcrumb-item"><a href="#!">Professor</a></li>
                                                        <li class="breadcrumb-item"><a href="#!">Frequência</a></li>
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
                                                                <h3 class="mb-1" id="presenca-percentual">0%</h3>
                                                                <p class="mb-0">Presença na Aula</p>
                                                            </div>
                                                            <div class="col-4 text-right">
                                                                <i class="feather icon-check-circle f-40"></i>
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
                                                                <h3 class="mb-1" id="ausencias-justificadas">0</h3>
                                                                <p class="mb-0">Ausências Justificadas</p>
                                                            </div>
                                                            <div class="col-4 text-right">
                                                                <i class="feather icon-alert-circle f-40"></i>
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
                                                <form id="formSelecao" method="GET" action="frequencia.php">
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label>Turma</label>
                                                                <select class="form-control" name="id_turma" required>
                                                                    <option value="">Selecione...</option>
                                                                    <?php foreach ($turmas as $turma): ?>
                                                                        <option value="<?= $turma['id_turma'] ?>"
                                                                            <?= isset($_GET['id_turma']) && $_GET['id_turma'] == $turma['id_turma'] ? 'selected' : '' ?>>
                                                                            <?= $turma['nome'] ?> - <?= $turma['curso_nome'] ?>
                                                                        </option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label>Disciplina</label>
                                                                <select class="form-control" name="id_disciplina" required>
                                                                    <option value="">Selecione...</option>
                                                                    <?php foreach ($disciplinas as $disciplina): ?>
                                                                        <option value="<?= $disciplina['id_disciplina'] ?>"
                                                                            <?= isset($_GET['id_disciplina']) && $_GET['id_disciplina'] == $disciplina['id_disciplina'] ? 'selected' : '' ?>>
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

                                        <?php if (isset($_GET['id_turma']) && isset($_GET['id_disciplina']) && isset($_GET['ano_letivo'])): ?>
                                            <?php
                                                $turma_id = intval($_GET['id_turma']);
                                                $disciplina_id = intval($_GET['id_disciplina']);
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
                                                    $query_alunos = "SELECT a.id_aluno, u.nome, u.foto_perfil, 
                                                                    m.numero_matricula, t.classe, t.turno
                                                                  FROM matricula m
                                                                  JOIN aluno a ON m.aluno_id_aluno = a.id_aluno
                                                                  JOIN usuario u ON a.usuario_id_usuario = u.id_usuario
                                                                  JOIN turma t ON m.turma_id_turma = t.id_turma
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
                                                    
                                                    // Buscar estatísticas de frequência
                                                    $query_stats = "SELECT 
                                                                    COUNT(DISTINCT f.aluno_id_aluno) as total_registros,
                                                                    COUNT(DISTINCT CASE WHEN f.presenca = 'presente' THEN f.id_frequencia_aluno END) as total_presentes,
                                                                    COUNT(DISTINCT CASE WHEN f.presenca = 'ausente' THEN f.id_frequencia_aluno END) as total_ausentes,
                                                                    COUNT(DISTINCT CASE WHEN f.presenca = 'justificado' THEN f.id_frequencia_aluno END) as total_justificados,
                                                                    COUNT(DISTINCT CASE WHEN f.tipo_aula = 'normal' THEN f.id_frequencia_aluno END) as total_aulas_normais,
                                                                    COUNT(DISTINCT CASE WHEN f.tipo_aula = 'reposicao' THEN f.id_frequencia_aluno END) as total_reposicoes,
                                                                    COUNT(DISTINCT CASE WHEN f.tipo_aula = 'atividade_externa' THEN f.id_frequencia_aluno END) as total_atividades_externas
                                                                  FROM frequencia_aluno f
                                                                  JOIN matricula m ON m.aluno_id_aluno = f.aluno_id_aluno
                                                                  WHERE f.disciplina_id_disciplina = ? 
                                                                  AND f.turma_id_turma = ? 
                                                                  AND m.ano_letivo = ?";
                                                    $stmt_stats = $conn->prepare($query_stats);
                                                    $stmt_stats->bind_param("iii", $disciplina_id, $turma_id, $ano_letivo);
                                                    $stmt_stats->execute();
                                                    $stats = $stmt_stats->get_result()->fetch_assoc();
                                                    
                                                    // Buscar frequência para o histórico
                                                    $query_frequencia = "SELECT f.id_frequencia_aluno, f.data_aula, f.presenca, 
                                                                      f.tipo_aula, f.observacao,
                                                                      u.nome as aluno_nome, m.numero_matricula
                                                                  FROM frequencia_aluno f
                                                                  JOIN aluno a ON f.aluno_id_aluno = a.id_aluno
                                                                  JOIN usuario u ON a.usuario_id_usuario = u.id_usuario
                                                                  JOIN matricula m ON m.aluno_id_aluno = a.id_aluno
                                                                  WHERE f.disciplina_id_disciplina = ? 
                                                                  AND f.turma_id_turma = ? 
                                                                  AND m.ano_letivo = ?
                                                                  ORDER BY f.data_aula DESC, u.nome";
                                                    $stmt_frequencia = $conn->prepare($query_frequencia);
                                                    $stmt_frequencia->bind_param("iii", $disciplina_id, $turma_id, $ano_letivo);
                                                    $stmt_frequencia->execute();
                                                    $frequencias = $stmt_frequencia->get_result()->fetch_all(MYSQLI_ASSOC);
                                            ?>
                                            
                                            <div class="card card-table">
                                                <div class="card-header">
                                                    <h5>Lançar Frequência - <?= $disciplina_nome ?> | <?= $turma_nome ?> (<?= $ano_letivo ?>)</h5>
                                                </div>
                                                <div class="card-body">
                                                    <form id="formLancarFrequencia" method="POST" action="frequencia.php">
                                                        <input type="hidden" name="id_turma" value="<?= $turma_id ?>">
                                                        <input type="hidden" name="id_disciplina" value="<?= $disciplina_id ?>">
                                                        <input type="hidden" name="ano_letivo" value="<?= $ano_letivo ?>">
                                                        
                                                        <div class="row mb-4">
                                                            <div class="col-md-3">
                                                                <div class="form-group">
                                                                    <label>Data da Aula *</label>
                                                                    <input type="date" class="form-control" name="data_aula" required value="<?= date('Y-m-d') ?>">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <div class="form-group">
                                                                    <label>Tipo de Aula *</label>
                                                                    <select class="form-control" name="tipo_aula" required>
                                                                        <option value="normal">Aula Normal</option>
                                                                        <option value="reposicao">Reposição</option>
                                                                        <option value="atividade_externa">Atividade Externa</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label>Observações</label>
                                                                    <input type="text" class="form-control" name="observacao" placeholder="Ex: Aula sobre geometria">
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
                                                                        <th width="20%">Presença *</th>
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
                                                                                    <select class="form-control presenca-select" 
                                                                                            name="frequencias[<?= $aluno['id_aluno'] ?>]" required>
                                                                                        <option value="presente">Presente</option>
                                                                                        <option value="ausente">Ausente</option>
                                                                                        <option value="justificado">Justificado</option>
                                                                                    </select>
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
                                                                <button type="submit" name="lancar_frequencia" class="btn btn-primary">
                                                                    <i class="feather icon-save"></i> Lançar Frequência
                                                                </button>
                                                            </div>
                                                        <?php endif; ?>
                                                    </form>
                                                </div>
                                            </div>
                                            
                                            <div class="card card-table">
                                                <div class="card-header">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <h5>Histórico de Frequência</h5>
                                                        <div>
                                                            <select class="form-control form-control-sm" id="filtroPresenca" style="width: 150px;">
                                                                <option value="">Todas presenças</option>
                                                                <option value="presente">Presentes</option>
                                                                <option value="ausente">Ausentes</option>
                                                                <option value="justificado">Justificados</option>
                                                            </select>
                                                            <select class="form-control form-control-sm" id="filtroTipoAula" style="width: 150px;">
                                                                <option value="">Todos tipos</option>
                                                                <option value="normal">Aulas Normais</option>
                                                                <option value="reposicao">Reposições</option>
                                                                <option value="atividade_externa">Atividades Externas</option>
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
                                                                    <th>Tipo Aula</th>
                                                                    <th>Aluno</th>
                                                                    <th>Presença</th>
                                                                    <th>Observação</th>
                                                                    <th>Ações</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php if (!empty($frequencias)): ?>
                                                                    <?php foreach ($frequencias as $freq): ?>
                                                                        <tr data-presenca="<?= $freq['presenca'] ?>" data-tipo-aula="<?= $freq['tipo_aula'] ?>">
                                                                            <td><?= date('d/m/Y', strtotime($freq['data_aula'])) ?></td>
                                                                            <td>
                                                                                <?php
                                                                                    $badge_class = '';
                                                                                    $tipo_text = '';
                                                                                    switch($freq['tipo_aula']) {
                                                                                        case 'normal': $badge_class = 'badge-normal'; $tipo_text = 'Normal'; break;
                                                                                        case 'reposicao': $badge_class = 'badge-reposicao'; $tipo_text = 'Reposição'; break;
                                                                                        case 'atividade_externa': $badge_class = 'badge-atividade_externa'; $tipo_text = 'Atividade Externa'; break;
                                                                                    }
                                                                                ?>
                                                                                <span class="badge <?= $badge_class ?>"><?= $tipo_text ?></span>
                                                                            </td>
                                                                            <td><?= htmlspecialchars($freq['aluno_nome']) ?></td>
                                                                            <td>
                                                                                <?php
                                                                                    $badge_class = '';
                                                                                    $presenca_text = '';
                                                                                    switch($freq['presenca']) {
                                                                                        case 'presente': $badge_class = 'badge-presente'; $presenca_text = 'Presente'; break;
                                                                                        case 'ausente': $badge_class = 'badge-ausente'; $presenca_text = 'Ausente'; break;
                                                                                        case 'justificado': $badge_class = 'badge-justificado'; $presenca_text = 'Justificado'; break;
                                                                                    }
                                                                                ?>
                                                                                <span class="badge <?= $badge_class ?>"><?= $presenca_text ?></span>
                                                                            </td>
                                                                            <td><?= htmlspecialchars($freq['observacao'] ?? '-') ?></td>
                                                                            <td>
                                                                                <button class="btn btn-sm btn-danger btn-excluir" data-id="<?= $freq['id_frequencia'] ?>">
                                                                                    <i class="feather icon-trash-2"></i>
                                                                                </button>
                                                                            </td>
                                                                        </tr>
                                                                    <?php endforeach; ?>
                                                                <?php else: ?>
                                                                    <tr>
                                                                        <td colspan="6" class="text-center">Nenhum registro de frequência encontrado</td>
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
            
            <?php if (isset($stats) && count($alunos) > 0): ?>
                const totalPresentes = <?= $stats['total_presentes'] ?? 0 ?>;
                const totalJustificados = <?= $stats['total_justificados'] ?? 0 ?>;
                const totalRegistros = <?= $stats['total_registros'] ?? 0 ?>;
                const totalAlunos = <?= count($alunos) ?>;
                
                // Calcular percentual de presença (considerando apenas aulas normais)
                const percentualPresenca = totalRegistros > 0 ? 
                    Math.round((totalPresentes / (totalRegistros * totalAlunos)) * 100) : 0;
                
                document.getElementById('presenca-percentual').textContent = percentualPresenca + '%';
                document.getElementById('ausencias-justificadas').textContent = totalJustificados;
            <?php else: ?>
                document.getElementById('presenca-percentual').textContent = '0%';
                document.getElementById('ausencias-justificadas').textContent = '0';
            <?php endif; ?>
            
            // Filtros do histórico
            $(document).ready(function() {
                $('#filtroPresenca, #filtroTipoAula').change(function() {
                    const presenca = $('#filtroPresenca').val();
                    const tipoAula = $('#filtroTipoAula').val();
                    
                    $('#tabelaHistorico tbody tr').each(function() {
                        const rowPresenca = $(this).data('presenca');
                        const rowTipoAula = $(this).data('tipo-aula');
                        
                        const showPresenca = presenca === '' || rowPresenca === presenca;
                        const showTipoAula = tipoAula === '' || rowTipoAula === tipoAula;
                        
                        $(this).toggle(showPresenca && showTipoAula);
                    });
                });
                
                // Excluir frequência
                $('.btn-excluir').click(function() {
                    const id = $(this).data('id_frequencia');
                    const linha = $(this).closest('tr');
                    
                    if (confirm('Tem certeza que deseja excluir este registro de frequência?')) {
                        $.ajax({
                            url: '../../process/professor/excluir_frequencia.php',
                            method: 'POST',
                            data: { id_frequencia: id_frequencia },
                            dataType: 'json',
                            success: function(response) {
                                if (response.success) {
                                    linha.fadeOut(function() {
                                        linha.remove();
                                        alert('Registro de frequência excluído com sucesso!');
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
                $('#formLancarFrequencia').validate({
                    errorClass: 'text-danger',
                    rules: {
                        'data_aula': { required: true },
                        'tipo_aula': { required: true }
                    },
                    messages: {
                        'data_aula': 'Informe a data da aula',
                        'tipo_aula': 'Selecione o tipo de aula'
                    },
                    highlight: function(element) {
                        $(element).addClass('is-invalid');
                    },
                    unhighlight: function(element) {
                        $(element).removeClass('is-invalid');
                    }
                });
                
                $('.presenca-select').each(function() {
                    $(this).rules('add', {
                        required: true,
                        messages: {
                            required: "Selecione a presença"
                        }
                    });
                });
            });
        </script>
    </body>
</html>
<?php $conn->close(); ?>