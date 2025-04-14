<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<nav class="pcoded-navbar">
    <div class="pcoded-inner-navbar main-menu">
        <div class="pcoded-navigatio-lavel">Navegacao</div>
        <ul class="pcoded-item pcoded-left-item">
            <li class="pcoded-hasmenu <?= ($current_page == 'dashboard.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="./dashboard.php">
                    <span class="pcoded-micon"><i class="feather icon-home"></i></span>
                    <span class="pcoded-mtext">Dashboard</span>
                </a>
            </li>
            <li class="pcoded-hasmenu <?= ($current_page == 'matricula.php') ? 'active pcoded-trigger' : '' ?> <?= ($current_page == 'tranferencias.php') ? 'active' : '' ?></li>">
                <a href="javascript:void(0)">
                    <span class="pcoded-micon"><i class="feather icon-user-plus"></i></span>
                    <span class="pcoded-mtext">Matrículas</span>
                </a>
                <ul class="pcoded-submenu">
                    <li class="<?= ($current_page == 'matricula.php') ? 'active' : '' ?> ">
                        <a href="./matricula.php">
                            <span class="pcoded-mtext">Matrículas</span>
                        </a>
                    </li>
                    <li class="<?= ($current_page == 'tranferencias.php') ? 'active' : '' ?> ">
                        <a href="#">
                            <span class="pcoded-mtext">Transferências</span>
                        </a>
                    </li>
                </ul>
            </li>
            <li class="pcoded-hasmenu <?= ($current_page == 'matricula.php') ? 'active pcoded-trigger' : '' ?> <?= ($current_page == 'tranferencias.php') ? 'active' : '' ?></li>">
                <a href="javascript:void(0)">
                    <span class="pcoded-micon"><i class="feather icon-user-plus"></i></span>
                    <span class="pcoded-mtext">Gerenciar Usuarios</span>
                </a>
                <ul class="pcoded-submenu">
                    <li class="<?= ($current_page == 'matricula.php') ? 'active' : '' ?> ">
                        <a href="./professor.php">
                            <span class="pcoded-mtext">G. Professor</span>
                        </a>
                    </li>
                    <li class="<?= ($current_page == 'coordenador.php') ? 'active' : '' ?> ">
                        <a href="./coordenador.php">
                            <span class="pcoded-mtext">G. Coordenador</span>
                        </a>
                    </li>
                </ul>
            </li>
            <li class="pcoded-hasmenu<?= ($current_page == 'turmas.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="./turmas.php">
                    <span class="pcoded-micon"><i class="feather icon-layers"></i></span>
                    <span class="pcoded-mtext">Gerenciar Turmas</span>
                </a>
            </li>
            <li class="pcoded-hasmenu <?= ($current_page == 'documentos.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="./documentos.php">
                    <span class="pcoded-micon"><i class="feather icon-file-text"></i></span>
                    <span class="pcoded-mtext">Documentos Admins.</span>
                </a>
            </li>
            <li class="pcoded-hasmenu <?= ($current_page == 'comunicados.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="./comunicados.php">
                    <span class="pcoded-micon"><i class="feather icon-mail"></i></span>
                    <span class="pcoded-mtext">Envio de Comunicados</span>
                </a>
            </li>
            <li class="pcoded-hasmenu <?= ($current_page == 'relatorios.php') ? 'active' : '' ?>">
                <a href="./relatorios.php">
                    <span class="pcoded-micon"><i class="feather icon-bar-chart"></i></span>
                    <span class="pcoded-mtext">Relatórios Acadêmicos</span>
                </a>
            </li>
            
        </ul>
    </div>
</nav>