<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<nav class="pcoded-navbar">
    <div class="pcoded-inner-navbar main-menu">
        <div class="pcoded-navigatio-lavel">Navegacao</div>
        <ul class="pcoded-item pcoded-left-item">
            <li class="pcoded-hasmenu <?= ($current_page == 'dashboard.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="../diretor_geral/dashboard.php">
                    <span class="pcoded-micon"><i class="feather icon-home"></i></span>
                    <span class="pcoded-mtext">Dashboard</span>
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
            <li class="pcoded-hasmenu <?= ($current_page == 'admins_docs.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="../compartilhados/admins_docs.php">
                    <span class="pcoded-micon"><i class="feather icon-layers"></i></span>
                    <span class="pcoded-mtext">Documentos Administrativos</span>
                </a>
            </li>
            <li class="pcoded-hasmenu <?= ($current_page == 'aprovacao_planos.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="../compartilhados/aprovacao_planos.php">
                    <span class="pcoded-micon"><i class="feather icon-layers"></i></span>
                    <span class="pcoded-mtext">Aprovar Planos-Aula</span>
                </a>
            </li>
            <li class="pcoded-hasmenu <?= ($current_page == '') ? 'active pcoded-trigger' : '' ?>">
                <a href="javascript:void(0)">
                    <span class="pcoded-micon"><i class="feather icon-sidebar"></i></span>
                    <span class="pcoded-mtext">Gerenciar Usuários</span>
                </a>
                <ul class="pcoded-submenu">
                    <li class=" pcoded-hasmenu <?= ($current_page == '') ? 'active pcoded-trigger' : '' ?>">
                        <a href="#">
                            <span class="pcoded-mtext">G. Alunos</span>
                        </a>
                        <ul class="pcoded-submenu">
                            <li class="<?= ($current_page == 'matricular.php') ? 'active pcoded-trigger' : '' ?>">
                                <a href="../compartilhados/matricular.php">
                                    <span class="pcoded-mtext">Fazer Matrículas</span>
                                </a>
                            </li>
                            <li class="<?= ($current_page == '') ? 'active pcoded-trigger' : '' ?>">
                                <a href="#">
                                    <span class="pcoded-mtext">Historico do aluno</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class=" pcoded-hasmenu <?= ($current_page == 'professor.php') ? 'active pcoded-trigger' : '' ?>"></li></li>
                        <a href="javascript:void(0)">
                            <span class="pcoded-mtext">G. Professores</span>
                        </a>
                        <ul class="pcoded-submenu">
                            <li class="<?= ($current_page == 'professor.php') ? 'active pcoded-trigger' : '' ?>">
                                <a href="../compartilhados/professor.php">
                                    <span class="pcoded-mtext">Cadastrar Professor</span>
                                </a>
                            </li>
                            <li class="<?= ($current_page == '') ? 'active pcoded-trigger' : '' ?>">
                                <a href="#" target="_blank">
                                    <span class="pcoded-mtext">Historico do Professor</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="<?= ($current_page == 'coordenador.php') ? 'active pcoded-trigger' : '' ?>">
                        <a href="../diretor_geral/coordenador.php">
                            <span class="pcoded-mtext">G. Coordenadores</span>
                        </a>
                    </li>
                </ul>
            </li>
            <li class="pcoded-hasmenu <?= ($current_page == 'cursos.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="../diretor_geral/cursos.php">
                    <span class="pcoded-micon"><i class="feather icon-layers"></i></span>
                    <span class="pcoded-mtext">Gerenciar Cursos</span>
                </a>
            </li>

            <li class="pcoded-hasmenu <?= ($current_page == '../compartilhados/comunicados.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="../compartilhados/comunicados.php">
                    <span class="pcoded-micon"><i class="feather icon-layers"></i></span>
                    <span class="pcoded-mtext">Comunicados</span>
                </a>
            </li>
        </ul>
    </div>
</nav>