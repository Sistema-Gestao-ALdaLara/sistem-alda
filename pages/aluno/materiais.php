<?php
require_once '../../includes/common/permissoes.php';
verificarPermissao(['aluno']);
require_once '../../process/verificar_sessao.php';
require_once '../../database/conexao.php';

// Obter informações do aluno
$id_usuario = $_SESSION['id_usuario'];
$sql_aluno = "SELECT a.id_aluno, a.turma_id_turma, t.curso_id_curso, t.classe 
              FROM aluno a
              JOIN turma t ON a.turma_id_turma = t.id_turma
              WHERE a.usuario_id_usuario = ?";
$stmt = $conn->prepare($sql_aluno);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
$aluno = $result->fetch_assoc();

if (!$aluno) {
    die("Acesso negado ou aluno não encontrado.");
}

$id_aluno = $aluno['id_aluno'];
$id_turma = $aluno['turma_id_turma'];
$id_curso = $aluno['curso_id_curso'];
$classe = $aluno['classe'];

// Filtros (convertendo para inteiro para segurança)
$filtro_disciplina = isset($_GET['disciplina']) ? (int)$_GET['disciplina'] : 0;

// Consulta otimizada com filtro de disciplina no SQL
$sql_materiais = "SELECT DISTINCT m.*, d.nome as disciplina_nome, c.nome as curso_nome
                 FROM materiais_apoio m
                 LEFT JOIN disciplina d ON m.disciplina_id = d.id_disciplina
                 JOIN curso c ON m.curso_id = c.id_curso
                 LEFT JOIN material_destinatario md ON m.id_material = md.material_id
                 WHERE m.curso_id = ?
                 AND (
                     NOT EXISTS (
                         SELECT 1 FROM material_destinatario 
                         WHERE material_id = m.id_material
                     )
                     OR
                     (md.tipo_destino = 'curso' AND md.curso_id = ?)
                     OR
                     (md.tipo_destino = 'classe' AND md.classe = ?)
                     OR
                     (md.tipo_destino = 'turma' AND md.turma_id = ?)
                 )
                 AND (m.classe IS NULL OR m.classe = 'todas' OR m.classe = ?)
                 AND (m.disciplina_id = ? OR ? = 0) -- Filtro de disciplina integrado
                 ORDER BY m.data_upload DESC";

$stmt = $conn->prepare($sql_materiais);
$stmt->bind_param("iisisii", $id_curso, $id_curso, $classe, $id_turma, $classe, $filtro_disciplina, $filtro_disciplina);
$stmt->execute();
$materiais = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Consulta para disciplinas (mantém UNION para listar todas do curso + com materiais)
$sql_disciplinas = "SELECT d.id_disciplina, d.nome
                   FROM disciplina d
                   WHERE d.curso_id_curso = ?
                   UNION
                   SELECT DISTINCT d.id_disciplina, d.nome
                   FROM disciplina d
                   JOIN materiais_apoio m ON d.id_disciplina = m.disciplina_id
                   WHERE m.curso_id = ?
                   ORDER BY nome";

$stmt = $conn->prepare($sql_disciplinas);
$stmt->bind_param("ii", $id_curso, $id_curso);
$stmt->execute();
$disciplinas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$title = "Materiais de Apoio";
?>

<?php require_once '../../includes/common/head.php'; ?>

<body>
    <?php require_once '../../includes/common/preloader.php'; ?>

    <div id="pcoded" class="pcoded">
        <div class="pcoded-overlay-box"></div>
        <div class="pcoded-container navbar-wrapper">
            <?php require_once '../../includes/aluno/navbar.php'; ?>

            <div class="pcoded-main-container">
                <div class="pcoded-wrapper">
                    <?php require_once '../../includes/aluno/sidebar.php'; ?>
                    
                    <div class="pcoded-content">
                        <div class="pcoded-inner-content">
                            <div class="main-body bg-img">
                                <div class="page-wrapper">
                                    <div class="page-body">
                                        <div class="card card-table mb-4">
                                            <div class="card-header">
                                                <h5>Materiais de Apoio</h5>
                                            </div>
                                            <div class="card-block">
                                                <form method="GET" action="" class="row">
                                                    <div class="col-md-4 mb-2">
                                                        <select class="form-control" name="disciplina">
                                                            <option value="0">Todas as Disciplinas</option>
                                                            <?php foreach ($disciplinas as $disciplina): ?>
                                                                <option value="<?= $disciplina['id_disciplina'] ?>" <?= $filtro_disciplina == $disciplina['id_disciplina'] ? 'selected' : '' ?>>
                                                                    <?= htmlspecialchars($disciplina['nome']) ?>
                                                                </option>
                                                            <?php endforeach; ?>
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
                                                <h5>Materiais Disponíveis</h5>
                                            </div>
                                            <div class="card-block">
                                                <?php if (!empty($materiais)): ?>
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered table-striped">
                                                            <thead>
                                                                <tr>
                                                                    <th>Nome</th>
                                                                    <th>Disciplina</th>
                                                                    <th>Descrição</th>
                                                                    <th>Data</th>
                                                                    <th>Ações</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($materiais as $material): ?>
                                                                    <tr>
                                                                        <td><?= htmlspecialchars($material['nome']) ?></td>
                                                                        <td><?= $material['disciplina_nome'] ? htmlspecialchars($material['disciplina_nome']) : '<em>Geral</em>' ?></td>
                                                                        <td><?= htmlspecialchars($material['descricao']) ?></td>
                                                                        <td><?= date('d/m/Y H:i', strtotime($material['data_upload'])) ?></td>
                                                                        <td>
                                                                            <a href="../../<?= htmlspecialchars($material['caminho_arquivo']) ?>" 
                                                                               class="btn btn-primary btn-sm"
                                                                               download="<?= htmlspecialchars($material['nome']) ?>">
                                                                                <i class="feather icon-download"></i> Baixar
                                                                            </a>
                                                                        </td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="alert alert-info">
                                                        Nenhum material disponível no momento.
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