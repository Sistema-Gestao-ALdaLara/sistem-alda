<?php
require_once '../../includes/common/permissoes.php';
verificarPermissao(['professor']);
require_once '../../process/verificar_sessao.php';
require_once '../../database/conexao.php';

// Obter informações do professor
$id_usuario = $_SESSION['id_usuario'];
$sql_professor = "SELECT p.id_professor, c.nome as nome_curso, c.id_curso 
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
$id_curso = $professor['id_curso'];

// Filtros
$filtro_disciplina = isset($_GET['disciplina']) ? $_GET['disciplina'] : '';
$filtro_classe = isset($_GET['classe']) ? $_GET['classe'] : 'todas';

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

// Obter materiais do professor
$sql_materiais = "SELECT m.*, d.nome as disciplina_nome
                 FROM materiais_apoio m
                 LEFT JOIN disciplina d ON m.disciplina_id = d.id_disciplina
                 WHERE m.usuario_id_upload = ?
                 AND m.curso_id = ?";

$params = [$id_usuario, $id_curso];
$types = "ii";

// Aplicar filtros
if (!empty($filtro_disciplina)) {
    $sql_materiais .= " AND (m.disciplina_id = ? OR m.disciplina_id IS NULL)";
    $params[] = $filtro_disciplina;
    $types .= "i";
}

if ($filtro_classe != 'todas') {
    $sql_materiais .= " AND (m.classe = ? OR m.classe IS NULL OR m.classe = 'todas')";
    $params[] = $filtro_classe;
    $types .= "s";
}

$sql_materiais .= " ORDER BY m.data_upload DESC";

$stmt = $conn->prepare($sql_materiais);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$materiais = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Obter destinatários para cada material
foreach ($materiais as &$material) {
    $sql_destinatarios = "SELECT md.*, t.nome as turma_nome, c.nome as curso_nome
                         FROM material_destinatario md
                         LEFT JOIN turma t ON md.turma_id = t.id_turma
                         LEFT JOIN curso c ON md.curso_id = c.id_curso
                         WHERE md.material_id = ?";
    $stmt = $conn->prepare($sql_destinatarios);
    $stmt->bind_param("i", $material['id_material']);
    $stmt->execute();
    $material['destinatarios'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

$title = "Materiais de Apoio - " . htmlspecialchars($nome_curso);
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
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h5>Materiais de Apoio - <?= htmlspecialchars($nome_curso) ?></h5>
                                                    <a href="enviar_material.php" class="btn btn-primary btn-sm">
                                                        <i class="feather icon-plus"></i> Novo Material
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="card-block">
                                                <form method="GET" action="" class="row">
                                                    <div class="col-md-4 mb-2">
                                                        <select class="form-control" name="disciplina">
                                                            <option value="">Todas as Disciplinas</option>
                                                            <?php foreach ($disciplinas as $disciplina): ?>
                                                                <option value="<?= $disciplina['id_disciplina'] ?>" <?= $filtro_disciplina == $disciplina['id_disciplina'] ? 'selected' : '' ?>>
                                                                    <?= htmlspecialchars($disciplina['nome']) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4 mb-2">
                                                        <select class="form-control" name="classe">
                                                            <option value="todas">Todas as Classes</option>
                                                            <option value="10ª" <?= $filtro_classe == '10ª' ? 'selected' : '' ?>>10ª Classe</option>
                                                            <option value="11ª" <?= $filtro_classe == '11ª' ? 'selected' : '' ?>>11ª Classe</option>
                                                            <option value="12ª" <?= $filtro_classe == '12ª' ? 'selected' : '' ?>>12ª Classe</option>
                                                            <option value="13ª" <?= $filtro_classe == '13ª' ? 'selected' : '' ?>>13ª Classe</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4 mb-2">
                                                        <button type="submit" class="btn btn-primary">
                                                            <i class="feather icon-filter"></i> Filtrar
                                                        </button>
                                                        <a href="materiais.php" class="btn btn-secondary">
                                                            <i class="feather icon-refresh-ccw"></i> Limpar
                                                        </a>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>

                                        <div class="card card-table">
                                            <div class="card-header">
                                                <h5>Meus Materiais</h5>
                                            </div>
                                            <div class="card-block">
                                                <?php if (!empty($materiais)): ?>
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered table-striped">
                                                            <thead>
                                                                <tr>
                                                                    <th>Nome</th>
                                                                    <th>Disciplina</th>
                                                                    <th>Classe</th>
                                                                    <th>Destinatários</th>
                                                                    <th>Data</th>
                                                                    <th>Ações</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($materiais as $material): ?>
                                                                    <tr>
                                                                        <td><?= htmlspecialchars($material['nome']) ?></td>
                                                                        <td><?= $material['disciplina_nome'] ? htmlspecialchars($material['disciplina_nome']) : '<em>Geral</em>' ?></td>
                                                                        <td><?= $material['classe'] ? htmlspecialchars($material['classe']) : 'Todas' ?></td>
                                                                        <td>
                                                                            <?php 
                                                                                $destinos = [];
                                                                                foreach ($material['destinatarios'] as $dest) {
                                                                                    if ($dest['tipo_destino'] == 'turma') {
                                                                                        $destinos[] = $dest['turma_nome'];
                                                                                    } elseif ($dest['tipo_destino'] == 'curso') {
                                                                                        $destinos[] = 'Todo o curso';
                                                                                    } elseif ($dest['tipo_destino'] == 'classe') {
                                                                                        $destinos[] = $dest['classe'] . ' Classe';
                                                                                    }
                                                                                }
                                                                                echo implode(', ', array_unique($destinos));
                                                                            ?>
                                                                        </td>
                                                                        <td><?= date('d/m/Y H:i', strtotime($material['data_upload'])) ?></td>
                                                                        <td>
                                                                            <a href="../../<?= htmlspecialchars($material['caminho_arquivo']) ?>" 
                                                                               class="btn btn-primary btn-sm"
                                                                               download="<?= htmlspecialchars($material['nome']) ?>">
                                                                                <i class="feather icon-download"></i> Baixar
                                                                            </a>
                                                                            <a href="editar_material.php?id=<?= $material['id_material'] ?>" 
                                                                               class="btn btn-warning btn-sm">
                                                                                <i class="feather icon-edit"></i> Editar
                                                                            </a>
                                                                            <button onclick="confirmarExclusao(<?= $material['id_material'] ?>)" 
                                                                                    class="btn btn-danger btn-sm">
                                                                                <i class="feather icon-trash"></i> Excluir
                                                                            </button>
                                                                        </td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="alert alert-info">
                                                        Nenhum material encontrado com os filtros selecionados.
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
        function confirmarExclusao(id) {
            if (confirm('Tem certeza que deseja excluir este material? Esta ação não pode ser desfeita.')) {
                window.location.href = 'excluir_material.php?id=' + id;
            }
        }
    </script>
</body>
</html>