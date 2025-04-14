<?php
    require_once '../../includes/common/permissoes.php';
    verificarPermissao(['professor']);
    require_once '../../process/verificar_sessao.php';
    
    $title = "Professor";
?>
<!DOCTYPE html>
<html lang="pt">

<?php require_once '../../includes/common//head.php'; ?>

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
                                                <a href="/professor/turmas.php" class="col-xl-3 col-md-6 d-block text-decoration-none text-reset">
                                                    <div class="card card-estatistica card-table update-card">
                                                        <div class="card-block">
                                                            <div class="row align-items-end">
                                                                <div class="col-8">
                                                                    <h4 class="text-white">5</h4>
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
                                                <a href="/professor/alunos.php" class="col-xl-3 col-md-6 d-block text-decoration-none text-reset">
                                                    <div class="card card-estatistica card-provas card-table update-card">
                                                        <div class="card-block">
                                                            <div class="row align-items-end">
                                                                <div class="col-8">
                                                                    <h4 class="text-white">120</h4>
                                                                    <h6 class="text-white m-b-0">
                                                                        <i class="feather icon-users"></i> Alunos Cadastrados
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
                                                <a href="/professor/notas.php" class="col-xl-3 col-md-6 d-block text-decoration-none text-reset">
                                                    <div class="card card-estatistica card-avaliacoes card-table update-card">
                                                        <div class="card-block">
                                                            <div class="row align-items-end">
                                                                <div class="col-8">
                                                                    <h4 class="text-white">32</h4>
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
                                                <a href="/professor/notas.php" class="col-xl-3 col-md-6 d-block text-decoration-none text-reset">
                                                    <div class="card card-estatistica card-trabalhos card-table update-card">
                                                        <div class="card-block">
                                                            <div class="row align-items-end">
                                                                <div class="col-8">
                                                                    <h4 class="text-white">10</h4>
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
                                            
                                                <!-- Avisos Enviados -->
                                                <a href="/professor/notificacoes.php" class="col-xl-3 col-md-6 d-block text-decoration-none text-reset">
                                                    <div class="card bg-c-blue update-card">
                                                        <div class="card-block">
                                                            <div class="row align-items-end">
                                                                <div class="col-8">
                                                                    <h4 class="text-white">
                                                                    <span class="badge"><?php
                                                                        // Consulta para obter comunicados relevantes para o usuário atual
                                                                        $query = "SELECT c.*, u.nome AS remetente 
                                                                                FROM comunicado c
                                                                                JOIN usuario u ON c.usuario_id_usuario = u.id_usuario
                                                                                ORDER BY c.data DESC
                                                                                LIMIT 5";
                                                                        $result = $conn->query($query);
                                                                        $total_comunicados = $result->num_rows;
                                                                        ?>
                                                                        <?php if ($total_comunicados > 0): ?>
                                                                            <?= $total_comunicados ?>
                                                                        <?php endif; ?>
                                                                    </span>
                                                                    </h4>
                                                                    <h6 class="text-white m-b-0">
                                                                        <i class="feather icon-bell"></i> Avisos Enviados
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
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td>João Silva</td>
                                                                    <td>I11AM</td>
                                                                    <td>Matemática</td>
                                                                    <td>12.5</td>
                                                                    <td>20/03/2025</td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Maria Santos</td>
                                                                    <td>I11BM</td>
                                                                    <td>Fisica</td>
                                                                    <td>13.0</td>
                                                                    <td>18/03/2025</td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Carlos Oliveira</td>
                                                                    <td>I11CM</td>
                                                                    <td>Física</td>
                                                                    <td>12.2</td>
                                                                    <td>17/03/2025</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="card mb-4 card-table">
                                                <div class="card-header">
                                                    <h5>Últimos Avisos Enviados</h5>
                                                </div>
                                                <div class="card-block">
                                                    <ul class="list-group">
                                                        <li class="list-group-item card-table">
                                                            <i class="feather icon-bell"></i> Prova de Matemática adiada para 25/03.
                                                        </li>
                                                        <li class="list-group-item card-table">
                                                            <i class="feather icon-bell"></i> Entrega do trabalho de História até sexta-feira.
                                                        </li>
                                                        <li class="list-group-item card-table">
                                                            <i class="feather icon-bell"></i> Revisão para a avaliação de Ciências amanhã.
                                                        </li>
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


        <?php require_once '../../includes/common/js_imports.php'; ?>
    </body>

</html>
