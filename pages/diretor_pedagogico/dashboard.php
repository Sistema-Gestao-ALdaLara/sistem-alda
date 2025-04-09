<?php
    require_once '../../includes/common/permissoes.php';
    verificarPermissao(['diretor_pedagogico']);
    require_once '../../process/verificar_sessao.php';
?>
<!DOCTYPE html>
<html lang="pt">

<?php require_once '../../includes/common//head.php'; ?>

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
                                            <!-- task start -->
                                            <!-- Total de Alunos Matriculados -->
                                            <a href="/diretor-pedagogico/relatorios.php" class="col-xl-3 col-md-6 d-block text-decoration-none text-reset">
                                                <div class="card bg-c-yellow update-card">
                                                    <div class="card-block">
                                                        <div class="row align-items-end">
                                                            <div class="col-8">
                                                                <h4 class="text-white">4.235</h4>
                                                                <h6 class="text-white m-b-0">
                                                                    <i class="feather icon-users"></i> Total de Alunos Matriculados
                                                                </h6>
                                                            </div>
                                                            <div class="col-4 text-right">
                                                                <i class="feather icon-users text-white" style="font-size: 40px;"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card-footer">
                                                        <p class="text-white m-b-0"><i class="feather icon-clock text-white f-14 m-r-10"></i>update : 2:15 am</p>
                                                    </div>
                                                </div>
                                            </a>

                                            <!-- Total de Professores Cadastrados -->
                                            <a href="/diretor-pedagogico/professores.php" class="col-xl-3 col-md-6 d-block text-decoration-none text-reset">
                                                <div class="card bg-c-green update-card">
                                                    <div class="card-block">
                                                        <div class="row align-items-end">
                                                            <div class="col-8">
                                                                <h4 class="text-white">127</h4>
                                                                <h6 class="text-white m-b-0">
                                                                    <i class="feather icon-briefcase"></i> Total de Professores
                                                                </h6>
                                                            </div>
                                                            <div class="col-4 text-right">
                                                                <i class="feather icon-briefcase text-white" style="font-size: 40px;"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card-footer">
                                                        <p class="text-white m-b-0"><i class="feather icon-clock text-white f-14 m-r-10"></i>update : 2:15 am</p>
                                                    </div>
                                                </div>
                                            </a>

                                            <!-- Relatório de Frequência dos Alunos -->
                                            <a href="/diretor-pedagogico/relatorios.php?tipo=frequencia" class="col-xl-3 col-md-6 d-block text-decoration-none text-reset">
                                                <div class="card bg-c-pink update-card">
                                                    <div class="card-block">
                                                        <div class="row align-items-end">
                                                            <div class="col-8">
                                                                <h4 class="text-white">145</h4>
                                                                <h6 class="text-white m-b-0">
                                                                    <i class="feather icon-check-square"></i> Relatório de Frequência
                                                                </h6>
                                                            </div>
                                                            <div class="col-4 text-right">
                                                                <i class="feather icon-check-square text-white" style="font-size: 40px;"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card-footer">
                                                        <p class="text-white m-b-0"><i class="feather icon-clock text-white f-14 m-r-10"></i>update : 2:15 am</p>
                                                    </div>
                                                </div>
                                            </a>

                                            <!-- Desempenho Acadêmico Geral -->
                                            <a href="/diretor-pedagogico/relatorios.php?tipo=desempenho" class="col-xl-3 col-md-6 d-block text-decoration-none text-reset">
                                                <div class="card bg-c-lite-green update-card">
                                                    <div class="card-block">
                                                        <div class="row align-items-end">
                                                            <div class="col-8">
                                                                <h4 class="text-white">85% / 15%</h4>
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
                                                        <p class="text-white m-b-0"><i class="feather icon-clock text-white f-14 m-r-10"></i>update : 2:15 am</p>
                                                    </div>
                                                </div>
                                            </a>

                                            <!-- task  end -->
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
</script>
</body>

</html>
