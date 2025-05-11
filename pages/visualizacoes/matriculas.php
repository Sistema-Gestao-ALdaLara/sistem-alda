<?php
require_once '../../includes/common/permissoes.php';
verificarPermissao(['secretaria', 'diretor_geral', 'diretor_pedagogico', 'coordenador']);
require_once '../../process/verificar_sessao.php';
require_once '../../database/conexao.php';

// Obter parâmetros do relatório
$ano_letivo = $_GET['ano_letivo'] ?? date('Y');
$trimestre = $_GET['trimestre'] ?? null;
$id_curso = $_GET['id_curso'] ?? null;
$id_turma = $_GET['id_turma'] ?? null;
$ordenacao = $_GET['ordenacao'] ?? 'nome_asc';

// Construir consulta SQL
$query = "SELECT m.*, a.nome as aluno_nome, t.nome as turma_nome, c.nome as curso_nome 
          FROM matricula m
          JOIN aluno al ON m.aluno_id_aluno = al.id_aluno
          JOIN usuario a ON al.usuario_id_usuario = a.id_usuario
          JOIN turma t ON m.turma_id_turma = t.id_turma
          JOIN curso c ON t.curso_id_curso = c.id_curso
          WHERE m.ano_letivo = ?";

$params = [$ano_letivo];
$types = "i";

if ($trimestre) {
    $query .= " AND m.trimestre = ?";
    $params[] = $trimestre;
    $types .= "i";
}

if ($id_curso) {
    $query .= " AND t.curso_id_curso = ?";
    $params[] = $id_curso;
    $types .= "i";
}

if ($id_turma) {
    $query .= " AND m.turma_id_turma = ?";
    $params[] = $id_turma;
    $types .= "i";
}

// Adicionar ordenação
switch ($ordenacao) {
    case 'nome_desc':
        $query .= " ORDER BY a.nome DESC";
        break;
    case 'data_asc':
        $query .= " ORDER BY m.data_matricula ASC";
        break;
    case 'data_desc':
        $query .= " ORDER BY m.data_matricula DESC";
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
$matriculas = $result->fetch_all(MYSQLI_ASSOC);

$title = "Relatório de Matrículas";
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
        #pdfUploadForm {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="report-header">
            <div class="report-title">Relatório de Matrículas</div>
            <div class="report-filters">
                Ano Letivo: <?= $ano_letivo ?> | 
                <?= $trimestre ? "Trimestre: $trimestre | " : "" ?>
                <?= $id_curso ? "Curso: " . htmlspecialchars($matriculas[0]['curso_nome']) . " | " : "" ?>
                <?= $id_turma ? "Turma: " . htmlspecialchars($matriculas[0]['turma_nome']) : "" ?>
            </div>
        </div>
        
        <table class="report-table">
            <thead>
                <tr>
                    <th>Nº Matrícula</th>
                    <th>Aluno</th>
                    <th>Turma</th>
                    <th>Curso</th>
                    <th>Data Matrícula</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($matriculas as $matricula): ?>
                <tr>
                    <td><?= htmlspecialchars($matricula['numero_matricula']) ?></td>
                    <td><?= htmlspecialchars($matricula['aluno_nome']) ?></td>
                    <td><?= htmlspecialchars($matricula['turma_nome']) ?></td>
                    <td><?= htmlspecialchars($matricula['curso_nome']) ?></td>
                    <td><?= date('d/m/Y', strtotime($matricula['data_matricula'])) ?></td>
                    <td><?= ucfirst($matricula['status_matricula']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="print-actions no-print">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="feather icon-printer"></i> Imprimir/Salvar PDF
            </button>
            <a href="../index.php" class="btn btn-secondary">
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