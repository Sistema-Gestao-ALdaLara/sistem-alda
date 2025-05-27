<?php
    require_once '../../includes/common/permissoes.php';
    verificarPermissao(['diretor_geral']);
    require_once '../../process/verificar_sessao.php';
    require_once '../../database/conexao.php';
    
    $title = "D. Geral";
    
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
    
    // Total de Funcionários (secretaria + diretores + coordenadores ativos)
    $sql_funcionarios = "SELECT COUNT(*) as total FROM (
                        SELECT s.id_secretaria FROM secretaria s JOIN usuario u ON s.usuario_id_usuario = u.id_usuario WHERE u.status = 'ativo'
                        UNION ALL
                        SELECT c.id_coordenador FROM coordenador c JOIN usuario u ON c.usuario_id_usuario = u.id_usuario WHERE u.status = 'ativo'
                        UNION ALL
                        SELECT u.id_usuario FROM usuario u WHERE u.tipo IN ('diretor_geral', 'diretor_pedagogico') AND u.status = 'ativo'
                        ) as funcionarios";
    $result_funcionarios = $conn->query($sql_funcionarios);
    $total_funcionarios = $result_funcionarios->fetch_assoc()['total'];
    
    // Total de professores e funcionários
    $total_prof_func = $total_professores + $total_funcionarios;
    
    // Desempenho Acadêmico (taxa de aprovação/reprovação)
    $sql_desempenho = "SELECT 
                        ROUND((SUM(CASE WHEN n.nota >= 10 THEN 1 ELSE 0 END) / COUNT(*)) * 100) as aprovados,
                        ROUND((SUM(CASE WHEN n.nota < 10 THEN 1 ELSE 0 END) / COUNT(*)) * 100) as reprovados
                    FROM nota n";
    $result_desempenho = $conn->query($sql_desempenho);
    $desempenho = $result_desempenho->fetch_assoc();
    $aprovados = $desempenho['aprovados'] ?? 0;
    $reprovados = $desempenho['reprovados'] ?? 0;

    // Total de Cursos
    $sql_cursos = "SELECT COUNT(*) as total FROM curso";
    $result_cursos = $conn->query($sql_cursos);
    $total_cursos = $result_cursos->fetch_assoc()['total'];
    
    // Planos de ensino pendentes de aprovação
    $sql_planos_pendentes = "SELECT COUNT(*) as total FROM plano_ensino WHERE status = 'submetido'";
    $result_planos_pendentes = $conn->query($sql_planos_pendentes);
    $planos_pendentes = $result_planos_pendentes->fetch_assoc()['total'];
    
    // Últimos comunicados
    $sql_comunicados = "SELECT c.titulo, c.data, u.nome as autor 
                       FROM comunicado c
                       JOIN usuario u ON c.usuario_id_usuario = u.id_usuario
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

            <?php require_once '../../includes/diretor_geral/navbar.php'; ?>

            <!--sidebar-->
            <div class="pcoded-main-container">
                <div class="pcoded-wrapper">
                    <?php require_once '../../includes/diretor_geral/sidebar.php'; ?>
                    <div class="pcoded-content">
                        <div class="pcoded-inner-content">
                            <div class="main-body bg-img">
                                <div class="page-wrapper">

                                    <div class="page-body">
                                        <div class="alert alert-info mb-4">
                                            <strong>Informação:</strong> Alguns dados como "Movimentação de Alunos" não estão disponíveis na base de dados atual.
                                        </div>
                                        
                                        <div class="row">
                                            <!-- Total de Alunos Matriculados -->
                                            <a href="#" class="col-xl-3 col-md-6 d-block text-decoration-none text-reset">
                                                <div class="card bg-c-yellow update-card">
                                                    <div class="card-block">
                                                        <div class="row align-items-end">
                                                            <div class="col-8">
                                                                <h4 class="text-white"><?php echo $total_alunos; ?></h4>
                                                                <h6 class="text-white m-b-0"><i class="feather icon-users"></i> Alunos Matriculados</h6>
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
                                            
                                            <!-- Total de Professores e Funcionários -->
                                            <a href="#" class="col-xl-3 col-md-6 d-block text-decoration-none text-reset">
                                                <div class="card bg-c-green update-card">
                                                    <div class="card-block">
                                                        <div class="row align-items-end">
                                                            <div class="col-8">
                                                                <h4 class="text-white"><?php echo $total_prof_func; ?></h4>
                                                                <h6 class="text-white m-b-0"><i class="feather icon-briefcase"></i> Professores e Funcionários</h6>
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
                                            
                                            <!-- Planos Pendentes -->
                                            <a href="../compartilhados/aprovacao_planos.php" class="col-xl-3 col-md-6 d-block text-decoration-none text-reset">
                                                <div class="card bg-c-pink update-card">
                                                    <div class="card-block">
                                                        <div class="row align-items-end">
                                                            <div class="col-8">
                                                                <h4 class="text-white"><?php echo $planos_pendentes; ?></h4>
                                                                <h6 class="text-white m-b-0"><i class="feather icon-alert-circle"></i> Planos Pendentes</h6>
                                                            </div>
                                                            <div class="col-4 text-right">
                                                                <i class="feather icon-alert-circle text-white" style="font-size: 40px;"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card-footer">
                                                        <p class="text-white m-b-0"><i class="feather icon-clock text-white f-14 m-r-10"></i>Atualizado agora</p>
                                                    </div>
                                                </div>
                                            </a>
                                            
                                            <!-- Total de Cursos -->
                                            <a href="../diretor_geral/cursos.php" class="col-xl-3 col-md-6 d-block text-decoration-none text-reset">
                                                <div class="card bg-c-lite-green update-card">
                                                    <div class="card-block">
                                                        <div class="row align-items-end">
                                                            <div class="col-8">
                                                                <h4 class="text-white"><?php echo $total_cursos; ?></h4>
                                                                <h6 class="text-white m-b-0"><i class="feather icon-book"></i> Cursos Oferecidos</h6>
                                                            </div>
                                                            <div class="col-4 text-right">
                                                                <i class="feather icon-book text-white" style="font-size: 40px;"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card-footer">
                                                        <p class="text-white m-b-0"><i class="feather icon-clock text-white f-14 m-r-10"></i>Atualizado agora</p>
                                                    </div>
                                                </div>
                                            </a>
                                            
                                            <!-- Desempenho Acadêmico -->
                                            <a href="../compartilhados/relatorios.php?tipo=desempenho" class="col-xl-3 col-md-6 d-block text-decoration-none text-reset">
                                                <div class="card bg-c-blue update-card">
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
                                                        <p class="text-white m-b-0">
                                                            <i class="feather icon-clock text-white f-14 m-r-10"></i>Atualizado agora
                                                        </p>
                                                    </div>
                                                </div>
                                            </a>
                                            
                                            <!-- Movimentação de Alunos (dados não disponíveis) -->
                                            <a href="#" class="col-xl-3 col-md-6 d-block text-decoration-none text-reset">
                                                <div class="card bg-c-lite-green update-card">
                                                    <div class="card-block">
                                                        <div class="row align-items-end">
                                                            <div class="col-8">
                                                                <h4 class="text-white">
                                                                    <span class="badge badge-warning">BD</span>
                                                                </h4>
                                                                <h6 class="text-white m-b-0"><i class="feather icon-refresh-ccw"></i> Movimentação de Alunos</h6>
                                                            </div>
                                                            <div class="col-4 text-right">
                                                                <i class="feather icon-refresh-ccw text-white" style="font-size: 40px;"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card-footer">
                                                        <p class="text-white m-b-0"><i class="feather icon-clock text-white f-14 m-r-10"></i>Dados não disponíveis</p>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        
                                        <div class="row">
                                            <!-- Últimos Comunicados -->
                                            <div class="col-md-6">
                                                <div class="card mb-4 card-table">
                                                    <div class="card-header">
                                                        <h5>Últimos Comunicados</h5>
                                                    </div>
                                                    <div class="card-block">
                                                        <ul class="list-group">
                                                            <?php if($result_comunicados->num_rows > 0): ?>
                                                                <?php while($comunicado = $result_comunicados->fetch_assoc()): ?>
                                                                    <li class="list-group-item text-white card-table">
                                                                        <i class="feather icon-mail text-primary"></i> 
                                                                        <span><?php echo htmlspecialchars($comunicado['titulo']); ?></span>
                                                                        <small class="d-block text-muted">Por <?php echo htmlspecialchars($comunicado['autor']); ?> em <?php echo date('d/m/Y H:i', strtotime($comunicado['data'])); ?></small>
                                                                    </li>
                                                                <?php endwhile; ?>
                                                            <?php else: ?>
                                                                <li class="list-group-item text-white card-table">
                                                                    Nenhum comunicado recente
                                                                </li>
                                                            <?php endif; ?>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Estatísticas Rápidas -->
                                            <div class="col-md-6">
                                                <div class="card mb-4 card-table">
                                                    <div class="card-header">
                                                        <h5>Estatísticas Rápidas</h5>
                                                    </div>
                                                    <div class="card-block">
                                                        <div class="row">
                                                            <div class="col-6 text-center mb-3">
                                                                <div class="bg-c-yellow p-3 rounded">
                                                                    <h4 class="text-white"><?php echo $total_professores; ?></h4>
                                                                    <small class="text-white">Professores</small>
                                                                </div>
                                                            </div>
                                                            <div class="col-6 text-center mb-3">
                                                                <div class="bg-c-green p-3 rounded">
                                                                    <h4 class="text-white"><?php echo $total_funcionarios; ?></h4>
                                                                    <small class="text-white">Funcionários</small>
                                                                </div>
                                                            </div>
                                                            <div class="col-6 text-center">
                                                                <div class="bg-c-pink p-3 rounded">
                                                                    <h4 class="text-white"><?php echo $planos_pendentes; ?></h4>
                                                                    <small class="text-white">Planos Pendentes</small>
                                                                </div>
                                                            </div>
                                                            <div class="col-6 text-center">
                                                                <div class="bg-c-blue p-3 rounded">
                                                                    <h4 class="text-white"><?php echo $total_cursos; ?></h4>
                                                                    <small class="text-white">Cursos</small>
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
    
    <?php include '../../includes/common/js_imports.php'; ?>
</body>

</html>