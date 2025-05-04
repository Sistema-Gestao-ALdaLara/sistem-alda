<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav class="pcoded-navbar">
    <div class="pcoded-inner-navbar main-menu">
        <div class="pcoded-navigatio-lavel">Navegação</div>
        <ul class="pcoded-item pcoded-left-item">

            <!-- Dashboard -->
            <li class="pcoded-hasmenu <?= ($current_page == 'dashboard.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="dashboard.php">
                    <span class="pcoded-micon"><i class="feather icon-home"></i></span>
                    <span class="pcoded-mtext">Dashboard</span>
                </a>
            </li>

            <!-- Minhas Disciplinas -->
            <li class="<?= ($current_page == 'disciplinas.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="disciplinas.php">
                    <span class="pcoded-micon"><i class="feather icon-book"></i></span>
                    <span class="pcoded-mtext">Minhas Disciplinas</span>
                </a>
            </li>

            <!-- Minhas Notas -->
            <li class="<?= ($current_page == 'notas.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="notas.php">
                    <span class="pcoded-micon"><i class="feather icon-bar-chart"></i></span>
                    <span class="pcoded-mtext">Minhas Notas</span>
                </a>
            </li>

            <!-- Horários e Calendário -->
            <li class="<?= ($current_page == 'calendario.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="calendario.php">
                    <span class="pcoded-micon"><i class="feather icon-calendar"></i></span>
                    <span class="pcoded-mtext">Horários e Calendário</span>
                </a>
            </li>

            <!-- Materiais de Apoio -->
            <li class="<?= ($current_page == 'materiais.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="materiais.php">
                    <span class="pcoded-micon"><i class="feather icon-folder"></i></span>
                    <span class="pcoded-mtext">Materiais de Apoio</span>
                </a>
            </li>

            <!-- Notificações -->
            <li class="<?= ($current_page == 'notificacoes.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="notificacoes.php">
                    <span class="pcoded-micon"><i class="feather icon-bell"></i></span>
                    <span class="pcoded-mtext">Notificações</span>
                </a>
            </li>

            <!-- Atualizar Perfil -->
            <li class="<?= ($current_page == 'perfil.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="../compartilhados/perfil.php">
                    <span class="pcoded-micon"><i class="feather icon-user"></i></span>
                    <span class="pcoded-mtext">Atualizar Perfil</span>
                </a>
            </li>

            <!-- Contato com a Escola -->
            <li class="<?= ($current_page == 'contato.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="contato.php">
                    <span class="pcoded-micon"><i class="feather icon-mail"></i></span>
                    <span class="pcoded-mtext">Contato com a Escola</span>
                </a>
            </li>

        </ul>
    </div>
</nav>