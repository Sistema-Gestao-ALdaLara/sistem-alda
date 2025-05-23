<?php
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
$mes = $_GET['mes'] ?? null;

// Construir consulta SQL
$query = "SELECT 
            f.*, 
            a.nome as aluno_nome, 
            t.nome as turma_nome, 
            c.nome as curso_nome,
            d.nome as disciplina_nome,
            u.nome as professor_nome
          FROM frequencia_aluno f
          JOIN aluno al ON f.aluno_id_aluno = al.id_aluno
          JOIN usuario a ON al.usuario_id_usuario = a.id_usuario
          JOIN disciplina d ON f.disciplina_id_disciplina = d.id_disciplina
          JOIN turma t ON f.turma_id_turma = t.id_turma
          JOIN curso c ON t.curso_id_curso = c.id_curso
          LEFT JOIN professor_tem_disciplina pd ON d.id_disciplina = pd.disciplina_id_disciplina
          LEFT JOIN professor p ON pd.professor_id_professor = p.id_professor
          LEFT JOIN usuario u ON p.usuario_id_usuario = u.id_usuario
          WHERE al.ano_letivo = ?";

$params = [$ano_letivo];
$types = "i";

if ($trimestre) {
    $query .= " AND QUARTER(f.data_aula) = ?";
    $params[] = $trimestre;
    $types .= "i";
}

if ($id_curso) {
    $query .= " AND t.curso_id_curso = ?";
    $params[] = $id_curso;
    $types .= "i";
}

if ($id_turma) {
    $query .= " AND f.turma_id_turma = ?";
    $params[] = $id_turma;
    $types .= "i";
}

if ($id_disciplina) {
    $query .= " AND f.disciplina_id_disciplina = ?";
    $params[] = $id_disciplina;
    $types .= "i";
}

if ($mes) {
    $query .= " AND MONTH(f.data_aula) = ?";
    $params[] = $mes;
    $types .= "i";
}

$query .= " ORDER BY f.data_aula DESC, a.nome ASC";

// Preparar e executar a consulta
$stmt = $conn->prepare($query);
if ($types) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$frequencias = $result->fetch_all(MYSQLI_ASSOC);

// Calcular estatísticas de frequência
$estatisticas = [];
foreach ($frequencias as $freq) {
    $key = $freq['aluno_id_aluno'] . '_' . $freq['disciplina_id_disciplina'];
    if (!isset($estatisticas[$key])) {
        $estatisticas[$key] = [
            'aluno_nome' => $freq['aluno_nome'],
            'disciplina_nome' => $freq['disciplina_nome'],
            'total' => 0,
            'presentes' => 0,
            'ausentes' => 0,
            'justificados' => 0
        ];
    }
    
    $estatisticas[$key]['total']++;
    switch ($freq['presenca']) {
        case 'presente':
            $estatisticas[$key]['presentes']++;
            break;
        case 'ausente':
            $estatisticas[$key]['ausentes']++;
            break;
        case 'justificado':
            $estatisticas[$key]['justificados']++;
            break;
    }
}

$title = "Relatório de Frequência";
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
        .presente {
            color: green;
        }
        .ausente {
            color: red;
        }
        .justificado {
            color: orange;
        }
        .stats-row {
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
            <div class="report-title">Relatório de Frequência</div>
            <div class="report-filters">
                Ano Letivo: <?= $ano_letivo ?> | 
                <?= $trimestre ? "Trimestre: $trimestre | " : "" ?>
                <?= $mes ? "Mês: $mes | " : "" ?>
                <?= $id_curso ? "Curso: " . htmlspecialchars($frequencias[0]['curso_nome'] ?? '') . " | " : "" ?>
                <?= $id_turma ? "Turma: " . htmlspecialchars($frequencias[0]['turma_nome'] ?? '') . " | " : "" ?>
                <?= $id_disciplina ? "Disciplina: " . htmlspecialchars($frequencias[0]['disciplina_nome'] ?? '') : "" ?>
            </div>
        </div>
        
        <!-- Tabela de Frequência Detalhada -->
        <table class="report-table">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Aluno</th>
                    <th>Turma</th>
                    <th>Disciplina</th>
                    <th>Professor</th>
                    <th>Presença</th>
                    <th>Tipo Aula</th>
                    <th>Observação</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($frequencias as $freq): ?>
                <tr>
                    <td><?= date('d/m/Y', strtotime($freq['data_aula'])) ?></td>
                    <td><?= htmlspecialchars($freq['aluno_nome']) ?></td>
                    <td><?= htmlspecialchars($freq['turma_nome']) ?></td>
                    <td><?= htmlspecialchars($freq['disciplina_nome']) ?></td>
                    <td><?= htmlspecialchars($freq['professor_nome'] ?? 'N/A') ?></td>
                    <td class="<?= $freq['presenca'] ?>"><?= ucfirst($freq['presenca']) ?></td>
                    <td><?= ucfirst(str_replace('_', ' ', $freq['tipo_aula'])) ?></td>
                    <td><?= htmlspecialchars($freq['observacao'] ?? '') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <!-- Tabela de Estatísticas -->
        <h4>Estatísticas de Frequência</h4>
        <table class="report-table">
            <thead>
                <tr>
                    <th>Aluno</th>
                    <th>Disciplina</th>
                    <th>Total Aulas</th>
                    <th>Presentes</th>
                    <th>Ausentes</th>
                    <th>Justificados</th>
                    <th>% Presença</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($estatisticas as $stats): ?>
                <tr class="stats-row">
                    <td><?= htmlspecialchars($stats['aluno_nome']) ?></td>
                    <td><?= htmlspecialchars($stats['disciplina_nome']) ?></td>
                    <td><?= $stats['total'] ?></td>
                    <td><?= $stats['presentes'] ?></td>
                    <td><?= $stats['ausentes'] ?></td>
                    <td><?= $stats['justificados'] ?></td>
                    <td><?= $stats['total'] > 0 ? number_format(($stats['presentes'] + $stats['justificados']) / $stats['total'] * 100, 2) . '%' : '0%' ?></td>
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
            <!-- Opção para enviar para destinatários -->
            <button class="btn btn-success" data-toggle="modal" data-target="#enviarModal">
                <i class="feather icon-send"></i> Enviar para Destinatários
            </button>
        </div>
    </div>
    
    <!-- Modal para enviar relatório -->
    <div class="modal fade" id="enviarModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Enviar Relatório</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="alertDownload" class="alert alert-warning">
                        <i class="feather icon-alert-triangle"></i> Por favor, primeiro baixe o relatório usando o botão "Imprimir/Salvar PDF" antes de enviar.
                    </div>
                    
                    <form id="formEnviarRelatorio" method="POST" action="enviar_relatorio.php" enctype="multipart/form-data" style="display: none;">
                        <input type="hidden" name="tipo" value="matriculas">
                        <input type="hidden" name="ano_letivo" value="<?= $ano_letivo ?>">
                        <input type="hidden" name="trimestre" value="<?= $trimestre ?>">
                        <input type="hidden" name="id_curso" value="<?= $id_curso ?>">
                        <input type="hidden" name="id_turma" value="<?= $id_turma ?>">
                        <input type="hidden" name="ordenacao" value="<?= $ordenacao ?>">
                        
                        <div class="form-group">
                            <label for="arquivo_pdf">Arquivo PDF do Relatório *</label>
                            <input type="file" class="form-control" id="arquivo_pdf" name="arquivo_pdf" accept=".pdf" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Destinatários *</label>
                            <div class="checkbox-fade fade-in-primary">
                                <label>
                                    <input type="checkbox" name="destinatarios[]" value="tipo_usuario:diretor_geral">
                                    <span class="cr"><i class="cr-icon feather icon-check"></i></span>
                                    <span>Diretores Gerais</span>
                                </label>
                            </div>
                            <div class="checkbox-fade fade-in-primary">
                                <label>
                                    <input type="checkbox" name="destinatarios[]" value="tipo_usuario:diretor_pedagogico">
                                    <span class="cr"><i class="cr-icon feather icon-check"></i></span>
                                    <span>Diretores Pedagógicos</span>
                                </label>
                            </div>
                            <div class="checkbox-fade fade-in-primary">
                                <label>
                                    <input type="checkbox" name="destinatarios[]" value="tipo_usuario:coordenador">
                                    <span class="cr"><i class="cr-icon feather icon-check"></i></span>
                                    <span>Coordenadores</span>
                                </label>
                            </div>
                            <div class="checkbox-fade fade-in-primary">
                                <label>
                                    <input type="checkbox" name="destinatarios[]" value="tipo_usuario:professor">
                                    <span class="cr"><i class="cr-icon feather icon-check"></i></span>
                                    <span>Professores</span>
                                </label>
                            </div>
                            <div class="checkbox-fade fade-in-primary">
                                <label>
                                    <input type="checkbox" name="destinatarios[]" value="tipo_usuario:secretaria">
                                    <span class="cr"><i class="cr-icon feather icon-check"></i></span>
                                    <span>Secretaria</span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="mensagem">Mensagem (opcional)</label>
                            <textarea class="form-control" id="mensagem" name="mensagem" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" id="btnEnviarRelatorio" class="btn btn-primary" disabled>
                        Enviar Relatório
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php require_once '../../includes/common/js_imports.php'; ?>

    <script>
        // Verificar se o usuário já tentou baixar o relatório
        let tentouBaixar = false;
        
        // Quando clicar no botão de imprimir/salvar PDF
        document.querySelector('button[onclick="window.print()"]').addEventListener('click', function() {
            tentouBaixar = true;
            
            // Mostrar o formulário de envio e esconder o alerta
            document.getElementById('alertDownload').style.display = 'none';
            document.getElementById('formEnviarRelatorio').style.display = 'block';
            document.getElementById('btnEnviarRelatorio').disabled = false;
            document.getElementById('btnEnviarRelatorio').setAttribute('onclick', "document.getElementById('formEnviarRelatorio').submit()");
        });
        
        // Quando abrir o modal, verificar se já tentou baixar
        $('#enviarModal').on('show.bs.modal', function() {
            if (tentouBaixar) {
                document.getElementById('alertDownload').style.display = 'none';
                document.getElementById('formEnviarRelatorio').style.display = 'block';
                document.getElementById('btnEnviarRelatorio').disabled = false;
                document.getElementById('btnEnviarRelatorio').setAttribute('onclick', "document.getElementById('formEnviarRelatorio').submit()");
            } else {
                document.getElementById('alertDownload').style.display = 'block';
                document.getElementById('formEnviarRelatorio').style.display = 'none';
                document.getElementById('btnEnviarRelatorio').disabled = true;
                document.getElementById('btnEnviarRelatorio').removeAttribute('onclick');
            }
        });
    </script>
</body>
</html>