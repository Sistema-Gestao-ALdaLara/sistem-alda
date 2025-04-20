<?php
require_once __DIR__ . '/../../includes/common/permissoes.php';
verificarPermissao(['professor']);
require_once __DIR__ . '/../../process/verificar_sessao.php';
require_once __DIR__ . '/../../database/conexao.php';

// Verificação de sessão
if (!isset($_SESSION['id_usuario'])) {
    header("Location: /login.php");
    exit();
}

// 1. Obter informações do professor
$query_professor = "SELECT p.id_professor, c.nome as nome_curso, u.nome as nome_professor
                    FROM professor p
                    JOIN curso c ON p.curso_id_curso = c.id_curso
                    JOIN usuario u ON p.usuario_id_usuario = u.id_usuario
                    WHERE p.usuario_id_usuario = ?";
$stmt_professor = $conn->prepare($query_professor);
$stmt_professor->bind_param("i", $_SESSION['id_usuario']);
$stmt_professor->execute();
$professor = $stmt_professor->get_result()->fetch_assoc();

if (!$professor) {
    header("Location: /professor/dashboard.php");
    exit();
}

// 2. Obter disciplinas do professor agrupadas por classe e turma
$query_disciplinas = "SELECT d.id_disciplina, d.nome as nome_disciplina, ptd.classe,
                      GROUP_CONCAT(DISTINCT t.nome ORDER BY t.nome SEPARATOR ', ') as turmas
                      FROM professor_tem_disciplina ptd
                      JOIN disciplina d ON ptd.disciplina_id_disciplina = d.id_disciplina
                      JOIN turma t ON t.classe = ptd.classe
                      WHERE ptd.professor_id_professor = ?
                      GROUP BY d.id_disciplina, ptd.classe
                      ORDER BY ptd.classe, d.nome";
$stmt_disciplinas = $conn->prepare($query_disciplinas);
$stmt_disciplinas->bind_param("i", $professor['id_professor']);
$stmt_disciplinas->execute();
$disciplinas_result = $stmt_disciplinas->get_result();

$disciplinas_por_classe = [];
while ($disciplina = $disciplinas_result->fetch_assoc()) {
    $classe = $disciplina['classe'];
    if (!isset($disciplinas_por_classe[$classe])) {
        $disciplinas_por_classe[$classe] = [];
    }
    $disciplinas_por_classe[$classe][] = $disciplina;
}

// 3. Processar formulário de lançamento de notas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['lancar_nota'])) {
    $aluno_id = (int)$_POST['aluno_id'];
    $disciplina_id = (int)$_POST['disciplina_id'];
    $nota = (float)$_POST['nota'];
    $tipo_avaliacao = $_POST['tipo_avaliacao'];
    $trimestre = (int)$_POST['trimestre'];
    $peso = (float)$_POST['peso'];
    $descricao = $conn->real_escape_string($_POST['descricao']);
    $turma_id = (int)$_POST['turma_id'];
    
    // Validar dados
    if ($nota < 0 || $nota > 20) {
        $mensagem_erro = "A nota deve estar entre 0 e 20";
    } elseif ($peso <= 0 || $peso > 5) {
        $mensagem_erro = "O peso deve ser entre 0.1 e 5";
    } else {
        // Verificar se o professor tem permissão para lançar nota nesta disciplina/turma
        $query_verifica = "SELECT 1 FROM professor_tem_disciplina ptd
                          JOIN turma t ON t.classe = ptd.classe
                          WHERE ptd.professor_id_professor = ? 
                          AND ptd.disciplina_id_disciplina = ?
                          AND t.id_turma = ?";
        $stmt_verifica = $conn->prepare($query_verifica);
        $stmt_verifica->bind_param("iii", $professor['id_professor'], $disciplina_id, $turma_id);
        $stmt_verifica->execute();
        
        if ($stmt_verifica->get_result()->num_rows > 0) {
            // Inserir nota no banco de dados
            $query_insere = "INSERT INTO nota (nota, data, tipo_avaliacao, trimestre, descricao, peso, aluno_id_aluno, disciplina_id_disciplina)
                             VALUES (?, NOW(), ?, ?, ?, ?, ?, ?)";
            $stmt_insere = $conn->prepare($query_insere);
            $stmt_insere->bind_param("dsisdii", $nota, $tipo_avaliacao, $trimestre, $descricao, $peso, $aluno_id, $disciplina_id);
            
            if ($stmt_insere->execute()) {
                $mensagem_sucesso = "Nota lançada com sucesso!";
                
                // Registrar na frequência (opcional)
                $query_frequencia = "INSERT INTO frequencia_aluno 
                                    (data_aula, presenca, tipo_aula, observacao, aluno_id_aluno, disciplina_id_disciplina, turma_id_turma)
                                    VALUES (CURDATE(), 'presente', 'normal', 'Avaliação: {$descricao}', ?, ?, ?)";
                $stmt_frequencia = $conn->prepare($query_frequencia);
                $stmt_frequencia->bind_param("iii", $aluno_id, $disciplina_id, $turma_id);
                $stmt_frequencia->execute();
            } else {
                $mensagem_erro = "Erro ao lançar nota: " . $conn->error;
            }
        } else {
            $mensagem_erro = "Você não tem permissão para lançar notas nesta disciplina/turma";
        }
    }
}

// 4. Obter turmas do professor (para seleção)
$query_turmas = "SELECT DISTINCT t.id_turma, t.nome, t.classe, t.turno
                FROM turma t
                JOIN professor_tem_disciplina ptd ON t.classe = ptd.classe
                WHERE ptd.professor_id_professor = ?
                ORDER BY t.classe, t.turno, t.nome";
$stmt_turmas = $conn->prepare($query_turmas);
$stmt_turmas->bind_param("i", $professor['id_professor']);
$stmt_turmas->execute();
$turmas = $stmt_turmas->get_result();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <?php require_once __DIR__ . '/../../includes/common/head.php'; ?>
    <title>Lançamento de Notas - Área do Professor</title>
    <style>
        .card-disciplina {
            margin-bottom: 20px;
            border-left: 4px solid #4680ff;
        }
        .table-alunos th {
            background-color: #f8f9fa;
        }
        .badge-classe {
            font-size: 1rem;
            padding: 5px 10px;
            background-color: #4680ff;
        }
        .form-nota {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #eee;
        }
        .disciplina-card {
            transition: all 0.3s ease;
            border: 1px solid #eee;
        }
        .disciplina-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .turma-info {
            font-size: 0.9rem;
            color: #666;
        }
    </style>
</head>
<body>
    <?php require_once __DIR__ . '/../../includes/common/preloader.php'; ?>

    <div id="pcoded" class="pcoded">
        <div class="pcoded-overlay-box"></div>
        <div class="pcoded-container navbar-wrapper">
            <?php require_once __DIR__ . '/../../includes/professor/navbar.php'; ?>

            <div class="pcoded-main-container">
                <div class="pcoded-wrapper">
                    <?php require_once __DIR__ . '/../../includes/professor/sidebar.php'; ?>
                    
                    <div class="pcoded-content">
                        <div class="pcoded-inner-content">
                            <div class="main-body">
                                <div class="page-wrapper">
                                    <div class="page-body">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="card">
                                                    <div class="card-header">
                                                        <h5>
                                                            <i class="feather icon-edit"></i> Lançamento de Notas
                                                            <span class="float-right">
                                                                Curso: <?= htmlspecialchars($professor['nome_curso']) ?> | 
                                                                Prof. <?= htmlspecialchars($professor['nome_professor']) ?>
                                                            </span>
                                                        </h5>
                                                    </div>
                                                    <div class="card-body">
                                                        <?php if (isset($mensagem_sucesso)): ?>
                                                            <div class="alert alert-success icons-alert">
                                                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                                    <i class="icofont icofont-close-line-circled"></i>
                                                                </button>
                                                                <p><i class="feather icon-check-circle"></i> <?= $mensagem_sucesso ?></p>
                                                            </div>
                                                        <?php endif; ?>
                                                        
                                                        <?php if (isset($mensagem_erro)): ?>
                                                            <div class="alert alert-danger icons-alert">
                                                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                                    <i class="icofont icofont-close-line-circled"></i>
                                                                </button>
                                                                <p><i class="feather icon-alert-circle"></i> <?= $mensagem_erro ?></p>
                                                            </div>
                                                        <?php endif; ?>
                                                        
                                                        <div class="form-nota">
                                                            <h5><i class="feather icon-plus"></i> Lançar Nova Nota</h5>
                                                            <form method="POST">
                                                                <div class="row">
                                                                    <div class="col-md-4">
                                                                        <div class="form-group">
                                                                            <label for="turma_id">Turma</label>
                                                                            <select class="form-control" id="turma_id" name="turma_id" required>
                                                                                <option value="">Selecione uma turma</option>
                                                                                <?php while ($turma = $turmas->fetch_assoc()): ?>
                                                                                    <option value="<?= $turma['id_turma'] ?>">
                                                                                        <?= htmlspecialchars($turma['classe']) ?> - <?= htmlspecialchars($turma['nome']) ?> (<?= htmlspecialchars($turma['turno']) ?>)
                                                                                    </option>
                                                                                <?php endwhile; ?>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <div class="col-md-4">
                                                                        <div class="form-group">
                                                                            <label for="disciplina_id">Disciplina</label>
                                                                            <select class="form-control" id="disciplina_id" name="disciplina_id" required disabled>
                                                                                <option value="">Selecione primeiro a turma</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <div class="col-md-4">
                                                                        <div class="form-group">
                                                                            <label for="aluno_id">Aluno</label>
                                                                            <select class="form-control" id="aluno_id" name="aluno_id" required disabled>
                                                                                <option value="">Selecione primeiro a turma</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                
                                                                <div class="row">
                                                                    <div class="col-md-3">
                                                                        <div class="form-group">
                                                                            <label for="nota">Nota (0-20)</label>
                                                                            <input type="number" class="form-control" id="nota" name="nota" min="0" max="20" step="0.01" required>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <div class="col-md-3">
                                                                        <div class="form-group">
                                                                            <label for="tipo_avaliacao">Tipo de Avaliação</label>
                                                                            <select class="form-control" id="tipo_avaliacao" name="tipo_avaliacao" required>
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
                                                                            <label for="trimestre">Trimestre</label>
                                                                            <select class="form-control" id="trimestre" name="trimestre" required>
                                                                                <option value="1">1º Trimestre</option>
                                                                                <option value="2">2º Trimestre</option>
                                                                                <option value="3">3º Trimestre</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <div class="col-md-2">
                                                                        <div class="form-group">
                                                                            <label for="peso">Peso (0.1-5)</label>
                                                                            <input type="number" class="form-control" id="peso" name="peso" min="0.1" max="5" step="0.1" value="1.0" required>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <div class="col-md-2">
                                                                        <div class="form-group">
                                                                            <label for="data_avaliacao">Data</label>
                                                                            <input type="date" class="form-control" id="data_avaliacao" name="data_avaliacao" value="<?= date('Y-m-d') ?>" required>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <div class="col-md-12">
                                                                        <div class="form-group">
                                                                            <label for="descricao">Descrição</label>
                                                                            <input type="text" class="form-control" id="descricao" name="descricao" placeholder="Ex: Prova escrita sobre..." required>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                
                                                                <button type="submit" name="lancar_nota" class="btn btn-primary">
                                                                    <i class="feather icon-save"></i> Lançar Nota
                                                                </button>
                                                            </form>
                                                        </div>
                                                        
                                                        <h4 class="mb-4"><i class="feather icon-book"></i> Minhas Disciplinas por Classe</h4>
                                                        
                                                        <?php if (empty($disciplinas_por_classe)): ?>
                                                            <div class="alert alert-info icons-alert">
                                                                <i class="feather icon-info"></i> 
                                                                Você não está atribuído a nenhuma disciplina no momento.
                                                            </div>
                                                        <?php else: ?>
                                                            <?php foreach ($disciplinas_por_classe as $classe => $disciplinas): ?>
                                                                <div class="card card-disciplina">
                                                                    <div class="card-header">
                                                                        <h5>
                                                                            <span class="badge badge-classe">Classe <?= htmlspecialchars($classe) ?></span>
                                                                        </h5>
                                                                    </div>
                                                                    <div class="card-body">
                                                                        <div class="row">
                                                                            <?php foreach ($disciplinas as $disciplina): ?>
                                                                                <div class="col-md-4 mb-3">
                                                                                    <div class="card disciplina-card">
                                                                                        <div class="card-body">
                                                                                            <h5><?= htmlspecialchars($disciplina['nome_disciplina']) ?></h5>
                                                                                            <p class="turma-info">
                                                                                                <i class="feather icon-users"></i> Turmas: <?= htmlspecialchars($disciplina['turmas']) ?>
                                                                                            </p>
                                                                                            <div class="btn-group">
                                                                                                <a href="ver_alunos.php?disciplina_id=<?= $disciplina['id_disciplina'] ?>&classe=<?= $classe ?>" 
                                                                                                   class="btn btn-sm btn-outline-primary">
                                                                                                    <i class="feather icon-users"></i> Alunos
                                                                                                </a>
                                                                                                <a href="ver_notas.php?disciplina_id=<?= $disciplina['id_disciplina'] ?>&classe=<?= $classe ?>" 
                                                                                                   class="btn btn-sm btn-outline-info">
                                                                                                    <i class="feather icon-list"></i> Notas
                                                                                                </a>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            <?php endforeach; ?>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            <?php endforeach; ?>
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

    <?php require_once __DIR__ . '/../../includes/common/js_imports.php'; ?>
    <script>
        $(document).ready(function() {
            // Carregar disciplinas quando selecionar uma turma
            $('#turma_id').change(function() {
                var turmaId = $(this).val();
                var professorId = <?= $professor['id_professor'] ?>;
                
                if (turmaId) {
                    // Obter a classe da turma selecionada
                    var selectedOption = $(this).find('option:selected');
                    var classe = selectedOption.text().split(' - ')[0];
                    
                    // Carregar disciplinas do professor para aquela classe
                    $.ajax({
                        url: '/api/get_disciplinas_por_professor.php',
                        type: 'GET',
                        data: { 
                            professor_id: professorId,
                            classe: classe
                        },
                        success: function(data) {
                            $('#disciplina_id').empty().append('<option value="">Selecione a disciplina</option>');
                            $.each(data, function(key, value) {
                                $('#disciplina_id').append('<option value="'+value.id_disciplina+'">'+value.nome_disciplina+'</option>');
                            });
                            $('#disciplina_id').prop('disabled', false);
                        }
                    });
                    
                    // Carregar alunos da turma
                    $.ajax({
                        url: '/api/get_alunos_por_turma.php',
                        type: 'GET',
                        data: { turma_id: turmaId },
                        success: function(data) {
                            $('#aluno_id').empty().append('<option value="">Selecione o aluno</option>');
                            $.each(data, function(key, value) {
                                $('#aluno_id').append('<option value="'+value.id_aluno+'">'+value.nome_aluno+'</option>');
                            });
                            $('#aluno_id').prop('disabled', false);
                        }
                    });
                } else {
                    $('#disciplina_id').empty().append('<option value="">Selecione primeiro a turma</option>').prop('disabled', true);
                    $('#aluno_id').empty().append('<option value="">Selecione primeiro a turma</option>').prop('disabled', true);
                }
            });
        });
    </script>
</body>
</html>