<?php
require_once '../../includes/common/permissoes.php';
verificarPermissao(['coordenador']);
require_once '../../process/verificar_sessao.php';
require_once '../../database/conexao.php';

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
$nome_curso = $coordenador['nome_curso'];

// Filtros
$filtro_disciplina = isset($_GET['disciplina']) ? $_GET['disciplina'] : '';
$filtro_status = isset($_GET['status']) ? $_GET['status'] : 'aprovado';
$filtro_ano = isset($_GET['ano']) ? $_GET['ano'] : date('Y');

// Obter anos disponíveis
$sql_anos = "SELECT DISTINCT ano_letivo FROM plano_ensino 
            JOIN disciplina d ON plano_ensino.id_disciplina = d.id_disciplina
            WHERE d.curso_id_curso = ?
            ORDER BY ano_letivo DESC";
$stmt = $conn->prepare($sql_anos);
$stmt->bind_param("i", $id_curso);
$stmt->execute();
$anos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Obter disciplinas do curso
$sql_disciplinas = "SELECT d.id_disciplina, d.nome
                   FROM disciplina d
                   WHERE d.curso_id_curso = ?
                   ORDER BY d.nome";
$stmt = $conn->prepare($sql_disciplinas);
$stmt->bind_param("i", $id_curso);
$stmt->execute();
$disciplinas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Obter planos de ensino da tabela dedicada
$sql_planos = "SELECT pe.*, d.nome as disciplina_nome, 
              u.nome as professor_nome, u.id_usuario as id_professor,
              co.nome as coordenador_nome
              FROM plano_ensino pe
              JOIN disciplina d ON pe.id_disciplina = d.id_disciplina
              LEFT JOIN professor p ON pe.id_professor = p.id_professor
              LEFT JOIN usuario u ON p.usuario_id_usuario = u.id_usuario
              LEFT JOIN coordenador c ON pe.id_coordenador_aprovador = c.id_coordenador
              LEFT JOIN usuario co ON c.usuario_id_usuario = co.id_usuario
              WHERE d.curso_id_curso = ?
              AND pe.ano_letivo = ?";

// Aplicar filtros
if (!empty($filtro_disciplina)) {
    $sql_planos .= " AND pe.id_disciplina = ?";
}

if ($filtro_status != 'todos') {
    $sql_planos .= " AND pe.status = ?";
}

$sql_planos .= " ORDER BY pe.ano_letivo DESC, pe.trimestre DESC, d.nome ASC";

$stmt = $conn->prepare($sql_planos);

if (!empty($filtro_disciplina) && $filtro_status != 'todos') {
    $stmt->bind_param("iiis", $id_curso, $filtro_ano, $filtro_disciplina, $filtro_status);
} elseif (!empty($filtro_disciplina)) {
    $stmt->bind_param("iii", $id_curso, $filtro_ano, $filtro_disciplina);
} elseif ($filtro_status != 'todos') {
    $stmt->bind_param("iis", $id_curso, $filtro_ano, $filtro_status);
} else {
    $stmt->bind_param("ii", $id_curso, $filtro_ano);
}

$stmt->execute();
$planos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$title = "Planos de Ensino - " . htmlspecialchars($nome_curso);
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
                                        <div class="card mb-4">
                                            <div class="card-header">
                                                <h5>Planos de Ensino - <?= htmlspecialchars($nome_curso) ?></h5>
                                            </div>
                                            <div class="card-block">
                                                <form method="GET" action="" class="row">
                                                    <div class="col-md-3 mb-2">
                                                        <select class="form-control" name="ano">
                                                            <?php foreach ($anos as $ano): ?>
                                                                <option value="<?= $ano['ano_letivo'] ?>" <?= $filtro_ano == $ano['ano_letivo'] ? 'selected' : '' ?>>
                                                                    <?= $ano['ano_letivo'] ?>
                                                                </option>
                                                            <?php endforeach; ?>
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
                                                    <div class="col-md-3 mb-2">
                                                        <button type="submit" class="btn btn-primary">
                                                            <i class="feather icon-filter"></i> Filtrar
                                                        </button>
                                                        <a href="planos.php" class="btn btn-secondary">
                                                            <i class="feather icon-refresh-ccw"></i> Limpar
                                                        </a>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>

                                        <div class="card">
                                            <div class="card-header">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h5>Lista de Planos de Ensino</h5>
                                                    <button class="btn btn-success" data-toggle="modal" data-target="#modalAdicionarPlano">
                                                        <i class="feather icon-plus"></i> Adicionar Plano
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="card-block">
                                                <?php if (!empty($planos)): ?>
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered table-striped">
                                                            <thead>
                                                                <tr>
                                                                    <th>Ano</th>
                                                                    <th>Trim.</th>
                                                                    <th>Disciplina</th>
                                                                    <th>Professor</th>
                                                                    <th>Status</th>
                                                                    <th>Ações</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($planos as $plano): ?>
                                                                    <tr>
                                                                        <td><?= $plano['ano_letivo'] ?></td>
                                                                        <td><?= $plano['trimestre'] ?></td>
                                                                        <td><?= htmlspecialchars($plano['disciplina_nome']) ?></td>
                                                                        <td><?= $plano['professor_nome'] ? htmlspecialchars($plano['professor_nome']) : 'N/D' ?></td>
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
                                                                        <td>
                                                                            <a href="../../uploads/<?= htmlspecialchars($plano['caminho_arquivo']) ?>" 
                                                                               class="btn btn-primary btn-sm" download>
                                                                                <i class="feather icon-download"></i>
                                                                            </a>
                                                                            <button class="btn btn-info btn-sm" data-toggle="modal" 
                                                                                    data-target="#modalDetalhesPlano<?= $plano['id_plano'] ?>">
                                                                                <i class="feather icon-eye"></i>
                                                                            </button>
                                                                            <?php if ($plano['status'] == 'submetido'): ?>
                                                                                <button class="btn btn-success btn-sm" onclick="aprovarPlano(<?= $plano['id_plano'] ?>)">
                                                                                    <i class="feather icon-check"></i>
                                                                                </button>
                                                                                <button class="btn btn-danger btn-sm" onclick="rejeitarPlano(<?= $plano['id_plano'] ?>)">
                                                                                    <i class="feather icon-x"></i>
                                                                                </button>
                                                                            <?php endif; ?>
                                                                        </td>
                                                                    </tr>

                                                                    <!-- Modal Detalhes -->
                                                                    <div class="modal fade" id="modalDetalhesPlano<?= $plano['id_plano'] ?>" tabindex="-1" role="dialog">
                                                                        <div class="modal-dialog modal-lg" role="document">
                                                                            <div class="modal-content">
                                                                                <div class="modal-header">
                                                                                    <h5 class="modal-title">Detalhes do Plano de Ensino</h5>
                                                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                                        <span aria-hidden="true">&times;</span>
                                                                                    </button>
                                                                                </div>
                                                                                <div class="modal-body">
                                                                                    <div class="row">
                                                                                        <div class="col-md-6">
                                                                                            <p><strong>Ano Letivo:</strong> <?= $plano['ano_letivo'] ?></p>
                                                                                            <p><strong>Trimestre:</strong> <?= $plano['trimestre'] ?></p>
                                                                                            <p><strong>Disciplina:</strong> <?= htmlspecialchars($plano['disciplina_nome']) ?></p>
                                                                                            <p><strong>Professor:</strong> <?= $plano['professor_nome'] ? htmlspecialchars($plano['professor_nome']) : 'N/D' ?></p>
                                                                                            <p><strong>Status:</strong> <span class="badge badge-<?= $badge_class ?>"><?= ucfirst($plano['status']) ?></span></p>
                                                                                            <?php if ($plano['coordenador_nome']): ?>
                                                                                                <p><strong>Aprovado por:</strong> <?= htmlspecialchars($plano['coordenador_nome']) ?></p>
                                                                                            <?php endif; ?>
                                                                                        </div>
                                                                                        <div class="col-md-6">
                                                                                            <p><strong>Conteúdo Programático:</strong></p>
                                                                                            <p><?= nl2br(htmlspecialchars($plano['conteudo_programatico'])) ?></p>
                                                                                            
                                                                                            <p><strong>Metodologia:</strong></p>
                                                                                            <p><?= nl2br(htmlspecialchars($plano['metodologia'])) ?></p>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="row mt-3">
                                                                                        <div class="col-md-6">
                                                                                            <p><strong>Critérios de Avaliação:</strong></p>
                                                                                            <p><?= nl2br(htmlspecialchars($plano['criterios_avaliacao'])) ?></p>
                                                                                        </div>
                                                                                        <div class="col-md-6">
                                                                                            <p><strong>Bibliografia:</strong></p>
                                                                                            <p><?= nl2br(htmlspecialchars($plano['bibliografia'])) ?></p>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="modal-footer">
                                                                                    <a href="../../uploads/<?= htmlspecialchars($plano['caminho_arquivo']) ?>" 
                                                                                       class="btn btn-primary" download>
                                                                                        <i class="feather icon-download"></i> Baixar Plano
                                                                                    </a>
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

    <!-- Modal Adicionar Plano -->
    <div class="modal fade" id="modalAdicionarPlano" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Adicionar Novo Plano de Ensino</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="processar_plano.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="ano_letivo">Ano Letivo *</label>
                                    <input type="number" class="form-control" id="ano_letivo" name="ano_letivo" 
                                           value="<?= date('Y') ?>" min="2000" max="2050" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="trimestre">Trimestre *</label>
                                    <select class="form-control" id="trimestre" name="trimestre" required>
                                        <option value="1">1º Trimestre</option>
                                        <option value="2">2º Trimestre</option>
                                        <option value="3">3º Trimestre</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="disciplina">Disciplina *</label>
                                    <select class="form-control" id="disciplina" name="id_disciplina" required>
                                        <option value="">Selecione uma disciplina</option>
                                        <?php foreach ($disciplinas as $disciplina): ?>
                                            <option value="<?= $disciplina['id_disciplina'] ?>">
                                                <?= htmlspecialchars($disciplina['nome']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="nome">Nome do Plano *</label>
                            <input type="text" class="form-control" id="nome" name="nome" 
                                   value="Plano de Ensino - " required>
                        </div>
                        <div class="form-group">
                            <label for="descricao">Descrição *</label>
                            <textarea class="form-control" id="descricao" name="descricao" rows="2" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="conteudo">Conteúdo Programático *</label>
                            <textarea class="form-control" id="conteudo" name="conteudo" rows="3" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="metodologia">Metodologia *</label>
                            <textarea class="form-control" id="metodologia" name="metodologia" rows="3" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="criterios">Critérios de Avaliação *</label>
                            <textarea class="form-control" id="criterios" name="criterios" rows="2" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="bibliografia">Bibliografia *</label>
                            <textarea class="form-control" id="bibliografia" name="bibliografia" rows="2" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="arquivo">Arquivo (PDF/DOCX) *</label>
                            <input type="file" class="form-control-file" id="arquivo" name="arquivo" accept=".pdf,.doc,.docx" required>
                            <small class="form-text text-muted">Tamanho máximo: 5MB</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar Plano</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php require_once '../../includes/common/js_imports.php'; ?>
    
    <script>
        function confirmarExclusao(id) {
            if (confirm('Tem certeza que deseja excluir este plano de ensino?')) {
                window.location.href = 'excluir_plano.php?id=' + id;
            }
        }
        
        function aprovarPlano(id) {
            if (confirm('Deseja aprovar este plano de ensino?')) {
                window.location.href = 'aprovar_plano.php?id=' + id + '&status=aprovado';
            }
        }
        
        function rejeitarPlano(id) {
            var motivo = prompt('Informe o motivo da rejeição:');
            if (motivo !== null) {
                window.location.href = 'aprovar_plano.php?id=' + id + '&status=rejeitado&motivo=' + encodeURIComponent(motivo);
            }
        }
        
        // Atualizar nome do plano ao selecionar disciplina
        document.getElementById('disciplina').addEventListener('change', function() {
            var disciplinaNome = this.options[this.selectedIndex].text;
            if (disciplinaNome && this.value !== '') {
                document.getElementById('nome').value = 'Plano de Ensino - ' + disciplinaNome;
            }
        });
    </script>
</body>
</html>