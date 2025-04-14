<?php
require_once '../../database/conexao.php';

if (isset($_GET['id'])) {
    $id_matricula = intval($_GET['id']);
    
    $query = "SELECT m.*, u.nome, u.bi_numero, c.nome as curso_nome, t.nome as turma_nome
              FROM matricula m
              JOIN aluno a ON m.aluno_id_aluno = a.id_aluno
              JOIN usuario u ON a.usuario_id_usuario = u.id_usuario
              LEFT JOIN curso c ON m.curso_id_curso = c.id_curso
              LEFT JOIN turma t ON m.turma_id_turma = t.id_turma
              WHERE m.id_matricula = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_matricula);
    $stmt->execute();
    $result = $stmt->get_result();
    $matricula = $result->fetch_assoc();

    // Gerar HTML do comprovante
    header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Comprovante de Matrícula</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
        .comprovante { max-width: 800px; margin: 0 auto; border: 2px solid #000; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; }
        .logo { max-width: 150px; }
        .title { font-size: 24px; font-weight: bold; margin: 10px 0; }
        .dados { margin: 20px 0; }
        .dados table { width: 100%; border-collapse: collapse; }
        .dados td { padding: 8px; border: 1px solid #ddd; }
        .dados td:first-child { font-weight: bold; width: 30%; }
        .footer { text-align: center; margin-top: 40px; font-size: 12px; }
        .assinatura { margin-top: 60px; border-top: 1px solid #000; width: 300px; text-align: center; margin-left: auto; }
    </style>
</head>
<body>
    <div class="comprovante">
        <div class="header">
            <img src="../public/img/logo.png" alt="Logo" class="logo">
            <div class="title">ESCOLA ALDA LARA</div>
            <div>Comprovante de Matrícula</div>
        </div>
        
        <div class="dados">
            <table>
                <tr>
                    <td>Número da Matrícula:</td>
                    <td><?= htmlspecialchars($matricula['numero_matricula']) ?></td>
                </tr>
                <tr>
                    <td>Nome do Aluno:</td>
                    <td><?= htmlspecialchars($matricula['nome']) ?></td>
                </tr>
                <tr>
                    <td>BI/Nº Documento:</td>
                    <td><?= htmlspecialchars($matricula['bi_numero']) ?></td>
                </tr>
                <tr>
                    <td>Curso:</td>
                    <td><?= htmlspecialchars($matricula['curso_nome'] ?? 'N/D') ?></td>
                </tr>
                <tr>
                    <td>Turma:</td>
                    <td><?= htmlspecialchars($matricula['turma_nome'] ?? 'N/D') ?></td>
                </tr>
                <tr>
                    <td>Classe:</td>
                    <td><?= htmlspecialchars($matricula['classe']) ?></td>
                </tr>
                <tr>
                    <td>Turno:</td>
                    <td><?= htmlspecialchars($matricula['turno']) ?></td>
                </tr>
                <tr>
                    <td>Ano Letivo:</td>
                    <td><?= htmlspecialchars($matricula['ano_letivo']) ?></td>
                </tr>
                <tr>
                    <td>Data da Matrícula:</td>
                    <td><?= date('d/m/Y', strtotime($matricula['data_matricula'])) ?></td>
                </tr>
                <tr>
                    <td>Status:</td>
                    <td><?= ucfirst($matricula['status_matricula']) ?></td>
                </tr>
            </table>
        </div>
        
        <div class="assinatura">
            <p>Assinatura do Responsável</p>
        </div>
        
        <div class="footer">
            <p>Este documento é válido apenas com o carimbo e assinatura da secretaria da escola</p>
            <p>Emitido em: <?= date('d/m/Y H:i:s') ?></p>
        </div>
    </div>
    
    <script>
        // Imprimir automaticamente ao carregar
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>
<?php
} else {
    die("ID da matrícula não fornecido");
}
?>