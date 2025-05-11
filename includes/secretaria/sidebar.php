<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<nav class="pcoded-navbar">
    <div class="pcoded-inner-navbar main-menu">
        <div class="pcoded-navigatio-lavel">Navegacao</div>
        <ul class="pcoded-item pcoded-left-item">
            <li class="pcoded-hasmenu <?= ($current_page == 'dashboard.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="../secretaria/dashboard.php">
                    <span class="pcoded-micon"><i class="feather icon-home"></i></span>
                    <span class="pcoded-mtext">Dashboard</span>
                </a>
            </li>
            <li class="pcoded-hasmenu <?= ($current_page == 'matricula.php') ? 'active pcoded-trigger' : '' ?> <?= ($current_page == 'tranferencias.php') ? 'active' : '' ?></li>">
                <a href="../compartilhados/matricular.php">
                    <span class="pcoded-micon"><i class="feather icon-user-plus"></i></span>
                    <span class="pcoded-mtext">Matrículas</span>
                </a>
            </li>
            <li class="pcoded-hasmenu<?= ($current_page == 'turmas.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="../secretaria/turmas.php">
                    <span class="pcoded-micon"><i class="feather icon-layers"></i></span>
                    <span class="pcoded-mtext">Gerenciar Turmas</span>
                </a>
            </li>
            <li class="pcoded-hasmenu <?= ($current_page == 'matricula.php') ? 'active pcoded-trigger' : '' ?> <?= ($current_page == 'tranferencias.php') ? 'active' : '' ?></li>">
                <a href="javascript:void(0)">
                    <span class="pcoded-micon"><i class="feather icon-user-plus"></i></span>
                    <span class="pcoded-mtext">Gerenciar Usuarios</span>
                </a>
                <ul class="pcoded-submenu">
                    <li class="<?= ($current_page == 'professor.php') ? 'active' : '' ?> ">
                        <a href="../compartilhados/professor.php">
                            <span class="pcoded-mtext">G. Professor</span>
                        </a>
                    </li>
                </ul>
            </li>
            <li class="pcoded-hasmenu<?= ($current_page == 'boletins.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="../secretaria/boletins.php">
                    <span class="pcoded-micon"><i class="feather icon-layers"></i></span>
                    <span class="pcoded-mtext">Boletins</span>
                </a>
            </li>
            <li class="pcoded-hasmenu <?= ($current_page == 'admins_docs.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="../compartilhados/admins_docs.php">
                    <span class="pcoded-micon"><i class="feather icon-layers"></i></span>
                    <span class="pcoded-mtext">Docs Administrativos</span>
                </a>
            </li>
            <li class="pcoded-hasmenu <?= ($current_page == 'comunicados.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="../compartilhados/comunicados.php">
                    <span class="pcoded-micon"><i class="feather icon-mail"></i></span>
                    <span class="pcoded-mtext">Envio de Comunicados</span>
                </a>
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
                    <li class="<?= ($current_page == 'relatorio.php') ? 'active pcoded-trigger' : '' ?>">
                        <a href="../compartilhados/relatorios.php">
                            <span class="pcoded-mtext">Criar</span>
                        </a>
                    </li>

                </ul>
            </li>
            
        </ul>
    </div>
</nav>

