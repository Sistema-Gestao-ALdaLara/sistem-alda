<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<nav class="pcoded-navbar">
    <div class="pcoded-inner-navbar main-menu">
        <div class="pcoded-navigatio-lavel">Navegação</div>
        <ul class="pcoded-item pcoded-left-item">

            <!-- 1. VISÃO GERAL -->
            <li class="pcoded-hasmenu <?= ($current_page == 'dashboard.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="../diretor_pedagogico/dashboard.php">
                    <span class="pcoded-micon"><i class="feather icon-home"></i></span>
                    <span class="pcoded-mtext">Dashboard</span>
                </a>
            </li>

            <!-- 2. SUPERVISÃO PEDAGÓGICA -->
            <div class="pcoded-navigatio-lavel">Supervisão Pedagógica</div>
            
            <li class="pcoded-hasmenu <?= (in_array($current_page, ['matricular.php', 'planos_ensino.php'])) ? 'active pcoded-trigger' : '' ?>">
                <a href="javascript:void(0)">
                    <span class="pcoded-micon"><i class="feather icon-book-open"></i></span>
                    <span class="pcoded-mtext">Gestão Acadêmica</span>
                </a>
                <ul class="pcoded-submenu">
                    <li class="<?= ($current_page == 'matricular.php') ? 'active' : '' ?>">
                        <a href="../compartilhados/matricular.php">
                            <span class="pcoded-mtext">Matrículas</span>
                        </a>
                    </li>
                    <li class="<?= ($current_page == 'aprovacao_planos.php') ? 'active' : '' ?>">
                        <a href="../compartilhados/aprovacao_planos.php">
                            <span class="pcoded-mtext">Aprovação de Planos</span>
                        </a>
                    </li>
                </ul>
            </li>
            
            <li class="pcoded-hasmenu <?= (in_array($current_page, ['professor.php'])) ? 'active pcoded-trigger' : '' ?>">
                <a href="javascript:void(0)">
                    <span class="pcoded-micon"><i class="feather icon-users"></i></span>
                    <span class="pcoded-mtext">Corpo Docente</span>
                </a>
                <ul class="pcoded-submenu">
                    <li class="<?= ($current_page == 'professor.php') ? 'active' : '' ?>">
                        <a href="../compartilhados/professor.php">
                            <span class="pcoded-mtext">Registro de Professores</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- 3. RELATÓRIOS E DOCUMENTOS -->
            <div class="pcoded-navigatio-lavel">Relatórios e Documentos</div>
            
            <li class="pcoded-hasmenu <?= (in_array($current_page, ['visualizar_relatorio.php', 'relatorio.php'])) ? 'active pcoded-trigger' : '' ?>">
                <a href="javascript:void(0)">
                    <span class="pcoded-micon"><i class="feather icon-file-text"></i></span>
                    <span class="pcoded-mtext">Relatórios</span>
                </a>
                <ul class="pcoded-submenu">
                    <li class="<?= ($current_page == 'visualizar_relatorio.php') ? 'active' : '' ?>">
                        <a href="../compartilhados/visualizar_relatorio.php">
                            <span class="pcoded-mtext">Visualizar</span>
                        </a>
                    </li>
                    <li class="<?= ($current_page == 'relatorios.php') ? 'active' : '' ?>">
                        <a href="../compartilhados/relatorios.php">
                            <span class="pcoded-mtext">Gerar</span>
                        </a>
                    </li>
                </ul>
            </li>
            
            <li class="<?= ($current_page == 'admins_docs.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="../compartilhados/admins_docs.php">
                    <span class="pcoded-micon"><i class="feather icon-file"></i></span>
                    <span class="pcoded-mtext">Documentos</span>
                </a>
            </li>
        </ul>
    </div>
</nav>