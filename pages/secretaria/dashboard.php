<?php
    require_once '../../includes/common/permissoes.php';
    verificarPermissao(['secretaria']);
    require_once '../../process/verificar_sessao.php';

    $title = "Secretaria";
?>
<!DOCTYPE html>
<html lang="pt">

<?php require_once '../../includes/common/head.php'; ?>

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
                                        <div class="row">
                                            <!-- Total de Alunos Ativos -->
                                            <a href="/secretaria/alunos.php" class="col-xl-3 col-md-6 d-block text-decoration-none text-reset">
                                                <div class="card bg-c-yellow update-card">
                                                    <div class="card-block">
                                                        <div class="row align-items-end">
                                                            <div class="col-8">
                                                                <h4 class="text-white">850</h4>
                                                                <h6 class="text-white m-b-0">
                                                                    <i class="feather icon-users"></i> Alunos Ativos
                                                                </h6>
                                                            </div>
                                                            <div class="col-4 text-right">
                                                                <i class="feather icon-users text-white" style="font-size: 40px;"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card-footer">
                                                        <p class="text-white m-b-0">
                                                            <i class="feather icon-clock text-white f-14 m-r-10"></i> Atualizado: Hoje
                                                        </p>
                                                    </div>
                                                </div>
                                            </a>
                                        
                                            <!-- Alunos Transferidos Recentemente -->
                                            <a href="/secretaria/matriculas.php?tipo=transferencias" class="col-xl-3 col-md-6 d-block text-decoration-none text-reset">
                                                <div class="card bg-c-green update-card">
                                                    <div class="card-block">
                                                        <div class="row align-items-end">
                                                            <div class="col-8">
                                                                <h4 class="text-white">35</h4>
                                                                <h6 class="text-white m-b-0">
                                                                    <i class="feather icon-refresh-ccw"></i> Transferências Recentes
                                                                </h6>
                                                            </div>
                                                            <div class="col-4 text-right">
                                                                <i class="feather icon-refresh-ccw text-white" style="font-size: 40px;"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card-footer">
                                                        <p class="text-white m-b-0">
                                                            <i class="feather icon-clock text-white f-14 m-r-10"></i> Atualizado: Hoje
                                                        </p>
                                                    </div>
                                                </div>
                                            </a>
                                        
                                            <!-- Matrículas Pendentes -->
                                            <a href="/secretaria/matriculas.php?tipo=pendentes" class="col-xl-3 col-md-6 d-block text-decoration-none text-reset">
                                                <div class="card bg-c-pink update-card">
                                                    <div class="card-block">
                                                        <div class="row align-items-end">
                                                            <div class="col-8">
                                                                <h4 class="text-white">12</h4>
                                                                <h6 class="text-white m-b-0">
                                                                    <i class="feather icon-edit"></i> Matrículas Pendentes
                                                                </h6>
                                                            </div>
                                                            <div class="col-4 text-right">
                                                                <i class="feather icon-edit text-white" style="font-size: 40px;"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card-footer">
                                                        <p class="text-white m-b-0">
                                                            <i class="feather icon-clock text-white f-14 m-r-10"></i> Atualizado: Hoje
                                                        </p>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        
                                        <!-- Tabela com Últimos Registros (Matrículas e Transferências) -->
                                        <div class="card card-table">
                                            <div class="card-header">
                                                <h5>Últimos Registros (Matrículas e Transferências)</h5>
                                            </div>
                                            <div class="card-block table-border-style">
                                                <div class="table-responsive">
                                                    <table class="table table-bordered table-custom">
                                                        <thead>
                                                            <tr>
                                                                <th>#</th>
                                                                <th>Aluno</th>
                                                                <th>Turma</th>
                                                                <th>Tipo</th>
                                                                <th>Data</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <th scope="row">1</th>
                                                                <td>João Silva</td>
                                                                <td>I11AM</td>
                                                                <td>Matrícula</td>
                                                                <td>10/03/2025</td>
                                                            </tr>
                                                            <tr>
                                                                <th scope="row">2</th>
                                                                <td>Maria Santos</td>
                                                                <td>I11BM</td>
                                                                <td>Transferência</td>
                                                                <td>09/03/2025</td>
                                                            </tr>
                                                            <tr>
                                                                <th scope="row">3</th>
                                                                <td>Carlos Oliveira</td>
                                                                <td>I11CM</td>
                                                                <td>Matrícula</td>
                                                                <td>08/03/2025</td>
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
