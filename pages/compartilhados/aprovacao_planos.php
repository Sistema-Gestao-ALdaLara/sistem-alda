<?php
require_once '../../includes/common/permissoes.php';
verificarPermissao(['diretor_geral', 'diretor_pedagogico']);
require_once '../../process/verificar_sessao.php';
require_once '../../database/conexao.php';

$title = "Aprovação de Planos de Ensino";

// Filtros
$status = isset($_GET['status']) ? $_GET['status'] : 'submetido';
$id_curso = isset($_GET['id_curso']) ? intval($_GET['id_curso']) : null;
$ano_letivo = isset($_GET['ano_letivo']) ? intval($_GET['ano_letivo']) : date('Y');
$trimestre = isset($_GET['trimestre']) ? $_GET['trimestre'] : null;

// Obter planos submetidos para aprovação
$query = "SELECT pe.id_plano, pe.titulo, pe.ano_letivo, pe.trimestre, pe.status,
          d.nome AS disciplina, c.nome AS curso, u.nome AS professor,
          pe.data_submissao, pe.data_aprovacao, u2.nome AS diretor_aprovador,
          pe.caminho_arquivo, pe.conteudo_programatico, pe.metodologia,
          pe.criterios_avaliacao, pe.bibliografia, pe.motivo_rejeicao
          FROM plano_ensino pe
          JOIN disciplina d ON pe.id_disciplina = d.id_disciplina
          JOIN curso c ON d.curso_id_curso = c.id_curso
          LEFT JOIN professor p ON pe.id_professor = p.id_professor
          LEFT JOIN usuario u ON p.usuario_id_usuario = u.id_usuario
          LEFT JOIN usuario u2 ON pe.id_diretor_aprovador = u2.id_usuario
          WHERE pe.status IN ('submetido', 'aprovado', 'rejeitado')
          AND pe.ano_letivo = ?";

$params = [$ano_letivo];
$types = "i";

if ($status != 'todos') {
    $query .= " AND pe.status = ?";
    $params[] = $status;
    $types .= "s";
}

if ($id_curso) {
    $query .= " AND d.curso_id_curso = ?";
    $params[] = $id_curso;
    $types .= "i";
}

if ($trimestre && $trimestre != 'todos') {
    $query .= " AND pe.trimestre = ?";
    $params[] = $trimestre;
    $types .= "s";
}

$query .= " ORDER BY pe.status ASC, pe.data_submissao DESC";

$stmt = $conn->prepare($query);

if ($params) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$planos = $result->fetch_all(MYSQLI_ASSOC);

// Obter cursos para filtros
$result_cursos = $conn->query("SELECT id_curso, nome FROM curso ORDER BY nome");
$cursos = $result_cursos->fetch_all(MYSQLI_ASSOC);

$tipo = $_SESSION['tipo_usuario'];
?>

<!DOCTYPE html>
<html lang="pt">

<?php require_once '../../includes/common/head.php'; ?>

<body>
    <?php require_once '../../includes/common/preloader.php'; ?>

    <div id="pcoded" class="pcoded">
        <div class="pcoded-overlay-box"></div>
        <div class="pcoded-container navbar-wrapper">

            <?php require_once "../../includes/$tipo/navbar.php"; ?>

            <div class="pcoded-main-container">
                <div class="pcoded-wrapper">
                    <?php require_once "../../includes/$tipo/sidebar.php"; ?>
                    
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

                                        <div class="card card-table mb-4">
                                            <div class="card-header">
                                                <h5>Aprovação de Planos de Ensino</h5>
                                            </div>
                                            <div class="card-block">
                                                <form method="GET" action="" class="row">
                                                    <div class="col-md-3 mb-2">
                                                        <label>Ano Letivo</label>
                                                        <input type="number" class="form-control" name="ano_letivo" 
                                                               value="<?= $ano_letivo ?>" min="2000" max="2050">
                                                    </div>
                                                    <div class="col-md-3 mb-2">
                                                        <label>Trimestre</label>
                                                        <select class="form-control" name="trimestre">
                                                            <option value="todos">Todos</option>
                                                            <option value="1" <?= $trimestre == '1' ? 'selected' : '' ?>>1º Trimestre</option>
                                                            <option value="2" <?= $trimestre == '2' ? 'selected' : '' ?>>2º Trimestre</option>
                                                            <option value="3" <?= $trimestre == '3' ? 'selected' : '' ?>>3º Trimestre</option>
                                                            <option value="anual" <?= $trimestre == 'anual' ? 'selected' : '' ?>>Anual</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-3 mb-2">
                                                        <label>Curso</label>
                                                        <select class="form-control" name="id_curso">
                                                            <option value="">Todos os cursos</option>
                                                            <?php foreach ($cursos as $curso): ?>
                                                                <option value="<?= $curso['id_curso'] ?>" 
                                                                    <?= $id_curso == $curso['id_curso'] ? 'selected' : '' ?>>
                                                                    <?= htmlspecialchars($curso['nome']) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-3 mb-2">
                                                        <label>Status</label>
                                                        <select class="form-control" name="status">
                                                            <option value="todos">Todos</option>
                                                            <option value="submetido" <?= $status == 'submetido' ? 'selected' : '' ?>>Submetidos</option>
                                                            <option value="aprovado" <?= $status == 'aprovado' ? 'selected' : '' ?>>Aprovados</option>
                                                            <option value="rejeitado" <?= $status == 'rejeitado' ? 'selected' : '' ?>>Rejeitados</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <button type="submit" class="btn btn-primary">
                                                            <i class="feather icon-filter"></i> Filtrar
                                                        </button>
                                                        <a href="aprovacao_planos.php" class="btn btn-secondary">
                                                            <i class="feather icon-refresh-ccw"></i> Limpar
                                                        </a>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>

                                        <div class="card card-table">
                                            <div class="card-header">
                                                <h5>Planos para Aprovação</h5>
                                            </div>
                                            <div class="card-block">
                                                <?php if (!empty($planos)): ?>
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered table-striped">
                                                            <thead>
                                                                <tr>
                                                                    <th>Título</th>
                                                                    <th>Disciplina</th>
                                                                    <th>Curso</th>
                                                                    <th>Professor</th>
                                                                    <th>Ano/Trimestre</th>
                                                                    <th>Status</th>
                                                                    <th>Submetido em</th>
                                                                    <th>Ações</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($planos as $plano): ?>
                                                                    <tr>
                                                                        <td><?= htmlspecialchars($plano['titulo']) ?></td>
                                                                        <td><?= htmlspecialchars($plano['disciplina']) ?></td>
                                                                        <td><?= htmlspecialchars($plano['curso']) ?></td>
                                                                        <td><?= $plano['professor'] ? htmlspecialchars($plano['professor']) : 'N/D' ?></td>
                                                                        <td><?= $plano['ano_letivo'] ?>/<?= $plano['trimestre'] == 'anual' ? 'Anual' : $plano['trimestre'] . 'º' ?></td>
                                                                        <td>
                                                                            <?php 
                                                                                $badge_class = [
                                                                                    'submetido' => 'warning',
                                                                                    'aprovado' => 'success',
                                                                                    'rejeitado' => 'danger'
                                                                                ][$plano['status']];
                                                                            ?>
                                                                            <span class="badge badge-<?= $badge_class ?>">
                                                                                <?= ucfirst($plano['status']) ?>
                                                                            </span>
                                                                        </td>
                                                                        <td><?= date('d/m/Y H:i', strtotime($plano['data_submissao'])) ?></td>
                                                                        <td>
                                                                            <button class="btn btn-info btn-sm" data-toggle="modal" 
                                                                                    data-target="#modalDetalhesPlano<?= $plano['id_plano'] ?>">
                                                                                <i class="feather icon-eye"></i> Ver
                                                                            </button>
                                                                            <?php if ($plano['caminho_arquivo']): ?>
                                                                                <a href="../../uploads/<?= htmlspecialchars($plano['caminho_arquivo']) ?>" 
                                                                                   class="btn btn-primary btn-sm" download>
                                                                                    <i class="feather icon-download"></i>
                                                                                </a>
                                                                            <?php endif; ?>
                                                                            <?php if ($plano['status'] == 'submetido'): ?>
                                                                                <button class="btn btn-success btn-sm" onclick="aprovarPlano(<?= $plano['id_plano'] ?>)">
                                                                                    <i class="feather icon-check"></i> Aprovar
                                                                                </button>
                                                                                <button class="btn btn-danger btn-sm" onclick="rejeitarPlano(<?= $plano['id_plano'] ?>)">
                                                                                    <i class="feather icon-x"></i> Rejeitar
                                                                                </button>
                                                                            <?php endif; ?>
                                                                        </td>
                                                                    </tr>

                                                                    <!-- Modal Detalhes -->
                                                                    <div class="modal fade" id="modalDetalhesPlano<?= $plano['id_plano'] ?>" tabindex="-1" role="dialog">
                                                                        <div class="modal-dialog modal-lg" role="document">
                                                                            <div class="modal-content">
                                                                                <div class="modal-header">
                                                                                    <h5 class="modal-title">Detalhes do Plano: <?= htmlspecialchars($plano['titulo']) ?></h5>
                                                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                                        <span aria-hidden="true">&times;</span>
                                                                                    </button>
                                                                                </div>
                                                                                <div class="modal-body">
                                                                                    <div class="row">
                                                                                        <div class="col-md-6">
                                                                                            <p><strong>Disciplina:</strong> <?= htmlspecialchars($plano['disciplina']) ?></p>
                                                                                            <p><strong>Curso:</strong> <?= htmlspecialchars($plano['curso']) ?></p>
                                                                                            <p><strong>Professor:</strong> <?= $plano['professor'] ? htmlspecialchars($plano['professor']) : 'N/D' ?></p>
                                                                                        </div>
                                                                                        <div class="col-md-6">
                                                                                            <p><strong>Ano Letivo:</strong> <?= $plano['ano_letivo'] ?></p>
                                                                                            <p><strong>Trimestre:</strong> <?= $plano['trimestre'] == 'anual' ? 'Anual' : $plano['trimestre'] . 'º Trimestre' ?></p>
                                                                                            <p><strong>Status:</strong> <span class="badge badge-<?= $badge_class ?>"><?= ucfirst($plano['status']) ?></span></p>
                                                                                            <?php if ($plano['status'] == 'aprovado' && $plano['diretor_aprovador']): ?>
                                                                                                <p><strong>Aprovado por:</strong> <?= htmlspecialchars($plano['diretor_aprovador']) ?></p>
                                                                                                <p><strong>Data aprovação:</strong> <?= date('d/m/Y H:i', strtotime($plano['data_aprovacao'])) ?></p>
                                                                                            <?php elseif ($plano['status'] == 'rejeitado'): ?>
                                                                                                <p><strong>Motivo rejeição:</strong> <?= htmlspecialchars($plano['motivo_rejeicao']) ?></p>
                                                                                            <?php endif; ?>
                                                                                        </div>
                                                                                    </div>
                                                                                    
                                                                                    <div class="row mt-3">
                                                                                        <div class="col-md-12">
                                                                                            <h5>Conteúdo Programático</h5>
                                                                                            <div class="border p-3"><?= nl2br(htmlspecialchars($plano['conteudo_programatico'])) ?></div>
                                                                                        </div>
                                                                                    </div>
                                                                                    
                                                                                    <div class="row mt-3">
                                                                                        <div class="col-md-6">
                                                                                            <h5>Metodologia</h5>
                                                                                            <div class="border p-3"><?= nl2br(htmlspecialchars($plano['metodologia'])) ?></div>
                                                                                        </div>
                                                                                        <div class="col-md-6">
                                                                                            <h5>Critérios de Avaliação</h5>
                                                                                            <div class="border p-3"><?= nl2br(htmlspecialchars($plano['criterios_avaliacao'])) ?></div>
                                                                                        </div>
                                                                                    </div>
                                                                                    
                                                                                    <div class="row mt-3">
                                                                                        <div class="col-md-12">
                                                                                            <h5>Bibliografia</h5>
                                                                                            <div class="border p-3"><?= nl2br(htmlspecialchars($plano['bibliografia'])) ?></div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="modal-footer">
                                                                                    <?php if ($plano['caminho_arquivo']): ?>
                                                                                        <a href="../../uploads/<?= htmlspecialchars($plano['caminho_arquivo']) ?>" 
                                                                                           class="btn btn-primary" download>
                                                                                            <i class="feather icon-download"></i> Baixar Plano
                                                                                        </a>
                                                                                    <?php endif; ?>
                                                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="alert alert-info">
                                                        Nenhum plano encontrado com os filtros selecionados.
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
        function aprovarPlano(id) {
            if (confirm('Deseja aprovar este plano de ensino?')) {
                window.location.href = 'processar_aprovacao.php?id=' + id + '&acao=aprovar';
            }
        }
        
        function rejeitarPlano(id) {
            var motivo = prompt('Por favor, informe o motivo da rejeição:');
            if (motivo !== null && motivo.trim() !== '') {
                window.location.href = 'processar_aprovacao.php?id=' + id + '&acao=rejeitar&motivo=' + encodeURIComponent(motivo);
            } else if (motivo !== null) {
                alert('É necessário informar o motivo da rejeição.');
            }
        }
    </script>
</body>
</html>