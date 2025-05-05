<?php
require_once '../../includes/common/permissoes.php';
verificarPermissao(['professor']);
require_once '../../process/verificar_sessao.php';
require_once '../../database/conexao.php';

// Obter informações do professor
$id_usuario = $_SESSION['id_usuario'];
$sql_professor = "SELECT p.id_professor, c.nome as nome_curso 
                 FROM professor p
                 JOIN curso c ON p.curso_id_curso = c.id_curso
                 WHERE p.usuario_id_usuario = ?";
$stmt = $conn->prepare($sql_professor);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
$professor = $result->fetch_assoc();

if (!$professor) {
    die("Acesso negado ou professor não encontrado.");
}

$id_professor = $professor['id_professor'];
$nome_curso = $professor['nome_curso'];

// Filtros
$filtro_disciplina = isset($_GET['disciplina']) ? $_GET['disciplina'] : '';
$filtro_status = isset($_GET['status']) ? $_GET['status'] : 'todos';
$filtro_ano = isset($_GET['ano']) ? $_GET['ano'] : date('Y');
$filtro_trimestre = isset($_GET['trimestre']) ? $_GET['trimestre'] : 'todos';

// Obter disciplinas do professor
$sql_disciplinas = "SELECT d.id_disciplina, d.nome
                   FROM disciplina d
                   JOIN professor_tem_disciplina ptd ON d.id_disciplina = ptd.disciplina_id_disciplina
                   WHERE ptd.professor_id_professor = ?
                   ORDER BY d.nome";
$stmt = $conn->prepare($sql_disciplinas);
$stmt->bind_param("i", $id_professor);
$stmt->execute();
$disciplinas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Obter anos disponíveis
$sql_anos = "SELECT DISTINCT ano_letivo FROM plano_ensino 
            WHERE id_professor = ?
            ORDER BY ano_letivo DESC";
$stmt = $conn->prepare($sql_anos);
$stmt->bind_param("i", $id_professor);
$stmt->execute();
$anos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Obter planos de ensino do professor
$sql_planos = "SELECT pe.*, d.nome as disciplina_nome
              FROM plano_ensino pe
              JOIN disciplina d ON pe.id_disciplina = d.id_disciplina
              WHERE pe.id_professor = ?
              AND pe.ano_letivo = ?";

$params = [$id_professor, $filtro_ano];
$types = "ii";

// Aplicar filtros
if (!empty($filtro_disciplina)) {
    $sql_planos .= " AND pe.id_disciplina = ?";
    $params[] = $filtro_disciplina;
    $types .= "i";
}

if ($filtro_status != 'todos') {
    $sql_planos .= " AND pe.status = ?";
    $params[] = $filtro_status;
    $types .= "s";
}

if ($filtro_trimestre != 'todos') {
    $sql_planos .= " AND pe.trimestre = ?";
    $params[] = $filtro_trimestre;
    $types .= "s";
}

$sql_planos .= " ORDER BY pe.ano_letivo DESC, pe.trimestre DESC, d.nome ASC";

$stmt = $conn->prepare($sql_planos);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$planos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$title = "Meus Planos de Ensino - " . htmlspecialchars($nome_curso);
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

                                    <div class="page-body">
                                        <div class="card card-table mb-4">
                                            <div class="card-header">
                                                <h5>Meus Planos de Ensino - <?= htmlspecialchars($nome_curso) ?></h5>
                                            </div>
                                            <div class="card-block">
                                                <form method="GET" action="" class="row">
                                                    <div class="col-md-3 mb-2">
                                                        <select class="form-control" name="ano">
                                                            <option value="todos">Todos os Anos</option>
                                                            <?php foreach ($anos as $ano): ?>
                                                                <option value="<?= $ano['ano_letivo'] ?>" <?= $filtro_ano == $ano['ano_letivo'] ? 'selected' : '' ?>>
                                                                    <?= $ano['ano_letivo'] ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-3 mb-2">
                                                        <select class="form-control" name="trimestre">
                                                            <option value="todos">Todos os Trimestres</option>
                                                            <option value="1" <?= $filtro_trimestre == '1' ? 'selected' : '' ?>>1º Trimestre</option>
                                                            <option value="2" <?= $filtro_trimestre == '2' ? 'selected' : '' ?>>2º Trimestre</option>
                                                            <option value="3" <?= $filtro_trimestre == '3' ? 'selected' : '' ?>>3º Trimestre</option>
                                                            <option value="anual" <?= $filtro_trimestre == 'anual' ? 'selected' : '' ?>>Anual</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-3 mb-2">
                                                        <select class="form-control" name="disciplina">
                                                            <option value="">Todas as Disciplinas</option>
                                                            <?php foreach ($disciplinas as $disciplina): ?>
                                                                <option value="<?= $disciplina['id_disciplina'] ?>" <?= $filtro_disciplina == $disciplina['id_disciplina'] ? 'selected' : '' ?>>
                                                                    <?= htmlspecialchars($disciplina['nome']) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-3 mb-2">
                                                        <select class="form-control" name="status">
                                                            <option value="todos">Todos os Status</option>
                                                            <option value="rascunho" <?= $filtro_status == 'rascunho' ? 'selected' : '' ?>>Rascunho</option>
                                                            <option value="submetido" <?= $filtro_status == 'submetido' ? 'selected' : '' ?>>Submetido</option>
                                                            <option value="aprovado" <?= $filtro_status == 'aprovado' ? 'selected' : '' ?>>Aprovado</option>
                                                            <option value="rejeitado" <?= $filtro_status == 'rejeitado' ? 'selected' : '' ?>>Rejeitado</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <button type="submit" class="btn btn-primary">
                                                            <i class="feather icon-filter"></i> Filtrar
                                                        </button>
                                                        <a href="meus_planos.php" class="btn btn-secondary">
                                                            <i class="feather icon-refresh-ccw"></i> Limpar
                                                        </a>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>

                                        <div class="card card-table">
                                            <div class="card-header">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h5>Lista de Planos de Ensino</h5>
                                                </div>
                                            </div>
                                            <div class="card-block">
                                                <?php if (!empty($planos)): ?>
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered table-striped">
                                                            <thead>
                                                                <tr>
                                                                    <th>Título</th>
                                                                    <th>Disciplina</th>
                                                                    <th>Ano</th>
                                                                    <th>Trimestre</th>
                                                                    <th>Status</th>
                                                                    <th>Última Atualização</th>
                                                                    <th>Ações</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($planos as $plano): ?>
                                                                    <tr>
                                                                        <td><?= htmlspecialchars($plano['titulo']) ?></td>
                                                                        <td><?= htmlspecialchars($plano['disciplina_nome']) ?></td>
                                                                        <td><?= $plano['ano_letivo'] ?></td>
                                                                        <td><?= $plano['trimestre'] == 'anual' ? 'Anual' : $plano['trimestre'] . 'º' ?></td>
                                                                        <td>
                                                                            <?php 
                                                                                $badge_class = [
                                                                                    'rascunho' => 'warning',
                                                                                    'submetido' => 'info',
                                                                                    'aprovado' => 'success',
                                                                                    'rejeitado' => 'danger'
                                                                                ][$plano['status']];
                                                                            ?>
                                                                            <span class="badge badge-<?= $badge_class ?>">
                                                                                <?= ucfirst($plano['status']) ?>
                                                                            </span>
                                                                        </td>
                                                                        <td><?= date('d/m/Y H:i', strtotime($plano['status'] == 'aprovado' ? $plano['data_aprovacao'] : $plano['data_submissao'])) ?></td>
                                                                        <td>
                                                                            <a href="visualizar_plano.php?id=<?= $plano['id_plano'] ?>" class="btn btn-info btn-sm">
                                                                                <i class="feather icon-eye"></i> Visualizar
                                                                            </a>
                                                                            <?php if ($plano['caminho_arquivo']): ?>
                                                                                <a href="../../uploads/<?= htmlspecialchars($plano['caminho_arquivo']) ?>" 
                                                                                   class="btn btn-primary btn-sm" download>
                                                                                    <i class="feather icon-download"></i> Baixar
                                                                                </a>
                                                                            <?php endif; ?>
                                                                            <?php if ($plano['status'] == 'rascunho' || $plano['status'] == 'rejeitado'): ?>
                                                                                <a href="editar_plano.php?id=<?= $plano['id_plano'] ?>" class="btn btn-warning btn-sm">
                                                                                    <i class="feather icon-edit"></i> Editar
                                                                                </a>
                                                                            <?php endif; ?>
                                                                            <?php if ($plano['status'] == 'rascunho'): ?>
                                                                                <button class="btn btn-success btn-sm" onclick="submeterPlano(<?= $plano['id_plano'] ?>)">
                                                                                    <i class="feather icon-send"></i> Submeter
                                                                                </button>
                                                                            <?php endif; ?>
                                                                        </td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="alert alert-info">
                                                        Nenhum plano de ensino encontrado com os filtros selecionados.
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
        function submeterPlano(id) {
            if (confirm('Deseja submeter este plano para aprovação? Após a submissão, não será possível editá-lo.')) {
                window.location.href = 'submeter_plano.php?id=' + id;
            }
        }
    </script>
</body>
</html>