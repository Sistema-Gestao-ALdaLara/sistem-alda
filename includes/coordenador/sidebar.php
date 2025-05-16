<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<nav class="pcoded-navbar">
    <div class="pcoded-inner-navbar main-menu">
        <div class="pcoded-navigatio-lavel">Navegação</div>
        <ul class="pcoded-item pcoded-left-item">

            <!-- 1. DASHBOARD E VISÃO GERAL -->
            <li class="pcoded-hasmenu <?= ($current_page == 'dashboard.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="../coordenador/dashboard.php">
                    <span class="pcoded-micon"><i class="feather icon-home"></i></span>
                    <span class="pcoded-mtext">Dashboard</span>
                </a>
            </li>

            <!-- 2. GESTÃO PEDAGÓGICA -->
            <div class="pcoded-navigatio-lavel">Gestão Pedagógica</div>
            
            <li class="<?= ($current_page == 'professores.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="../coordenador/professores.php">
                    <span class="pcoded-micon"><i class="feather icon-users"></i></span>
                    <span class="pcoded-mtext">Supervisão de Professores</span>
                </a>
            </li>
            
            <li class="<?= ($current_page == 'desempenho.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="../coordenador/desempenho.php">
                    <span class="pcoded-micon"><i class="feather icon-bar-chart-2"></i></span>
                    <span class="pcoded-mtext">Desempenho dos Alunos</span>
                </a>
            </li>
            
            <li class="<?= ($current_page == 'planos.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="../coordenador/planos.php">
                    <span class="pcoded-micon"><i class="feather icon-file-text"></i></span>
                    <span class="pcoded-mtext">Planos de Ensino</span>
                </a>
            </li>

            <!-- 3. GESTÃO ACADÊMICA -->
            <div class="pcoded-navigatio-lavel">Gestão Acadêmica</div>
            
            <li class="<?= ($current_page == 'disciplinas.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="../coordenador/disciplinas.php">
                    <span class="pcoded-micon"><i class="feather icon-book"></i></span>
                    <span class="pcoded-mtext">Disciplinas</span>
                </a>
            </li>
            
            <li class="<?= ($current_page == 'horarios.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="../coordenador/horarios.php">
                    <span class="pcoded-micon"><i class="feather icon-clock"></i></span>
                    <span class="pcoded-mtext">Horários</span>
                </a>
            </li>
            
            <li class="<?= ($current_page == 'turmas.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="./turmas.php">
                    <span class="pcoded-micon"><i class="feather icon-settings"></i></span>
                    <span class="pcoded-mtext">Turmas</span>
                </a>
            </li>

            <!-- 4. DOCUMENTOS E RECURSOS -->
            <div class="pcoded-navigatio-lavel">Documentos e Recursos</div>
            
            <li class="pcoded-hasmenu <?= ($current_page == 'admins_docs.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="../compartilhados/admins_docs.php">
                    <span class="pcoded-micon"><i class="feather icon-layers"></i></span>
                    <span class="pcoded-mtext">Documentos Administrativos</span>
                </a>
            </li>

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

        </ul>
    </div>
</nav>