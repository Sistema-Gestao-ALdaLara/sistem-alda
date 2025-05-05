<?php require_once '../../includes/common/funcoes.php'; ?>
<nav class="navbar header-navbar pcoded-header">
    <div class="navbar-wrapper">

        <div class="navbar-logo">
            <a class="mobile-menu" id="mobile-collapse" href="#!">
                <i class="feather icon-menu"></i>
            </a>
            <a href="dashboard.htm">
                <img class="img-fluid" src="../../public/libraries/assets/images/logo.png" height="50px" width="50px" alt="Theme-Logo"> <span class="font-italic font-weight-bold text-uppercase text-warning text-center">D. GERAL|Alda Lara</span>
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
                            <?php
                            require_once '../../database/conexao.php';
                            
                            $id_usuario = $_SESSION['id_usuario'];
                            $nome_usuario = $_SESSION['nome_usuario'];
                            
                            $query = "SELECT foto_perfil FROM usuario WHERE id_usuario = ?";
                            $stmt = $conn->prepare($query);
                            $stmt->bind_param('i', $id_usuario);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            
                            if ($result->num_rows > 0) {
                                $user = $result->fetch_assoc();
                                $foto_perfil = $user['foto_perfil'];
                                
                                if (!empty($foto_perfil) && file_exists('../../' . $foto_perfil)) {
                                    echo '<img src="../../' . htmlspecialchars($foto_perfil) . '" class="img-radius" alt="User-Profile-Image">';
                                } else {
                                    // Mostra avatar com a primeira letra do nome
                                    $inicial = strtoupper(substr($nome_usuario, 0, 1));
                                    echo '<div class="avatar-inicial" style="display:inline-block; width:40px; height:40px; border-radius:50%; background-color:gray; font-size: 16px; color:black; text-align:center; line-height:40px; font-weight:bold;">' . $inicial . '</div>';
                                }
                            } else {
                                echo '<img src="../../public/libraries/assets/images/avatar-4.jpg" class="img-radius" alt="User-Profile-Image">';
                            }
                            ?>
                            <span><?php echo htmlspecialchars($nome_usuario); ?></span>
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