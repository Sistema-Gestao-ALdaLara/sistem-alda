<?php
require_once 'conexao.php';
session_start();

// Processar formulário de nova matrícula
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nova_matricula'])) {
    // Validar e sanitizar dados
    $nome = $conn->real_escape_string($_POST['nome']);
    $bi_numero = $conn->real_escape_string($_POST['bi_numero']);
    $email = $conn->real_escape_string($_POST['email']);
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
    $data_nascimento = $_POST['data_nascimento'];
    $genero = $_POST['genero'];
    $naturalidade = $conn->real_escape_string($_POST['naturalidade']);
    $nacionalidade = $conn->real_escape_string($_POST['nacionalidade']);
    $municipio = $conn->real_escape_string($_POST['municipio']);
    $nome_encarregado = $conn->real_escape_string($_POST['nome_encarregado']);
    $contacto_encarregado = $conn->real_escape_string($_POST['contacto_encarregado']);
    $id_curso = intval($_POST['id_curso']);
    $id_turma = intval($_POST['id_turma']);
    $ano_letivo = intval($_POST['ano_letivo']);
    $classe = $_POST['classe'];
    $turno = $_POST['turno'];
    
    // Iniciar transação
    $conn->begin_transaction();
    
    try {
        // 1. Criar usuário
        $stmt = $conn->prepare("INSERT INTO usuario 
            (nome, email, senha, bi_numero, tipo, status) 
            VALUES (?, ?, ?, ?, 'aluno', 'ativo')");
        $stmt->bind_param("ssss", $nome, $email, $senha, $bi_numero);
        $stmt->execute();
        $id_usuario = $conn->insert_id;
        
        // 2. Criar aluno
        $stmt = $conn->prepare("INSERT INTO aluno 
            (data_nascimento, genero, naturalidade, nacionalidade, municipio, 
             nome_encarregado, contacto_encarregado, usuario_id_usuario, 
             turma_id_turma, curso_id_curso) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssiii", $data_nascimento, $genero, $naturalidade, 
            $nacionalidade, $municipio, $nome_encarregado, $contacto_encarregado, 
            $id_usuario, $id_turma, $id_curso);
        $stmt->execute();
        $id_aluno = $conn->insert_id;
        
        // 3. Gerar número de matrícula automático
        $stmt = $conn->prepare("SELECT MAX(id_matricula) as ultimo_id FROM matricula WHERE ano_letivo = ?");
        $stmt->bind_param("i", $ano_letivo);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $sequencial = $row['ultimo_id'] ? $row['ultimo_id'] + 1 : 1;
        $numero_matricula = 'AL-' . $ano_letivo . '-' . str_pad($sequencial, 4, '0', STR_PAD_LEFT);
        
        // 4. Criar matrícula
        $stmt = $conn->prepare("INSERT INTO matricula 
        (ano_letivo, classe, turno, numero_matricula, data_matricula, 
         turma_id_turma, aluno_id_aluno, curso_id_curso, status_matricula, comprovativo_pagamento) 
        VALUES (?, ?, ?, ?, NOW(), ?, ?, ?, 'ativa', 'confirmado')");
        $stmt->bind_param("isssiii", 
            $ano_letivo, $classe, $turno, $numero_matricula, $id_turma, $id_aluno, $id_curso);
        $stmt->execute();
    
        
        $conn->commit();
        $_SESSION['sucesso'] = "Matrícula registrada com sucesso! Número: $numero_matricula";
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['erro'] = "Erro ao registrar matrícula: " . $e->getMessage();
    }
    
    header('Location: matriculas.php');
    exit();
}

// Filtros
$status = isset($_GET['status']) ? $_GET['status'] : 'ativa';
$id_curso = isset($_GET['id_curso']) ? intval($_GET['id_curso']) : null;
$ano_letivo = isset($_GET['ano_letivo']) ? intval($_GET['ano_letivo']) : date('Y');

// Obter matrículas com filtros
$query = "SELECT m.id_matricula, a.id_aluno, u.nome, u.bi_numero, 
          c.nome AS curso, t.nome AS turma, m.data_matricula, 
          m.ano_letivo, m.classe, m.turno, m.numero_matricula,
          m.status_matricula
          FROM matricula m
          JOIN aluno a ON m.aluno_id_aluno = a.id_aluno
          JOIN usuario u ON a.usuario_id_usuario = u.id_usuario
          LEFT JOIN curso c ON m.curso_id_curso = c.id_curso
          LEFT JOIN turma t ON m.turma_id_turma = t.id_turma
          WHERE m.ano_letivo = ?";

$params = [$ano_letivo];
$types = "i";

if ($status != 'todos') {
    $query .= " AND m.status_matricula = ?";
    $params[] = $status;
    $types .= "s";
}

if ($id_curso) {
    $query .= " AND m.curso_id_curso = ?";
    $params[] = $id_curso;
    $types .= "i";
}

$query .= " ORDER BY m.data_matricula DESC";

$stmt = $conn->prepare($query);

if ($params) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$matriculas = $result->fetch_all(MYSQLI_ASSOC);

// Obter cursos para filtros
$result_cursos = $conn->query("SELECT id_curso, nome FROM curso ORDER BY nome");
$cursos = $result_cursos->fetch_all(MYSQLI_ASSOC);

// Obter turmas para select
$turmas = [];
if ($result_turmas = $conn->query("SELECT id_turma, nome, curso_id_curso FROM turma")) {
    $turmas = $result_turmas->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <title>SECRETARIA - Gestão de Matrículas | Alda Lara</title>
    <!-- HTML5 Shim and Respond.js IE10 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 10]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
      <![endif]-->
    <!-- Meta -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="#">
    <meta name="keywords" content="Admin , Responsive, Landing, Bootstrap, App, Template, Mobile, iOS, Android, apple, creative app">
    <meta name="author" content="#">
    <!-- Favicon icon -->
    <link rel="icon" href="libraries\assets\images\favicon.ico" type="image/x-icon">
    <!-- Google font-->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600" rel="stylesheet">
    <!-- Required Fremwork -->
    <link rel="stylesheet" type="text/css" href="libraries\bower_components\bootstrap\css\bootstrap.min.css">
    <!-- feather Awesome -->
    <link rel="stylesheet" type="text/css" href="libraries\assets\icon\feather\css\feather.css">
    <!-- Style.css -->
    <link rel="stylesheet" type="text/css" href="libraries\assets\css\style.css">
    <link rel="stylesheet" type="text/css" href="libraries\assets\css\jquery.mCustomScrollbar.css">

    <style>
        .bg-img {
          width: 100%;
          height: auto;
          background-image: url('../public/img/bg.jpg');
          background-size: cover;
          background-position: center;
          background-repeat: no-repeat;
        }

        .table-custom {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(8px);
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
        }

        .table-custom th,
        .table-custom td {
            padding: 12px;
            color: #ffffff;
        }

        .table-custom thead {
            background: rgba(7, 200, 206, 0.55);
            color: white;
            font-weight: bold;
        }

        .table-custom tbody tr:hover {
            background: rgba(255, 255, 255, 0.3);
            transition: 0.3s;
        }

        /* Estilo específico para os cards que contêm tabelas */
        .card-table {
            background: rgba(19, 125, 171, 0.082);
            backdrop-filter: blur(10px);
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            color: white !important;
        }

        /* Ajuste no cabeçalho do card */
        .card-table .card-header {
            background: rgba(7, 200, 206, 0.836);
            color: white !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
        }

        /* Estilo da tabela dentro do card */
        .card-table .table {
            background: transparent;
        }
        
        .badge-ativa { background-color: #28a745; }
        .badge-cancelada { background-color: #dc3545; }
        .badge-trancada { background-color: #ffc107; color: #000; }
        
        .numero-matricula {
            font-family: monospace;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <!-- Pre-loader -->
    <div class="theme-loader">
        <div class="ball-scale">
            <div class='contain'>
                <div class="ring"><div class="frame"></div></div>
                <div class="ring"><div class="frame"></div></div>
                <div class="ring"><div class="frame"></div></div>
                <div class="ring"><div class="frame"></div></div>
                <div class="ring"><div class="frame"></div></div>
                <div class="ring"><div class="frame"></div></div>
                <div class="ring"><div class="frame"></div></div>
                <div class="ring"><div class="frame"></div></div>
                <div class="ring"><div class="frame"></div></div>
                <div class="ring"><div class="frame"></div></div>
            </div>
        </div>
    </div>

    <div id="pcoded" class="pcoded">
        <div class="pcoded-overlay-box"></div>
        <div class="pcoded-container navbar-wrapper">
            <?php include 'navbar.php'; ?>
            
            <div class="pcoded-main-container">
                <div class="pcoded-wrapper">
                    <?php include 'sidebar.php'; ?>
                    
                    <div class="pcoded-content">
                        <div class="pcoded-inner-content">
                            <div class="main-body bg-img">
                                <div class="page-wrapper">
                                    <div class="page-body">
                                        <!-- Mensagens de feedback -->
                                        <?php if (isset($_SESSION['sucesso'])): ?>
                                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                                            <?= $_SESSION['sucesso'] ?>
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <?php unset($_SESSION['sucesso']); endif; ?>
                                        
                                        <?php if (isset($_SESSION['erro'])): ?>
                                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                            <?= $_SESSION['erro'] ?>
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <?php unset($_SESSION['erro']); endif; ?>
                                        
                                        <div class="row">
                                            <div class="col-12 mt-4">
                                                <!-- Filtros -->
                                                <div class="card card-table mb-3">
                                                    <div class="card-header">
                                                        <h5>Filtrar Matrículas</h5>
                                                    </div>
                                                    <div class="card-block">
                                                        <form id="formFiltros" method="GET" action="">
                                                            <div class="row">
                                                                <div class="col-md-3">
                                                                    <div class="form-group">
                                                                        <label for="status">Status</label>
                                                                        <select class="form-control" id="status" name="status">
                                                                            <option value="ativa" <?= $status == 'ativa' ? 'selected' : '' ?>>Ativas</option>
                                                                            <option value="cancelada" <?= $status == 'cancelada' ? 'selected' : '' ?>>Canceladas</option>
                                                                            <option value="trancada" <?= $status == 'trancada' ? 'selected' : '' ?>>Trancadas</option>
                                                                            <option value="todos" <?= $status == 'todos' ? 'selected' : '' ?>>Todos</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <div class="form-group">
                                                                        <label for="id_curso">Curso</label>
                                                                        <select class="form-control" id="id_curso" name="id_curso">
                                                                            <option value="">Todos os cursos</option>
                                                                            <?php foreach ($cursos as $curso): ?>
                                                                            <option value="<?= $curso['id_curso'] ?>" <?= $id_curso == $curso['id_curso'] ? 'selected' : '' ?>>
                                                                                <?= htmlspecialchars($curso['nome']) ?>
                                                                            </option>
                                                                            <?php endforeach; ?>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <div class="form-group">
                                                                        <label for="ano_letivo">Ano Letivo</label>
                                                                        <select class="form-control" id="ano_letivo" name="ano_letivo">
                                                                            <option value="<?= date('Y')-1 ?>"><?= date('Y')-1 ?></option>
                                                                            <option value="<?= date('Y') ?>" selected><?= date('Y') ?></option>
                                                                            <option value="<?= date('Y')+1 ?>"><?= date('Y')+1 ?></option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-3 d-flex align-items-end">
                                                                    <button type="submit" class="btn btn-primary btn-block">
                                                                        <i class="feather icon-filter"></i> Filtrar
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>

                                                <!-- Tabela de Matrículas -->
                                                <div class="card card-table">
                                                    <div class="card-header d-flex justify-content-between align-items-center">
                                                        <h5 class="text-white mb-0">Gestão de Matrículas - Ano Letivo <?= $ano_letivo ?></h5>
                                                        <div>
                                                            <button class="btn btn-primary" data-toggle="modal" data-target="#modalNovaMatricula">
                                                                <i class="feather icon-plus"></i> Nova Matrícula
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="card-block">
                                                        <div class="table-responsive">
                                                            <table class="table table-custom">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Nº Matrícula</th>
                                                                        <th>Aluno</th>
                                                                        <th>BI</th>
                                                                        <th>Curso</th>
                                                                        <th>Turma</th>
                                                                        <th>Classe</th>
                                                                        <th>Turno</th>
                                                                        <th>Data</th>
                                                                        <th>Status</th>
                                                                        <th>Ações</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php if (empty($matriculas)): ?>
                                                                    <tr>
                                                                        <td colspan="10" class="text-center">Nenhuma matrícula encontrada com os filtros selecionados</td>
                                                                    </tr>
                                                                    <?php else: ?>
                                                                    <?php foreach ($matriculas as $matricula): ?>
                                                                    <tr>
                                                                        <td class="numero-matricula"><?= htmlspecialchars($matricula['numero_matricula']) ?></td>
                                                                        <td><?= htmlspecialchars($matricula['nome']) ?></td>
                                                                        <td><?= htmlspecialchars($matricula['bi_numero']) ?></td>
                                                                        <td><?= htmlspecialchars($matricula['curso'] ?? 'N/D') ?></td>
                                                                        <td><?= htmlspecialchars($matricula['turma'] ?? 'N/D') ?></td>
                                                                        <td><?= htmlspecialchars($matricula['classe'] ?? 'N/D') ?></td>
                                                                        <td><?= htmlspecialchars($matricula['turno'] ?? 'N/D') ?></td>
                                                                        <td><?= date('d/m/Y', strtotime($matricula['data_matricula'])) ?></td>
                                                                        <td>
                                                                            <?php 
                                                                            $badge_class = '';
                                                                            if ($matricula['status_matricula'] == 'ativa') $badge_class = 'badge-ativa';
                                                                            elseif ($matricula['status_matricula'] == 'cancelada') $badge_class = 'badge-cancelada';
                                                                            else $badge_class = 'badge-trancada';
                                                                            ?>
                                                                            <span class="badge <?= $badge_class ?>">
                                                                                <?= ucfirst($matricula['status_matricula']) ?>
                                                                            </span>
                                                                        </td>
                                                                        <td>
                                                                            <div class="btn-group btn-group-sm">
                                                                                <button class="btn btn-info btn-sm" onclick="editarMatricula(<?= $matricula['id_matricula'] ?>)">
                                                                                    <i class="feather icon-edit"></i>
                                                                                </button>
                                                                                <button class="btn btn-secondary btn-sm" onclick="emitirComprovante(<?= $matricula['id_matricula'] ?>)">
                                                                                    <i class="feather icon-printer"></i>
                                                                                </button>
                                                                                <button class="btn btn-danger btn-sm" onclick="cancelarMatricula(<?= $matricula['id_matricula'] ?>)">
                                                                                    <i class="feather icon-x"></i>
                                                                                </button>
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                    <?php endforeach; ?>
                                                                    <?php endif; ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Nova Matrícula -->
    <div class="modal fade" id="modalNovaMatricula" tabindex="-1" role="dialog" aria-labelledby="modalNovaMatriculaLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalNovaMatriculaLabel">Nova Matrícula</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="formMatricula" method="POST" action="">
                        <input type="hidden" name="nova_matricula" value="1">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nome">Nome Completo *</label>
                                    <input type="text" class="form-control" id="nome" name="nome" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="bi_numero">Nº do BI *</label>
                                    <input type="text" class="form-control" id="bi_numero" name="bi_numero" 
                                        pattern="[0-9]{9}[A-Z]{2}[0-9]{3}" required>
                                    <small class="form-text text-muted">Formato: 123456789LA123</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">Email *</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="senha">Senha *</label>
                                    <input type="password" class="form-control" id="senha" name="senha" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="data_nascimento">Data de Nascimento *</label>
                                    <input type="date" class="form-control" id="data_nascimento" name="data_nascimento" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="genero">Gênero *</label>
                                    <select class="form-control" id="genero" name="genero" required>
                                        <option value="Masculino">Masculino</option>
                                        <option value="Feminino">Feminino</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="naturalidade">Naturalidade *</label>
                                    <input type="text" class="form-control" id="naturalidade" name="naturalidade" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="nacionalidade">Nacionalidade *</label>
                                    <input type="text" class="form-control" id="nacionalidade" name="nacionalidade" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="municipio">Município *</label>
                                    <input type="text" class="form-control" id="municipio" name="municipio" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="ano_letivo_matricula">Ano Letivo *</label>
                                    <select class="form-control" id="ano_letivo_matricula" name="ano_letivo" required>
                                        <option value="<?= date('Y') ?>" selected><?= date('Y') ?></option>
                                        <option value="<?= date('Y')+1 ?>"><?= date('Y')+1 ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="nome_encarregado">Nome do Encarregado *</label>
                                    <input type="text" class="form-control" id="nome_encarregado" name="nome_encarregado" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="contacto_encarregado">Contacto do Encarregado *</label>
                                    <input type="text" class="form-control" id="contacto_encarregado" name="contacto_encarregado" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="curso_matricula">Curso *</label>
                                    <select class="form-control" id="curso_matricula" name="id_curso" required>
                                        <option value="">Selecione um curso</option>
                                        <?php foreach ($cursos as $curso): ?>
                                        <option value="<?= $curso['id_curso'] ?>"><?= htmlspecialchars($curso['nome']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="turma_matricula">Turma *</label>
                                    <select class="form-control" id="turma_matricula" name="id_turma" required>
                                        <option value="">Selecione um curso primeiro</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="classe_matricula">Classe *</label>
                                    <select class="form-control" id="classe_matricula" name="classe" required>
                                        <option value="10ª">10ª Classe</option>
                                        <option value="11ª">11ª Classe</option>
                                        <option value="12ª">12ª Classe</option>
                                        <option value="13ª">13ª Classe</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="turno_matricula">Turno *</label>
                                    <select class="form-control" id="turno_matricula" name="turno" required>
                                        <option value="Manhã">Manhã</option>
                                        <option value="Tarde">Tarde</option>
                                        <option value="Noite">Noite</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Registrar Matrícula</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="libraries/bower_components/jquery/js/jquery.min.js"></script>
    <script src="libraries/bower_components/jquery-ui/js/jquery-ui.min.js"></script>
    <script src="libraries/bower_components/popper.js/js/popper.min.js"></script>
    <script src="libraries/bower_components/bootstrap/js/bootstrap.min.js"></script>
    <script src="libraries/bower_components/jquery-slimscroll/js/jquery.slimscroll.js"></script>
    <script src="libraries/bower_components/modernizr/js/modernizr.js"></script>
    <script src="libraries/assets/js/jquery.mCustomScrollbar.concat.min.js"></script>
    <script src="libraries/assets/js/pcoded.min.js"></script>
    <script src="libraries/assets/js/vartical-layout.min.js"></script>
    <script src="libraries/assets/js/script.min.js"></script>

    <script>
    // Funções do Sistema
    function carregarTurmas(id_curso, elemento) {
        if(id_curso) {
            $.ajax({
                url: 'getTurma.php',
                method: 'GET',
                data: { id_curso: id_curso },
                success: function(response) {
                    $(elemento).html(response);
                },
                error: function() {
                    $(elemento).html('<option value="">Erro ao carregar turmas</option>');
                }
            });
        } else {
            $(elemento).html('<option value="">Selecione um curso primeiro</option>');
        }
    }
    
    function editarMatricula(id) {
        $.ajax({
            url: 'get_matricula.php',
            method: 'GET',
            data: { id: id },
            dataType: 'json',
            success: function(matricula) {
                // Implementar lógica para edição
                alert('Editar matrícula ID: ' + id);
            },
            error: function() {
                alert('Erro ao carregar dados da matrícula');
            }
        });
    }
    
    function cancelarMatricula(id) {
        if(confirm('Tem certeza que deseja cancelar esta matrícula?\nEsta ação não pode ser desfeita.')) {
            $.ajax({
                url: 'cancelar_matricula.php',
                method: 'POST',
                data: { id: id },
                dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        alert(response.message);
                        location.reload();
                    } else {
                        alert('Erro: ' + response.message);
                    }
                },
                error: function() {
                    alert('Erro na comunicação com o servidor');
                }
            });
        }
    }
    
    function emitirComprovante(id) {
        window.open('comprovante_matricula.php?id=' + id, '_blank');
    }
    
    // Carregar turmas quando um curso é selecionado
    $(document).ready(function() {
        $('#curso_matricula').change(function() {
            carregarTurmas($(this).val(), '#turma_matricula');
        });
        
        // Validação do formulário
        $('#formMatricula').submit(function(e) {
            // Validar BI
            const bi = $('#bi_numero').val();
            const regex = /^[0-9]{9}[A-Z]{2}[0-9]{3}$/;
            if(!regex.test(bi)) {
                alert('Número de BI inválido. Formato correto: 123456789LA123');
                e.preventDefault();
                return false;
            }
            
            // Outras validações podem ser adicionadas aqui
            return true;
        });
    });
    </script>
</body>
</html>