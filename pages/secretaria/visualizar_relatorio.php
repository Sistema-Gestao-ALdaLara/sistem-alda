<?php
require_once '../../includes/common/permissoes.php';
verificarPermissao(['secretaria']);
require_once '../../process/verificar_sessao.php';
require_once '../../database/conexao.php';

// Verificar se existem parâmetros de relatório na sessão
if (!isset($_SESSION['relatorio_params'])) {
    $_SESSION['erro'] = "Nenhum parâmetro de relatório encontrado.";
    header('Location: relatorios.php');
    exit();
}

$params = $_SESSION['relatorio_params'];
unset($_SESSION['relatorio_params']);

// Validar parâmetros obrigatórios
if (!isset($params['tipo']) || !isset($params['ano_letivo'])) {
    $_SESSION['erro'] = "Parâmetros de relatório inválidos.";
    header('Location: relatorios.php');
    exit();
}

// Validar parâmetros para relatórios específicos
if (in_array($params['tipo'], ['notas', 'frequencia']) && (!isset($params['turma']) || !isset($params['trimestre']))) {
    $_SESSION['erro'] = "Turma e trimestre são obrigatórios para este tipo de relatório.";
    header('Location: relatorios.php');
    exit();
}

// Gerar relatório com base nos parâmetros
$dados_relatorio = [];
$titulo_relatorio = '';

switch ($params['tipo']) {
    case 'matriculas':
        $titulo_relatorio = "Lista de Matrículas - Ano Letivo {$params['ano_letivo']}";
        
        $query = "SELECT m.numero_matricula, u.nome, u.bi_numero, c.nome as curso, t.nome as turma, 
                         m.classe, m.status_matricula, m.data_matricula
                  FROM matricula m
                  JOIN aluno a ON m.aluno_id_aluno = a.id_aluno
                  JOIN usuario u ON a.usuario_id_usuario = u.id_usuario
                  JOIN curso c ON m.curso_id_curso = c.id_curso
                  JOIN turma t ON m.turma_id_turma = t.id_turma
                  WHERE m.ano_letivo = ?";
        
        $types = "s";
        $values = [$params['ano_letivo']];
        
        if (isset($params['curso']) && $params['curso']) {
            $query .= " AND m.curso_id_curso = ?";
            $types .= "i";
            $values[] = $params['curso'];
        }
        
        if (isset($params['turma']) && $params['turma']) {
            $query .= " AND m.turma_id_turma = ?";
            $types .= "i";
            $values[] = $params['turma'];
        }
        
        if (isset($params['classe']) && $params['classe']) {
            $query .= " AND m.classe = ?";
            $types .= "s";
            $values[] = $params['classe'];
        }
        
        $query .= " ORDER BY u.nome";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param($types, ...$values);
        $stmt->execute();
        $result = $stmt->get_result();
        $dados_relatorio = $result->fetch_all(MYSQLI_ASSOC);
        break;
        
    case 'notas':
        $titulo_relatorio = "Boletim de Notas - {$params['trimestre']}º Trimestre {$params['ano_letivo']}";
        
        $query = "SELECT a.id_aluno, u.nome as aluno, d.nome as disciplina, 
                         n.nota, n.tipo_avaliacao, n.descricao, n.data
                  FROM nota n
                  JOIN aluno a ON n.aluno_id_aluno = a.id_aluno
                  JOIN usuario u ON a.usuario_id_usuario = u.id_usuario
                  JOIN disciplina d ON n.disciplina_id_disciplina = d.id_disciplina
                  JOIN matricula m ON a.id_aluno = m.aluno_id_aluno
                  WHERE m.turma_id_turma = ? AND n.trimestre = ? AND m.ano_letivo = ?
                  ORDER BY u.nome, d.nome";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iis", $params['turma'], $params['trimestre'], $params['ano_letivo']);
        $stmt->execute();
        $result = $stmt->get_result();
        $notas = $result->fetch_all(MYSQLI_ASSOC);
        
        $alunos = [];
        foreach ($notas as $nota) {
            $aluno_id = $nota['id_aluno'];
            if (!isset($alunos[$aluno_id])) {
                $alunos[$aluno_id] = [
                    'nome' => $nota['aluno'],
                    'disciplinas' => []
                ];
            }
            
            if (!isset($alunos[$aluno_id]['disciplinas'][$nota['disciplina']])) {
                $alunos[$aluno_id]['disciplinas'][$nota['disciplina']] = [];
            }
            
            $alunos[$aluno_id]['disciplinas'][$nota['disciplina']][] = [
                'nota' => $nota['nota'],
                'tipo' => $nota['tipo_avaliacao'],
                'descricao' => $nota['descricao'],
                'data' => $nota['data']
            ];
        }
        
        $dados_relatorio = $alunos;
        break;
        
    case 'frequencia':
        $titulo_relatorio = "Relatório de Frequência - {$params['trimestre']}º Trimestre {$params['ano_letivo']}";
        
        $query = "SELECT a.id_aluno, u.nome as aluno, d.nome as disciplina, 
                         COUNT(CASE WHEN fa.presenca = 'presente' THEN 1 END) as presentes,
                         COUNT(CASE WHEN fa.presenca = 'ausente' THEN 1 END) as ausentes,
                         COUNT(CASE WHEN fa.presenca = 'justificado' THEN 1 END) as justificados,
                         COUNT(*) as total_aulas
                  FROM frequencia_aluno fa
                  JOIN aluno a ON fa.aluno_id_aluno = a.id_aluno
                  JOIN usuario u ON a.usuario_id_usuario = u.id_usuario
                  JOIN disciplina d ON fa.disciplina_id_disciplina = d.id_disciplina
                  JOIN matricula m ON a.id_aluno = m.aluno_id_aluno
                  WHERE m.turma_id_turma = ? AND YEAR(fa.data_aula) = ? 
                  AND MONTH(fa.data_aula) BETWEEN ? AND ?
                  GROUP BY a.id_aluno, d.id_disciplina
                  ORDER BY u.nome, d.nome";
        
        $mes_inicio = ($params['trimestre'] - 1) * 4 + 1;
        $mes_fim = $params['trimestre'] * 4;
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("isii", $params['turma'], $params['ano_letivo'], $mes_inicio, $mes_fim);
        $stmt->execute();
        $result = $stmt->get_result();
        $dados_relatorio = $result->fetch_all(MYSQLI_ASSOC);
        break;
        
    case 'planos_ensino':
        $titulo_relatorio = "Status dos Planos de Ensino - Ano Letivo {$params['ano_letivo']}";
        
        $query = "SELECT pe.id_plano, d.nome as disciplina, c.nome as curso, 
                         pe.trimestre, pe.status, pe.data_submissao, pe.data_aprovacao,
                         u.nome as professor, u2.nome as coordenador
                  FROM plano_ensino pe
                  JOIN disciplina d ON pe.id_disciplina = d.id_disciplina
                  JOIN curso c ON d.curso_id_curso = c.id_curso
                  LEFT JOIN professor p ON pe.id_professor = p.id_professor
                  LEFT JOIN usuario u ON p.usuario_id_usuario = u.id_usuario
                  LEFT JOIN coordenador co ON pe.id_coordenador_aprovador = co.id_coordenador
                  LEFT JOIN usuario u2 ON co.usuario_id_usuario = u2.id_usuario
                  WHERE pe.ano_letivo = ?";
        
        $types = "s";
        $values = [$params['ano_letivo']];
        
        if (isset($params['curso']) && $params['curso']) {
            $query .= " AND d.curso_id_curso = ?";
            $types .= "i";
            $values[] = $params['curso'];
        }
        
        if (isset($params['status']) && $params['status']) {
            $query .= " AND pe.status = ?";
            $types .= "s";
            $values[] = $params['status'];
        }
        
        $query .= " ORDER BY c.nome, d.nome";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param($types, ...$values);
        $stmt->execute();
        $result = $stmt->get_result();
        $dados_relatorio = $result->fetch_all(MYSQLI_ASSOC);
        break;
        
    case 'transferencias':
        $titulo_relatorio = "Relatório de Transferências e Cancelamentos - Ano Letivo {$params['ano_letivo']}";
        
        $query = "SELECT m.numero_matricula, u.nome, u.bi_numero, c.nome as curso, t.nome as turma, 
                         m.classe, m.status_matricula, m.data_matricula
                  FROM matricula m
                  JOIN aluno a ON m.aluno_id_aluno = a.id_aluno
                  JOIN usuario u ON a.usuario_id_usuario = u.id_usuario
                  JOIN curso c ON m.curso_id_curso = c.id_curso
                  JOIN turma t ON m.turma_id_turma = t.id_turma
                  WHERE m.ano_letivo = ? AND m.status_matricula IN ('trancada', 'cancelada')";
        
        $types = "s";
        $values = [$params['ano_letivo']];
        
        if (isset($params['curso']) && $params['curso']) {
            $query .= " AND m.curso_id_curso = ?";
            $types .= "i";
            $values[] = $params['curso'];
        }
        
        if (isset($params['status_matricula']) && $params['status_matricula']) {
            $query .= " AND m.status_matricula = ?";
            $types .= "s";
            $values[] = $params['status_matricula'];
        }
        
        $query .= " ORDER BY u.nome";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param($types, ...$values);
        $stmt->execute();
        $result = $stmt->get_result();
        $dados_relatorio = $result->fetch_all(MYSQLI_ASSOC);
        break;
        
    default:
        $_SESSION['erro'] = "Tipo de relatório inválido.";
        header('Location: relatorios.php');
        exit();
}

// Registrar o relatório na tabela relatorio
$parametros_str = json_encode([
    'ano_letivo' => $params['ano_letivo'],
    'curso' => $params['curso'] ?? null,
    'turma' => $params['turma'] ?? null,
    'classe' => $params['classe'] ?? null,
    'trimestre' => $params['trimestre'] ?? null,
    'status' => $params['status'] ?? null,
    'status_matricula' => $params['status_matricula'] ?? null
]);

$caminho_arquivo = null; // Não geramos arquivo físico
$query = "INSERT INTO relatorio (tipo, parametros, destinatarios, curso_id, turma_id, classe, trimestre, ano_letivo, caminho_arquivo, usuario_id_usuario) 
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($query);

// Ajustar valores e tipos para lidar com NULL
$curso_id = isset($params['curso']) && $params['curso'] ? (int)$params['curso'] : null;
$turma_id = isset($params['turma']) && $params['turma'] ? (int)$params['turma'] : null;
$classe = isset($params['classe']) && $params['classe'] ? $params['classe'] : null;
$trimestre = isset($params['trimestre']) && $params['trimestre'] ? (int)$params['trimestre'] : null;

// Usar referências para bind_param
$stmt->bind_param(
    "sssississi",
    $params['tipo'],
    $parametros_str,
    $params['destinatarios'],
    $curso_id,
    $turma_id,
    $classe,
    $trimestre,
    $params['ano_letivo'],
    $caminho_arquivo,
    $_SESSION['id_usuario']
);
$stmt->execute();

// Formatara destinatários para exibição
$destinatarios_formatados = implode(", ", array_map(function($d) {
    return ucfirst(str_replace("_", " ", $d));
}, explode(",", $params['destinatarios'])));
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($titulo_relatorio) ?> | Sistema Escolar</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; }
        .logo { max-width: 150px; }
        .title { font-size: 24px; font-weight: bold; margin: 10px 0; }
        .subtitle { font-size: 16px; color: #555; }
        .report-body { margin: 30px 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .footer { text-align: center; margin-top: 40px; font-size: 12px; color: #777; }
        .signature { margin-top: 60px; border-top: 1px solid #000; width: 300px; text-align: center; margin-left: auto; }
        .page-break { page-break-after: always; }
        .btn-back { margin-top: 20px; padding: 10px 20px; background-color: #4680ff; color: white; border: none; border-radius: 5px; cursor: pointer; }
        .btn-back:hover { background-color: #3b6ad1; }
        @media print {
            .btn-back { display: none; }
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="../../public/img/logo.png" alt="Logo" class="logo">
        <div class="title">ESCOLA ALDA LARA</div>
        <div class="subtitle"><?= htmlspecialchars($titulo_relatorio) ?></div>
    </div>
    
    <div class="report-body">
        <?php if ($params['tipo'] === 'matriculas' || $params['tipo'] === 'transferencias'): ?>
            <table>
                <thead>
                    <tr>
                        <th>Nº Matrícula</th>
                        <th>Nome do Aluno</th>
                        <th>BI/Nº Documento</th>
                        <th>Curso</th>
                        <th>Turma</th>
                        <th>Classe</th>
                        <th>Status</th>
                        <th>Data Matrícula</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($dados_relatorio)): ?>
                        <tr><td colspan="8" style="text-align: center;">Nenhum dado encontrado.</td></tr>
                    <?php else: ?>
                        <?php foreach ($dados_relatorio as $matricula): ?>
                        <tr>
                            <td><?= htmlspecialchars($matricula['numero_matricula']) ?></td>
                            <td><?= htmlspecialchars($matricula['nome']) ?></td>
                            <td><?= htmlspecialchars($matricula['bi_numero'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($matricula['curso']) ?></td>
                            <td><?= htmlspecialchars($matricula['turma']) ?></td>
                            <td><?= htmlspecialchars($matricula['classe']) ?></td>
                            <td><?= ucfirst(htmlspecialchars($matricula['status_matricula'])) ?></td>
                            <td><?= $matricula['data_matricula'] ? date('d/m/Y', strtotime($matricula['data_matricula'])) : 'N/A' ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            
        <?php elseif ($params['tipo'] === 'notas'): ?>
            <?php if (empty($dados_relatorio)): ?>
                <p style="text-align: center;">Nenhum dado encontrado.</p>
            <?php else: ?>
                <?php foreach ($dados_relatorio as $aluno_id => $aluno): ?>
                    <h3><?= htmlspecialchars($aluno['nome']) ?></h3>
                    <?php foreach ($aluno['disciplinas'] as $disciplina => $notas): ?>
                        <h4><?= htmlspecialchars($disciplina) ?></h4>
                        <table>
                            <thead>
                                <tr>
                                    <th>Nota</th>
                                    <th>Tipo</th>
                                    <th>Descrição</th>
                                    <th>Data</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($notas as $nota): ?>
                                <tr>
                                    <td><?= number_format($nota['nota'], 2) ?></td>
                                    <td><?= ucfirst(str_replace("_", " ", htmlspecialchars($nota['tipo']))) ?></td>
                                    <td><?= htmlspecialchars($nota['descricao'] ?? 'N/A') ?></td>
                                    <td><?= $nota['data'] ? date('d/m/Y', strtotime($nota['data'])) : 'N/A' ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endforeach; ?>
                    <div class="page-break"></div>
                <?php endforeach; ?>
            <?php endif; ?>
            
        <?php elseif ($params['tipo'] === 'frequencia'): ?>
            <table>
                <thead>
                    <tr>
                        <th>Aluno</th>
                        <th>Disciplina</th>
                        <th>Presentes</th>
                        <th>Ausentes</th>
                        <th>Justificados</th>
                        <th>Total Aulas</th>
                        <th>% Presença</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($dados_relatorio)): ?>
                        <tr><td colspan="7" style="text-align: center;">Nenhum dado encontrado.</td></tr>
                    <?php else: ?>
                        <?php foreach ($dados_relatorio as $freq): ?>
                        <tr>
                            <td><?= htmlspecialchars($freq['aluno']) ?></td>
                            <td><?= htmlspecialchars($freq['disciplina']) ?></td>
                            <td><?= $freq['presentes'] ?></td>
                            <td><?= $freq['ausentes'] ?></td>
                            <td><?= $freq['justificados'] ?></td>
                            <td><?= $freq['total_aulas'] ?></td>
                            <td><?= $freq['total_aulas'] > 0 ? number_format(($freq['presentes'] / $freq['total_aulas']) * 100, 2) . '%' : '0%' ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            
        <?php elseif ($params['tipo'] === 'planos_ensino'): ?>
            <table>
                <thead>
                    <tr>
                        <th>Disciplina</th>
                        <th>Curso</th>
                        <th>Trimestre</th>
                        <th>Professor</th>
                        <th>Status</th>
                        <th>Data Submissão</th>
                        <th>Data Aprovação</th>
                        <th>Coordenador</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($dados_relatorio)): ?>
                        <tr><td colspan="8" style="text-align: center;">Nenhum dado encontrado.</td></tr>
                    <?php else: ?>
                        <?php foreach ($dados_relatorio as $plano): ?>
                        <tr>
                            <td><?= htmlspecialchars($plano['disciplina']) ?></td>
                            <td><?= htmlspecialchars($plano['curso']) ?></td>
                            <td><?= $plano['trimestre'] ?>º</td>
                            <td><?= htmlspecialchars($plano['professor'] ?? 'N/A') ?></td>
                            <td><?= ucfirst(htmlspecialchars($plano['status'])) ?></td>
                            <td><?= $plano['data_submissao'] ? date('d/m/Y', strtotime($plano['data_submissao'])) : 'N/A' ?></td>
                            <td><?= $plano['data_aprovacao'] ? date('d/m/Y', strtotime($plano['data_aprovacao'])) : 'N/A' ?></td>
                            <td><?= htmlspecialchars($plano['coordenador'] ?? 'N/A') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    
    <div class="footer">
        <p>Relatório gerado em: <?= date('d/m/Y H:i:s') ?></p>
        <p>Destinatários: <?= htmlspecialchars($destinatarios_formatados) ?></p>
    </div>
    
    <div class="signature">
        <p>Assinatura do Responsável</p>
    </div>
    
    <button class="btn-back" onclick="window.location.href='relatorios.php'">Voltar</button>
    
    <script>
        // Imprimir automaticamente ao carregar (opcional)
        window.onload = function() {
            // Descomente para ativar impressão automática
            // window.print();
        };
    </script>
</body>
</html>