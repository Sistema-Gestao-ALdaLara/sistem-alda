<?php
require_once __DIR__ . '/../../includes/common/permissoes.php';
verificarPermissao(['aluno']);
require_once __DIR__ . '/../../process/verificar_sessao.php';
require_once __DIR__ . '/../../database/conexao.php';

// Função para obter informações do aluno
function getAlunoInfo($conn, $id_usuario) {
    $query = "SELECT a.id_aluno, t.nome as turma, c.nome as curso, c.id_curso as curso_id 
              FROM aluno a
              JOIN turma t ON a.turma_id_turma = t.id_turma
              JOIN curso c ON a.curso_id_curso = c.id_curso
              WHERE a.usuario_id_usuario = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

// Função para obter disciplinas do aluno
function getDisciplinasAluno($conn, $curso_id) {
    $query = "SELECT d.id_disciplina, d.nome as disciplina, 
                     u.nome as professor, u.foto_perfil,
                     p.id_professor
              FROM disciplina d
              LEFT JOIN professor p ON d.professor_id_professor = p.id_professor
              LEFT JOIN usuario u ON p.usuario_id_usuario = u.id_usuario
              WHERE d.curso_id_curso = ?
              ORDER BY d.nome";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $curso_id);
    $stmt->execute();
    
    return $stmt->get_result();
}

// Obter dados do aluno e disciplinas
$aluno = getAlunoInfo($conn, $_SESSION['id_usuario']);
$disciplinas = $aluno ? getDisciplinasAluno($conn, $aluno['curso_id']) : null;
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <?php require_once __DIR__ . '/../../includes/common/head.php'; ?>
    <title>Minhas Disciplinas</title>
    <style>
        .card-disciplina {
            transition: transform 0.3s;
            margin-bottom: 20px;
            height: 100%;
        }
        .card-disciplina:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .professor-img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .empty-message {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }
        .disciplina-info {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .professor-nome {
            margin-top: 10px;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <?php require_once __DIR__ . '/../../includes/common/preloader.php'; ?>

    <div id="pcoded" class="pcoded">
        <div class="pcoded-overlay-box"></div>
        <div class="pcoded-container navbar-wrapper">
            <?php require_once __DIR__ . '/../../includes/aluno/navbar.php'; ?>

            <div class="pcoded-main-container">
                <div class="pcoded-wrapper">
                    <?php require_once __DIR__ . '/../../includes/aluno/sidebar.php'; ?>
                    
                    <div class="pcoded-content">
                        <div class="pcoded-inner-content">
                            <div class="main-body bg-img">
                                <div class="page-wrapper">
                                    <div class="page-body">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="card card-table">
                                                    <div class="card-header d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                                                        <h5 class="mb-2 mb-md-0"><i class="feather icon-book"></i> Minhas Disciplinas</h5>
                                                        <?php if ($aluno): ?>
                                                            <div class="d-flex flex-wrap justify-content-center justify-content-md-end mt-2 mt-md-0 ">
                                                                <span class="text-dark badge badge-primary mr-2 mb-2 mb-md-0">Curso: <?= htmlspecialchars($aluno['curso']) ?></span>
                                                                <span class="text-dark badge badge-primary">Turma: <?= htmlspecialchars($aluno['turma']) ?></span>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="card-body">
                                                        <?php if ($disciplinas && $disciplinas->num_rows > 0): ?>
                                                            <div class="row">
                                                                <?php while ($disciplina = $disciplinas->fetch_assoc()): ?>
                                                                    <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-4">
                                                                        <div class="card card-disciplina h-100">
                                                                            <div class="card-header text-truncate">
                                                                                <?= htmlspecialchars($disciplina['disciplina']) ?>
                                                                            </div>
                                                                            <div class="card-body text-center">
                                                                                <div class="disciplina-info">
                                                                                    <img src="<?= !empty($disciplina['foto_perfil']) ? 
                                                                                        htmlspecialchars($disciplina['foto_perfil']) : 
                                                                                        'libraries/assets/images/avatar-2.jpg' ?>" 
                                                                                         alt="Professor" class="professor-img mb-3">
                                                                                    <div class="professor-nome text-truncate w-100">
                                                                                        <i class="feather icon-user"></i> 
                                                                                        <?= $disciplina['professor'] ? 
                                                                                            htmlspecialchars($disciplina['professor']) : 
                                                                                            'Professor não atribuído' ?>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="card-footer text-center bg-white">
                                                                                <div class="d-flex justify-content-center">
                                                                                    <a href="disciplina_detalhes.php?id=<?= $disciplina['id_disciplina'] ?>" 
                                                                                       class="btn btn-sm btn-primary mx-1">
                                                                                        <i class="feather icon-info"></i> <span class="d-none d-sm-inline">Detalhes</span>
                                                                                    </a>
                                                                                    <a href="materiais.php?disciplina=<?= $disciplina['id_disciplina'] ?>" 
                                                                                       class="btn btn-sm btn-success mx-1">
                                                                                        <i class="feather icon-folder"></i> <span class="d-none d-sm-inline">Materiais</span>
                                                                                    </a>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                <?php endwhile; ?>
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="empty-message">
                                                                <i class="feather icon-book-open" style="font-size: 3rem;"></i>
                                                                <p class="mt-3">
                                                                    <?= $aluno ? 
                                                                        'Nenhuma disciplina encontrada para o seu curso.' : 
                                                                        'Não foi possível carregar suas informações de aluno.' ?>
                                                                </p>
                                                            </div>
                                                        <?php endif; ?>
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

    <?php require_once __DIR__ . '/../../includes/common/js_imports.php'; ?>
</body>
</html>