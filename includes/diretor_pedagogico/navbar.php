<?php require_once '../../includes/common/funcoes.php'; ?>
<nav class="navbar header-navbar pcoded-header">
    <div class="navbar-wrapper">

        <div class="navbar-logo">
            <a class="mobile-menu" id="mobile-collapse" href="#!">
                <i class="feather icon-menu"></i>
            </a>
            <a href="dashboard.htm">
                <img class="img-fluid" src="../../public/libraries/assets/images/logo.png" height="50px" width="50px" alt="Theme-Logo"> <span class="font-italic font-weight-bold text-uppercase text-warning text-center">D. PEDAGOGICO|Alda Lara</span>
            </a>
            <a class="mobile-options">
                <i class="feather icon-more-horizontal"></i>
            </a>
        </div>

        <div class="navbar-container container-fluid">
            <ul class="nav-left">
                <li class="header-search">
                    <div class="main-search morphsearch-search">
                        <div class="input-group">
                            <span class="input-group-addon search-close"><i class="feather icon-x"></i></span>
                            <input type="text" class="form-control">
                            <span class="input-group-addon search-btn"><i class="feather icon-search"></i></span>
                        </div>
                    </div>
                </li>
                <li>
                    <a href="#!" onclick="javascript:toggleFullScreen()">
                        <i class="feather icon-maximize full-screen"></i>
                    </a>
                </li>
            </ul>
            <ul class="nav-right">
                <?php require_once '../../includes/common/header_notificacoes.php'; ?>
                <li class="user-profile header-notification">
                    <div class="dropdown-primary dropdown">
                        <div class="dropdown-toggle" data-toggle="dropdown">
                            <img src="../../public/libraries/assets/images/avatar-4.jpg" class="img-radius" alt="User-Profile-Image">
                            <span><?php echo htmlspecialchars($_SESSION['nome_usuario']); ?></span>
                            <i class="feather icon-chevron-down"></i>
                        </div>
                        <ul class="show-notification profile-notification dropdown-menu" data-dropdown-in="fadeIn" data-dropdown-out="fadeOut">
                            <li>
                                <a href="../compartilhados/perfil.php">
                                    <i class="feather icon-user"></i> Perfil
                                </a>
                            </li>
                            <li>
                                <a href="../../process/logout.php">
                                    <i class="feather icon-log-out"></i> Sair
                                </a>
                            </li>
                        </ul>
                    </div>
                </li> 
            </ul>
        </div>
    </div>
</nav>