<?php
require_once '../../includes/common/permissoes.php';
verificarPermissao(['secretaria']);
require_once '../../process/verificar_sessao.php';
require_once '../../database/conexao.php';

$title = "Secretaria";

// Processar formulário de matrícula (nova ou edição)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar e sanitizar dados comuns
    $nome = $conn->real_escape_string($_POST['nome']);
    $bi_numero = $conn->real_escape_string($_POST['bi_numero']);
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
    $classe = $conn->real_escape_string($_POST['classe']);
    
    // Verificar se é nova matrícula ou edição
    $isNovaMatricula = isset($_POST['nova_matricula']) && $_POST['nova_matricula'] == '1';

    // Iniciar transação
    $conn->begin_transaction();
    
    try {
        if ($isNovaMatricula) {
            // Processamento para NOVA matrícula
            $email = $conn->real_escape_string($_POST['email']);
            $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
            
            // Verificar se o e-mail já existe (apenas para nova matrícula)
            $stmt = $conn->prepare("SELECT id_usuario FROM usuario WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                throw new Exception("Este e-mail já está em uso.");
            }

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
            
            // 4. Criar matrícula (incluindo a classe)
            $stmt = $conn->prepare("INSERT INTO matricula 
                (ano_letivo, classe, numero_matricula, data_matricula, 
                turma_id_turma, aluno_id_aluno, curso_id_curso, status_matricula, comprovativo_pagamento) 
                VALUES (?, ?, ?, NOW(), ?, ?, ?, 'ativa', 'confirmado')");
            $stmt->bind_param("issiii", 
                $ano_letivo, $classe, $numero_matricula, $id_turma, $id_aluno, $id_curso);
            $stmt->execute();
            
            $_SESSION['sucesso'] = "Matrícula registrada com sucesso! Número: $numero_matricula";
        } else {
            // Processamento para EDIÇÃO de matrícula
            $id_matricula = intval($_POST['id_matricula']);
            $id_aluno = intval($_POST['id_aluno']);
            $id_usuario = intval($_POST['id_usuario']);
            
            // 1. Atualizar usuário (SEM email e senha)
            $stmt = $conn->prepare("UPDATE usuario SET nome = ?, bi_numero = ? WHERE id_usuario = ?");
            $stmt->bind_param("ssi", $nome, $bi_numero, $id_usuario);
            $stmt->execute();
            
            // 2. Atualizar aluno
            $stmt = $conn->prepare("UPDATE aluno SET 
                data_nascimento = ?, genero = ?, naturalidade = ?, nacionalidade = ?, municipio = ?,
                nome_encarregado = ?, contacto_encarregado = ?, turma_id_turma = ?, curso_id_curso = ?
                WHERE id_aluno = ?");
            $stmt->bind_param("sssssssiii", $data_nascimento, $genero, $naturalidade, 
                $nacionalidade, $municipio, $nome_encarregado, $contacto_encarregado, 
                $id_turma, $id_curso, $id_aluno);
            $stmt->execute();
            
            // 3. Atualizar matrícula (incluindo a classe)
            $stmt = $conn->prepare("UPDATE matricula SET 
                curso_id_curso = ?, turma_id_turma = ?, classe = ?, ano_letivo = ?
                WHERE id_matricula = ?");
            $stmt->bind_param("iisi", $id_curso, $id_turma, $classe, $ano_letivo, $id_matricula);
            $stmt->execute();
            
            $_SESSION['sucesso'] = "Matrícula atualizada com sucesso!";
        }
        
        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['erro'] = "Erro ao " . ($isNovaMatricula ? "registrar" : "atualizar") . " matrícula: " . $e->getMessage();
    }
    
    header('Location: matricula.php');
    exit();
}

// Filtros
$status = isset($_GET['status']) ? $_GET['status'] : 'ativa';
$id_curso = isset($_GET['id_curso']) ? intval($_GET['id_curso']) : null;
$ano_letivo = isset($_GET['ano_letivo']) ? intval($_GET['ano_letivo']) : date('Y');

// Obter matrículas com filtros (incluindo classe)
$query = "SELECT m.id_matricula, a.id_aluno, u.nome, u.bi_numero, 
        c.nome AS curso, t.nome AS turma, t.turno, m.data_matricula, 
        m.ano_letivo, m.classe, m.numero_matricula,
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

// Obter turmas para select (incluindo classe)
$turmas = [];
if ($result_turmas = $conn->query("SELECT id_turma, nome, classe, turno, curso_id_curso FROM turma")) {
    $turmas = $result_turmas->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="pt">
<?php require_once '../../includes/common//head.php'; ?>

<body>
    <?php require_once '../../includes/common/preloader.php'; ?>

    <div id="pcoded" class="pcoded">
        <div class="pcoded-overlay-box"></div>
        <div class="pcoded-container navbar-wrapper">

            <?php require_once '../../includes/secretaria/navbar.php'; ?>

            <!--sidebar-->
            <div class="pcoded-main-container">
                <div class="pcoded-wrapper">
                    <?php require_once '../../includes/secretaria/sidebar.php'; ?>

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
                                                    <!-- Filtros Atualizados -->
                                                    <div class="card card-table mb-3">
                                                        <div class="card-header bg-primary text-white">
                                                            <h5 class="mb-0"><i class="feather icon-filter"></i> Filtrar Matrículas</h5>
                                                        </div>
                                                        <div class="card-body">
                                                            <form id="formFiltros" method="GET" action="">
                                                                <div class="row">
                                                                    <!-- Status -->
                                                                    <div class="col-md-3 mb-3">
                                                                        <label for="status" class="form-label">Status da Matrícula</label>
                                                                        <select class="form-select" id="status" name="status">
                                                                            <option value="ativa" <?= $status == 'ativa' ? 'selected' : '' ?>>Ativas</option>
                                                                            <option value="cancelada" <?= $status == 'cancelada' ? 'selected' : '' ?>>Canceladas</option>
                                                                            <option value="trancada" <?= $status == 'trancada' ? 'selected' : '' ?>>Trancadas</option>
                                                                            <option value="todos" <?= $status == 'todos' ? 'selected' : '' ?>>Todos os Status</option>
                                                                        </select>
                                                                    </div>
                                                                    
                                                                    <!-- Curso -->
                                                                    <div class="col-md-3 mb-3">
                                                                        <label for="id_curso" class="form-label">Curso</label>
                                                                        <select class="form-select" id="id_curso" name="id_curso">
                                                                            <option value="">Todos os Cursos</option>
                                                                            <?php foreach ($cursos as $curso): ?>
                                                                            <option value="<?= $curso['id_curso'] ?>" <?= $id_curso == $curso['id_curso'] ? 'selected' : '' ?>>
                                                                                <?= htmlspecialchars($curso['nome']) ?>
                                                                            </option>
                                                                            <?php endforeach; ?>
                                                                        </select>
                                                                    </div>
                                                                    
                                                                    <!-- Ano Letivo -->
                                                                    <div class="col-md-3 mb-3">
                                                                        <label for="ano_letivo" class="form-label">Ano Letivo</label>
                                                                        <select class="form-select" id="ano_letivo" name="ano_letivo">
                                                                            <option value="<?= date('Y')-1 ?>"><?= date('Y')-1 ?></option>
                                                                            <option value="<?= date('Y') ?>" selected><?= date('Y') ?></option>
                                                                            <option value="<?= date('Y')+1 ?>"><?= date('Y')+1 ?></option>
                                                                        </select>
                                                                    </div>
                                                                    
                                                                    <!-- Botão Filtrar -->
                                                                    <div class="col-md-3 mb-3 d-flex align-items-end">
                                                                        <div class="d-grid gap-2">
                                                                            <button type="submit" class="btn btn-primary">
                                                                                <i class="feather icon-filter"></i> Aplicar Filtros
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                
                                                                <!-- Filtros Adicionais (Opcional) - Bootstrap 4.0.0 -->
                                                                <div class="row">
                                                                    <div class="col-12">
                                                                        <a class="btn btn-sm btn-link text-decoration-none" data-toggle="collapse" href="#filtrosAvancados" role="button" aria-expanded="false" aria-controls="filtrosAvancados">
                                                                            <i class="feather icon-plus"></i> Filtros Adicionais
                                                                        </a>
                                                                        <div class="collapse mt-2" id="filtrosAvancados">
                                                                            <div class="row">
                                                                                <div class="col-md-4 mb-3">
                                                                                    <label for="classe" class="form-label">Classe</label>
                                                                                    <select class="form-control" id="classe" name="classe">
                                                                                        <option value="">Todas as Classes</option>
                                                                                        <option value="10ª">10ª Classe</option>
                                                                                        <option value="11ª">11ª Classe</option>
                                                                                        <option value="12ª">12ª Classe</option>
                                                                                        <option value="13ª">13ª Classe</option>
                                                                                    </select>
                                                                                </div>
                                                                                <div class="col-md-4 mb-3">
                                                                                    <label for="turno" class="form-label">Turno</label>
                                                                                    <select class="form-control" id="turno" name="turno">
                                                                                        <option value="">Todos os Turnos</option>
                                                                                        <option value="Manhã">Manhã</option>
                                                                                        <option value="Tarde">Tarde</option>
                                                                                        <option value="Noite">Noite</option>
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>

                                                    <!-- Tabela de Matrículas -->
                                                    <!-- Tabela de Matrículas Atualizada -->
                                                    <div class="card card-table">
                                                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                                            <h5 class="mb-0">Gestão de Matrículas - Ano Letivo <?= $ano_letivo ?></h5>
                                                            <div>
                                                                <button class="btn btn-light" data-toggle="modal" data-target="#modalMatricula">
                                                                    <i class="feather icon-plus"></i> Nova Matrícula
                                                                </button>
                                                            </div>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="table-responsive">
                                                                <table class="table table-hover">
                                                                    <thead class="table-dark">
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
                                                                            <th class="text-end">Ações</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <?php if (empty($matriculas)): ?>
                                                                        <tr>
                                                                            <td colspan="10" class="text-center py-4">
                                                                                <div class="d-flex flex-column align-items-center">
                                                                                    <i class="feather icon-search mb-2" style="font-size: 2rem;"></i>
                                                                                    <p class="mb-0">Nenhuma matrícula encontrada com os filtros selecionados</p>
                                                                                    <a href="?status=todos" class="btn btn-sm btn-link mt-2">Limpar filtros</a>
                                                                                </div>
                                                                            </td>
                                                                        </tr>
                                                                        <?php else: ?>
                                                                        <?php foreach ($matriculas as $matricula): ?>
                                                                        <tr>
                                                                            <td class="fw-bold"><?= htmlspecialchars($matricula['numero_matricula']) ?></td>
                                                                            <td><?= htmlspecialchars($matricula['nome']) ?></td>
                                                                            <td><?= htmlspecialchars($matricula['bi_numero']) ?></td>
                                                                            <td><?= htmlspecialchars($matricula['curso'] ?? 'N/D') ?></td>
                                                                            <td><?= htmlspecialchars($matricula['turma'] ?? 'N/D') ?></td>
                                                                            <td><?= htmlspecialchars($matricula['classe'] ?? 'N/D') ?></td>
                                                                            <td><?= htmlspecialchars($matricula['turno'] ?? 'N/D') ?></td>
                                                                            <td><?= date('d/m/Y', strtotime($matricula['data_matricula'])) ?></td>
                                                                            <td>
                                                                                <?php 
                                                                                $badge_class = [
                                                                                    'ativa' => 'bg-success',
                                                                                    'cancelada' => 'bg-danger',
                                                                                    'trancada' => 'bg-warning'
                                                                                ][$matricula['status_matricula'] ?? 'ativa'];
                                                                                ?>
                                                                                <span class="badge <?= $badge_class ?>">
                                                                                    <?= ucfirst($matricula['status_matricula']) ?>
                                                                                </span>
                                                                            </td>
                                                                            <td class="text-end">
                                                                                <div class="btn-group btn-group-sm" role="group">
                                                                                    <button class="btn btn-outline-primary" onclick="editarMatricula(<?= $matricula['id_matricula'] ?>)">
                                                                                        <i class="feather icon-edit"></i>
                                                                                    </button>
                                                                                    <button class="btn btn-outline-secondary" onclick="emitirComprovante(<?= $matricula['id_matricula'] ?>)">
                                                                                        <i class="feather icon-printer"></i>
                                                                                    </button>
                                                                                    <?php if ($matricula['status_matricula'] == 'ativa'): ?>
                                                                                    <button class="btn btn-outline-danger" onclick="cancelarMatricula(<?= $matricula['id_matricula'] ?>)">
                                                                                        <i class="feather icon-x"></i>
                                                                                    </button>
                                                                                    <?php else: ?>
                                                                                    <button class="btn btn-outline-success" onclick="reativarMatricula(<?= $matricula['id_matricula'] ?>)">
                                                                                        <i class="feather icon-check"></i>
                                                                                    </button>
                                                                                    <?php endif; ?>
                                                                                </div>
                                                                            </td>
                                                                        </tr>
                                                                        <?php endforeach; ?>
                                                                        <?php endif; ?>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                            
                                                            <!-- Paginação (opcional) -->
                                                            <div class="d-flex justify-content-between align-items-center mt-3">
                                                                <div class="text-muted">
                                                                    Mostrando <?= count($matriculas) ?> registros
                                                                </div>
                                                                <nav aria-label="Page navigation">
                                                                    <ul class="pagination pagination-sm">
                                                                        <li class="page-item disabled">
                                                                            <a class="page-link" href="#" tabindex="-1">Anterior</a>
                                                                        </li>
                                                                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                                                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                                                                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                                                                        <li class="page-item">
                                                                            <a class="page-link" href="#">Próxima</a>
                                                                        </li>
                                                                    </ul>
                                                                </nav>
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

        <!-- Modal Matrícula (serve para novo e edição) -->
        <div class="modal fade" id="modalMatricula" tabindex="-1" role="dialog" aria-labelledby="modalMatriculaLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="modalMatriculaLabel">Nova Matrícula</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="formMatricula" method="POST" action="">
                            <input type="hidden" name="id_matricula" id="id_matricula">
                            <input type="hidden" name="id_aluno" id="id_aluno">
                            <input type="hidden" name="id_usuario" id="id_usuario">
                            <input type="hidden" name="nova_matricula" id="nova_matricula" value="1">
                            
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
                                        <select class="form-control" id="turno_matricula" name="turno" disabled>
                                            <option value="">Selecione a turma primeiro</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                                <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-primary" id="btnSubmit">Registrar Matrícula</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scripts -->
        <?php require_once '../../includes/common/js_imports.php'; ?>

        <script>
                // Funções do Sistema
                function carregarTurmas(id_curso, elemento) {
                if(id_curso) {
                    $.ajax({
                        url: '../../process/consultas/getTurma.php',
                        method: 'GET',
                        data: { id_curso: id_curso },
                        success: function(response) {
                            $(elemento).html(response);
                            // Habilitar o campo de turno
                            $('#turno_matricula').prop('disabled', false);
                        },
                        error: function() {
                            $(elemento).html('<option value="">Erro ao carregar turmas</option>');
                        }
                    });
                } else {
                    $(elemento).html('<option value="">Selecione um curso primeiro</option>');
                    $('#turno_matricula').prop('disabled', true);
                }
            }

            // Adicionar este evento para atualizar o turno quando selecionar uma turma
            $(document).on('change', '#turma_matricula', function() {
                const turno = $(this).find(':selected').data('turno');
                $('#turno_matricula').val(turno);
            });
            
            function editarMatricula(id) {
                $.ajax({
                    url: '../../process/consultasget_matricula.php',
                    method: 'GET',
                    data: { id: id },
                    dataType: 'json',
                    success: function(response) {
                        if(response.success) {
                            const matricula = response.data;
                            
                            // Atualiza o modal para modo de edição
                            $('#modalMatriculaLabel').text('Editar Matrícula');
                            $('#btnSubmit').text('Salvar Alterações');
                            $('#nova_matricula').val('0');
                            
                            // Preenche os campos do formulário
                            $('#id_matricula').val(matricula.id_matricula);
                            $('#id_aluno').val(matricula.id_aluno);
                            $('#id_usuario').val(matricula.usuario_id_usuario);
                            $('#nome').val(matricula.nome);
                            $('#bi_numero').val(matricula.bi_numero);
                            $('#email').val(matricula.email);
                            $('#data_nascimento').val(matricula.data_nascimento);
                            $('#genero').val(matricula.genero);
                            $('#naturalidade').val(matricula.naturalidade);
                            $('#nacionalidade').val(matricula.nacionalidade);
                            $('#municipio').val(matricula.municipio);
                            $('#nome_encarregado').val(matricula.nome_encarregado);
                            $('#contacto_encarregado').val(matricula.contacto_encarregado);
                            $('#ano_letivo_matricula').val(matricula.ano_letivo);
                            $('#curso_matricula').val(matricula.curso_id_curso).trigger('change');
                            
                            // Aguarda o carregamento das turmas
                            setTimeout(() => {
                                $('#turma_matricula').val(matricula.turma_id_turma);
                            }, 300);
                            
                            $('#classe_matricula').val(matricula.classe);
                            $('#turno_matricula').val(matricula.turno);
                            
                            // Exibe o modal
                            $('#modalMatricula').modal('show');
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function() {
                        alert('Erro ao carregar dados da matrícula');
                    }
                });
            }
            
            function cancelarMatricula(id) {
                if(confirm('Tem certeza que deseja cancelar esta matrícula?\nEsta ação não pode ser desfeita.')) {
                    $.ajax({
                        url: '../../action/secretaria/cancelar_matricula.php',
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
                window.open('../../process/secretaria/comprovante_matricula.php?id=' + id, '_blank');
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