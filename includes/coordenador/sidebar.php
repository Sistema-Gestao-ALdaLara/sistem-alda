<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<nav class="pcoded-navbar">
    <div class="pcoded-inner-navbar main-menu">
        <div class="pcoded-navigatio-lavel">Navegação</div>
        <ul class="pcoded-item pcoded-left-item">

            <!-- Dashboard -->
            <li class="pcoded-hasmenu <?= ($current_page == 'dashboard.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="../coordenador/dashboard.php">
                    <span class="pcoded-micon"><i class="feather icon-home"></i></span>
                    <span class="pcoded-mtext">Dashboard</span>
                </a>
            </li>

            <!-- Supervisão de Professores -->
            <li class="<?= ($current_page == 'professores.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="../coordenador/professores.php">
                    <span class="pcoded-micon"><i class="feather icon-users"></i></span>
                    <span class="pcoded-mtext">Supervisão de Professores</span>
                </a>
            </licla>

            <!-- Desempenho dos Alunos -->
            <li class="<?= ($current_page == 'desempenho.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="../coordenador/desempenho.php">
                    <span class="pcoded-micon"><i class="feather icon-bar-chart-2"></i></span>
                    <span class="pcoded-mtext">Desempenho dos Alunos</span>
                </a>
            </li>

            <!-- Planos de Ensino -->
            <li class="<?= ($current_page == 'planos.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="../coordenador/planos.php">
                    <span class="pcoded-micon"><i class="feather icon-file-text"></i></span>
                    <span class="pcoded-mtext">Planos de Ensino</span>
                </a>
            </li>
            
            <li class="pcoded-hasmenu <?= ($current_page == 'admins_docs.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="../compartilhados/admins_docs.php">
                    <span class="pcoded-micon"><i class="feather icon-layers"></i></span>
                    <span class="pcoded-mtext">Documentos Administrativos</span>
                </a>
            </li>

            <!-- Lancar Disciplinas -->
            <li class="<?= ($current_page == 'disciplinas.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="../coordenador/disciplinas.php">
                    <span class="pcoded-micon"><i class="feather icon-book"></i></span>
                    <span class="pcoded-mtext">G. Disciplinas</span>
                </a>
            </li>

            <!-- Difinir Horarios do curso -->
            <li class="<?= ($current_page == 'horarios.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="../coordenador/horarios.php">
                    <span class="pcoded-micon"><i class="feather icon-clock"></i></span>
                    <span class="pcoded-mtext">Definir Horarios</span>
                </a>
            </li>

            <!-- Ajustes de Turmas e Disciplinas -->
            <li class="<?= ($current_page == 'turmas.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="./turmas.php">
                    <span class="pcoded-micon"><i class="feather icon-settings"></i></span>
                    <span class="pcoded-mtext">Ajustes de Turmas</span>
                </a>
            </li>

        </ul>
    </div>
</nav>