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
    $id_coordenador = $coordenador['id_coordenador'];

    // Processar formulário de adição de disciplina
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['adicionar_disciplina'])) {
            $nome_disciplina = trim($_POST['nome_disciplina']);
            
            $sql = "INSERT INTO disciplina (nome, curso_id_curso) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $nome_disciplina, $id_curso);
            
            if ($stmt->execute()) {
                $_SESSION['mensagem'] = "Disciplina adicionada com sucesso!";
                $_SESSION['tipo_mensagem'] = "success";
            } else {
                $_SESSION['mensagem'] = "Erro ao adicionar disciplina: " . $conn->error;
                $_SESSION['tipo_mensagem'] = "danger";
            }
        }
        
        // Processar remoção de disciplina
        if (isset($_POST['remover_disciplina'])) {
            $id_disciplina = $_POST['id_disciplina'];
            
            $sql = "DELETE FROM disciplina WHERE id_disciplina = ? AND curso_id_curso = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $id_disciplina, $id_curso);
            
            if ($stmt->execute()) {
                $_SESSION['mensagem'] = "Disciplina removida com sucesso!";
                $_SESSION['tipo_mensagem'] = "success";
            } else {
                $_SESSION['mensagem'] = "Erro ao remover disciplina: " . $conn->error;
                $_SESSION['tipo_mensagem'] = "danger";
            }
        }
        
        // Processar atribuição de professor a disciplina
        if (isset($_POST['atribuir_professor'])) {
            $id_disciplina = $_POST['id_disciplina'];
            $id_professor = $_POST['id_professor'];
            
            // Verificar se já existe a associação
            $sql_check = "SELECT * FROM professor_tem_disciplina 
                          WHERE professor_id_professor = ? AND disciplina_id_disciplina = ?";
            $stmt = $conn->prepare($sql_check);
            $stmt->bind_param("ii", $id_professor, $id_disciplina);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows == 0) {
                $sql = "INSERT INTO professor_tem_disciplina (professor_id_professor, disciplina_id_disciplina)
                        VALUES (?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $id_professor, $id_disciplina);
                
                if ($stmt->execute()) {
                    $_SESSION['mensagem'] = "Professor atribuído com sucesso!";
                    $_SESSION['tipo_mensagem'] = "success";
                } else {
                    $_SESSION['mensagem'] = "Erro ao atribuir professor: " . $conn->error;
                    $_SESSION['tipo_mensagem'] = "danger";
                }
            } else {
                $_SESSION['mensagem'] = "Este professor já está atribuído a esta disciplina.";
                $_SESSION['tipo_mensagem'] = "warning";
            }
        }
        
        // Processar remoção de professor de disciplina
        if (isset($_POST['remover_professor_disciplina'])) {
            $id_disciplina = $_POST['id_disciplina'];
            $id_professor = $_POST['id_professor'];
            
            $sql = "DELETE FROM professor_tem_disciplina 
                    WHERE professor_id_professor = ? AND disciplina_id_disciplina = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $id_professor, $id_disciplina);
            
            if ($stmt->execute()) {
                $_SESSION['mensagem'] = "Professor removido da disciplina com sucesso!";
                $_SESSION['tipo_mensagem'] = "success";
            } else {
                $_SESSION['mensagem'] = "Erro ao remover professor da disciplina: " . $conn->error;
                $_SESSION['tipo_mensagem'] = "danger";
            }
        }
    }

    // Obter disciplinas do curso
    $sql_disciplinas = "SELECT d.id_disciplina, d.nome 
                        FROM disciplina d
                        WHERE d.curso_id_curso = ?
                        ORDER BY d.nome";
    $stmt = $conn->prepare($sql_disciplinas);
    $stmt->bind_param("i", $id_curso);
    $stmt->execute();
    $disciplinas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Obter professores do curso
    $sql_professores = "SELECT p.id_professor, u.nome 
                        FROM professor p
                        JOIN usuario u ON p.usuario_id_usuario = u.id_usuario
                        WHERE p.curso_id_curso = ?
                        ORDER BY u.nome";
    $stmt = $conn->prepare($sql_professores);
    $stmt->bind_param("i", $id_curso);
    $stmt->execute();
    $professores = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Obter associações de professores a disciplinas
    $sql_professores_disciplinas = "SELECT pd.professor_id_professor, pd.disciplina_id_disciplina, 
                                           u.nome as nome_professor, d.nome as nome_disciplina
                                    FROM professor_tem_disciplina pd
                                    JOIN professor p ON pd.professor_id_professor = p.id_professor
                                    JOIN usuario u ON p.usuario_id_usuario = u.id_usuario
                                    JOIN disciplina d ON pd.disciplina_id_disciplina = d.id_disciplina
                                    WHERE d.curso_id_curso = ?
                                    ORDER BY d.nome, u.nome";
    $stmt = $conn->prepare($sql_professores_disciplinas);
    $stmt->bind_param("i", $id_curso);
    $stmt->execute();
    $professores_disciplinas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $title = "Gerenciar Disciplinas - Coordenador";
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

            <!--sidebar-->
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

                                        <div class="card mb-4 card-table">
                                            <div class="card-header">
                                                <h5>Adicionar Nova Disciplina - <?= htmlspecialchars($nome_curso) ?></h5>
                                            </div>
                                            <div class="card-block">
                                                <form method="POST" action="">
                                                    <div class="row">
                                                        <div class="col-md-8">
                                                            <div class="form-group">
                                                                <label for="nome_disciplina">Nome da Disciplina</label>
                                                                <input type="text" class="form-control" id="nome_disciplina" name="nome_disciplina" required>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group" style="margin-top: 30px;">
                                                                <button type="submit" name="adicionar_disciplina" class="btn btn-primary">
                                                                    Adicionar Disciplina
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>

                                        <div class="card mb-4 card-table">
                                            <div class="card-header">
                                                <h5>Disciplinas do Curso</h5>
                                            </div>
                                            <div class="card-block">
                                                <div class="table-responsive">
                                                    <table class="table table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th>Disciplina</th>
                                                                <th>Ações</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($disciplinas as $disciplina): ?>
                                                                <tr>
                                                                    <td><?= htmlspecialchars($disciplina['nome']) ?></td>
                                                                    <td>
                                                                        <form method="POST" style="display: inline;">
                                                                            <input type="hidden" name="id_disciplina" value="<?= $disciplina['id_disciplina'] ?>">
                                                                            <button type="submit" name="remover_disciplina" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja remover esta disciplina? Todas as associações com professores serão perdidas.')">
                                                                                <i class="feather icon-trash-2"></i> Remover
                                                                            </button>
                                                                        </form>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                            <?php if (empty($disciplinas)): ?>
                                                                <tr>
                                                                    <td colspan="2" class="text-center">Nenhuma disciplina cadastrada ainda.</td>
                                                                </tr>
                                                            <?php endif; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card mb-4 card-table">
                                            <div class="card-header">
                                                <h5>Atribuir Professor a Disciplina</h5>
                                            </div>
                                            <div class="card-block">
                                                <form method="POST" action="">
                                                    <div class="row">
                                                        <div class="col-md-5">
                                                            <div class="form-group">
                                                                <label for="id_disciplina">Disciplina</label>
                                                                <select class="form-control" id="id_disciplina" name="id_disciplina" required>
                                                                    <?php foreach ($disciplinas as $disciplina): ?>
                                                                        <option value="<?= $disciplina['id_disciplina'] ?>"><?= htmlspecialchars($disciplina['nome']) ?></option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-5">
                                                            <div class="form-group">
                                                                <label for="id_professor">Professor</label>
                                                                <select class="form-control" id="id_professor" name="id_professor" required>
                                                                    <?php foreach ($professores as $professor): ?>
                                                                        <option value="<?= $professor['id_professor'] ?>"><?= htmlspecialchars($professor['nome']) ?></option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <div class="form-group" style="margin-top: 30px;">
                                                                <button type="submit" name="atribuir_professor" class="btn btn-primary">
                                                                    Atribuir
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>

                                        <div class="card card-table">
                                            <div class="card-header">
                                                <h5>Professores por Disciplina</h5>
                                            </div>
                                            <div class="card-block">
                                                <div class="table-responsive">
                                                    <table class="table table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th>Disciplina</th>
                                                                <th>Professor</th>
                                                                <th>Ações</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($professores_disciplinas as $assoc): ?>
                                                                <tr>
                                                                    <td><?= htmlspecialchars($assoc['nome_disciplina']) ?></td>
                                                                    <td><?= htmlspecialchars($assoc['nome_professor']) ?></td>
                                                                    <td>
                                                                        <form method="POST" style="display: inline;">
                                                                            <input type="hidden" name="id_disciplina" value="<?= $assoc['disciplina_id_disciplina'] ?>">
                                                                            <input type="hidden" name="id_professor" value="<?= $assoc['professor_id_professor'] ?>">
                                                                            <button type="submit" name="remover_professor_disciplina" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja remover este professor desta disciplina?')">
                                                                                <i class="feather icon-trash-2"></i> Remover
                                                                            </button>
                                                                        </form>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                            <?php if (empty($professores_disciplinas)): ?>
                                                                <tr>
                                                                    <td colspan="3" class="text-center">Nenhum professor atribuído a disciplinas ainda.</td>
                                                                </tr>
                                                            <?php endif; ?>
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
</body>
</html>