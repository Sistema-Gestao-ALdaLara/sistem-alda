<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<nav class="pcoded-navbar">
    <div class="pcoded-inner-navbar main-menu">
        <div class="pcoded-navigatio-lavel">Navegacao</div>
        <ul class="pcoded-item pcoded-left-item">
            <li class="pcoded-hasmenu  pcoded-hasmenu <?= ($current_page == 'dashboard.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="../diretor_pedagogico/dashboard.php">
                    <span class="pcoded-micon"><i class="feather icon-home"></i></span>
                    <span class="pcoded-mtext">Dashboard</span>
                </a>
            </li>
            <li class="pcoded-hasmenu <?= ($current_page == 'admins_docs.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="../compartilhados/admins_docs.php">
                    <span class="pcoded-micon"><i class="feather icon-layers"></i></span>
                    <span class="pcoded-mtext">Documentos Administrativos</span>
                </a>
            </li>
            <li class=" pcoded-hasmenu <?= ($current_page == 'professor.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="javascript:void(0)">
                    <span class="pcoded-micon"><i class="feather icon-layers"></i></span>
                    <span class="pcoded-mtext">Gerenciar Profs.</span>
                </a>
                <ul class="pcoded-submenu">
                    <li class=" <?= ($current_page == 'professor.php') ? 'active pcoded-trigger' : '' ?>">
                        <a href="../compartilhados/professor.php">
                            <span class="pcoded-mtext">Registrar</span>
                        </a>
                    </li>
                    <li class=" <?= ($current_page == 'professor.php') ? 'active pcoded-trigger' : '' ?>">
                        <a href="#">
                            <span class="pcoded-mtext">Task2</span>
                        </a>
                    </li>
                    <li class=" pcoded-hasmenu <?= ($current_page == '.php') ? 'active pcoded-trigger' : '' ?>">
                        <a href="#">
                            <span class="pcoded-mtext">Task3</span>
                        </a>
                    </li>

                </ul>
            </li>

            <li class=" pcoded-hasmenu <?= ($current_page == $p) ? 'active pcoded-trigger' : '' ?>">
                <a href="javascript:void(0)">
                    <span class="pcoded-micon"><i class="feather icon-layers"></i></span>
                    <span class="pcoded-mtext">Supervisao de ensino</span>
                </a>
                <ul class="pcoded-submenu">
                    <li class=" pcoded-hasmenu <?= ($current_page == 'matricular.php') ? 'active pcoded-trigger'&($p = $current_page) : '' ?>">
                        <a href="../compartilhados/matricular.php">
                            <span class="pcoded-mtext">Matrículas</span>
                        </a>
                    </li>
                    <li class=" pcoded-hasmenu <?= ($current_page == 'planos_ensino.php') ? 'active pcoded-trigger'&($p = $current_page) : '' ?>">
                        <a href="../compartilhados/planos_ensino.php">
                            <span class="pcoded-mtext">Planos de Aula</span>
                        </a>
                    </li>
                </ul>
            </li>
            <li class="pcoded-hasmenu <?= ($current_page == 'visualizar_relatorio.php' || $current_page == 'relatorio.php' ) ? 'active pcoded-trigger' : '' ?>">
                <a href="javascript:void(0)">
                    <span class="pcoded-micon"><i class="feather icon-layers"></i></span>
                    <span class="pcoded-mtext">Relatórios Acadêmicos</span>
                </a>
                <ul class="pcoded-submenu">
                    <li class="<?= ($current_page == 'visualizar_relatorio.php') ? 'active pcoded-trigger' : '' ?>">
                        <a href="../compartilhados/visualizar_relatorio.php">
                            <span class="pcoded-mtext">Visualizar</span>
                        </a>
                    </li>
                    <li class="<?= ($current_page == 'relatorios.php') ? 'active pcoded-trigger' : '' ?>">
                        <a href="../compartilhados/relatorios.php">
                            <span class="pcoded-mtext">Criar</span>
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</nav>