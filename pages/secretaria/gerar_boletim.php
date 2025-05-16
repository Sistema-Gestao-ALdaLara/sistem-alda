<?php
require_once '../../includes/common/permissoes.php';
verificarPermissao(['secretaria', 'diretor_geral', 'diretor_pedagogico']);
require_once '../../process/verificar_sessao.php';
require_once '../../database/conexao.php';

if (!isset($_GET['aluno_id'])) {
    die("ID do aluno não fornecido");
}

$aluno_id = intval($_GET['aluno_id']);

// Consulta para obter dados do aluno
$query_aluno = "SELECT a.*, u.nome as aluno_nome, u.bi_numero, u.foto_perfil,
                t.nome as turma_nome, t.classe, t.turno,
                c.nome as curso_nome
                FROM aluno a
                JOIN usuario u ON a.usuario_id_usuario = u.id_usuario
                JOIN turma t ON a.turma_id_turma = t.id_turma
                JOIN curso c ON t.curso_id_curso = c.id_curso
                WHERE a.id_aluno = ?";

$stmt_aluno = $conn->prepare($query_aluno);
$stmt_aluno->bind_param("i", $aluno_id);
$stmt_aluno->execute();
$result_aluno = $stmt_aluno->get_result();

if ($result_aluno->num_rows === 0) {
    die("Aluno não encontrado");
}

$aluno = $result_aluno->fetch_assoc();

// Obter disciplinas do curso
$query_disciplinas = "SELECT d.id_disciplina, d.nome as disciplina_nome
                      FROM disciplina d
                      WHERE d.curso_id_curso = ?
                      ORDER BY d.nome";
$stmt_disciplinas = $conn->prepare($query_disciplinas);
$stmt_disciplinas->bind_param("i", $aluno['turma_id_turma']);
$stmt_disciplinas->execute();
$result_disciplinas = $stmt_disciplinas->get_result();
$disciplinas = $result_disciplinas->fetch_all(MYSQLI_ASSOC);

// Obter notas do aluno
$notas = [];
$query_notas = "SELECT n.*, d.nome as disciplina_nome
                FROM nota n
                JOIN disciplina d ON n.disciplina_id_disciplina = d.id_disciplina
                WHERE n.aluno_id_aluno = ?
                ORDER BY n.trimestre, d.nome";
$stmt_notas = $conn->prepare($query_notas);
$stmt_notas->bind_param("i", $aluno_id);
$stmt_notas->execute();
$result_notas = $stmt_notas->get_result();

while ($nota = $result_notas->fetch_assoc()) {
    $notas[$nota['trimestre']][$nota['disciplina_id_disciplina']][] = $nota;
}

// Calcular médias ponderadas por trimestre
$medias_trimestrais = [];
$pesos_trimestrais = [1 => 0.3, 2 => 0.3, 3 => 0.4]; // Pesos dos trimestres (ajuste conforme necessário)

foreach ([1, 2, 3] as $trimestre) {
    foreach ($disciplinas as $disciplina) {
        $id_disciplina = $disciplina['id_disciplina'];
        $soma_ponderada = 0;
        $total_pesos = 0;
        
        if (isset($notas[$trimestre][$id_disciplina])) {
            foreach ($notas[$trimestre][$id_disciplina] as $nota) {
                $soma_ponderada += $nota['nota'] * $nota['peso'];
                $total_pesos += $nota['peso'];
            }
            
            if ($total_pesos > 0) {
                $media = $soma_ponderada / $total_pesos;
                // Arredondamento para 1 casa decimal (0.5 arredonda para cima)
                $medias_trimestrais[$trimestre][$id_disciplina] = round($media * 2) / 2;
            } else {
                $medias_trimestrais[$trimestre][$id_disciplina] = 0.0;
            }
        } else {
            $medias_trimestrais[$trimestre][$id_disciplina] = 0.0;
        }
    }
}

// Calcular médias finais por disciplina (considerando pesos dos trimestres)
$medias_finais_disciplinas = [];
foreach ($disciplinas as $disciplina) {
    $id_disciplina = $disciplina['id_disciplina'];
    $soma_final = 0;
    $pesos_utilizados = 0;
    
    foreach ([1, 2, 3] as $trimestre) {
        if (isset($medias_trimestrais[$trimestre][$id_disciplina])) {
            $soma_final += $medias_trimestrais[$trimestre][$id_disciplina] * $pesos_trimestrais[$trimestre];
            $pesos_utilizados += $pesos_trimestrais[$trimestre];
        }
    }
    
    if ($pesos_utilizados > 0) {
        $media_final = $soma_final / $pesos_utilizados;
        $medias_finais_disciplinas[$id_disciplina] = round($media_final * 2) / 2;
    } else {
        $medias_finais_disciplinas[$id_disciplina] = 0.0;
    }
}

// Calcular média geral do aluno
$soma_geral = 0;
$count_disciplinas = 0;

foreach ($medias_finais_disciplinas as $media) {
    $soma_geral += $media;
    $count_disciplinas++;
}

$media_geral = $count_disciplinas > 0 ? round(($soma_geral / $count_disciplinas) * 2) / 2 : 0.0;

// Determinar situação final
$situacao_final = 'APROVADO';
foreach ($medias_finais_disciplinas as $media) {
    if ($media < 10) {
        $situacao_final = 'REPROVADO';
        break;
    }
}

// Determinar situação
$situacao_final = ($media_geral >= 10) ? 'Aprovado' : 'Reprovado';

// Gerar HTML do boletim
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Boletim Escolar</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 16px; }
        .boletim { max-width: 700px; margin: 0 auto; border: 2px solid #000; padding: 16px; }
        .header { text-align: center; margin-bottom: 20px; }
        .logo { max-width: 120px; }
        .title { font-size: 18px; font-weight: bold; margin: 10px 0; }
        .subtitle { font-size: 14px; margin-bottom: 20px; }
        .dados { margin: 20px 0; }
        .dados table { width: 100%; border-collapse: collapse; }
        .dados td { padding: 8px; border: 1px solid #ddd; }
        .dados td:first-child { font-weight: bold; width: 30%; }
        .notas { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .notas th, .notas td { border: 1px solid #ddd; padding: 8px; text-align: center; }
        .notas th { background-color: #f2f2f2; }
        .disciplina { text-align: left; }
        .media-final { font-weight: bold; background-color: #f8f9fa; }
        .aprovado { color: #28a745; }
        .reprovado { color: #dc3545; }
        .assinaturas { display: flex; justify-content: space-between; margin-top: 50px; }
        .assinatura { width: 250px; border-top: 1px solid #000; text-align: center; padding-top: 5px; }
        .footer { text-align: center; margin-top: 20px; font-size: 12px; }
        .foto-aluno { width: 80px; height: 100px; object-fit: cover; border: 1px solid #ddd; float: right; }
    </style>
</head>
<body>
    <div class="boletim">
        <div class="header">
            <img src="../../public/libraries/assets/images/logo.png" alt="Logo da Escola" class="logo">
            <div class="title">ESCOLA ALDA LARA</div>
            <div>BOLETIM DE AVALIAÇÃO ESCOLAR</div>
        </div>
        
        <div class="dados">
            <?php if (!empty($aluno['foto_perfil'])): ?>
            <img src="../../uploads/perfis/<?= htmlspecialchars($aluno['foto_perfil']) ?>" 
                 alt="Foto do Aluno" class="foto-aluno">
            <?php endif; ?>
            
            <table>
                <tr>
                    <td>Nome do Aluno:</td>
                    <td><?= htmlspecialchars($aluno['aluno_nome']) ?></td>
                </tr>
                <tr>
                    <td>Nº de BI/Processo:</td>
                    <td><?= htmlspecialchars($aluno['bi_numero']) ?></td>
                </tr>
                <tr>
                    <td>Data de Nascimento:</td>
                    <td><?= date('d/m/Y', strtotime($aluno['data_nascimento'])) ?></td>
                </tr>
                <tr>
                    <td>Naturalidade:</td>
                    <td><?= htmlspecialchars($aluno['naturalidade']) ?></td>
                </tr>
                <tr>
                    <td>Nome do Encarregado:</td>
                    <td><?= htmlspecialchars($aluno['nome_encarregado']) ?></td>
                </tr>
                <tr>
                    <td>Contacto do Encarregado:</td>
                    <td><?= htmlspecialchars($aluno['contacto_encarregado']) ?></td>
                </tr>
                <tr>
                    <td>Ano Letivo:</td>
                    <td><?= $aluno['ano_letivo'] ?></td>
                </tr>
                <tr>
                    <td>Classe:</td>
                    <td><?= $aluno['classe'] ?></td>
                </tr>
                <tr>
                    <td>Turma:</td>
                    <td><?= htmlspecialchars($aluno['turma_nome']) ?></td>
                </tr>
                <tr>
                    <td>Curso:</td>
                    <td><?= htmlspecialchars($aluno['curso_nome']) ?></td>
                </tr>
                <tr>
                    <td>Turno:</td>
                    <td><?= htmlspecialchars($aluno['turno']) ?></td>
                </tr>
            </table>
        </div>
        
        <h3 style="text-align: center;">RESULTADOS DE AVALIAÇÃO</h3>
        
        <table class="notas">
            <thead>
                <tr>
                    <th rowspan="2">Disciplinas</th>
                    <th colspan="3">Médias Trimestrais</th>
                    <th rowspan="2">Média Final</th>
                </tr>
                <tr>
                    <th>1º Trim</th>
                    <th>2º Trim</th>
                    <th>3º Trim</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($disciplinas as $disciplina): 
                    $id_disciplina = $disciplina['id_disciplina'];
                    $media_final = number_format($medias_finais_disciplinas[$id_disciplina] ?? 0.0, 1);
                    
                    // Classes CSS para formatação
                    $classe_media_final = ($medias_finais_disciplinas[$id_disciplina] < 10) ? 'nota-baixa' : 'nota-alta';
                ?>
                <tr>
                    <td class="disciplina"><?= htmlspecialchars($disciplina['disciplina_nome']) ?></td>
                    
                    <!-- 1º Trimestre -->
                    <td class="<?= ($medias_trimestrais[1][$id_disciplina] < 10) ? 'nota-baixa' : '0' ?>">
                        <?= number_format($medias_trimestrais[1][$id_disciplina] ?? 0.0, 1) ?>
                    </td>
                    
                    <!-- 2º Trimestre -->
                    <td class="<?= ($medias_trimestrais[2][$id_disciplina] < 10) ? 'nota-baixa' : '0' ?>">
                        <?= number_format($medias_trimestrais[2][$id_disciplina] ?? 0.0, 1) ?>
                    </td>
                    
                    <!-- 3º Trimestre -->
                    <td class="<?= ($medias_trimestrais[3][$id_disciplina] < 10) ? 'nota-baixa' : '0' ?>">
                        <?= number_format($medias_trimestrais[3][$id_disciplina] ?? 0.0, 1) ?>
                    </td>
                    
                    <!-- Média Final da Disciplina -->
                    <td class="<?= $classe_media_final ?>">
                        <?= $media_final ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                
                <!-- Média Geral -->
                <tr class="media-final">
                    <td colspan="4" style="text-align: right; font-weight: bold;">MÉDIA GERAL:</td>
                    <td style="font-weight: bold;"><?= number_format($media_geral, 1) ?></td>
                </tr>
                
                <!-- Situação Final -->
                <tr>
                    <td colspan="4" style="text-align: right; font-weight: bold;">SITUAÇÃO FINAL:</td>
                    <td class="situacao-<?= strtolower($situacao_final) ?>">
                        <?= $situacao_final ?>
                    </td>
                </tr>
            </tbody>
        </table>
        
        <div style="margin: 14px 0;">
            <h4>OBSERVAÇÕES:</h4>
            <p style="border: 1px solid #ddd; padding: 10px; min-height: 60px;">
                <?= $situacao_final === 'Aprovado' ? 
                    'O aluno demonstrou bom desempenho ao longo do ano letivo.' : 
                    'O aluno não atingiu os objetivos mínimos estabelecidos para aprovação.' ?>
            </p>
        </div>
        
        <div class="assinaturas">
            <div class="assinatura">
                <p>O Professor Titular</p>
            </div>
            <div class="assinatura">
                <p>O Director da Escola</p>
            </div>
            <div class="assinatura">
                <p>O Encarregado de Educação</p>
            </div>
        </div>
        
        <div class="footer">
            <p>Emitido em: <?= date('d/m/Y H:i') ?> pelo Sistema de Gestão Escolar</p>
            <p>Este documento é válido apenas com o carimbo e assinatura do Director da Escola</p>
        </div>
    </div>
    
    <script>
        // Função para baixar como PDF (sem bibliotecas externas)
        function downloadAsPDF() {
            // Cria um novo window para impressão
            const printWindow = window.open('', '', 'width=800,height=600');
            printWindow.document.write('<html><head><title>Boletim Escolar</title>');
            printWindow.document.write('<style>' + document.querySelector('style').innerHTML + '</style>');
            printWindow.document.write('</head><body>');
            printWindow.document.write(document.querySelector('.boletim').outerHTML);
            printWindow.document.write('</body></html>');
            printWindow.document.close();
            
            // Espera o conteúdo carregar e imprime
            setTimeout(() => {
                printWindow.print();
            }, 500);
        }
        
        // Imprimir automaticamente ao carregar
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>