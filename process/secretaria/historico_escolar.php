<?php
require_once "../auth/permissoes.php";
verificarPermissao(['secretaria']);
require_once '../../database/conexao.php';

if (!$pdo) {
    die("Erro na conexão com o banco de dados");
}

$id_aluno = isset($_GET['id']) ? intval($_GET['id']) : null;

if (!$id_aluno) {
    die('ID do aluno não fornecido');
}

// Obter dados do aluno
$stmt = $pdo->prepare("SELECT u.nome, u.bi_numero, a.numero_matricula
                      FROM aluno a
                      JOIN usuario u ON a.id_usuario = u.id_usuario
                      WHERE a.id_aluno = ?");
$stmt->execute([$id_aluno]);
$aluno = $stmt->fetch();

if (!$aluno) {
    die('Aluno não encontrado');
}

// Obter histórico acadêmico
$stmt = $pdo->prepare("SELECT 
                      m.ano_letivo, c.nome AS curso, t.nome AS turma,
                      m.status_matricula, m.tipo_matricula,
                      DATE_FORMAT(m.data_matricula, '%d/%m/%Y') AS data_matricula
                      FROM matricula m
                      LEFT JOIN curso c ON m.id_curso = c.id_curso
                      LEFT JOIN turma t ON m.id_turma = t.id_turma
                      WHERE m.id_aluno = ?
                      ORDER BY m.ano_letivo DESC");
$stmt->execute([$id_aluno]);
$historico = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obter disciplinas e notas (simplificado)
$stmt = $pdo->prepare("SELECT d.nome AS disciplina, n.nota, n.faltas, p.nome AS periodo
                      FROM notas n
                      JOIN disciplina d ON n.id_disciplina = d.id_disciplina
                      JOIN periodo_letivo p ON n.id_periodo = p.id_periodo
                      WHERE n.id_aluno = ?
                      ORDER BY p.ano, p.semestre, d.nome");
$stmt->execute([$id_aluno]);
$notas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Agora vamos exibir os dados em HTML ao invés de gerar PDF
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <title>Histórico Escolar - <?= htmlspecialchars($aluno['nome']) ?></title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .historico-header {
            background-color: #f8f9fa;
            padding: 20px;
            margin-bottom: 30px;
            border-bottom: 2px solid #dee2e6;
        }
        .table-historic {
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="historico-header text-center">
            <h1>Histórico Escolar</h1>
            <h3><?= htmlspecialchars($aluno['nome']) ?></h3>
            <p>BI: <?= htmlspecialchars($aluno['bi_numero']) ?> | Matrícula: <?= htmlspecialchars($aluno['numero_matricula']) ?></p>
        </div>

        <h4>Histórico de Matrículas</h4>
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-historic">
                <thead class="table-light">
                    <tr>
                        <th>Ano Letivo</th>
                        <th>Curso</th>
                        <th>Turma</th>
                        <th>Status</th>
                        <th>Tipo</th>
                        <th>Data</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($historico as $registro): ?>
                    <tr>
                        <td><?= htmlspecialchars($registro['ano_letivo']) ?></td>
                        <td><?= htmlspecialchars($registro['curso'] ?? 'N/D') ?></td>
                        <td><?= htmlspecialchars($registro['turma'] ?? 'N/D') ?></td>
                        <td>
                            <span class="badge <?= $registro['status_matricula'] === 'aprovada' ? 'bg-success' : 
                                              ($registro['status_matricula'] === 'rejeitada' ? 'bg-danger' : 'bg-warning') ?>">
                                <?= ucfirst($registro['status_matricula']) ?>
                            </span>
                        </td>
                        <td><?= $registro['tipo_matricula'] == 'regular' ? 'Regular' : 'Transferência' ?></td>
                        <td><?= htmlspecialchars($registro['data_matricula']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if (!empty($notas)): ?>
        <h4>Desempenho Acadêmico</h4>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Disciplina</th>
                        <th>Período</th>
                        <th>Nota</th>
                        <th>Faltas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($notas as $nota): ?>
                    <tr>
                        <td><?= htmlspecialchars($nota['disciplina']) ?></td>
                        <td><?= htmlspecialchars($nota['periodo']) ?></td>
                        <td><?= htmlspecialchars($nota['nota']) ?></td>
                        <td><?= htmlspecialchars($nota['faltas']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <div class="row mt-4">
            <div class="col-md-6 text-center">
                <p>__________________________</p>
                <p>Secretária</p>
            </div>
            <div class="col-md-6 text-center">
                <p>__________________________</p>
                <p>Diretor</p>
            </div>
        </div>
        
        <div class="text-end mt-3">
            <small class="text-muted">Emitido em: <?= date('d/m/Y H:i:s') ?></small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>