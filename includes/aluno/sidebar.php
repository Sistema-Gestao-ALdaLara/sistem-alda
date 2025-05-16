<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav class="pcoded-navbar">
    <div class="pcoded-inner-navbar main-menu">
        <div class="pcoded-navigatio-lavel">Navegação</div>
        <ul class="pcoded-item pcoded-left-item">

            <!-- 1. VISÃO GERAL -->
            <li class="pcoded-hasmenu <?= ($current_page == 'dashboard.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="../aluno/dashboard.php">
                    <span class="pcoded-micon"><i class="feather icon-home"></i></span>
                    <span class="pcoded-mtext">Dashboard</span>
                </a>
            </li>

            <!-- 2. VIDA ACADÊMICA -->
            <div class="pcoded-navigatio-lavel">Vida Acadêmica</div>
            
            <li class="<?= ($current_page == 'disciplinas.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="../aluno/disciplinas.php">
                    <span class="pcoded-micon"><i class="feather icon-book"></i></span>
                    <span class="pcoded-mtext">Minhas Disciplinas</span>
                </a>
            </li>
            
            <li class="<?= ($current_page == 'notas.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="../aluno/notas.php">
                    <span class="pcoded-micon"><i class="feather icon-bar-chart"></i></span>
                    <span class="pcoded-mtext">Minhas Notas</span>
                </a>
            </li>
            
            <li class="<?= ($current_page == 'calendario.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="../aluno/calendario.php">
                    <span class="pcoded-micon"><i class="feather icon-calendar"></i></span>
                    <span class="pcoded-mtext">Horários e Calendário</span>
                </a>
            </li>
            
            <li class="<?= ($current_page == 'materiais.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="../aluno/materiais.php">
                    <span class="pcoded-micon"><i class="feather icon-folder"></i></span>
                    <span class="pcoded-mtext">Materiais de Apoio</span>
                </a>
            </li>

            <!-- 3. COMUNICAÇÃO -->
            <div class="pcoded-navigatio-lavel">Comunicação</div>
            
            <li class="<?= ($current_page == 'visualizar_comunicados.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="../compartilhados/visualizar_comunicados.php">
                    <span class="pcoded-micon"><i class="feather icon-bell"></i></span>
                    <span class="pcoded-mtext">Notificações</span>
                </a>
            </li>
            
            <li class="<?= ($current_page == 'contato.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="../aluno/contato.php">
                    <span class="pcoded-micon"><i class="feather icon-mail"></i></span>
                    <span class="pcoded-mtext">Contato com a Escola</span>
                </a>
            </li>

            <!-- 4. CONFIGURAÇÕES -->
            <div class="pcoded-navigatio-lavel">Configurações</div>
            
            <li class="<?= ($current_page == 'perfil.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="../compartilhados/perfil.php">
                    <span class="pcoded-micon"><i class="feather icon-user"></i></span>
                    <span class="pcoded-mtext">Meu Perfil</span>
                </a>
            </li>

        </ul>
    </div>
</nav>