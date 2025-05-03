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
            <li class="pcoded-hasmenu <?= ($current_page == '') ? 'active pcoded-trigger' : '' ?>">
                <a href="javascript:void(0)">
                    <span class="pcoded-micon"><i class="feather icon-layers"></i></span>
                    <span class="pcoded-mtext">Relatórios Admins</span>
                </a>
                <ul class="pcoded-submenu">
                    <li class="<?= ($current_page == '') ? 'active pcoded-trigger' : '' ?>">
                        <a href="#">
                            <span class="pcoded-mtext">Task1</span>
                        </a>
                    </li>
                    <li class="<?= ($current_page == '') ? 'active pcoded-trigger' : '' ?>">
                        <a href="#">
                            <span class="pcoded-mtext">Task2</span>
                        </a>
                    </li>
                    <li class="<?= ($current_page == '') ? 'active pcoded-trigger' : '' ?></li>">
                        <a href="#">
                            <span class="pcoded-mtext">Task3</span>
                        </a>
                    </li>

                </ul>
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
                        <a href="coordenador.php">
                            <span class="pcoded-mtext">G. Coordenadores</span>
                        </a>
                    </li>
                </ul>
            </li>
            <li class="pcoded-hasmenu <?= ($current_page == 'matricular.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="javascript:void(0)">
                    <span class="pcoded-micon"><i class="feather icon-layers"></i></span>
                    <span class="pcoded-mtext">Matrículas</span>
                </a>
                <ul class="pcoded-submenu">
                    <li class="<?= ($current_page == 'matricular.php') ? 'active pcoded-trigger' : '' ?>">
                        <a href="../compartilhados/matricular.php">
                            <span class="pcoded-mtext">Matrículas</span>
                        </a>
                    </li>
                </ul>
            </li>
            <li class="pcoded-hasmenu <?= ($current_page == 'cursos.php') ? 'active pcoded-trigger' : '' ?>">
                <a href="cursos.php">
                    <span class="pcoded-micon"><i class="feather icon-layers"></i></span>
                    <span class="pcoded-mtext">G. Cursos</span>
                </a>
            </li>

            <li class="pcoded-hasmenu <?= ($current_page == '') ? 'active pcoded-trigger' : '' ?>">
                <a href="javascript:void(0)">
                    <span class="pcoded-micon"><i class="feather icon-layers"></i></span>
                    <span class="pcoded-mtext">Configurações</span>
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