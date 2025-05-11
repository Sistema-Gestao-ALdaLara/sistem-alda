<?php
    require_once '../../includes/common/permissoes.php';
    verificarPermissao(['aluno']);
    require_once '../../process/verificar_sessao.php';
    require_once '../../database/conexao.php';

    $title = "Comunicação";

    // Obter dados do aluno
    $id_usuario = $_SESSION['id_usuario'];
    $dados_aluno = [];
    $turma_aluno = '';
    $curso_aluno = '';
    $ano_letivo = '';

    // Dados básicos do aluno
    $sql = "SELECT a.*, u.nome, u.foto_perfil, u.email, t.nome as nome_turma, t.id_turma, 
                   c.nome as nome_curso, c.id_curso, m.ano_letivo as ano_letivo_matricula
            FROM aluno a 
            JOIN usuario u ON a.usuario_id_usuario = u.id_usuario 
            JOIN turma t ON a.turma_id_turma = t.id_turma 
            JOIN curso c ON t.curso_id_curso = c.id_curso
            JOIN matricula m ON m.aluno_id_aluno = a.id_aluno
            WHERE u.id_usuario = ? AND m.status_matricula = 'ativa'";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    $dados_aluno = $result->fetch_assoc();
    $stmt->close();

    // Obter coordenador do curso do aluno
    $coordenador = null;
    if ($dados_aluno) {
        $turma_aluno = $dados_aluno['nome_turma'];
        $curso_aluno = $dados_aluno['nome_curso'];
        $ano_letivo = $dados_aluno['ano_letivo_matricula'] ?? $dados_aluno['ano_letivo'];
        
        $sql_coordenador = "SELECT u.nome, u.email, u.foto_perfil 
                            FROM coordenador c
                            JOIN usuario u ON c.usuario_id_usuario = u.id_usuario
                            WHERE c.curso_id_curso = ?";
        $stmt_coordenador = $conn->prepare($sql_coordenador);
        $stmt_coordenador->bind_param("i", $dados_aluno['id_curso']);
        $stmt_coordenador->execute();
        $result_coordenador = $stmt_coordenador->get_result();
        $coordenador = $result_coordenador->fetch_assoc();
        $stmt_coordenador->close();
    }

    // Obter professores da turma do aluno
    $professores = [];
    if ($dados_aluno) {
        $sql_professores = "SELECT u.nome, u.email, u.foto_perfil, d.nome as disciplina
                            FROM professor_tem_turma pt
                            JOIN professor p ON pt.professor_id_professor = p.id_professor
                            JOIN usuario u ON p.usuario_id_usuario = u.id_usuario
                            JOIN professor_tem_disciplina pd ON p.id_professor = pd.professor_id_professor
                            JOIN disciplina d ON pd.disciplina_id_disciplina = d.id_disciplina
                            WHERE pt.turma_id_turma = ?
                            GROUP BY u.id_usuario, d.id_disciplina
                            ORDER BY u.nome";
        $stmt_professores = $conn->prepare($sql_professores);
        $stmt_professores->bind_param("i", $dados_aluno['id_turma']);
        $stmt_professores->execute();
        $result_professores = $stmt_professores->get_result();
        while ($row = $result_professores->fetch_assoc()) {
            $professores[] = $row;
        }
        $stmt_professores->close();
    }

    // Obter secretaria
    $secretaria = [];
    $sql_secretaria = "SELECT u.nome, u.email, u.foto_perfil, s.setor
                      FROM secretaria s
                      JOIN usuario u ON s.usuario_id_usuario = u.id_usuario
                      WHERE u.status = 'ativo'";
    $result_secretaria = $conn->query($sql_secretaria);
    while ($row = $result_secretaria->fetch_assoc()) {
        $secretaria[] = $row;
    }
?>

<!DOCTYPE html>
<html lang="pt">

<?php require_once '../../includes/common/head.php'; ?>

<body>
    <?php require_once '../../includes/common/preloader.php'; ?>

    <div id="pcoded" class="pcoded">
        <div class="pcoded-overlay-box"></div>
        <div class="pcoded-container navbar-wrapper">

            <?php require_once '../../includes/aluno/navbar.php'; ?>

            <!--sidebar-->
            <div class="pcoded-main-container">
                <div class="pcoded-wrapper">
                    <?php require_once '../../includes/aluno/sidebar.php'; ?>
                    
                    <div class="pcoded-content">
                        <div class="pcoded-inner-content">
                            <div class="main-body bg-img">
                                <div class="page-wrapper">

                                    <div class="page-body">
                                        <div class="row">
                                            <!-- Card de Perfil -->
                                            <div class="col-xl-4 col-md-6">
                                                <div class="card user-card">
                                                    <div class="card-block text-center">
                                                        <div class="user-image">
                                                            <img src="<?php echo $dados_aluno['foto_perfil'] ? '../../uploads/alunos/'.$dados_aluno['foto_perfil'] : '../../public/libraries/assets/images/avatar-4.jpg'; ?>" 
                                                                class="img-radius" width="100" height="100" alt="Foto do Aluno">
                                                        </div>
                                                        <h4 class="m-t-15 text-uppercase"><?php echo htmlspecialchars($dados_aluno['nome'] ?? $_SESSION['nome_usuario']); ?></h4>
                                                        <p class="text-muted">Curso: <?php echo htmlspecialchars($curso_aluno); ?></p>
                                                        <p class="text-muted">Turma: <?php echo htmlspecialchars($turma_aluno); ?></p>
                                                    </div>
                                                </div>
                                            </div>
                                        
                                            <!-- Acesso Rápido -->
                                            <div class="col-xl-8 col-md-6">
                                                <div class="row">
                                                    <a href="comunicados.php" class="col-6 col-md-4 d-block text-decoration-none text-reset">
                                                        <div class="card bg-c-blue">
                                                            <div class="card-block text-center">
                                                                <i class="feather icon-mail f-30 text-white"></i>
                                                                <h6 class="text-white m-t-10">Comunicados</h6>
                                                            </div>
                                                            <div class="card-footer bg-c-green">
                                                                <p class="text-white m-b-0">
                                                                    <i class="feather icon-arrow-right f-14 m-r-10"></i> Ver Comunicados
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </a>
                                                    <a href="mensagens.php" class="col-6 col-md-4 d-block text-decoration-none text-reset">
                                                        <div class="card bg-c-green">
                                                            <div class="card-block text-center">
                                                                <i class="feather icon-message-square f-30 text-white"></i>
                                                                <h6 class="text-white m-t-10">Mensagens</h6>
                                                            </div>
                                                            <div class="card-footer bg-c-yellow">
                                                                <p class="text-white m-b-0">
                                                                    <i class="feather icon-arrow-right f-14 m-r-10"></i> Enviar Mensagem
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </a>
                                                    <a href="duvidas.php" class="col-6 col-md-4 d-block text-decoration-none text-reset">
                                                        <div class="card bg-c-yellow">
                                                            <div class="card-block text-center">
                                                                <i class="feather icon-help-circle f-30 text-white"></i>
                                                                <h6 class="text-white m-t-10">Dúvidas</h6>
                                                            </div>
                                                            <div class="card-footer bg-c-blue">
                                                                <p class="text-white m-b-0">
                                                                    <i class="feather icon-arrow-right f-14 m-r-10"></i> Enviar Dúvida
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </a>
                                                </div>
                                            </div>
                                        
                                            <!-- Coordenador do Curso -->
                                            <div class="col-xl-6 col-md-12">
                                                <div class="card">
                                                    <div class="card-header">
                                                        <h5><i class="feather icon-user"></i> Coordenador do Curso</h5>
                                                    </div>
                                                    <div class="card-block">
                                                        <?php if ($coordenador): ?>
                                                            <div class="row align-items-center">
                                                                <div class="col-auto">
                                                                    <img src="<?php echo $coordenador['foto_perfil'] ? '../../uploads/professores/'.$coordenador['foto_perfil'] : '../../public/libraries/assets/images/avatar-4.jpg'; ?>" 
                                                                        class="img-radius" width="80" height="80" alt="Foto do Coordenador">
                                                                </div>
                                                                <div class="col">
                                                                    <h5><?php echo htmlspecialchars($coordenador['nome']); ?></h5>
                                                                    <p class="text-muted">Coordenador de <?php echo htmlspecialchars($curso_aluno); ?></p>
                                                                    <div class="m-t-15">
                                                                    <a href="https://mail.google.com/mail/?view=cm&fs=1&to=<?php echo urlencode($coordenador['email']);
                                                                            ?>&su=Contato via Sistema Escolar&body=Meu email de contato: <?php echo urlencode($dados_aluno['email'] ?? ''); ?>" 
                                                                            target="_blank" class="btn btn-primary btn-sm">
                                                                        <i class="feather icon-mail"></i> Abrir no Gmail
                                                                    </a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="alert alert-warning">
                                                                Nenhum coordenador atribuído ao seu curso.
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        
                                            <!-- Professores da Turma -->
                                            <div class="col-xl-6 col-md-12">
                                                <div class="card">
                                                    <div class="card-header">
                                                        <h5><i class="feather icon-users"></i> Professores da Turma</h5>
                                                    </div>
                                                    <div class="card-block">
                                                        <?php if (count($professores) > 0): ?>
                                                            <div class="table-responsive">
                                                                <table class="table table-hover">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>Professor</th>
                                                                            <th>Disciplina</th>
                                                                            <th>Ações</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <?php foreach ($professores as $professor): ?>
                                                                            <tr>
                                                                                <td>
                                                                                    <div class="d-flex align-items-center">
                                                                                        <img src="<?php echo $professor['foto_perfil'] ? '../../uploads/professores/'.$professor['foto_perfil'] : '../../public/libraries/assets/images/avatar-4.jpg'; ?>" 
                                                                                            class="img-radius" width="40" height="40" alt="Foto do Professor">
                                                                                        <div class="ml-3">
                                                                                            <h6 class="m-b-0"><?php echo htmlspecialchars($professor['nome']); ?></h6>
                                                                                        </div>
                                                                                    </div>
                                                                                </td>
                                                                                <td><?php echo htmlspecialchars($professor['disciplina']); ?></td>
                                                                                <td>
                                                                                    <a href="https://mail.google.com/mail/?view=cm&fs=1&to=<?php echo urlencode($professor['email']);
                                                                                            ?>&su=Contato via Sistema Escolar&body=Meu email de contato: <?php echo urlencode($dados_aluno['email'] ?? ''); ?>" 
                                                                                            target="_blank" class="btn btn-primary btn-sm">
                                                                                        <i class="feather icon-mail"></i> Abrir no Gmail
                                                                                    </a>
                                                                                </td>
                                                                            </tr>
                                                                        <?php endforeach; ?>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="alert alert-warning">
                                                                Nenhum professor atribuído à sua turma.
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        
                                            <!-- Secretaria -->
                                            <div class="col-xl-12">
                                                <div class="card">
                                                    <div class="card-header">
                                                        <h5><i class="feather icon-briefcase"></i> Secretaria Escolar</h5>
                                                    </div>
                                                    <div class="card-block">
                                                        <?php if (count($secretaria) > 0): ?>
                                                            <div class="row">
                                                                <?php foreach ($secretaria as $secretario): ?>
                                                                    <div class="col-xl-4 col-md-6">
                                                                        <div class="card contact-card">
                                                                            <div class="card-block">
                                                                                <div class="row align-items-center">
                                                                                    <div class="col-auto">
                                                                                        <img src="<?php echo $secretario['foto_perfil'] ? '../../uploads/secretaria/'.$secretario['foto_perfil'] : '../../public/libraries/assets/images/avatar-4.jpg'; ?>" 
                                                                                            class="img-radius" width="60" height="60" alt="Foto da Secretaria">
                                                                                    </div>
                                                                                    <div class="col">
                                                                                        <h5><?php echo htmlspecialchars($secretario['nome']); ?></h5>
                                                                                        <p class="text-muted"><?php echo htmlspecialchars($secretario['setor']); ?></p>
                                                                                        <div class="m-t-10">
                                                                                            <a href="https://mail.google.com/mail/?view=cm&fs=1&to=<?php echo urlencode($secretario['email']);
                                                                                                    ?>&su=Contato via Sistema Escolar&body=Meu email de contato: <?php echo urlencode($dados_aluno['email'] ?? ''); ?>" 
                                                                                                    target="_blank" class="btn btn-primary btn-sm">
                                                                                                <i class="feather icon-mail"></i> Abrir no Gmail
                                                                                            </a>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="alert alert-warning">
                                                                Nenhum membro da secretaria disponível no momento.
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

    <?php require_once '../../includes/common/js_imports.php'; ?>
</body>
</html>