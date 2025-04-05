<?php
require_once "../auth/permissoes.php";
verificarPermissao(['secretaria']);

require_once "../config/conexao.php";

$id = isset($_GET['id']) ? intval($_GET['id']) : null;

if (!$id) {
    die('ID da matrícula não fornecido');
}

$stmt = $pdo->prepare("SELECT 
                      u.nome, u.bi_numero,
                      a.numero_matricula,
                      c.nome AS curso, t.nome AS turma,
                      m.data_matricula, m.ano_letivo, m.tipo_matricula
                      FROM matricula m
                      JOIN aluno a ON m.id_aluno = a.id_aluno
                      JOIN usuario u ON a.id_usuario = u.id_usuario
                      LEFT JOIN curso c ON m.id_curso = c.id_curso
                      LEFT JOIN turma t ON m.id_turma = t.id_turma
                      WHERE m.id_matricula = ?");

$stmt->execute([$id]);
$matricula = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$matricula) {
    die('Matrícula não encontrada');
}

// Gerar PDF ou HTML do comprovante
// Aqui você pode usar uma biblioteca como TCPDF, Dompdf ou FPDF
// Este é um exemplo básico em HTML

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Comprovante de Matrícula</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
        .comprovante { max-width: 800px; margin: 0 auto; border: 2px solid #000; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { margin: 0; }
        .header p { margin: 5px 0; }
        .dados { margin-bottom: 20px; }
        .assinaturas { display: flex; justify-content: space-between; margin-top: 50px; }
        .assinatura { width: 250px; border-top: 1px solid #000; text-align: center; padding-top: 5px; }
    </style>
</head>
<body>
    <div class="comprovante">
        <div class="header">
            <h1>ESCOLA ALDA LARA</h1>
            <p>Av. Hoji Ya Henda, Luanda - Angola</p>
            <p>Tel: 222 123 456 | Email: secretaria@escolaaldalara.edu.ao</p>
        </div>
        
        <h2 style="text-align: center;">COMPROVANTE DE MATRÍCULA</h2>
        
        <div class="dados">
            <p><strong>Nº Matrícula:</strong> <?= htmlspecialchars($matricula['numero_matricula']) ?></p>
            <p><strong>Aluno:</strong> <?= htmlspecialchars($matricula['nome']) ?></p>
            <p><strong>BI:</strong> <?= htmlspecialchars($matricula['bi_numero']) ?></p>
            <p><strong>Curso:</strong> <?= htmlspecialchars($matricula['curso'] ?? 'N/D') ?></p>
            <p><strong>Turma:</strong> <?= htmlspecialchars($matricula['turma'] ?? 'N/D') ?></p>
            <p><strong>Ano Letivo:</strong> <?= htmlspecialchars($matricula['ano_letivo']) ?></p>
            <p><strong>Tipo:</strong> <?= $matricula['tipo_matricula'] == 'regular' ? 'Matrícula Regular' : 'Transferência' ?></p>
            <p><strong>Data:</strong> <?= date('d/m/Y', strtotime($matricula['data_matricula'])) ?></p>
        </div>
        
        <div class="assinaturas">
            <div class="assinatura">
                <p>__________________________</p>
                <p>Secretária</p>
            </div>
            <div class="assinatura">
                <p>__________________________</p>
                <p>Responsável Financeiro</p>
            </div>
        </div>
    </div>
    
    <script>
        // Imprimir automaticamente ao carregar
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>