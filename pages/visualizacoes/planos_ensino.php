<?php
require_once '../../includes/common/permissoes.php';
verificarPermissao(['diretor_geral', 'diretor_pedagogico', 'coordenador']);
require_once '../../process/verificar_sessao.php';
require_once '../../database/conexao.php';

// Obter parâmetros do relatório
$ano_letivo = $_GET['ano_letivo'] ?? date('Y');
$trimestre = $_GET['trimestre'] ?? null;
$id_curso = $_GET['id_curso'] ?? null;
$status = $_GET['status'] ?? null;

// Construir consulta SQL
$query = "SELECT 
            p.*, 
            d.nome as disciplina_nome,
            c.nome as curso_nome,
            u.nome as professor_nome,
            u2.nome as diretor_nome
          FROM plano_ensino p
          JOIN disciplina d ON p.id_disciplina = d.id_disciplina
          JOIN curso c ON d.curso_id_curso = c.id_curso
          LEFT JOIN professor pr ON p.id_professor = pr.id_professor
          LEFT JOIN usuario u ON pr.usuario_id_usuario = u.id_usuario
          LEFT JOIN usuario u2 ON p.id_diretor_aprovador = u2.id_usuario
          WHERE p.ano_letivo = ?";

$params = [$ano_letivo];
$types = "i";

if ($trimestre && $trimestre != 'anual') {
    $query .= " AND p.trimestre = ?";
    $params[] = $trimestre;
    $types .= "i";
}

if ($id_curso) {
    $query .= " AND d.curso_id_curso = ?";
    $params[] = $id_curso;
    $types .= "i";
}

if ($status) {
    $query .= " AND p.status = ?";
    $params[] = $status;
    $types .= "s";
}

$query .= " ORDER BY p.status, d.nome";

// Preparar e executar a consulta
$stmt = $conn->prepare($query);
if ($types) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$planos = $result->fetch_all(MYSQLI_ASSOC);

// Estatísticas de status
$estatisticas = [
    'rascunho' => 0,
    'submetido' => 0,
    'aprovado' => 0,
    'rejeitado' => 0,
    'total' => 0
];

foreach ($planos as $plano) {
    $estatisticas[$plano['status']]++;
    $estatisticas['total']++;
}

$title = "Relatório de Planos de Ensino";
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
        .status-rascunho {
            background-color: #fff3cd;
        }
        .status-submetido {
            background-color: #cce5ff;
        }
        .status-aprovado {
            background-color: #d4edda;
        }
        .status-rejeitado {
            background-color: #f8d7da;
        }
        .chart-container {
            margin: 30px 0;
            height: 300px;
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
            .chart-container {
                height: 250px;
            }
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container-fluid">
        <div class="report-header">
            <div class="report-title">Relatório de Planos de Ensino</div>
            <div class="report-filters">
                Ano Letivo: <?= $ano_letivo ?> | 
                <?= $trimestre ? "Trimestre: " . ($trimestre == 'anual' ? 'Anual' : $trimestre . 'º') . " | " : "" ?>
                <?= $id_curso ? "Curso: " . htmlspecialchars($planos[0]['curso_nome'] ?? '') . " | " : "" ?>
                <?= $status ? "Status: " . ucfirst($status) : "" ?>
            </div>
        </div>
        
        <!-- Estatísticas -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5>Resumo por Status</h5>
                        <ul>
                            <li>Total: <?= $estatisticas['total'] ?></li>
                            <li>Aprovados: <?= $estatisticas['aprovado'] ?></li>
                            <li>Submetidos: <?= $estatisticas['submetido'] ?></li>
                            <li>Rascunhos: <?= $estatisticas['rascunho'] ?></li>
                            <li>Rejeitados: <?= $estatisticas['rejeitado'] ?></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="chart-container">
                    <canvas id="chartStatus"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Tabela de Planos -->
        <table class="report-table">
            <thead>
                <tr>
                    <th>Título</th>
                    <th>Disciplina</th>
                    <th>Curso</th>
                    <th>Professor</th>
                    <th>Trimestre</th>
                    <th>Status</th>
                    <th>Data Submissão</th>
                    <th>Data Aprovação</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($planos as $plano): ?>
                <tr class="status-<?= $plano['status'] ?>">
                    <td><?= htmlspecialchars($plano['titulo']) ?></td>
                    <td><?= htmlspecialchars($plano['disciplina_nome']) ?></td>
                    <td><?= htmlspecialchars($plano['curso_nome']) ?></td>
                    <td><?= htmlspecialchars($plano['professor_nome'] ?? 'N/A') ?></td>
                    <td><?= $plano['trimestre'] == 'anual' ? 'Anual' : $plano['trimestre'] . 'º' ?></td>
                    <td><?= ucfirst($plano['status']) ?></td>
                    <td><?= $plano['data_submissao'] ? date('d/m/Y H:i', strtotime($plano['data_submissao'])) : 'N/A' ?></td>
                    <td><?= $plano['data_aprovacao'] ? date('d/m/Y H:i', strtotime($plano['data_aprovacao'])) : 'N/A' ?></td>
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
        // Gráfico de status dos planos
        const ctxStatus = document.getElementById('chartStatus').getContext('2d');
        
        const chartStatus = new Chart(ctxStatus, {
            type: 'pie',
            data: {
                labels: ['Aprovados', 'Submetidos', 'Rascunhos', 'Rejeitados'],
                datasets: [{
                    data: [
                        <?= $estatisticas['aprovado'] ?>,
                        <?= $estatisticas['submetido'] ?>,
                        <?= $estatisticas['rascunho'] ?>,
                        <?= $estatisticas['rejeitado'] ?>
                    ],
                    backgroundColor: [
                        '#28a745',
                        '#007bff',
                        '#ffc107',
                        '#dc3545'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Distribuição por Status'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    </script>

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