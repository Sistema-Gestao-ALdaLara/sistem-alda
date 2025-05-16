<?php
require_once '../../database/conexao.php';

if (isset($_GET['id_aluno']) && isset($_GET['id_disciplina']) && isset($_GET['trimestre'])) {
    // Consulta para buscar notas conforme parâmetros
    $query = "SELECT n.*, d.nome as disciplina_nome 
              FROM nota n
              JOIN disciplina d ON n.disciplina_id_disciplina = d.id_disciplina
              WHERE n.aluno_id_aluno = ? 
              AND n.disciplina_id_disciplina = ?
              AND n.trimestre = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iii", $_GET['id_aluno'], $_GET['id_disciplina'], $_GET['trimestre']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Gerar HTML do relatório de notas
    header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <style>
        /* Estilos para o relatório de notas */
    </style>
</head>
<body>
    <!-- Conteúdo do relatório de notas -->
    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>
<?php
}
?><?php
require_once '../../includes/common/permissoes.php';
verificarPermissao(['secretaria', 'diretor_geral', 'diretor_pedagogico', 'coordenador', 'professor']);
require_once '../../process/verificar_sessao.php';
require_once '../../database/conexao.php';

// Obter parâmetros do relatório
$ano_letivo = $_GET['ano_letivo'] ?? date('Y');
$trimestre = $_GET['trimestre'] ?? null;
$id_curso = $_GET['id_curso'] ?? null;
$id_turma = $_GET['id_turma'] ?? null;
$id_disciplina = $_GET['id_disciplina'] ?? null;
$ordenacao = $_GET['ordenacao'] ?? 'nome_asc';

// Construir consulta SQL
$query = "SELECT 
            n.*, 
            a.nome as aluno_nome, 
            t.nome as turma_nome, 
            c.nome as curso_nome,
            d.nome as disciplina_nome,
            u.nome as professor_nome
          FROM nota n
          JOIN aluno al ON n.aluno_id_aluno = al.id_aluno
          JOIN usuario a ON al.usuario_id_usuario = a.id_usuario
          JOIN disciplina d ON n.disciplina_id_disciplina = d.id_disciplina
          JOIN turma t ON al.turma_id_turma = t.id_turma
          JOIN curso c ON t.curso_id_curso = c.id_curso
          LEFT JOIN professor_tem_disciplina pd ON d.id_disciplina = pd.disciplina_id_disciplina
          LEFT JOIN professor p ON pd.professor_id_professor = p.id_professor
          LEFT JOIN usuario u ON p.usuario_id_usuario = u.id_usuario
          WHERE n.trimestre = ?";

$params = [$trimestre];
$types = "i";

if ($ano_letivo) {
    $query .= " AND YEAR(n.data) = ?";
    $params[] = $ano_letivo;
    $types .= "i";
}

if ($id_curso) {
    $query .= " AND t.curso_id_curso = ?";
    $params[] = $id_curso;
    $types .= "i";
}

if ($id_turma) {
    $query .= " AND al.turma_id_turma = ?";
    $params[] = $id_turma;
    $types .= "i";
}

if ($id_disciplina) {
    $query .= " AND n.disciplina_id_disciplina = ?";
    $params[] = $id_disciplina;
    $types .= "i";
}

// Adicionar ordenação
switch ($ordenacao) {
    case 'nome_desc':
        $query .= " ORDER BY a.nome DESC";
        break;
    case 'nota_asc':
        $query .= " ORDER BY n.nota ASC";
        break;
    case 'nota_desc':
        $query .= " ORDER BY n.nota DESC";
        break;
    case 'disciplina_asc':
        $query .= " ORDER BY d.nome ASC";
        break;
    case 'disciplina_desc':
        $query .= " ORDER BY d.nome DESC";
        break;
    default:
        $query .= " ORDER BY a.nome ASC";
}

// Preparar e executar a consulta
$stmt = $conn->prepare($query);
if ($types) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$notas = $result->fetch_all(MYSQLI_ASSOC);

// Calcular médias por aluno e disciplina
$medias = [];
foreach ($notas as $nota) {
    $key = $nota['aluno_id_aluno'] . '_' . $nota['disciplina_id_disciplina'];
    if (!isset($medias[$key])) {
        $medias[$key] = [
            'aluno_nome' => $nota['aluno_nome'],
            'disciplina_nome' => $nota['disciplina_nome'],
            'total' => 0,
            'peso_total' => 0,
            'quantidade' => 0
        ];
    }
    $medias[$key]['total'] += $nota['nota'] * $nota['peso'];
    $medias[$key]['peso_total'] += $nota['peso'];
    $medias[$key]['quantidade']++;
}

// Calcular média final para cada aluno/disciplina
foreach ($medias as &$media) {
    $media['media'] = $media['peso_total'] > 0 ? $media['total'] / $media['peso_total'] : 0;
}

$title = "Relatório de Notas";
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <?php require_once '../../includes/common/head.php'; ?>
    <style>
        .report-header {
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        .report-title {
            font-size: 24px;
            font-weight: bold;
        }
        .report-filters {
            font-size: 14px;
            color: #666;
        }
        .report-table {
            width: 100%;
            margin-bottom: 20px;
        }
        .report-table th {
            background-color: #f8f9fa;
            text-align: left;
            padding: 10px;
        }
        .report-table td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .media-row {
            background-color: #f0f8ff;
            font-weight: bold;
        }
        .print-actions {
            margin-top: 20px;
            text-align: right;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                padding: 20px;
                font-size: 12px;
            }
            .report-table {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="report-header">
            <div class="report-title">Relatório de Notas</div>
            <div class="report-filters">
                Ano Letivo: <?= $ano_letivo ?> | 
                <?= $trimestre ? "Trimestre: $trimestre | " : "" ?>
                <?= $id_curso ? "Curso: " . htmlspecialchars($notas[0]['curso_nome'] ?? '') . " | " : "" ?>
                <?= $id_turma ? "Turma: " . htmlspecialchars($notas[0]['turma_nome'] ?? '') . " | " : "" ?>
                <?= $id_disciplina ? "Disciplina: " . htmlspecialchars($notas[0]['disciplina_nome'] ?? '') : "" ?>
            </div>
        </div>
        
        <!-- Tabela de Notas Detalhadas -->
        <table class="report-table">
            <thead>
                <tr>
                    <th>Aluno</th>
                    <th>Turma</th>
                    <th>Disciplina</th>
                    <th>Professor</th>
                    <th>Tipo Avaliação</th>
                    <th>Nota</th>
                    <th>Peso</th>
                    <th>Data</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($notas as $nota): ?>
                <tr>
                    <td><?= htmlspecialchars($nota['aluno_nome']) ?></td>
                    <td><?= htmlspecialchars($nota['turma_nome']) ?></td>
                    <td><?= htmlspecialchars($nota['disciplina_nome']) ?></td>
                    <td><?= htmlspecialchars($nota['professor_nome'] ?? 'N/A') ?></td>
                    <td><?= ucfirst(str_replace('_', ' ', $nota['tipo_avaliacao'])) ?></td>
                    <td><?= number_format($nota['nota'], 2) ?></td>
                    <td><?= number_format($nota['peso'], 2) ?></td>
                    <td><?= date('d/m/Y', strtotime($nota['data'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <!-- Tabela de Médias -->
        <h4>Médias por Aluno/Disciplina</h4>
        <table class="report-table">
            <thead>
                <tr>
                    <th>Aluno</th>
                    <th>Disciplina</th>
                    <th>Quant. Avaliações</th>
                    <th>Média Ponderada</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($medias as $media): ?>
                <tr class="media-row">
                    <td><?= htmlspecialchars($media['aluno_nome']) ?></td>
                    <td><?= htmlspecialchars($media['disciplina_nome']) ?></td>
                    <td><?= $media['quantidade'] ?></td>
                    <td><?= number_format($media['media'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="print-actions no-print">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="feather icon-printer"></i> Imprimir/Salvar PDF
            </button>
            <a href="../compartilhados/relatorios.php" class="btn btn-secondary">
                <i class="feather icon-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    <?php require_once '../../includes/common/js_imports.php'; ?>
</body>
</html>