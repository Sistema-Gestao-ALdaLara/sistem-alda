<?php
    require_once '../../includes/common/permissoes.php';
    verificarPermissao(['coordenador']);
    require_once '../../process/verificar_sessao.php';
    require_once '../../database/conexao.php';

    $title = "Coordenador";
    
    // Obter o ID do coordenador logado
    $usuario_id = $_SESSION['id_usuario'];
    
    // Buscar informações do coordenador
    $sql_coordenador = "SELECT c.*, u.nome FROM coordenador c 
                        JOIN usuario u ON c.usuario_id_usuario = u.id_usuario 
                        WHERE c.usuario_id_usuario = ?";
    $stmt_coordenador = $conn->prepare($sql_coordenador);
    $stmt_coordenador->bind_param("i", $usuario_id);
    $stmt_coordenador->execute();
    $result_coordenador = $stmt_coordenador->get_result();
    $coordenador = $result_coordenador->fetch_assoc();
    $curso_id = $coordenador['curso_id_curso'];
    
    // Contar professores supervisionados
    $sql_professores = "SELECT COUNT(*) as total FROM professor WHERE curso_id_curso = ?";
    $stmt_professores = $conn->prepare($sql_professores);
    $stmt_professores->bind_param("i", $curso_id);
    $stmt_professores->execute();
    $result_professores = $stmt_professores->get_result();
    $total_professores = $result_professores->fetch_assoc()['total'];
    
    // Calcular taxa de aprovação (exemplo simplificado)
    $sql_aprovacao = "SELECT 
                        (SUM(CASE WHEN nota >= 10 THEN 1 ELSE 0 END) / COUNT(*)) * 100 as taxa 
                      FROM nota n
                      JOIN aluno a ON n.aluno_id_aluno = a.id_aluno
                      JOIN turma t ON a.turma_id_turma = t.id_turma
                      WHERE t.curso_id_curso = ?";
    $stmt_aprovacao = $conn->prepare($sql_aprovacao);
    $stmt_aprovacao->bind_param("i", $curso_id);
    $stmt_aprovacao->execute();
    $result_aprovacao = $stmt_aprovacao->get_result();
    $taxa_aprovacao = $result_aprovacao->fetch_assoc()['taxa'] ?? 0;
    
    // Contar planos de ensino ativos
    $sql_planos = "SELECT COUNT(*) as total FROM plano_ensino 
                   WHERE id_disciplina IN (SELECT id_disciplina FROM disciplina WHERE curso_id_curso = ?)
                   AND status = 'aprovado'";
    $stmt_planos = $conn->prepare($sql_planos);
    $stmt_planos->bind_param("i", $curso_id);
    $stmt_planos->execute();
    $result_planos = $stmt_planos->get_result();
    $total_planos = $result_planos->fetch_assoc()['total'];
    
    // Últimas notas lançadas
    $sql_notas = "SELECT u.nome as aluno_nome, t.nome as turma_nome, d.nome as disciplina_nome, 
                  n.nota, n.data, n.tipo_avaliacao
                  FROM nota n
                  JOIN aluno a ON n.aluno_id_aluno = a.id_aluno
                  JOIN usuario u ON a.usuario_id_usuario = u.id_usuario
                  JOIN turma t ON a.turma_id_turma = t.id_turma
                  JOIN disciplina d ON n.disciplina_id_disciplina = d.id_disciplina
                  WHERE t.curso_id_curso = ?
                  ORDER BY n.data DESC LIMIT 5";
    $stmt_notas = $conn->prepare($sql_notas);
    $stmt_notas->bind_param("i", $curso_id);
    $stmt_notas->execute();
    $result_notas = $stmt_notas->get_result();
    
    // Últimos comunicados
    $sql_comunicados = "SELECT c.titulo, c.mensagem, c.data, u.nome as autor
                       FROM comunicado c
                       JOIN usuario u ON c.usuario_id_usuario = u.id_usuario
                       JOIN comunicado_destinatario cd ON c.id_comunicado = cd.comunicado_id
                       WHERE cd.tipo_destinatario = 'coordenador' OR cd.tipo_destinatario = 'todos'
                       ORDER BY c.data DESC LIMIT 3";
    $stmt_comunicados = $conn->prepare($sql_comunicados);
    $stmt_comunicados->execute();
    $result_comunicados = $stmt_comunicados->get_result();
?>
<!DOCTYPE html>
<html lang="pt">

<?php require_once '../../includes/common//head.php'; ?>

<body>
    <?php require_once '../../includes/common/preloader.php'; ?>

    <div id="pcoded" class="pcoded">
        <div class="pcoded-overlay-box"></div>
        <div class="pcoded-container navbar-wrapper">

            <?php require_once '../../includes/coordenador/navbar.php'; ?>

            <!--sidebar-->
            <div class="pcoded-main-container">
                <div class="pcoded-wrapper">
                    <?php require_once '../../includes/coordenador/sidebar.php'; ?>
                    
                    <div class="pcoded-content">
                        <div class="pcoded-inner-content">
                            <div class="main-body bg-img">
                                <div class="page-wrapper">

                                    <div class="page-body">
                                        <?php if(!isset($coordenador)): ?>
                                            <div class="alert alert-warning">
                                                <strong>Aviso:</strong> Seu perfil de coordenador não está completamente configurado. Por favor, contate a administração.
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="row">
                                            <!-- Supervisão de Professores -->
                                            <a href="../coordenador/professores.php" class="col-xl-3 col-md-6 d-block text-decoration-none text-reset">
                                                <div class="card bg-c-yellow update-card">
                                                    <div class="card-block">
                                                        <div class="row align-items-end">
                                                            <div class="col-8">
                                                                <h4 class="text-white"><?php echo $total_professores; ?></h4>
                                                                <h6 class="text-white m-b-0">
                                                                    <i class="feather icon-users"></i> Professores Supervisionados
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
                                        
                                            <!-- Desempenho Acadêmico -->
                                            <a href="../coordenador/desempenho.php" class="col-xl-3 col-md-6 d-block text-decoration-none text-reset">
                                                <div class="card bg-c-green update-card">
                                                    <div class="card-block">
                                                        <div class="row align-items-end">
                                                            <div class="col-8">
                                                                <h4 class="text-white"><?php echo round($taxa_aprovacao, 1); ?>%</h4>
                                                                <h6 class="text-white m-b-0">
                                                                    <i class="feather icon-bar-chart-2"></i> Taxa de Aprovação
                                                                </h6>
                                                            </div>
                                                            <div class="col-4 text-right">
                                                                <i class="feather icon-bar-chart-2 text-white" style="font-size: 40px;"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card-footer">
                                                        <p class="text-white m-b-0">
                                                            <i class="feather icon-clock text-white f-14 m-r-10"></i> Atualizado hoje
                                                        </p>
                                                    </div>
                                                </div>
                                            </a>
                                        
                                            <!-- Planos de Ensino -->
                                            <a href="../coordenador/planos.php" class="col-xl-3 col-md-6 d-block text-decoration-none text-reset">
                                                <div class="card bg-c-blue update-card">
                                                    <div class="card-block">
                                                        <div class="row align-items-end">
                                                            <div class="col-8">
                                                                <h4 class="text-white"><?php echo $total_planos; ?></h4>
                                                                <h6 class="text-white m-b-0">
                                                                    <i class="feather icon-book-open"></i> Planos de Ensino Ativos
                                                                </h6>
                                                            </div>
                                                            <div class="col-4 text-right">
                                                                <i class="feather icon-book-open text-white" style="font-size: 40px;"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card-footer">
                                                        <p class="text-white m-b-0">
                                                            <i class="feather icon-clock text-white f-14 m-r-10"></i> Última revisão: 2 dias atrás
                                                        </p>
                                                    </div>
                                                </div>
                                            </a>
                                            
                                        <div class="card mr-2 mb-4 card-table col-12">
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
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php if($result_notas->num_rows > 0): ?>
                                                                <?php while($nota = $result_notas->fetch_assoc()): ?>
                                                                    <tr>
                                                                        <td><?php echo htmlspecialchars($nota['aluno_nome']); ?></td>
                                                                        <td><?php echo htmlspecialchars($nota['turma_nome']); ?></td>
                                                                        <td><?php echo htmlspecialchars($nota['disciplina_nome']); ?></td>
                                                                        <td><?php echo htmlspecialchars($nota['nota']); ?></td>
                                                                        <td><?php echo date('d/m/Y', strtotime($nota['data'])); ?></td>
                                                                    </tr>
                                                                <?php endwhile; ?>
                                                            <?php else: ?>
                                                                <tr>
                                                                    <td colspan="5" class="text-center">Nenhuma nota lançada recentemente</td>
                                                                </tr>
                                                            <?php endif; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card mb-4 card-table col-12">
                                            <div class="card-header">
                                                <h5>Últimos Avisos Enviados</h5>
                                            </div>
                                            <div class="card-block">
                                                <ul class="list-group">
                                                    <?php if($result_comunicados->num_rows > 0): ?>
                                                        <?php while($comunicado = $result_comunicados->fetch_assoc()): ?>
                                                            <li class="list-group-item text-white card-table">
                                                                <i class="feather icon-bell text-danger"></i> 
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
        $stmt_coordenador->close();
        $stmt_professores->close();
        $stmt_aprovacao->close();
        $stmt_planos->close();
        $stmt_notas->close();
        $stmt_comunicados->close();
        $conn->close();
    ?>
    
    <?php require_once '../../includes/common/js_imports.php'; ?>
</body>

</html>