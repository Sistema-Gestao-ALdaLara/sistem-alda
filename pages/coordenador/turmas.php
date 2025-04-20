<?php
require_once '../../includes/common/permissoes.php';
verificarPermissao(['coordenador']);
require_once '../../process/verificar_sessao.php';
require_once '../../database/conexao.php';

// Obter o ID do coordenador e do curso que ele coordena
$id_usuario = $_SESSION['id_usuario'];
$sql_coordenador = "SELECT c.id_coordenador, c.curso_id_curso, cr.nome as nome_curso 
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
$nome_curso = $coordenador['nome_curso'];

// Processar formulário de adição de turma
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adicionar_turma'])) {
    $nome_turma = trim($_POST['nome_turma']);
    $classe = trim($_POST['classe']);
    
    // Validar entrada
    if (empty($nome_turma)) {
        $_SESSION['mensagem'] = "O nome da turma é obrigatório!";
        $_SESSION['tipo_mensagem'] = "danger";
    } elseif (empty($classe)) {
        $_SESSION['mensagem'] = "A classe é obrigatória!";
        $_SESSION['tipo_mensagem'] = "danger";
    } else {
        $sql = "INSERT INTO turma (nome, classe, curso_id_curso) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $nome_turma, $classe, $id_curso);
        
        if ($stmt->execute()) {
            $_SESSION['mensagem'] = "Turma adicionada com sucesso!";
            $_SESSION['tipo_mensagem'] = "success";
            header("Location: turmas.php");
            exit();
        } else {
            $_SESSION['mensagem'] = "Erro ao adicionar turma: " . $conn->error;
            $_SESSION['tipo_mensagem'] = "danger";
        }
    }
}

// Processar remoção de turma
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remover_turma'])) {
    $id_turma = $_POST['id_turma'];
    
    // Verificar se a turma tem alunos matriculados
    $sql_check = "SELECT COUNT(*) as total FROM aluno WHERE turma_id_turma = ?";
    $stmt = $conn->prepare($sql_check);
    $stmt->bind_param("i", $id_turma);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['total'] > 0) {
        $_SESSION['mensagem'] = "Não é possível remover turma com alunos matriculados!";
        $_SESSION['tipo_mensagem'] = "danger";
    } else {
        // Verificar se a turma pertence ao curso do coordenador
        $sql_check = "SELECT id_turma FROM turma WHERE id_turma = ? AND curso_id_curso = ?";
        $stmt = $conn->prepare($sql_check);
        $stmt->bind_param("ii", $id_turma, $id_curso);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $_SESSION['mensagem'] = "Turma não encontrada ou você não tem permissão para removê-la!";
            $_SESSION['tipo_mensagem'] = "danger";
        } else {
            $sql = "DELETE FROM turma WHERE id_turma = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id_turma);
            
            if ($stmt->execute()) {
                $_SESSION['mensagem'] = "Turma removida com sucesso!";
                $_SESSION['tipo_mensagem'] = "success";
                header("Location: turmas.php");
                exit();
            } else {
                $_SESSION['mensagem'] = "Erro ao remover turma: " . $conn->error;
                $_SESSION['tipo_mensagem'] = "danger";
            }
        }
    }
}

// Obter turmas do curso com mais informações
$sql_turmas = "SELECT t.id_turma, t.nome, t.classe,
              (SELECT COUNT(*) FROM aluno a WHERE a.turma_id_turma = t.id_turma) as total_alunos,
              (SELECT COUNT(*) FROM matricula m 
               JOIN aluno a ON m.aluno_id_aluno = a.id_aluno 
               WHERE a.turma_id_turma = t.id_turma AND m.status_matricula = 'ativa') as alunos_ativos,
              (SELECT u.nome FROM professor_tem_turma pt
               JOIN professor p ON pt.professor_id_professor = p.id_professor
               JOIN usuario u ON p.usuario_id_usuario = u.id_usuario
               WHERE pt.turma_id_turma = t.id_turma LIMIT 1) as professor_responsavel
              FROM turma t
              WHERE t.curso_id_curso = ?
              ORDER BY t.classe, t.nome";
$stmt = $conn->prepare($sql_turmas);
$stmt->bind_param("i", $id_curso);
$stmt->execute();
$turmas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Obter lista de classes disponíveis para o dropdown
$sql_classes = "SELECT DISTINCT classe FROM turma WHERE curso_id_curso = ? ORDER BY classe";
$stmt = $conn->prepare($sql_classes);
$stmt->bind_param("i", $id_curso);
$stmt->execute();
$classes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$title = "Gerenciar Turmas - " . htmlspecialchars($nome_curso);
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
                                                <h5>Adicionar Nova Turma</h5>
                                            </div>
                                            <div class="card-block">
                                                <form method="POST" action="">
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="nome_turma">Nome da Turma *</label>
                                                                <input type="text" class="form-control" id="nome_turma" name="nome_turma" required>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="classe">Classe *</label>
                                                                <input type="text" class="form-control" id="classe" name="classe" 
                                                                       list="lista-classes" required>
                                                                <datalist id="lista-classes">
                                                                    <?php foreach ($classes as $classe): ?>
                                                                        <option value="<?= htmlspecialchars($classe['classe']) ?>">
                                                                    <?php endforeach; ?>
                                                                </datalist>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4 align-self-end">
                                                            <button type="submit" name="adicionar_turma" class="btn btn-primary">
                                                                <i class="feather icon-plus"></i> Adicionar Turma
                                                            </button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>

                                        <div class="card">
                                            <div class="card-header">
                                                <h5>Turmas do Curso - <?= htmlspecialchars($nome_curso) ?></h5>
                                                <span class="badge badge-primary"><?= count($turmas) ?> turmas</span>
                                            </div>
                                            <div class="card-block">
                                                <?php if (!empty($turmas)): ?>
                                                    <div class="table-responsive">
                                                        <table class="table table-hover">
                                                            <thead class="bg-c-blue text-white">
                                                                <tr>
                                                                    <th>Turma</th>
                                                                    <th>Classe</th>
                                                                    <th>Alunos</th>
                                                                    <th>Professor</th>
                                                                    <th>Ações</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($turmas as $turma): ?>
                                                                    <tr>
                                                                        <td>
                                                                            <strong><?= htmlspecialchars($turma['nome']) ?></strong>
                                                                        </td>
                                                                        <td><?= htmlspecialchars($turma['classe']) ?></td>
                                                                        <td>
                                                                            <span class="badge badge-success"><?= $turma['alunos_ativos'] ?> ativos</span>
                                                                            <span class="badge badge-secondary"><?= $turma['total_alunos'] ?> total</span>
                                                                        </td>
                                                                        <td>
                                                                            <?= $turma['professor_responsavel'] ? htmlspecialchars($turma['professor_responsavel']) : '<span class="text-muted">Nenhum</span>' ?>
                                                                        </td>
                                                                        <td>
                                                                            <div class="btn-group btn-group-sm">
                                                                                <a href="turma_detalhes.php?id=<?= $turma['id_turma'] ?>" class="btn btn-info">
                                                                                    <i class="feather icon-eye"></i>
                                                                                </a>
                                                                                <form method="POST" class="d-inline">
                                                                                    <input type="hidden" name="id_turma" value="<?= $turma['id_turma'] ?>">
                                                                                    <button type="submit" name="remover_turma" class="btn btn-danger" 
                                                                                            onclick="return confirm('Tem certeza que deseja remover a turma <?= htmlspecialchars(addslashes($turma['nome'])) ?>?')">
                                                                                        <i class="feather icon-trash-2"></i>
                                                                                    </button>
                                                                                </form>
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="alert alert-info">
                                                        Nenhuma turma cadastrada para este curso ainda.
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
    
    <script>
        // Script para confirmar exclusão
        document.addEventListener('DOMContentLoaded', function() {
            const deleteButtons = document.querySelectorAll('button[name="remover_turma"]');
            
            deleteButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    const turmaNome = this.closest('tr').querySelector('td:first-child strong').textContent;
                    if (!confirm(`Tem certeza que deseja remover a turma "${turmaNome}"?`)) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
</body>
</html>