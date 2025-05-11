<nav class="pcoded-navbar">
    <div class="pcoded-inner-navbar main-menu">
        <div class="pcoded-navigatio-lavel">Navegacao</div>
        <ul class="pcoded-item pcoded-left-item">
            <li class="pcoded-hasmenu active pcoded-trigger">
                <a href="../professor/dashboard.php">
                    <span class="pcoded-micon"><i class="feather icon-home"></i></span>
                    <span class="pcoded-mtext">Dashboard</span>
                </a>
            </li>

            <li class="pcoded-hasmenu">
                <a href="../professor/turmas.php">
                    <span class="pcoded-micon"><i class="feather icon-layers"></i></span>
                    <span class="pcoded-mtext">Minhas Turmas</span>
                </a>
            </li>
            <li class="pcoded-hasmenu">
                <a href="../professor/notas.php">
                    <span class="pcoded-micon"><i class="feather icon-edit"></i></span>
                    <span class="pcoded-mtext">Lançamento de Notas</span>
                </a>
            </li>
            <li class="pcoded-hasmenu">
                <a href="../professor/frequencia.php">
                    <span class="pcoded-micon"><i class="feather icon-edit"></i></span>
                    <span class="pcoded-mtext">Frequencias</span>
                </a>
            </li>
            <li class="pcoded-hasmenu">
                <a href="../professor/meus_planos.php">
                    <span class="pcoded-micon"><i class="feather icon-edit"></i></span>
                    <span class="pcoded-mtext">Planos de Aula</span>
                </a>
            </li>
            <li class="pcoded-hasmenu <?= ($current_page == 'admins_docs.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="../compartilhados/admins_docs.php">
                    <span class="pcoded-micon"><i class="feather icon-layers"></i></span>
                    <span class="pcoded-mtext">Documentos Administrativos</span>
                </a>
            </li>
            <li class="pcoded-hasmenu <?= ($current_page == 'materiais.php') ? 'active pcoded-trigger' : '' ?></li>">
                <a href="../professor/materiais.php">
                    <span class="pcoded-micon"><i class="feather icon-file"></i></span>
                    <span class="pcoded-mtext">Materiais Academicos</span>
                </a>
            </li>
            <li class="pcoded-hasmenu">
                <a href="../professor/alunos.php">
                    <span class="pcoded-micon"><i class="feather icon-users"></i></span>
                    <span class="pcoded-mtext">Perfil dos Alunos</span>
                </a>
            </li>
            <li class="pcoded-hasmenu">
                <a href="../professor/notificacoes.php">
                    <span class="pcoded-micon"><i class="feather icon-bell"></i></span>
                    <span class="pcoded-mtext">Notificações e Avisos</span>
                </a>
            </li>
            <li class="pcoded-hasmenu">
                <a href="../professor/meus_planos.php">
                    <span class="pcoded-micon"><i class="feather icon-file-text"></i></span>
                    <span class="pcoded-mtext">Planos de Aula</span>
                </a>
            </li>
            
        </ul>
    </div>
</nav>