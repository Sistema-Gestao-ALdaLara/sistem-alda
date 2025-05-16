<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<nav class="pcoded-navbar">
    <div class="pcoded-inner-navbar main-menu">
        <div class="pcoded-navigatio-lavel">Navegação</div>
        <ul class="pcoded-item pcoded-left-item">

            <!-- 1. VISÃO GERAL -->
            <li class="pcoded-hasmenu <?= ($current_page == 'dashboard.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="../diretor_geral/dashboard.php">
                    <span class="pcoded-micon"><i class="feather icon-home"></i></span>
                    <span class="pcoded-mtext">Dashboard</span>
                </a>
            </li>

            <!-- 2. GESTÃO ACADÊMICA -->
            <div class="pcoded-navigatio-lavel">Gestão Acadêmica</div>
            
            <li class="pcoded-hasmenu <?= ($current_page == 'visualizar_relatorio.php' || $current_page == 'relatorio.php') ? 'active pcoded-trigger' : '' ?>">
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
                    <li class="<?= ($current_page == 'relatorio.php') ? 'active' : '' ?>">
                        <a href="../compartilhados/relatorios.php">
                            <span class="pcoded-mtext">Gerar</span>
                        </a>
                    </li>
                </ul>
            </li>
            
            <li class="<?= ($current_page == 'aprovacao_planos.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="../compartilhados/aprovacao_planos.php">
                    <span class="pcoded-micon"><i class="feather icon-check-circle"></i></span>
                    <span class="pcoded-mtext">Aprovação de Planos</span>
                </a>
            </li>
            
            <li class="<?= ($current_page == 'cursos.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="../diretor_geral/cursos.php">
                    <span class="pcoded-micon"><i class="feather icon-book"></i></span>
                    <span class="pcoded-mtext">Cursos</span>
                </a>
            </li>

            <!-- 3. GESTÃO DE PESSOAS -->
            <div class="pcoded-navigatio-lavel">Gestão de Usuários</div>
            
            <li class="pcoded-hasmenu <?= (in_array($current_page, ['matricular.php', 'professor.php', 'coordenador.php'])) ? 'active pcoded-trigger' : '' ?>">
                <a href="javascript:void(0)">
                    <span class="pcoded-micon"><i class="feather icon-users"></i></span>
                    <span class="pcoded-mtext">Usuários</span>
                </a>
                <ul class="pcoded-submenu">
                    <!-- Alunos -->
                    <li class="pcoded-hasmenu <?= ($current_page == 'matricular.php') ? 'active' : '' ?>">
                        <a href="javascript:void(0)">
                            <span class="pcoded-mtext">Alunos</span>
                        </a>
                        <ul class="pcoded-submenu">
                            <li class="<?= ($current_page == 'matricular.php') ? 'active' : '' ?>">
                                <a href="../compartilhados/matricular.php">
                                    <span class="pcoded-mtext">Matrículas</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <!-- Professores -->
                    <li class="pcoded-hasmenu <?= ($current_page == 'professor.php') ? 'active' : '' ?>">
                        <a href="javascript:void(0)">
                            <span class="pcoded-mtext">Professores</span>
                        </a>
                        <ul class="pcoded-submenu">
                            <li class="<?= ($current_page == 'professor.php') ? 'active' : '' ?>">
                                <a href="../compartilhados/professor.php">
                                    <span class="pcoded-mtext">Cadastrar</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <!-- Coordenadores -->
                    <li class="pcoded-hasmenu <?= ($current_page == 'coordenador.php') ? 'active' : '' ?>">
                        <a href="javascript:void(0)">
                            <span class="pcoded-mtext">Coordenadores</span>
                        </a>
                        <ul class="pcoded-submenu">
                            <li class="<?= ($current_page == 'coordenador.php') ? 'active' : '' ?>">
                                <a href="../diretor_geral/coordenador.php">
                                    <span class="pcoded-mtext">Cadastrar</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </li>

            <!-- 4. ADMINISTRAÇÃO -->
            <div class="pcoded-navigatio-lavel">Administração</div>
            
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
            
        </ul>
    </div>
</nav>