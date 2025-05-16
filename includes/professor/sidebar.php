<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<nav class="pcoded-navbar">
    <div class="pcoded-inner-navbar main-menu">
        <div class="pcoded-navigatio-lavel">Navegação</div>
        <ul class="pcoded-item pcoded-left-item">

            <!-- 1. VISÃO GERAL -->
            <li class="pcoded-hasmenu <?= ($current_page == 'dashboard.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="../professor/dashboard.php">
                    <span class="pcoded-micon"><i class="feather icon-home"></i></span>
                    <span class="pcoded-mtext">Dashboard</span>
                </a>
            </li>

            <!-- 2. ATIVIDADES DE ENSINO -->
            <div class="pcoded-navigatio-lavel">Atividades de Ensino</div>
            
            <li class="<?= ($current_page == 'turmas.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="../professor/turmas.php">
                    <span class="pcoded-micon"><i class="feather icon-users"></i></span>
                    <span class="pcoded-mtext">Minhas Turmas</span>
                </a>
            </li>
            
            <li class="<?= ($current_page == 'alunos.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="../professor/alunos.php">
                    <span class="pcoded-micon"><i class="feather icon-user"></i></span>
                    <span class="pcoded-mtext">Perfil dos Alunos</span>
                </a>
            </li>

            <!-- 3. AVALIAÇÃO E FREQUÊNCIA -->
            <div class="pcoded-navigatio-lavel">Avaliação e Frequência</div>
            
            <li class="<?= ($current_page == 'notas.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="../professor/notas.php">
                    <span class="pcoded-micon"><i class="feather icon-check-square"></i></span>
                    <span class="pcoded-mtext">Lançamento de Notas</span>
                </a>
            </li>
            
            <li class="<?= ($current_page == 'frequencia.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="../professor/frequencia.php">
                    <span class="pcoded-micon"><i class="feather icon-calendar"></i></span>
                    <span class="pcoded-mtext">Registro de Frequência</span>
                </a>
            </li>

            <!-- 4. PLANEJAMENTO DIDÁTICO -->
            <div class="pcoded-navigatio-lavel">Planejamento Didático</div>
            
            <li class="<?= ($current_page == 'meus_planos.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="../professor/meus_planos.php">
                    <span class="pcoded-micon"><i class="feather icon-book"></i></span>
                    <span class="pcoded-mtext">Planos de Aula</span>
                </a>
            </li>
            
            <li class="<?= ($current_page == 'materiais.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="../professor/materiais.php">
                    <span class="pcoded-micon"><i class="feather icon-file-text"></i></span>
                    <span class="pcoded-mtext">Materiais Didáticos</span>
                </a>
            </li>

            <!-- 5. COMUNICAÇÃO E DOCUMENTOS -->
            <div class="pcoded-navigatio-lavel">Comunicação</div>
            
            <li class="<?= ($current_page == 'visualizar_comunicados.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="../compartilhados/visualizar_comunicados.php">
                    <span class="pcoded-micon"><i class="feather icon-bell"></i></span>
                    <span class="pcoded-mtext">Notificações</span>
                </a>
            </li>
            
            <li class="<?= ($current_page == 'admins_docs.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="../compartilhados/admins_docs.php">
                    <span class="pcoded-micon"><i class="feather icon-file"></i></span>
                    <span class="pcoded-mtext">Documentos</span>
                </a>
            </li>

            <!--  RELATÓRIOS -->
            <li class="<?= ($current_page == 'visualizar_relatorio.php') ? 'active' : '' ?>">
                <a href="../compartilhados/visualizar_relatorio.php">
                    <span class="pcoded-mtext">RELATÓRIOS</span>
                </a>
            </li>
            
        </ul>
    </div>
</nav>