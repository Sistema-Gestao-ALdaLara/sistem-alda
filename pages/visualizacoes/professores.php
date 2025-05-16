<?php
require_once '../../includes/common/permissoes.php';
verificarPermissao(['diretor_geral', 'diretor_pedagogico', 'coordenador']);
require_once '../../process/verificar_sessao.php';
require_once '../../database/conexao.php';

// Obter parâmetros do relatório
$id_curso = $_GET['id_curso'] ?? null;
$status = $_GET['status'] ?? 'ativo';

// Construir consulta SQL
$query = "SELECT 
            p.*, 
            u.nome, 
            u.email, 
            u.status as usuario_status,
            c.nome as curso_nome,
            COUNT(ptd.disciplina_id_disciplina) as num_disciplinas,
            COUNT(ptr.turma_id_turma) as num_turmas
          FROM professor p
          JOIN usuario u ON p.usuario_id_usuario = u.id_usuario
          JOIN curso c ON p.curso_id_curso = c.id_curso
          LEFT JOIN professor_tem_disciplina ptd ON p.id_professor = ptd.professor_id_professor
          LEFT JOIN professor_tem_turma ptr ON p.id_professor = ptr.professor_id_professor
          WHERE u.status = ?";

$params = [$status];
$types = "s";

if ($id_curso) {
    $query .= " AND p.curso_id_curso = ?";
    $params[] = $id_curso;
    $types .= "i";
}

$query .= " GROUP BY p.id_professor
            ORDER BY u.nome";

// Preparar e executar a consulta
$stmt = $conn->prepare($query);
if ($types) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$professores = $result->fetch_all(MYSQLI_ASSOC);

// Consulta para histórico
$query_historico = "SELECT 
                    h.*, 
                    d.nome as disciplina_nome,
                    t.nome as turma_nome
                  FROM historico_professor h
                  LEFT JOIN disciplina d ON h.disciplina_id_disciplina = d.id_disciplina
                  LEFT JOIN turma t ON h.turma_id_turma = t.id_turma
                  WHERE h.professor_id_professor = ?
                  ORDER BY h.data_inicio DESC";

$stmt_historico = $conn->prepare($query_historico);

$title = "Relatório de Professores";
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
        .historico-row {
            background-color: #f9f9f9;
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
            .historico-row {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="report-header">
            <div class="report-title">Relatório de Professores</div>
            <div class="report-filters">
                Status: <?= ucfirst($status) ?> | 
                <?= $id_curso ? "Curso: " . htmlspecialchars($professores[0]['curso_nome'] ?? '') : "Todos os Cursos" ?>
            </div>
        </div>
        
        <!-- Tabela de Professores -->
        <table class="report-table">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Curso</th>
                    <th>Disciplinas</th>
                    <th>Turmas</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($professores as $professor): ?>
                <tr>
                    <td><?= htmlspecialchars($professor['nome']) ?></td>
                    <td><?= htmlspecialchars($professor['email']) ?></td>
                    <td><?= htmlspecialchars($professor['curso_nome']) ?></td>
                    <td><?= $professor['num_disciplinas'] ?></td>
                    <td><?= $professor['num_turmas'] ?></td>
                    <td><?= ucfirst($professor['usuario_status']) ?></td>
                </tr>
                
                <!-- Linha de histórico (expandível) -->
                <tr class="historico-row">
                    <td colspan="6">
                        <strong>Histórico:</strong>
                        <?php
                            $stmt_historico->bind_param("i", $professor['id_professor']);
                            $stmt_historico->execute();
                            $historico = $stmt_historico->get_result()->fetch_all(MYSQLI_ASSOC);
                            
                            if (empty($historico)) {
                                echo "<p>Nenhum registro histórico encontrado.</p>";
                            } else {
                                echo "<ul>";
                                foreach ($historico as $reg) {
                                    echo "<li>";
                                    echo "<strong>" . date('d/m/Y', strtotime($reg['data_inicio'])) . "</strong> a ";
                                    echo $reg['data_fim'] ? "<strong>" . date('d/m/Y', strtotime($reg['data_fim'])) . "</strong>" : "atual";
                                    echo " - " . ($reg['cargo'] ?? 'Professor');
                                    
                                    if ($reg['disciplina_nome']) {
                                        echo " - Disciplina: " . htmlspecialchars($reg['disciplina_nome']);
                                    }
                                    
                                    if ($reg['turma_nome']) {
                                        echo " - Turma: " . htmlspecialchars($reg['turma_nome']);
                                    }
                                    
                                    if ($reg['descricao']) {
                                        echo "<br><em>" . htmlspecialchars($reg['descricao']) . "</em>";
                                    }
                                    
                                    echo "</li>";
                                }
                                echo "</ul>";
                            }
                        ?>
                    </td>
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