<?php
    require_once '../../includes/common/permissoes.php';
    verificarPermissao(['professor']);
    require_once '../../process/verificar_sessao.php';
    
    $title = "Lançamento de Notas";
    require_once '../../database/conexao.php';

    // Obter o ID do professor logado
    $id_usuario = $_SESSION['id_usuario'];
    $professor_id = 0;

    // Buscar o ID do professor na tabela professor
    $query_professor = "SELECT id_professor FROM professor WHERE usuario_id_usuario = ?";
    $stmt_professor = $conn->prepare($query_professor);
    $stmt_professor->bind_param("i", $id_usuario);
    $stmt_professor->execute();
    $result_professor = $stmt_professor->get_result();
    
    if ($result_professor->num_rows > 0) {
        $row_professor = $result_professor->fetch_assoc();
        $professor_id = $row_professor['id_professor'];
    }

    // Buscar turmas do professor
    $turmas = [];
    if ($professor_id > 0) {
        $query_turmas = "SELECT t.id_turma, t.nome, c.nome as curso_nome 
                        FROM turma t
                        JOIN curso c ON t.curso_id_curso = c.id_curso
                        JOIN professor_tem_turma pt ON pt.turma_id_turma = t.id_turma
                        WHERE pt.professor_id_professor = ?";
        $stmt_turmas = $conn->prepare($query_turmas);
        $stmt_turmas->bind_param("i", $professor_id);
        $stmt_turmas->execute();
        $result_turmas = $stmt_turmas->get_result();
        
        while ($row = $result_turmas->fetch_assoc()) {
            $turmas[] = $row;
        }
    }

    // Buscar disciplinas do professor
    $disciplinas = [];
    if ($professor_id > 0) {
        $query_disciplinas = "SELECT d.id_disciplina, d.nome, c.nome as curso_nome
                            FROM disciplina d
                            JOIN curso c ON d.curso_id_curso = c.id_curso
                            WHERE d.professor_id_professor = ?";
        $stmt_disciplinas = $conn->prepare($query_disciplinas);
        $stmt_disciplinas->bind_param("i", $professor_id);
        $stmt_disciplinas->execute();
        $result_disciplinas = $stmt_disciplinas->get_result();
        
        while ($row = $result_disciplinas->fetch_assoc()) {
            $disciplinas[] = $row;
        }
    }

    // Processar o formulário de lançamento de notas
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['lancar_notas'])) {
        $turma_id = intval($_POST['turma_id']);
        $disciplina_id = intval($_POST['disciplina_id']);
        $ano_letivo = intval($_POST['ano_letivo']);
        $data_nota = $_POST['data_nota'];
        
        // Verificar se a disciplina pertence ao professor
        $disciplina_valida = false;
        foreach ($disciplinas as $disciplina) {
            if ($disciplina['id_disciplina'] == $disciplina_id) {
                $disciplina_valida = true;
                break;
            }
        }
        
        if ($disciplina_valida) {
            // Processar cada nota enviada
            foreach ($_POST['notas'] as $matricula_id => $valor_nota) {
                $matricula_id = intval($matricula_id);
                $valor_nota = floatval(str_replace(',', '.', $valor_nota));
                
                // Verificar se a nota está no intervalo válido (0-20)
                if ($valor_nota >= 0 && $valor_nota <= 20) {
                    // Verificar se o aluno pertence à turma selecionada
                    $query_verifica_aluno = "SELECT a.id_aluno 
                                           FROM matricula m
                                           JOIN aluno a ON m.aluno_id_aluno = a.id_aluno
                                           WHERE m.id_matricula = ? AND m.turma_id_turma = ?";
                    $stmt_verifica = $conn->prepare($query_verifica_aluno);
                    $stmt_verifica->bind_param("ii", $matricula_id, $turma_id);
                    $stmt_verifica->execute();
                    $result_verifica = $stmt_verifica->get_result();
                    
                    if ($result_verifica->num_rows > 0) {
                        $row_verifica = $result_verifica->fetch_assoc();
                        $aluno_id = $row_verifica['id_aluno'];
                        
                        // Inserir a nota no banco de dados
                        $query_insere_nota = "INSERT INTO nota (nota, data, aluno_id_aluno, disciplina_id_disciplina)
                                            VALUES (?, ?, ?, ?)";
                        $stmt_insere = $conn->prepare($query_insere_nota);
                        $stmt_insere->bind_param("dsii", $valor_nota, $data_nota, $aluno_id, $disciplina_id);
                        $stmt_insere->execute();
                    }
                }
            }
            
            $_SESSION['mensagem_sucesso'] = "Notas lançadas com sucesso!";
            header("Location: notas.php");
            exit();
        } else {
            $_SESSION['mensagem_erro'] = "Você não tem permissão para lançar notas nesta disciplina.";
        }
    }
?>
<!DOCTYPE html>
<html lang="pt">

<?php require_once '../../includes/common/head.php'; ?>

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
                                                    <div class="d-inline">
                                                        <h4>Lançamento de Notas</h4>
                                                        <span>Registre as notas dos alunos por disciplina</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-4">
                                                <div class="page-header-breadcrumb">
                                                    <ul class="breadcrumb-title">
                                                        <li class="breadcrumb-item">
                                                            <a href="index.php"> <i class="feather icon-home"></i> </a>
                                                        </li>
                                                        <li class="breadcrumb-item"><a href="#!">Professor</a></li>
                                                        <li class="breadcrumb-item"><a href="#!">Lançamento de Notas</a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="page-body">
                                        <?php if (isset($_SESSION['mensagem_sucesso'])): ?>
                                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                                <?= $_SESSION['mensagem_sucesso']; ?>
                                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <?php unset($_SESSION['mensagem_sucesso']); ?>
                                        <?php endif; ?>
                                        
                                        <?php if (isset($_SESSION['mensagem_erro'])): ?>
                                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                                <?= $_SESSION['mensagem_erro']; ?>
                                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <?php unset($_SESSION['mensagem_erro']); ?>
                                        <?php endif; ?>

                                        <div class="row">
                                            <!-- Card de Estatísticas -->
                                            <div class="col-xl-4 col-md-6">
                                                <div class="card bg-c-yellow update-card">
                                                    <div class="card-block">
                                                        <div class="row align-items-end">
                                                            <div class="col-8">
                                                                <h4 class="text-white" id="total-alunos">0</h4>
                                                                <h6 class="text-white m-b-0">
                                                                    <i class="feather icon-users"></i> Alunos na Turma
                                                                </h6>
                                                            </div>
                                                            <div class="col-4 text-right">
                                                                <i class="feather icon-users text-white" style="font-size: 40px;"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card-footer">
                                                        <p class="text-white m-b-0">
                                                            <i class="feather icon-clock text-white f-14 m-r-10"></i> Atualizado agora
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-xl-4 col-md-6">
                                                <div class="card bg-c-green update-card">
                                                    <div class="card-block">
                                                        <div class="row align-items-end">
                                                            <div class="col-8">
                                                                <h4 class="text-white" id="media-turma">0.0</h4>
                                                                <h6 class="text-white m-b-0">
                                                                    <i class="feather icon-bar-chart"></i> Média da Turma
                                                                </h6>
                                                            </div>
                                                            <div class="col-4 text-right">
                                                                <i class="feather icon-bar-chart text-white" style="font-size: 40px;"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card-footer">
                                                        <p class="text-white m-b-0">
                                                            <i class="feather icon-clock text-white f-14 m-r-10"></i> Atualizado agora
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-xl-4 col-md-6">
                                                <div class="card bg-c-pink update-card">
                                                    <div class="card-block">
                                                        <div class="row align-items-end">
                                                            <div class="col-8">
                                                                <h4 class="text-white" id="notas-lancadas">0</h4>
                                                                <h6 class="text-white m-b-0">
                                                                    <i class="feather icon-edit"></i> Notas Lançadas
                                                                </h6>
                                                            </div>
                                                            <div class="col-4 text-right">
                                                                <i class="feather icon-edit text-white" style="font-size: 40px;"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card-footer">
                                                        <p class="text-white m-b-0">
                                                            <i class="feather icon-clock text-white f-14 m-r-10"></i> Atualizado agora
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card card-table">
                                            <div class="card-header">
                                                <h5>Selecionar Turma e Disciplina</h5>
                                            </div>
                                            <div class="card-block">
                                                <form id="formSelecionarTurmaDisciplina" method="GET" action="notas.php">
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="turma_id">Turma</label>
                                                                <select class="form-control" id="turma_id" name="turma_id" required>
                                                                    <option value="">Selecione uma turma</option>
                                                                    <?php foreach ($turmas as $turma): ?>
                                                                        <option value="<?= $turma['id_turma'] ?>" <?= isset($_GET['turma_id']) && $_GET['turma_id'] == $turma['id_turma'] ? 'selected' : '' ?>>
                                                                            <?= $turma['nome'] ?> - <?= $turma['curso_nome'] ?>
                                                                        </option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="disciplina_id">Disciplina</label>
                                                                <select class="form-control" id="disciplina_id" name="disciplina_id" required>
                                                                    <option value="">Selecione uma disciplina</option>
                                                                    <?php foreach ($disciplinas as $disciplina): ?>
                                                                        <option value="<?= $disciplina['id_disciplina'] ?>" <?= isset($_GET['disciplina_id']) && $_GET['disciplina_id'] == $disciplina['id_disciplina'] ? 'selected' : '' ?>>
                                                                            <?= $disciplina['nome'] ?> - <?= $disciplina['curso_nome'] ?>
                                                                        </option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <div class="form-group">
                                                                <label for="ano_letivo">Ano Letivo</label>
                                                                <select class="form-control" id="ano_letivo" name="ano_letivo" required>
                                                                    <?php 
                                                                        $ano_atual = date('Y');
                                                                        for ($i = $ano_atual - 2; $i <= $ano_atual + 1; $i++): 
                                                                    ?>
                                                                        <option value="<?= $i ?>" <?= isset($_GET['ano_letivo']) && $_GET['ano_letivo'] == $i ? 'selected' : '' ?>>
                                                                            <?= $i ?>
                                                                        </option>
                                                                    <?php endfor; ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <div class="form-group" style="margin-top: 25px;">
                                                                <button type="submit" class="btn btn-primary btn-sm btn-block">
                                                                    <i class="feather icon-search"></i> Buscar Alunos
                                                                </button>
                                                            </div>
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
                                                
                                                // Verificar se a disciplina pertence ao professor
                                                $disciplina_valida = false;
                                                foreach ($disciplinas as $disciplina) {
                                                    if ($disciplina['id_disciplina'] == $disciplina_id) {
                                                        $disciplina_valida = true;
                                                        break;
                                                    }
                                                }
                                                
                                                if ($disciplina_valida) {
                                                    // Buscar alunos da turma no ano letivo selecionado
                                                    $query_alunos = "SELECT 
                                                                        m.id_matricula,
                                                                        u.nome,
                                                                        u.foto_perfil,
                                                                        m.numero_matricula,
                                                                        m.classe,
                                                                        m.turno
                                                                    FROM matricula m
                                                                    JOIN aluno a ON m.aluno_id_aluno = a.id_aluno
                                                                    JOIN usuario u ON a.usuario_id_usuario = u.id_usuario
                                                                    WHERE m.turma_id_turma = ? AND m.ano_letivo = ?
                                                                    ORDER BY u.nome";
                                                    $stmt_alunos = $conn->prepare($query_alunos);
                                                    $stmt_alunos->bind_param("ii", $turma_id, $ano_letivo);
                                                    $stmt_alunos->execute();
                                                    $result_alunos = $stmt_alunos->get_result();
                                                    
                                                    $alunos = [];
                                                    while ($row = $result_alunos->fetch_assoc()) {
                                                        $alunos[] = $row;
                                                    }
                                                    
                                                    // Buscar informações da turma e disciplina
                                                    $query_turma = "SELECT nome FROM turma WHERE id_turma = ?";
                                                    $stmt_turma = $conn->prepare($query_turma);
                                                    $stmt_turma->bind_param("i", $turma_id);
                                                    $stmt_turma->execute();
                                                    $result_turma = $stmt_turma->get_result();
                                                    $turma_nome = $result_turma->fetch_assoc()['nome'];
                                                    
                                                    $query_disciplina = "SELECT nome FROM disciplina WHERE id_disciplina = ?";
                                                    $stmt_disciplina = $conn->prepare($query_disciplina);
                                                    $stmt_disciplina->bind_param("i", $disciplina_id);
                                                    $stmt_disciplina->execute();
                                                    $result_disciplina = $stmt_disciplina->get_result();
                                                    $disciplina_nome = $result_disciplina->fetch_assoc()['nome'];
                                                    
                                                    // Buscar notas já lançadas para estatísticas
                                                    $query_estatisticas = "SELECT 
                                                                        COUNT(DISTINCT n.aluno_id_aluno) as total_notas,
                                                                        AVG(n.nota) as media_turma
                                                                    FROM nota n
                                                                    JOIN matricula m ON m.aluno_id_aluno = n.aluno_id_aluno
                                                                    WHERE n.disciplina_id_disciplina = ? 
                                                                    AND m.turma_id_turma = ? 
                                                                    AND m.ano_letivo = ?";
                                                    $stmt_estatisticas = $conn->prepare($query_estatisticas);
                                                    $stmt_estatisticas->bind_param("iii", $disciplina_id, $turma_id, $ano_letivo);
                                                    $stmt_estatisticas->execute();
                                                    $result_estatisticas = $stmt_estatisticas->get_result();
                                                    $estatisticas = $result_estatisticas->fetch_assoc();
                                            ?>
                                            
                                            <div class="card card-table">
                                                <div class="card-header">
                                                    <h5>Lançar Notas - Turma: <?= $turma_nome ?> | Disciplina: <?= $disciplina_nome ?> | Ano Letivo: <?= $ano_letivo ?></h5>
                                                </div>
                                                <div class="card-block">
                                                    <form id="formLancarNotas" method="POST" action="notas.php">
                                                        <input type="hidden" name="turma_id" value="<?= $turma_id ?>">
                                                        <input type="hidden" name="disciplina_id" value="<?= $disciplina_id ?>">
                                                        <input type="hidden" name="ano_letivo" value="<?= $ano_letivo ?>">
                                                        
                                                        <div class="row mb-4">
                                                            <div class="col-md-3">
                                                                <div class="form-group">
                                                                    <label for="data_nota">Data da Avaliação</label>
                                                                    <input type="date" class="form-control" id="data_nota" name="data_nota" required value="<?= date('Y-m-d') ?>">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="table-responsive">
                                                            <table class="table table-bordered table-hover">
                                                                <thead>
                                                                    <tr>
                                                                        <th width="5%">#</th>
                                                                        <th width="15%">Foto</th>
                                                                        <th width="25%">Nome do Aluno</th>
                                                                        <th width="15%">Nº Matrícula</th>
                                                                        <th width="10%">Classe</th>
                                                                        <th width="10%">Turno</th>
                                                                        <th width="20%">Nota (0-20)</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php if (count($alunos) > 0): ?>
                                                                        <?php foreach ($alunos as $index => $aluno): ?>
                                                                            <tr>
                                                                                <td><?= $index + 1 ?></td>
                                                                                <td class="text-center">
                                                                                    <img src="../../public/uploads/perfil/<?= !empty($aluno['foto_perfil']) ? $aluno['foto_perfil'] : 'default.png' ?>" 
                                                                                         class="img-radius img-40" alt="Foto do Aluno">
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
                                                                            <td colspan="7" class="text-center">Nenhum aluno encontrado nesta turma para o ano letivo selecionado.</td>
                                                                        </tr>
                                                                    <?php endif; ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                        
                                                        <?php if (count($alunos) > 0): ?>
                                                            <div class="text-right mt-3">
                                                                <button type="submit" name="lancar_notas" class="btn btn-primary">
                                                                    <i class="feather icon-save"></i> Salvar Notas
                                                                </button>
                                                            </div>
                                                        <?php endif; ?>
                                                    </form>
                                                </div>
                                            </div>
                                            
                                            <div class="card card-table">
                                                <div class="card-header">
                                                    <h5>Histórico de Notas Lançadas</h5>
                                                </div>
                                                <div class="card-block">
                                                    <?php
                                                        // Buscar notas já lançadas para esta turma e disciplina
                                                        $query_notas = "SELECT 
                                                                            n.id_nota,
                                                                            n.nota,
                                                                            n.data,
                                                                            u.nome as aluno_nome,
                                                                            m.numero_matricula
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
                                                        $result_notas = $stmt_notas->get_result();
                                                        
                                                        $notas = [];
                                                        while ($row = $result_notas->fetch_assoc()) {
                                                            $notas[] = $row;
                                                        }
                                                    ?>
                                                    
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered table-hover">
                                                            <thead>
                                                                <tr>
                                                                    <th>Data</th>
                                                                    <th>Aluno</th>
                                                                    <th>Nº Matrícula</th>
                                                                    <th>Nota</th>
                                                                    <th>Ações</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php if (count($notas) > 0): ?>
                                                                    <?php foreach ($notas as $nota): ?>
                                                                        <tr>
                                                                            <td><?= date('d/m/Y', strtotime($nota['data'])) ?></td>
                                                                            <td><?= htmlspecialchars($nota['aluno_nome']) ?></td>
                                                                            <td><?= htmlspecialchars($nota['numero_matricula']) ?></td>
                                                                            <td><?= number_format($nota['nota'], 2, ',', '.') ?></td>
                                                                            <td>
                                                                                <button class="btn btn-sm btn-danger btn-excluir-nota" data-id="<?= $nota['id_nota'] ?>">
                                                                                    <i class="feather icon-trash-2"></i> Excluir
                                                                                </button>
                                                                            </td>
                                                                        </tr>
                                                                    <?php endforeach; ?>
                                                                <?php else: ?>
                                                                    <tr>
                                                                        <td colspan="5" class="text-center">Nenhuma nota lançada ainda para esta turma e disciplina.</td>
                                                                    </tr>
                                                                <?php endif; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <script>
                                                // Atualizar estatísticas
                                                document.getElementById('total-alunos').textContent = '<?= count($alunos) ?>';
                                                document.getElementById('notas-lancadas').textContent = '<?= $estatisticas['total_notas'] ?? 0 ?>';
                                                document.getElementById('media-turma').textContent = '<?= isset($estatisticas['media_turma']) ? number_format($estatisticas['media_turma'], 2, ',', '.') : '0.00' ?>';
                                            </script>
                                            
                                            <?php } else { ?>
                                                <div class="alert alert-danger">
                                                    Você não tem permissão para acessar esta disciplina ou os parâmetros são inválidos.
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
            $(document).ready(function() {
                // Validação do formulário de notas
                $('#formLancarNotas').validate({
                    errorClass: 'text-danger',
                    errorElement: 'span',
                    highlight: function(element) {
                        $(element).addClass('is-invalid');
                    },
                    unhighlight: function(element) {
                        $(element).removeClass('is-invalid');
                    },
                    rules: {
                        'data_nota': {
                            required: true
                        }
                    },
                    messages: {
                        'data_nota': {
                            required: "Por favor, informe a data da avaliação"
                        }
                    }
                });
                
                // Validação das notas individuais
                $('.nota-input').each(function() {
                    $(this).rules('add', {
                        required: true,
                        min: 0,
                        max: 20,
                        messages: {
                            required: "Informe a nota",
                            min: "Nota mínima é 0",
                            max: "Nota máxima é 20"
                        }
                    });
                });
                
                // Exclusão de nota
                $('.btn-excluir-nota').click(function() {
                    var id_nota = $(this).data('id');
                    var linha = $(this).closest('tr');
                    
                    if (confirm('Tem certeza que deseja excluir esta nota?')) {
                        $.ajax({
                            url: '../../process/professor/excluir_nota.php',
                            type: 'POST',
                            data: { id_nota: id_nota },
                            dataType: 'json',
                            success: function(response) {
                                if (response.success) {
                                    linha.fadeOut(300, function() {
                                        $(this).remove();
                                    });
                                    
                                    // Mostrar mensagem de sucesso
                                    alert('Nota excluída com sucesso!');
                                } else {
                                    alert('Erro ao excluir nota: ' + response.message);
                                }
                            },
                            error: function() {
                                alert('Erro ao comunicar com o servidor.');
                            }
                        });
                    }
                });
            });
        </script>
    </body>
</html>
<?php $conn->close(); ?>