<?php
header('Content-Type: application/json');
require 'conexao.php'; // Arquivo para conexão com o banco

$idAluno = $_GET['idAluno'] ?? '';

if (empty($idAluno)) {
    echo json_encode(["error" => "ID do aluno não informado"]);
    exit;
}

// Consulta para buscar o histórico acadêmico (notas e frequência)
$sql = "SELECT d.nome AS disciplina, n.nota, n.data_lancamento, f.frequencia 
        FROM nota n 
        JOIN disciplina d ON n.id_disciplina = d.id_disciplina
        LEFT JOIN frequencia_aluno f ON n.id_aluno = f.id_aluno AND n.id_disciplina = f.id_disciplina
        WHERE n.id_aluno = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idAluno);
$stmt->execute();
$result = $stmt->get_result();

$dados = [];
while ($row = $result->fetch_assoc()) {
    $dados[] = [
        "disciplina" => $row['disciplina'],
        "nota" => $row['nota'],
        "data" => $row['data_lancamento'],
        "frequencia" => $row['frequencia']
    ];
}

echo json_encode(["data" => $dados]);

$stmt->close();
$conn->close();
