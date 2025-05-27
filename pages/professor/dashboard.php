<?php
    require_once '../../includes/common/permissoes.php';
    verificarPermissao(['professor']);
    require_once '../../process/verificar_sessao.php';
    require_once '../../database/conexao.php';
    
    $title = "Professor";
    
    // Obter ID do professor logado
    $usuario_id = $_SESSION['id_usuario'];
    
    // Consultas para os dados do dashboard
    // Total de Turmas do Professor
    $sql_turmas = "SELECT COUNT(DISTINCT pt.turma_id_turma) as total 
                  FROM professor_tem_turma pt
                  JOIN professor p ON pt.professor_id_professor = p.id_professor
                  WHERE p.usuario_id_usuario = ?";
    $stmt_turmas = $conn->prepare($sql_turmas);
    $stmt_turmas->bind_param("i", $usuario_id);
    $stmt_turmas->execute();
    $result_turmas = $stmt_turmas->get_result();
    $total_turmas = $result_turmas->fetch_assoc()['total'];
    
    // Total de Alunos nas turmas do professor
    $sql_alunos = "SELECT COUNT(DISTINCT a.id_aluno) as total
                  FROM aluno a
                  JOIN professor_tem_turma pt ON a.turma_id_turma = pt.turma_id_turma
                  JOIN professor p ON pt.professor_id_professor = p.id_professor
                  WHERE p.usuario_id_usuario = ?";
    $stmt_alunos = $conn->prepare($sql_alunos);
    $stmt_alunos->bind_param("i", $usuario_id);
    $stmt_alunos->execute();
    $result_alunos = $stmt_alunos->get_result();
    $total_alunos = $result_alunos->fetch_assoc()['total'];
    
    // Total de Notas Lançadas pelo professor
    $sql_notas = "SELECT COUNT(*) as total
                 FROM nota n
                 JOIN disciplina d ON n.disciplina_id_disciplina = d.id_disciplina
                 JOIN professor_tem_disciplina ptd ON d.id_disciplina = ptd.disciplina_id_disciplina
                 JOIN professor p ON ptd.professor_id_professor = p.id_professor
                 WHERE p.usuario_id_usuario = ?";
    $stmt_notas = $conn->prepare($sql_notas);
    $stmt_notas->bind_param("i", $usuario_id);
    $stmt_notas->execute();
    $result_notas = $stmt_notas->get_result();
    $total_notas = $result_notas->fetch_assoc()['total'];
    
    // Total de Faltas Registradas pelo professor
    $sql_faltas = "SELECT COUNT(*) as total
                  FROM frequencia_aluno fa
                  JOIN disciplina d ON fa.disciplina_id_disciplina = d.id_disciplina
                  JOIN professor_tem_disciplina ptd ON d.id_disciplina = ptd.disciplina_id_disciplina
                  JOIN professor p ON ptd.professor_id_professor = p.id_professor
                  WHERE p.usuario_id_usuario = ? AND fa.presenca = 'ausente'";
    $stmt_faltas = $conn->prepare($sql_faltas);
    $stmt_faltas->bind_param("i", $usuario_id);
    $stmt_faltas->execute();
    $result_faltas = $stmt_faltas->get_result();
    $total_faltas = $result_faltas->fetch_assoc()['total'];
    
    // Últimas notas lançadas
    $sql_ultimas_notas = "SELECT u.nome as aluno_nome, t.nome as turma_nome, d.nome as disciplina_nome, 
                         n.nota, n.data, n.tipo_avaliacao
                         FROM nota n
                         JOIN aluno a ON n.aluno_id_aluno = a.id_aluno
                         JOIN usuario u ON a.usuario_id_usuario = u.id_usuario
                         JOIN turma t ON a.turma_id_turma = t.id_turma
                         JOIN disciplina d ON n.disciplina_id_disciplina = d.id_disciplina
                         JOIN professor_tem_disciplina ptd ON d.id_disciplina = ptd.disciplina_id_disciplina
                         JOIN professor p ON ptd.professor_id_professor = p.id_professor
                         WHERE p.usuario_id_usuario = ?
                         ORDER BY n.data DESC LIMIT 5";
    $stmt_ultimas_notas = $conn->prepare($sql_ultimas_notas);
    $stmt_ultimas_notas->bind_param("i", $usuario_id);
    $stmt_ultimas_notas->execute();
    $result_ultimas_notas = $stmt_ultimas_notas->get_result();
    
    // Últimos comunicados recebidos
    $sql_comunicados = "SELECT c.titulo, c.mensagem, c.data, u.nome as autor
                       FROM comunicado c
                       JOIN usuario u ON c.usuario_id_usuario = u.id_usuario
                       JOIN comunicado_destinatario cd ON c.id_comunicado = cd.comunicado_id
                       WHERE cd.tipo_destinatario = 'professor' OR cd.tipo_destinatario = 'todos'
                       ORDER BY c.data DESC LIMIT 3";
    $result_comunicados = $conn->query($sql_comunicados);
?>
<!DOCTYPE html>
<html lang="pt">

<head>
<?php require_once '../../includes/common/head.php'; ?>
</head>

<body>
    <?php require_once '../../includes/common/preloader.php'; ?>

    <div id="pcoded" class="pcoded">
        <div class="pcoded-overlay-box"></div>
        <div class="pcoded-container navbar-wrapper">

            <?php require_once '../../includes/professor/navbar.php'; ?>

            <!--sidebar-->
            <div class="pcoded-main-container">
                <div class="pcoded-wrapper">
                    <?php require_once '../../includes/professor/sidebar.php'; ?>
                    <div class="pcoded-content">
                        <div class="pcoded-inner-content">
                            <div class="main-body bg-img">
                                <div class="page-wrapper">

                                    <div class="page-body">
                                        <div class="row">
                                            <!-- Minhas Turmas -->
                                            <a href="../professor/turmas.php" class="col-xl-3 col-md-6 d-block text-decoration-none text-reset">
                                                <div class="card card-estatistica card-table update-card">
                                                    <div class="card-block">
                                                        <div class="row align-items-end">
                                                            <div class="col-8">
                                                                <h4 class="text-white"><?php echo $total_turmas; ?></h4>
                                                                <h6 class="text-white m-b-0">
                                                                    <i class="feather icon-layers"></i> Minhas Turmas
                                                                </h6>
                                                            </div>
                                                            <div class="col-4 text-right">
                                                                <i class="feather icon-layers text-white" style="font-size: 40px;"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card-footer">
                                                        <p class="text-white m-b-0">
                                                            <i class="feather icon-clock text-white f-14 m-r-10"></i> Atualizado agora
                                                        </p>
                                                    </div>
                                                </div>
                                            </a>
                                            
                                            <!-- Alunos Cadastrados -->
                                            <a href="../professor/alunos.php" class="col-xl-3 col-md-6 d-block text-decoration-none text-reset">
                                                <div class="card card-estatistica card-provas card-table update-card">
                                                    <div class="card-block">
                                                        <div class="row align-items-end">
                                                            <div class="col-8">
                                                                <h4 class="text-white"><?php echo $total_alunos; ?></h4>
                                                                <h6 class="text-white m-b-0">
                                                                    <i class="feather icon-users"></i> Alunos
                                                                </h6>
                                                            </div>
                                                            <div class="col-4 text-right">
                                                                <i class="feather icon-users text-white" style="font-size: 40px;"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card-footer">
                                                        <p class="text-white m-b-0">
                                                            <i class="feather icon-clock text-white f-14 m-r-10"></i> Atualizado agora
                                                        </p>
                                                    </div>
                                                </div>
                                            </a>
                                            
                                            <!-- Notas Lançadas -->
                                            <a href="../professor/notas.php" class="col-xl-3 col-md-6 d-block text-decoration-none text-reset">
                                                <div class="card card-estatistica card-avaliacoes card-table update-card">
                                                    <div class="card-block">
                                                        <div class="row align-items-end">
                                                            <div class="col-8">
                                                                <h4 class="text-white"><?php echo $total_notas; ?></h4>
                                                                <h6 class="text-white m-b-0">
                                                                    <i class="feather icon-edit"></i> Notas Lançadas
                                                                </h6>
                                                            </div>
                                                            <div class="col-4 text-right">
                                                                <i class="feather icon-edit text-white" style="font-size: 40px;"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card-footer">
                                                        <p class="text-white m-b-0">
                                                            <i class="feather icon-clock text-white f-14 m-r-10"></i> Atualizado agora
                                                        </p>
                                                    </div>
                                                </div>
                                            </a>
                                            
                                            <!-- Faltas Registradas -->
                                            <a href="../professor/frequencia.php" class="col-xl-3 col-md-6 d-block text-decoration-none text-reset">
                                                <div class="card card-estatistica card-trabalhos card-table update-card">
                                                    <div class="card-block">
                                                        <div class="row align-items-end">
                                                            <div class="col-8">
                                                                <h4 class="text-white"><?php echo $total_faltas; ?></h4>
                                                                <h6 class="text-white m-b-0">
                                                                    <i class="feather icon-check-square"></i> Faltas Registradas
                                                                </h6>
                                                            </div>
                                                            <div class="col-4 text-right">
                                                                <i class="feather icon-check-square text-white" style="font-size: 40px;"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card-footer">
                                                        <p class="text-white m-b-0">
                                                            <i class="feather icon-clock text-white f-14 m-r-10"></i> Atualizado agora
                                                        </p>
                                                    </div>
                                                </div>
                                            </a>
                                            
                                            <!-- Avisos Recebidos -->
                                            <a href="../compartilhados/visualizar_comunicados.php" class="col-xl-3 col-md-6 d-block text-decoration-none text-reset">
                                                <div class="card bg-c-blue update-card">
                                                    <div class="card-block">
                                                        <div class="row align-items-end">
                                                            <div class="col-8">
                                                                <h4 class="text-white">
                                                                    <?php 
                                                                    $total_comunicados = $result_comunicados->num_rows;
                                                                    echo $total_comunicados > 0 ? $total_comunicados : '0';
                                                                    ?>
                                                                </h4>
                                                                <h6 class="text-white m-b-0">
                                                                    <i class="feather icon-bell"></i> Avisos Recebidos
                                                                </h6>
                                                            </div>
                                                            <div class="col-4 text-right">
                                                                <i class="feather icon-bell text-white" style="font-size: 40px;"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card-footer">
                                                        <p class="text-white m-b-0">
                                                            <i class="feather icon-clock text-white f-14 m-r-10"></i> Atualizado agora
                                                        </p>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        
                                        <div class="card mb-4 card-table">
                                            <div class="card-header">
                                                <h5 class="text-white">Últimos Lançamentos de Notas</h5>
                                            </div>
                                            <div class="card-block table-border-style">
                                                <div class="table-responsive">
                                                    <table class="table table-bordered table-custom">
                                                        <thead>
                                                            <tr>
                                                                <th>Aluno</th>
                                                                <th>Turma</th>
                                                                <th>Disciplina</th>
                                                                <th>Nota</th>
                                                                <th>Data</th>
                                                                <th>Tipo</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php if($result_ultimas_notas->num_rows > 0): ?>
                                                                <?php while($nota = $result_ultimas_notas->fetch_assoc()): ?>
                                                                    <tr>
                                                                        <td><?php echo htmlspecialchars($nota['aluno_nome']); ?></td>
                                                                        <td><?php echo htmlspecialchars($nota['turma_nome']); ?></td>
                                                                        <td><?php echo htmlspecialchars($nota['disciplina_nome']); ?></td>
                                                                        <td><?php echo htmlspecialchars($nota['nota']); ?></td>
                                                                        <td><?php echo date('d/m/Y', strtotime($nota['data'])); ?></td>
                                                                        <td><?php echo htmlspecialchars($nota['tipo_avaliacao']); ?></td>
                                                                    </tr>
                                                                <?php endwhile; ?>
                                                            <?php else: ?>
                                                                <tr>
                                                                    <td colspan="6" class="text-center">Nenhuma nota lançada recentemente</td>
                                                                </tr>
                                                            <?php endif; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card mb-4 card-table">
                                            <div class="card-header">
                                                <h5>Últimos Avisos Recebidos</h5>
                                            </div>
                                            <div class="card-block">
                                                <ul class="list-group">
                                                    <?php if($result_comunicados->num_rows > 0): ?>
                                                        <?php while($comunicado = $result_comunicados->fetch_assoc()): ?>
                                                            <li class="list-group-item card-table">
                                                                <i class="feather icon-bell text-primary"></i> 
                                                                <span><?php echo htmlspecialchars($comunicado['titulo']); ?></span>
                                                                <small class="d-block text-muted">Por <?php echo htmlspecialchars($comunicado['autor']); ?> em <?php echo date('d/m/Y H:i', strtotime($comunicado['data'])); ?></small>
                                                            </li>
                                                        <?php endwhile; ?>
                                                    <?php else: ?>
                                                        <li class="list-group-item card-table">
                                                            Nenhum comunicado recente
                                                        </li>
                                                    <?php endif; ?>
                                                </ul>
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

    <?php 
        // Fechar conexões
        $stmt_turmas->close();
        $stmt_alunos->close();
        $stmt_notas->close();
        $stmt_faltas->close();
        $stmt_ultimas_notas->close();
        $conn->close();
    ?>
    
    <?php require_once '../../includes/common/js_imports.php'; ?>
</body>
</html>