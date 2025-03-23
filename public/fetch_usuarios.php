<?php
header('Content-Type: application/json');

// Conectar ao banco de dados
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "escola";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["error" => "Falha na conexão com o banco de dados"]));
}

// Capturar parâmetros do DataTables
$start = $_GET['start'] ?? 0;
$length = $_GET['length'] ?? 10;
$search = $_GET['search']['value'] ?? '';
$tipo_usuario = $_GET['tipo'] ?? '';
$turma = $_GET['turma'] ?? '';
$curso = $_GET['curso'] ?? '';

// Consulta base
$sql = "SELECT id_usuario, nome, email, tipo FROM usuarios WHERE 1";

// Filtro por tipo de usuário
if (!empty($tipo_usuario)) {
    $sql .= " AND tipo = '$tipo_usuario'";
}

// Filtro por turma (apenas para alunos)
if ($tipo_usuario == 'aluno' && !empty($turma)) {
    $sql .= " AND id_usuario IN (SELECT id_usuario FROM alunos WHERE id_turma = '$turma')";
}

// Filtro por curso (para alunos e coordenadores)
if (($tipo_usuario == 'aluno' || $tipo_usuario == 'coordenador') && !empty($curso)) {
    $sql .= " AND id_usuario IN (SELECT id_usuario FROM alunos WHERE id_curso = '$curso')";
}

// Filtro por pesquisa geral
if (!empty($search)) {
    $sql .= " AND (nome LIKE '%$search%' OR email LIKE '%$search%')";
}

// Ordenação e paginação
$sql .= " LIMIT $start, $length";
$result = $conn->query($sql);

// Montar resposta JSON
$dados = [];
while ($row = $result->fetch_assoc()) {
    $dados[] = [
        $row['nome'],
        $row['email'],
        ucfirst($row['tipo']),
        '<button class="btn btn-primary">Editar</button>'
    ];
}

// Contagem total de registros
$totalRecords = $conn->query("SELECT COUNT(*) AS total FROM usuarios")->fetch_assoc()['total'];

echo json_encode([
    "draw" => intval($_GET['draw'] ?? 1),
    "recordsTotal" => $totalRecords,
    "recordsFiltered" => count($dados),
    "data" => $dados
]);

$conn->close();
?>
