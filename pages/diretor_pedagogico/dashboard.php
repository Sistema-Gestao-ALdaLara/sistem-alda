<?php
    require_once '../../includes/common/permissoes.php';
    verificarPermissao(['diretor_pedagogico']);
    require_once '../../process/verificar_sessao.php';
    require_once '../../database/conexao.php';
    
    $title = "Pedagogico";
    
    // Consultas para os dados do dashboard
    // Total de Alunos Matriculados (ativos)
    $sql_alunos = "SELECT COUNT(*) as total FROM aluno a 
                  JOIN usuario u ON a.usuario_id_usuario = u.id_usuario 
                  WHERE u.status = 'ativo'";
    $result_alunos = $conn->query($sql_alunos);
    $total_alunos = $result_alunos->fetch_assoc()['total'];
    
    // Total de Professores (ativos)
    $sql_professores = "SELECT COUNT(*) as total FROM professor p 
                       JOIN usuario u ON p.usuario_id_usuario = u.id_usuario 
                       WHERE u.status = 'ativo'";
    $result_professores = $conn->query($sql_professores);
    $total_professores = $result_professores->fetch_assoc()['total'];
    
    // Desempenho Acadêmico (taxa de aprovação/reprovação)
    $sql_desempenho = "SELECT 
                        ROUND((SUM(CASE WHEN n.nota >= 10 THEN 1 ELSE 0 END) / COUNT(*)), 2) * 100 as aprovados,
                        ROUND((SUM(CASE WHEN n.nota < 10 THEN 1 ELSE 0 END) / COUNT(*)), 2) * 100 as reprovados
                      FROM nota n";
    $result_desempenho = $conn->query($sql_desempenho);
    $desempenho = $result_desempenho->fetch_assoc();
    $aprovados = $desempenho['aprovados'] ?? 0;
    $reprovados = $desempenho['reprovados'] ?? 0;
    
    // Frequência dos alunos (taxa de presença)
    $sql_frequencia = "SELECT 
                        ROUND((SUM(CASE WHEN fa.presenca = 'presente' THEN 1 ELSE 0 END) / COUNT(*)), 2) * 100 as presenca
                      FROM frequencia_aluno fa";
    $result_frequencia = $conn->query($sql_frequencia);
    $frequencia = $result_frequencia->fetch_assoc();
    $taxa_presenca = $frequencia['presenca'] ?? 0;
    
    // Planos de ensino pendentes de aprovação
    $sql_planos_pendentes = "SELECT COUNT(*) as total FROM plano_ensino WHERE status = 'submetido'";
    $result_planos_pendentes = $conn->query($sql_planos_pendentes);
    $planos_pendentes = $result_planos_pendentes->fetch_assoc()['total'];
    
    // Últimos planos submetidos
    $sql_ultimos_planos = "SELECT pe.titulo, pe.data_submissao, u.nome as professor, d.nome as disciplina
                          FROM plano_ensino pe
                          JOIN disciplina d ON pe.id_disciplina = d.id_disciplina
                          LEFT JOIN professor p ON pe.id_professor = p.id_professor
                          LEFT JOIN usuario u ON p.usuario_id_usuario = u.id_usuario
                          WHERE pe.status = 'submetido'
                          ORDER BY pe.data_submissao DESC LIMIT 3";
    $result_ultimos_planos = $conn->query($sql_ultimos_planos);
?>
<!DOCTYPE html>
<html lang="pt">

<?php require_once '../../includes/common/head.php'; ?>

<body>
    <?php require_once '../../includes/common/preloader.php'; ?>

    <div id="pcoded" class="pcoded">
        <div class="pcoded-overlay-box"></div>
        <div class="pcoded-container navbar-wrapper">

            <?php require_once '../../includes/diretor_pedagogico/navbar.php'; ?>

            <!--sidebar-->
            <div class="pcoded-main-container">
                <div class="pcoded-wrapper">
                    <?php require_once '../../includes/diretor_pedagogico/sidebar.php'; ?>
                    <div class="pcoded-content">
                        <div class="pcoded-inner-content">
                            <div class="main-body bg-img">
                                <div class="page-wrapper">

                                    <div class="page-body">
                                        <div class="row">
                                            <!-- Total de Alunos Matriculados -->
                                            <a href="../compartilhados/relatorios.php?tipo=matriculas" class="col-xl-3 col-md-6 d-block text-decoration-none text-reset">
                                                <div class="card bg-c-yellow update-card">
                                                    <div class="card-block">
                                                        <div class="row align-items-end">
                                                            <div class="col-8">
                                                                <h4 class="text-white"><?php echo $total_alunos; ?></h4>
                                                                <h6 class="text-white m-b-0">
                                                                    <i class="feather icon-users"></i> Alunos Matriculados
                                                                </h6>
                                                            </div>
                                                            <div class="col-4 text-right">
                                                                <i class="feather icon-users text-white" style="font-size: 40px;"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card-footer">
                                                        <p class="text-white m-b-0"><i class="feather icon-clock text-white f-14 m-r-10"></i>Atualizado agora</p>
                                                    </div>
                                                </div>
                                            </a>

                                            <!-- Total de Professores Cadastrados -->
                                            <a href="../compartilhados/professor.php" class="col-xl-3 col-md-6 d-block text-decoration-none text-reset">
                                                <div class="card bg-c-green update-card">
                                                    <div class="card-block">
                                                        <div class="row align-items-end">
                                                            <div class="col-8">
                                                                <h4 class="text-white"><?php echo $total_professores; ?></h4>
                                                                <h6 class="text-white m-b-0">
                                                                    <i class="feather icon-briefcase"></i> Professores
                                                                </h6>
                                                            </div>
                                                            <div class="col-4 text-right">
                                                                <i class="feather icon-briefcase text-white" style="font-size: 40px;"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card-footer">
                                                        <p class="text-white m-b-0"><i class="feather icon-clock text-white f-14 m-r-10"></i>Atualizado agora</p>
                                                    </div>
                                                </div>
                                            </a>

                                            <!-- Frequência dos Alunos -->
                                            <a href="../compartilhados/relatorios.php?tipo=frequencia" class="col-xl-3 col-md-6 d-block text-decoration-none text-reset">
                                                <div class="card bg-c-pink update-card">
                                                    <div class="card-block">
                                                        <div class="row align-items-end">
                                                            <div class="col-8">
                                                                <h4 class="text-white"><?php echo $taxa_presenca; ?>%</h4>
                                                                <h6 class="text-white m-b-0">
                                                                    <i class="feather icon-check-square"></i> Taxa de Presença
                                                                </h6>
                                                            </div>
                                                            <div class="col-4 text-right">
                                                                <i class="feather icon-check-square text-white" style="font-size: 40px;"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card-footer">
                                                        <p class="text-white m-b-0"><i class="feather icon-clock text-white f-14 m-r-10"></i>Atualizado agora</p>
                                                    </div>
                                                </div>
                                            </a>

                                            <!-- Desempenho Acadêmico Geral -->
                                            <a href="../compartilhados/relatorios.php?tipo=desempenho" class="col-xl-3 col-md-6 d-block text-decoration-none text-reset">
                                                <div class="card bg-c-lite-green update-card">
                                                    <div class="card-block">
                                                        <div class="row align-items-end">
                                                            <div class="col-8">
                                                                <h4 class="text-white"><?php echo $aprovados; ?>% / <?php echo $reprovados; ?>%</h4>
                                                                <h6 class="text-white m-b-0">
                                                                    <i class="feather icon-bar-chart"></i> Desempenho Acadêmico
                                                                </h6>
                                                            </div>
                                                            <div class="col-4 text-right">
                                                                <i class="feather icon-bar-chart text-white" style="font-size: 40px;"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card-footer">
                                                        <p class="text-white m-b-0"><i class="feather icon-clock text-white f-14 m-r-10"></i>Atualizado agora</p>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        
                                        <div class="row mt-4">
                                            <!-- Planos de Ensino Pendentes -->
                                            <div class="col-md-6">
                                                <div class="card mb-4 card-table">
                                                    <div class="card-header">
                                                        <h5>Planos de Ensino Pendentes (<?php echo $planos_pendentes; ?>)</h5>
                                                    </div>
                                                    <div class="card-block">
                                                        <?php if($planos_pendentes > 0): ?>
                                                            <ul class="list-group">
                                                                <?php while($plano = $result_ultimos_planos->fetch_assoc()): ?>
                                                                    <li class="list-group-item text-white card-table">
                                                                        <i class="feather icon-file-text text-warning"></i> 
                                                                        <span><?php echo htmlspecialchars($plano['titulo']); ?></span>
                                                                        <small class="d-block text-muted">
                                                                            Disciplina: <?php echo htmlspecialchars($plano['disciplina']); ?> | 
                                                                            Professor: <?php echo htmlspecialchars($plano['professor'] ?? 'Não atribuído'); ?>
                                                                        </small>
                                                                        <small class="d-block text-muted">
                                                                            Submetido em: <?php echo date('d/m/Y H:i', strtotime($plano['data_submissao'])); ?>
                                                                        </small>
                                                                    </li>
                                                                <?php endwhile; ?>
                                                            </ul>
                                                            <div class="text-center mt-2">
                                                                <a href="../compartilhados/aprovacao_planos.php" class="btn btn-sm btn-primary">Ver todos</a>
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="alert alert-success">
                                                                Nenhum plano de ensino pendente de aprovação
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Estatísticas Rápidas -->
                                            <div class="col-md-6">
                                                <div class="card mb-4 card-table">
                                                    <div class="card-header">
                                                        <h5>Estatísticas Pedagógicas</h5>
                                                    </div>
                                                    <div class="card-block">
                                                        <div class="row text-center">
                                                            <div class="col-6 mb-3">
                                                                <div class="bg-c-yellow p-3 rounded">
                                                                    <h4 class="text-white"><?php echo $total_alunos; ?></h4>
                                                                    <small class="text-white">Alunos</small>
                                                                </div>
                                                            </div>
                                                            <div class="col-6 mb-3">
                                                                <div class="bg-c-green p-3 rounded">
                                                                    <h4 class="text-white"><?php echo $total_professores; ?></h4>
                                                                    <small class="text-white">Professores</small>
                                                                </div>
                                                            </div>
                                                            <div class="col-6">
                                                                <div class="bg-c-pink p-3 rounded">
                                                                    <h4 class="text-white"><?php echo $aprovados; ?>%</h4>
                                                                    <small class="text-white">Aprovação</small>
                                                                </div>
                                                            </div>
                                                            <div class="col-6">
                                                                <div class="bg-c-blue p-3 rounded">
                                                                    <h4 class="text-white"><?php echo $taxa_presenca; ?>%</h4>
                                                                    <small class="text-white">Presença</small>
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
        </div>
    </div>

    <?php 
        // Fechar conexão
        $conn->close();
    ?>
    
    <?php require_once '../../includes/common/js_imports.php'; ?>
</body>
</html>