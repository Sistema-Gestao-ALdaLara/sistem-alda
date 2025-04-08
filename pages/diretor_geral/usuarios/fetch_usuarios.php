<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');
require 'conexao.php'; // Arquivo para conexão com o banco

// Capturar parâmetros do DataTables
$start = $_GET['start'] ?? 0;
$length = $_GET['length'] ?? 10;
$search = $_GET['search']['value'] ?? '';
$tipo_usuario = $_GET['tipo'] ?? '';
$turma = $_GET['turma'] ?? '';
$curso = $_GET['curso'] ?? '';
$data_filtered_count = 0;

// Consulta base
$sql = "SELECT id_usuario, nome, email, tipo FROM usuarios WHERE 1";
$data_total_count_query = "SELECT COUNT(*) AS total FROM usuario WHERE 1";
$params = [];
$types = "";

// Filtro por tipo de usuário
if (!empty($tipo_usuario)) {
    $sql .= " AND tipo = ?";
    $data_total_count_query .= " AND tipo = ?";
    $params[] = $tipo_usuario;
    $types .= "s";
}

// Filtro por turma (apenas para alunos)
if ($tipo_usuario == 'aluno' && !empty($turma)) {
    $sql .= " AND id_usuario IN (SELECT id_usuario FROM aluno WHERE id_turma = ?)";
    $data_total_count_query .= " AND id_usuario IN (SELECT id_usuario FROM aluno WHERE id_turma = ?)";
    $params[] = $turma;
    $types .= "s";
}

// Filtro por curso (para alunos, coordenadores e professores)
if (($tipo_usuario == 'aluno' || $tipo_usuario == 'coordenador' || $tipo_usuario == 'professor') && !empty($curso)) {
    $sql .= " AND id_usuario IN (SELECT id_usuario FROM usuarios WHERE id_curso = ?)";
    $data_total_count_query .= " AND id_usuario IN (SELECT id_usuario FROM usuario WHERE id_curso = ?)";
    $params[] = $curso;
    $types .= "s";
}

// Filtro por pesquisa geral
if (!empty($search)) {
    $sql .= " AND (nome LIKE ? OR email LIKE ?)";
    $data_total_count_query .= " AND (nome LIKE ? OR email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= "ss";
}

// Contagem de registros filtrados
$stmt = $conn->prepare($data_total_count_query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$data_filtered_count = $result->fetch_assoc()['total'];
$stmt->close();

// Ordenação e paginação
$sql .= " LIMIT ?, ?";
$params[] = $start;
$params[] = $length;
$types .= "ii";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$dados = [];
while ($row = $result->fetch_assoc()) {
    $editar_link = "editar-" . ($row['tipo'] == 'professor' ? "professor" : "aluno") . ".php?id=" . $row['id_usuario'];
    $dados[] = [
        $row['id_usuario'],
        $row['nome'],
        $row['email'],
        ucfirst($row['tipo']),
        '<a href="' . $editar_link . '" class="btn btn-primary">Editar</a>'
    ];
}
$stmt->close();

// Contagem total de registros
$totalRecords = $conn->query("SELECT COUNT(*) AS total FROM usuarios")->fetch_assoc()['total'];

// Retornar resposta JSON
echo json_encode([
    "draw" => intval($_GET['draw'] ?? 1),
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => $data_filtered_count,
    "data" => $dados
]);

$conn->close();
?>