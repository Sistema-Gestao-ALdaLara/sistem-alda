<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<nav class="pcoded-navbar">
    <div class="pcoded-inner-navbar main-menu">
        <div class="pcoded-navigatio-lavel">Navegação</div>
        <ul class="pcoded-item pcoded-left-item">

            <!-- 1. VISÃO GERAL -->
            <li class="pcoded-hasmenu <?= ($current_page == 'dashboard.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="../secretaria/dashboard.php">
                    <span class="pcoded-micon"><i class="feather icon-home"></i></span>
                    <span class="pcoded-mtext">Dashboard</span>
                </a>
            </li>

            <!-- 2. GESTÃO DE MATRÍCULAS -->
            <div class="pcoded-navigatio-lavel">Gestão de Matrículas</div>
            
            <li class="<?= ($current_page == 'matricula.php' || $current_page == 'tranferencias.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="../compartilhados/matricular.php">
                    <span class="pcoded-micon"><i class="feather icon-user-plus"></i></span>
                    <span class="pcoded-mtext">Processos de Matrícula</span>
                </a>
            </li>

            <!-- 3. GESTÃO ACADÊMICA -->
            <div class="pcoded-navigatio-lavel">Gestão Acadêmica</div>
            
            <li class="<?= ($current_page == 'turmas.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="../secretaria/turmas.php">
                    <span class="pcoded-micon"><i class="feather icon-users"></i></span>
                    <span class="pcoded-mtext">Turmas</span>
                </a>
            </li>
            
            <li class="<?= ($current_page == 'boletins.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="../secretaria/boletins.php">
                    <span class="pcoded-micon"><i class="feather icon-file-text"></i></span>
                    <span class="pcoded-mtext">Boletins</span>
                </a>
            </li>

            <!-- 4. GESTÃO DE USUÁRIOS -->
            <div class="pcoded-navigatio-lavel">Gestão de Usuários</div>
            
            <li class="pcoded-hasmenu <?= ($current_page == 'professor.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="javascript:void(0)">
                    <span class="pcoded-micon"><i class="feather icon-user"></i></span>
                    <span class="pcoded-mtext">Usuários</span>
                </a>
                <ul class="pcoded-submenu">
                    <li class="<?= ($current_page == 'professor.php') ? 'active' : '' ?>">
                        <a href="../compartilhados/professor.php">
                            <span class="pcoded-mtext">Professores</span>
                        </a>
                    </li>
                    <!-- Pode adicionar outros tipos de usuários aqui -->
                </ul>
            </li>

            <!-- 5. DOCUMENTOS E COMUNICAÇÃO -->
            <div class="pcoded-navigatio-lavel">Documentos e Comunicação</div>
            
            <li class="<?= ($current_page == 'admins_docs.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="../compartilhados/admins_docs.php">
                    <span class="pcoded-micon"><i class="feather icon-file"></i></span>
                    <span class="pcoded-mtext">Documentos</span>
                </a>
            </li>
            
            <li class="<?= ($current_page == 'comunicados.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="../compartilhados/comunicados.php">
                    <span class="pcoded-micon"><i class="feather icon-mail"></i></span>
                    <span class="pcoded-mtext">Comunicados</span>
                </a>
            </li>

            <!-- 6. RELATÓRIOS -->
            <div class="pcoded-navigatio-lavel">Relatórios</div>
            
            <li class="pcoded-hasmenu <?= ($current_page == 'visualizar_relatorio.php' || $current_page == 'relatorio.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="javascript:void(0)">
                    <span class="pcoded-micon"><i class="feather icon-printer"></i></span>
                    <span class="pcoded-mtext">Relatórios</span>
                </a>
                <ul class="pcoded-submenu">
                    <li class="<?= ($current_page == 'visualizar_relatorio.php') ? 'active' : '' ?>">
                        <a href="../compartilhados/visualizar_relatorio.php">
                            <span class="pcoded-mtext">Visualizar</span>
                        </a>
                    </li>
                    <li class="<?= ($current_page == 'relatorio.php') ? 'active' : '' ?>">
                        <a href="../compartilhados/relatorios.php">
                            <span class="pcoded-mtext">Gerar</span>
                        </a>
                    </li>
                </ul>
            </li>
            
        </ul>
    </div>
</nav>