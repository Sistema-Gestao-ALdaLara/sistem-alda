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

    // Processar formulário de adição de horário
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['adicionar_horario'])) {
            $dia_semana = $_POST['dia_semana'];
            $horario_inicio = $_POST['horario_inicio'];
            $horario_fim = $_POST['horario_fim'];
            $sala = $_POST['sala'];
            $id_professor = $_POST['id_professor'];
            $turma_id_turma = $_POST['turma_id_turma'];
            $id_disciplina = $_POST['id_disciplina'];
            
            $sql = "INSERT INTO cronograma_aula (dia_semana, horario_inicio, horario_fim, sala, id_professor, turma_id_turma, id_disciplina)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssiii", $dia_semana, $horario_inicio, $horario_fim, $sala, $id_professor, $turma_id_turma, $id_disciplina);
            
            if ($stmt->execute()) {
                $_SESSION['mensagem'] = "Horário adicionado com sucesso!";
                $_SESSION['tipo_mensagem'] = "success";
            } else {
                $_SESSION['mensagem'] = "Erro ao adicionar horário: " . $conn->error;
                $_SESSION['tipo_mensagem'] = "danger";
            }
        }
        
        // Processar remoção de horário
        if (isset($_POST['remover_horario'])) {
            $id_cronograma = $_POST['id_cronograma'];
            
            $sql = "DELETE FROM cronograma_aula WHERE id_cronograma_aula = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id_cronograma);
            
            if ($stmt->execute()) {
                $_SESSION['mensagem'] = "Horário removido com sucesso!";
                $_SESSION['tipo_mensagem'] = "success";
            } else {
                $_SESSION['mensagem'] = "Erro ao remover horário: " . $conn->error;
                $_SESSION['tipo_mensagem'] = "danger";
            }
        }
    }

    // Obter turmas do curso
    $sql_turmas = "SELECT id_turma, nome FROM turma WHERE curso_id_curso = ?";
    $stmt = $conn->prepare($sql_turmas);
    $stmt->bind_param("i", $id_curso);
    $stmt->execute();
    $turmas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Obter disciplinas do curso
    $sql_disciplinas = "SELECT id_disciplina, nome FROM disciplina WHERE curso_id_curso = ?";
    $stmt = $conn->prepare($sql_disciplinas);
    $stmt->bind_param("i", $id_curso);
    $stmt->execute();
    $disciplinas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Obter professores do curso
    $sql_professores = "SELECT p.id_professor, u.nome 
                        FROM professor p
                        JOIN usuario u ON p.usuario_id_usuario = u.id_usuario
                        WHERE p.curso_id_curso = ?";
    $stmt = $conn->prepare($sql_professores);
    $stmt->bind_param("i", $id_curso);
    $stmt->execute();
    $professores = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Obter horários existentes
    $sql_horarios = "SELECT ca.*, t.nome as nome_turma, d.nome as nome_disciplina, u.nome as nome_professor
                    FROM cronograma_aula ca
                    JOIN turma t ON ca.turma_id_turma = t.id_turma
                    JOIN disciplina d ON ca.id_disciplina = d.id_disciplina
                    JOIN professor p ON ca.id_professor = p.id_professor
                    JOIN usuario u ON p.usuario_id_usuario = u.id_usuario
                    WHERE t.curso_id_curso = ?
                    ORDER BY FIELD(ca.dia_semana, 'segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado'), ca.horario_inicio";
    $stmt = $conn->prepare($sql_horarios);
    $stmt->bind_param("i", $id_curso);
    $stmt->execute();
    $horarios = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $title = "Definir Horários - Coordenador";
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
                                                <h5>Definir Horários - <?= htmlspecialchars($nome_curso) ?></h5>
                                            </div>
                                            <div class="card-block">
                                                <form method="POST" action="">
                                                    <div class="row">
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label for="dia_semana">Dia da Semana</label>
                                                                <select class="form-control" id="dia_semana" name="dia_semana" required>
                                                                    <option value="segunda">Segunda-feira</option>
                                                                    <option value="terca">Terça-feira</option>
                                                                    <option value="quarta">Quarta-feira</option>
                                                                    <option value="quinta">Quinta-feira</option>
                                                                    <option value="sexta">Sexta-feira</option>
                                                                    <option value="sabado">Sábado</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <div class="form-group">
                                                                <label for="horario_inicio">Hora Início</label>
                                                                <input type="time" class="form-control" id="horario_inicio" name="horario_inicio" required>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <div class="form-group">
                                                                <label for="horario_fim">Hora Fim</label>
                                                                <input type="time" class="form-control" id="horario_fim" name="horario_fim" required>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <div class="form-group">
                                                                <label for="sala">Sala</label>
                                                                <input type="text" class="form-control" id="sala" name="sala" required>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="turma_id_turma">Turma</label>
                                                                <select class="form-control" id="turma_id_turma" name="turma_id_turma" required>
                                                                    <?php foreach ($turmas as $turma): ?>
                                                                        <option value="<?= $turma['id_turma'] ?>"><?= htmlspecialchars($turma['nome']) ?></option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="id_disciplina">Disciplina</label>
                                                                <select class="form-control" id="id_disciplina" name="id_disciplina" required>
                                                                    <?php foreach ($disciplinas as $disciplina): ?>
                                                                        <option value="<?= $disciplina['id_disciplina'] ?>"><?= htmlspecialchars($disciplina['nome']) ?></option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="id_professor">Professor</label>
                                                                <select class="form-control" id="id_professor" name="id_professor" required>
                                                                    <?php foreach ($professores as $professor): ?>
                                                                        <option value="<?= $professor['id_professor'] ?>"><?= htmlspecialchars($professor['nome']) ?></option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <button type="submit" name="adicionar_horario" class="btn btn-primary">Adicionar Horário</button>
                                                </form>
                                            </div>
                                        </div>

                                        <div class="card card-table">
                                            <div class="card-header">
                                                <h5>Cronograma de Aulas</h5>
                                            </div>
                                            <div class="card-block">
                                                <div class="table-responsive">
                                                    <table class="table table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th>Dia</th>
                                                                <th>Horário</th>
                                                                <th>Disciplina</th>
                                                                <th>Professor</th>
                                                                <th>Turma</th>
                                                                <th>Sala</th>
                                                                <th>Ações</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($horarios as $horario): ?>
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
                                                                            echo $dias[$horario['dia_semana']];
                                                                        ?>
                                                                    </td>
                                                                    <td><?= substr($horario['horario_inicio'], 0, 5) ?> - <?= substr($horario['horario_fim'], 0, 5) ?></td>
                                                                    <td><?= htmlspecialchars($horario['nome_disciplina']) ?></td>
                                                                    <td><?= htmlspecialchars($horario['nome_professor']) ?></td>
                                                                    <td><?= htmlspecialchars($horario['nome_turma']) ?></td>
                                                                    <td><?= htmlspecialchars($horario['sala']) ?></td>
                                                                    <td>
                                                                        <form method="POST" style="display: inline;">
                                                                            <input type="hidden" name="id_cronograma" value="<?= $horario['id_cronograma_aula'] ?>">
                                                                            <button type="submit" name="remover_horario" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja remover este horário?')">
                                                                                <i class="feather icon-trash-2"></i> Remover
                                                                            </button>
                                                                        </form>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                            <?php if (empty($horarios)): ?>
                                                                <tr>
                                                                    <td colspan="7" class="text-center">Nenhum horário cadastrado ainda.</td>
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