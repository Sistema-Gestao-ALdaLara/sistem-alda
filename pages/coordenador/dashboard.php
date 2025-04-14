<?php
    require_once '../../includes/common/permissoes.php';
    verificarPermissao(['coordenador']);
    require_once '../../process/verificar_sessao.php';

    $title = "Coordenador";
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
                                        <div class="row">
                                            <!-- Supervisão de Professores -->
                                            <a href="/coordenador/professores.php" class="col-xl-3 col-md-6 d-block text-decoration-none text-reset">
                                                <div class="card bg-c-yellow update-card">
                                                    <div class="card-block">
                                                        <div class="row align-items-end">
                                                            <div class="col-8">
                                                                <h4 class="text-white">12</h4>
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
                                            <a href="/coordenador/desempenho.php" class="col-xl-3 col-md-6 d-block text-decoration-none text-reset">
                                                <div class="card bg-c-green update-card">
                                                    <div class="card-block">
                                                        <div class="row align-items-end">
                                                            <div class="col-8">
                                                                <h4 class="text-white">78%</h4>
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
                                            <a href="/coordenador/planos.php" class="col-xl-3 col-md-6 d-block text-decoration-none text-reset">
                                                <div class="card bg-c-blue update-card">
                                                    <div class="card-block">
                                                        <div class="row align-items-end">
                                                            <div class="col-8">
                                                                <h4 class="text-white">9</h4>
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
                                        
                                            <!-- Ajustes de Turmas -->
                                            <a href="/coordenador/ajustes.php" class="col-xl-3 col-md-6 d-block text-decoration-none text-reset">
                                                <div class="card bg-c-pink update-card">
                                                    <div class="card-block">
                                                        <div class="row align-items-end">
                                                            <div class="col-8">
                                                                <h4 class="text-white">4</h4>
                                                                <h6 class="text-white m-b-0">
                                                                    <i class="feather icon-settings"></i> Ajustes Pendentes
                                                                </h6>
                                                            </div>
                                                            <div class="col-4 text-right">
                                                                <i class="feather icon-settings text-white" style="font-size: 40px;"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card-footer">
                                                        <p class="text-white m-b-0">
                                                            <i class="feather icon-clock text-white f-14 m-r-10"></i> Verificar ajustes
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
                                                                <td>9º A</td>
                                                                <td>Matemática</td>
                                                                <td>8.5</td>
                                                                <td>20/03/2025</td>
                                                            </tr>
                                                            <tr>
                                                                <td>Maria Santos</td>
                                                                <td>8º B</td>
                                                                <td>História</td>
                                                                <td>7.0</td>
                                                                <td>18/03/2025</td>
                                                            </tr>
                                                            <tr>
                                                                <td>Carlos Oliveira</td>
                                                                <td>7º C</td>
                                                                <td>Física</td>
                                                                <td>9.2</td>
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
                                                    <li class="list-group-item text-white card-table">
                                                        <i class="feather icon-bell text-danger"></i> <span>Prova de Matemática adiada para 25/03.</span>
                                                    </li>
                                                    <li class="list-group-item text-white card-table">
                                                        <i class="feather icon-bell text-danger"></i> <span>Entrega do trabalho de História até sexta-feira.</span>
                                                    </li>
                                                    <li class="list-group-item text-white card-table">
                                                        <i class="feather icon-bell text-danger"></i> <span>Revisão para a avaliação de Ciências amanhã.</span>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                        
                                        <div class="card mb-4 card-table">
                                            <div class="card-header">
                                                <h5>Ajustes Acadêmicos Pendentes</h5>
                                            </div>
                                            <div class="card-block table-border-style">
                                                <div class="table-responsive">
                                                    <table class="table table-bordered table-custom">
                                                        <thead>
                                                            <tr>
                                                                <th>Solicitação</th>
                                                                <th>Turma</th>
                                                                <th>Data</th>
                                                                <th>Status</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td>Alteração de horário</td>
                                                                <td>11º B</td>
                                                                <td>20/03/2025</td>
                                                                <td><span class="badge badge-warning">Pendente</span></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Fusão de turmas</td>
                                                                <td>10º C</td>
                                                                <td>18/03/2025</td>
                                                                <td><span class="badge badge-danger">Em Análise</span></td>
                                                            </tr>
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


    <?php require_once '../../includes/common/js_imports.php'; ?>
</body>

</html>
