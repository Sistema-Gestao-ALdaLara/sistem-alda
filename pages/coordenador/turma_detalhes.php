<?php
require_once '../../includes/common/permissoes.php';
verificarPermissao(['coordenador']);
require_once '../../process/verificar_sessao.php';
require_once '../../database/conexao.php';

// Verificar se o ID da turma foi fornecido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: turmas.php");
    exit();
}

$id_turma = $_GET['id'];

// Obter informações do coordenador e curso
$id_usuario = $_SESSION['id_usuario'];
$sql_coordenador = "SELECT c.curso_id_curso, cr.nome as nome_curso 
                FROM coordenador c
                JOIN curso cr ON c.curso_id_curso = cr.id_curso
                WHERE c.usuario_id_usuario = ?";
$stmt = $conn->prepare($sql_coordenador);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
$coordenador = $result->fetch_assoc();

if (!$coordenador) {
    die("Acesso negado ou coordenador não encontrado.");
}

$id_curso = $coordenador['curso_id_curso'];

// Processar formulário de edição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['editar_turma'])) {
        $nome_turma = $_POST['nome_turma'];
        $classe = $_POST['classe'];
        $professor_responsavel = $_POST['professor_responsavel'];
        
        // Atualizar turma
        $sql = "UPDATE turma SET nome = ?, classe = ? WHERE id_turma = ? AND curso_id_curso = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssii", $nome_turma, $classe, $id_turma, $id_curso);
        
        if ($stmt->execute()) {
            $_SESSION['mensagem'] = "Turma atualizada com sucesso!";
            $_SESSION['tipo_mensagem'] = "success";
        } else {
            $_SESSION['mensagem'] = "Erro ao atualizar turma: " . $conn->error;
            $_SESSION['tipo_mensagem'] = "danger";
        }
        
        // Atualizar professor responsável (se houver seleção)
        if (!empty($professor_responsavel)) {
            // Primeiro remove qualquer associação existente
            $sql = "DELETE FROM professor_tem_turma WHERE turma_id_turma = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id_turma);
            $stmt->execute();
            
            // Adiciona a nova associação
            $sql = "INSERT INTO professor_tem_turma (professor_id_professor, turma_id_turma) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $professor_responsavel, $id_turma);
            
            if (!$stmt->execute()) {
                $_SESSION['mensagem'] = "Turma atualizada, mas houve erro ao definir professor responsável: " . $conn->error;
                $_SESSION['tipo_mensagem'] = "warning";
            }
        }
        
        header("Location: turma_detalhes.php?id=" . $id_turma);
        exit();
    }
}

// Obter informações da turma
$sql_turma = "SELECT t.id_turma, t.nome, t.classe, c.nome as nome_curso
            FROM turma t
            JOIN curso c ON t.curso_id_curso = c.id_curso
            WHERE t.id_turma = ? AND t.curso_id_curso = ?";
$stmt = $conn->prepare($sql_turma);
$stmt->bind_param("ii", $id_turma, $id_curso);
$stmt->execute();
$result = $stmt->get_result();
$turma = $result->fetch_assoc();

if (!$turma) {
    die("Turma não encontrada ou você não tem permissão para acessá-la.");
}

// Obter professor responsável pela turma
$sql_professor_responsavel = "SELECT p.id_professor, u.nome
                            FROM professor_tem_turma pt
                            JOIN professor p ON pt.professor_id_professor = p.id_professor
                            JOIN usuario u ON p.usuario_id_usuario = u.id_usuario
                            WHERE pt.turma_id_turma = ?";
$stmt = $conn->prepare($sql_professor_responsavel);
$stmt->bind_param("i", $id_turma);
$stmt->execute();
$professor_responsavel = $stmt->get_result()->fetch_assoc();

// Obter lista de professores do curso para o dropdown
$sql_professores = "SELECT p.id_professor, u.nome
                FROM professor p
                JOIN usuario u ON p.usuario_id_usuario = u.id_usuario
                WHERE p.curso_id_curso = ? AND u.status = 'ativo'";
$stmt = $conn->prepare($sql_professores);
$stmt->bind_param("i", $id_curso);
$stmt->execute();
$professores = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Obter alunos da turma
$sql_alunos = "SELECT a.id_aluno, u.nome, u.email, u.bi_numero, 
                    m.ano_letivo, m.status_matricula,
                    a.nome_encarregado, a.contacto_encarregado
            FROM aluno a
            JOIN usuario u ON a.usuario_id_usuario = u.id_usuario
            JOIN matricula m ON m.aluno_id_aluno = a.id_aluno
            WHERE a.turma_id_turma = ?
            ORDER BY u.nome";
$stmt = $conn->prepare($sql_alunos);
$stmt->bind_param("i", $id_turma);
$stmt->execute();
$alunos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Obter disciplinas da turma (atualizada para usar professor_tem_disciplina)
$sql_disciplinas = "SELECT DISTINCT d.id_disciplina, d.nome, t.classe, 
                   GROUP_CONCAT(DISTINCT u.nome SEPARATOR ', ') as nome_professor
            FROM disciplina d
            LEFT JOIN professor_tem_disciplina ptd ON d.id_disciplina = ptd.disciplina_id_disciplina
            LEFT JOIN professor p ON ptd.professor_id_professor = p.id_professor
            LEFT JOIN usuario u ON p.usuario_id_usuario = u.id_usuario
            LEFT JOIN turma t ON d.curso_id_curso = t.curso_id_curso
            WHERE d.curso_id_curso = ? AND t.classe = ?
            GROUP BY d.id_disciplina
            ORDER BY d.nome";
$stmt = $conn->prepare($sql_disciplinas);
$stmt->bind_param("is", $id_curso, $turma['classe']);
$stmt->execute();
$disciplinas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Obter horário da turma
$sql_horario = "SELECT ca.*, d.nome as nome_disciplina, 
               u.nome as nome_professor, t.classe
            FROM cronograma_aula ca
            JOIN disciplina d ON ca.id_disciplina = d.id_disciplina
            JOIN turma t ON d.curso_id_curso = t.curso_id_curso
            JOIN professor p ON ca.id_professor = p.id_professor
            JOIN usuario u ON p.usuario_id_usuario = u.id_usuario
            WHERE ca.turma_id_turma = ?
            ORDER BY FIELD(ca.dia_semana, 'segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado'), 
            ca.horario_inicio";
$stmt = $conn->prepare($sql_horario);
$stmt->bind_param("i", $id_turma);
$stmt->execute();
$horario = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Obter estatísticas da turma
$sql_estatisticas = "SELECT 
                    COUNT(DISTINCT a.id_aluno) as total_alunos,
                    COUNT(DISTINCT CASE WHEN m.status_matricula = 'ativa' THEN a.id_aluno END) as alunos_ativos,
                    COUNT(DISTINCT d.id_disciplina) as total_disciplinas
                FROM turma t
                LEFT JOIN aluno a ON t.id_turma = a.turma_id_turma
                LEFT JOIN matricula m ON m.aluno_id_aluno = a.id_aluno
                LEFT JOIN disciplina d ON d.curso_id_curso = t.curso_id_curso
                WHERE t.id_turma = ?";
$stmt = $conn->prepare($sql_estatisticas);
$stmt->bind_param("i", $id_turma);
$stmt->execute();
$estatisticas = $stmt->get_result()->fetch_assoc();

$title = "Detalhes da Turma - " . htmlspecialchars($turma['nome']);
?>

<!DOCTYPE html>
<html lang="pt">

<?php require_once '../../includes/common/head.php'; ?>

<body>
    <?php require_once '../../includes/common/preloader.php'; ?>

    <div id="pcoded" class="pcoded">
        <div class="pcoded-overlay-box"></div>
        <div class="pcoded-container navbar-wrapper">

            <?php require_once '../../includes/coordenador/navbar.php'; ?>

            <div class="pcoded-main-container">
                <div class="pcoded-wrapper">
                    <?php require_once '../../includes/coordenador/sidebar.php'; ?>
                    
                    <div class="pcoded-content">
                        <div class="pcoded-inner-content">
                            <div class="main-body bg-img">
                                <div class="page-wrapper">

                                    <div class="page-body">
                                        <?php if (isset($_SESSION['mensagem'])): ?>
                                            <div class="alert alert-<?= $_SESSION['tipo_mensagem'] ?>">
                                                <?= $_SESSION['mensagem'] ?>
                                            </div>
                                            <?php unset($_SESSION['mensagem'], $_SESSION['tipo_mensagem']); ?>
                                        <?php endif; ?>

                                        <div class="card mb-4">
                                            <div class="card-header">
                                                <h5>Informações da Turma</h5>
                                                <div class="card-header-right">
                                                    <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalEditarTurma">
                                                        <i class="feather icon-edit"></i> Editar
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="card-block">
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="stat-card bg-c-blue">
                                                            <h6>Nome da Turma</h6>
                                                            <h4><?= htmlspecialchars($turma['nome']) ?></h4>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="stat-card bg-c-green">
                                                            <h6>Curso</h6>
                                                            <h4><?= htmlspecialchars($turma['nome_curso']) ?></h4>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="stat-card bg-c-yellow">
                                                            <h6>Classe</h6>
                                                            <h4><?= htmlspecialchars($turma['classe']) ?></h4>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="row mt-3">
                                                    <div class="col-md-4">
                                                        <div class="stat-card bg-c-pink">
                                                            <h6>Professor Responsável</h6>
                                                            <h4><?= $professor_responsavel ? htmlspecialchars($professor_responsavel['nome']) : 'Nenhum' ?></h4>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="stat-card bg-c-lite-green">
                                                            <h6>Alunos Ativos</h6>
                                                            <h4><?= $estatisticas['alunos_ativos'] ?> / <?= $estatisticas['total_alunos'] ?></h4>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="stat-card bg-c-orange">
                                                            <h6>Disciplinas</h6>
                                                            <h4><?= $estatisticas['total_disciplinas'] ?></h4>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Modal Editar Turma -->
                                        <div class="modal fade" id="modalEditarTurma" tabindex="-1" role="dialog">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Editar Turma</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <form method="POST">
                                                        <div class="modal-body">
                                                            <div class="form-group">
                                                                <label for="nome_turma">Nome da Turma</label>
                                                                <input type="text" class="form-control" id="nome_turma" name="nome_turma" 
                                                                       value="<?= htmlspecialchars($turma['nome']) ?>" required>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="classe">Classe</label>
                                                                <input type="text" class="form-control" id="classe" name="classe" 
                                                                       value="<?= htmlspecialchars($turma['classe']) ?>" required>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="professor_responsavel">Professor Responsável</label>
                                                                <select class="form-control" id="professor_responsavel" name="professor_responsavel">
                                                                    <option value="">-- Nenhum --</option>
                                                                    <?php foreach ($professores as $prof): ?>
                                                                        <option value="<?= $prof['id_professor'] ?>" 
                                                                            <?= ($professor_responsavel && $prof['id_professor'] == $professor_responsavel['id_professor']) ? 'selected' : '' ?>>
                                                                            <?= htmlspecialchars($prof['nome']) ?>
                                                                        </option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                                            <button type="submit" name="editar_turma" class="btn btn-primary">Salvar Alterações</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-6">
                                                <div class="card mb-4">
                                                    <div class="card-header">
                                                        <h5>Alunos Matriculados</h5>
                                                        <span class="badge badge-primary"><?= count($alunos) ?> alunos</span>
                                                    </div>
                                                    <div class="card-block">
                                                        <div class="table-responsive">
                                                            <table class="table table-hover">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Nome</th>
                                                                        <th>BI</th>
                                                                        <th>Status</th>
                                                                        <th>Ações</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php foreach ($alunos as $aluno): ?>
                                                                        <tr>
                                                                            <td><?= htmlspecialchars($aluno['nome']) ?></td>
                                                                            <td><?= htmlspecialchars($aluno['bi_numero']) ?></td>
                                                                            <td>
                                                                                <span class="badge badge-<?= $aluno['status_matricula'] == 'ativa' ? 'success' : 'warning' ?>">
                                                                                    <?= ucfirst($aluno['status_matricula']) ?>
                                                                                </span>
                                                                            </td>
                                                                            <td>
                                                                                <a href="aluno_detalhes.php?id=<?= $aluno['id_aluno'] ?>" class="btn btn-sm btn-primary">
                                                                                    <i class="feather icon-eye"></i>
                                                                                </a>
                                                                            </td>
                                                                        </tr>
                                                                    <?php endforeach; ?>
                                                                    <?php if (empty($alunos)): ?>
                                                                        <tr>
                                                                            <td colspan="4" class="text-center">Nenhum aluno matriculado nesta turma.</td>
                                                                        </tr>
                                                                    <?php endif; ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-lg-6">
                                                <div class="card mb-4">
                                                    <div class="card-header">
                                                        <h5>Disciplinas da Turma</h5>
                                                        <span class="badge badge-primary"><?= count($disciplinas) ?> disciplinas</span>
                                                    </div>
                                                    <div class="card-block">
                                                        <div class="table-responsive">
                                                            <table class="table table-hover">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Disciplina</th>
                                                                        <th>Classe</th>
                                                                        <th>Professor(es)</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php foreach ($disciplinas as $disciplina): ?>
                                                                        <tr>
                                                                            <td><?= htmlspecialchars($disciplina['nome']) ?></td>
                                                                            <td><?= htmlspecialchars($disciplina['classe']) ?></td>
                                                                            <td><?= $disciplina['nome_professor'] ? htmlspecialchars($disciplina['nome_professor']) : 'Nenhum' ?></td>
                                                                        </tr>
                                                                    <?php endforeach; ?>
                                                                    <?php if (empty($disciplinas)): ?>
                                                                        <tr>
                                                                            <td colspan="3" class="text-center">Nenhuma disciplina definida para esta turma.</td>
                                                                        </tr>
                                                                    <?php endif; ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card">
                                            <div class="card-header">
                                                <h5>Horário da Turma</h5>
                                            </div>
                                            <div class="card-block">
                                                <?php if (!empty($horario)): ?>
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered">
                                                            <thead>
                                                                <tr>
                                                                    <th>Dia</th>
                                                                    <th>Horário</th>
                                                                    <th>Disciplina</th>
                                                                    <th>Professor</th>
                                                                    <th>Sala</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($horario as $aula): ?>
                                                                    <tr>
                                                                        <td>
                                                                            <?php 
                                                                                $dias = [
                                                                                    'segunda' => 'Segunda',
                                                                                    'terca' => 'Terça',
                                                                                    'quarta' => 'Quarta',
                                                                                    'quinta' => 'Quinta',
                                                                                    'sexta' => 'Sexta',
                                                                                    'sabado' => 'Sábado'
                                                                                ];
                                                                                echo $dias[$aula['dia_semana']];
                                                                            ?>
                                                                        </td>
                                                                        <td><?= date('H:i', strtotime($aula['horario_inicio'])) ?> - <?= date('H:i', strtotime($aula['horario_fim'])) ?></td>
                                                                        <td><?= htmlspecialchars($aula['nome_disciplina']) ?> (<?= htmlspecialchars($aula['classe']) ?>)</td>
                                                                        <td><?= htmlspecialchars($aula['nome_professor']) ?></td>
                                                                        <td><?= htmlspecialchars($aula['sala']) ?></td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="alert alert-info">
                                                        Nenhum horário definido para esta turma.
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

    <?php require_once '../../includes/common/js_imports.php'; ?>
</body>
</html>