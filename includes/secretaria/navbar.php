<?php require_once '../../includes/common/funcoes.php'; ?>

<nav class="navbar header-navbar pcoded-header">
    <div class="navbar-wrapper">

        <div class="navbar-logo">
            <a class="mobile-menu" id="mobile-collapse" href="#!">
                <i class="feather icon-menu"></i>
            </a>
            <a href="dashboard.php">
                <img class="img-fluid" src="../../public/libraries/assets/images/logo.png" height="50px" width="50px" alt="Theme-Logo"> <span class="font-italic font-weight-bold text-uppercase text-warning text-center">SECRETARIA</span>
            </a>
            <a class="mobile-options">
                <i class="feather icon-more-horizontal"></i>
            </a>
        </div>

        <div class="navbar-container container-fluid">
            <ul class="nav-left">
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
                            // Conexão com o banco de dados (já deve estar estabelecida)
                            require_once '../../database/conexao.php';
                            
                            // ID do usuário logado
                            $id_usuario = $_SESSION['id_usuario'];
                            
                            // Consulta para obter a foto do usuário
                            $query = "SELECT foto_perfil FROM usuario WHERE id_usuario = ?";
                            $stmt = $conn->prepare($query);
                            $stmt->bind_param('i', $id_usuario);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            
                            if ($result->num_rows > 0) {
                                $user = $result->fetch_assoc();
                                $foto_perfil = $user['foto_perfil'];
                                
                                // Verifica se há foto cadastrada e se o arquivo existe
                                if (!empty($foto_perfil) && file_exists('../../' . $foto_perfil)) {
                                    $imagem_src = '../../' . htmlspecialchars($foto_perfil);
                                } else {
                                    // Imagem padrão caso não tenha foto ou o arquivo não exista
                                    $imagem_src = '../../public/libraries/assets/images/avatar-4.jpg';
                                }
                            } else {
                                // Imagem padrão caso ocorra algum erro na consulta
                                $imagem_src = '../../public/libraries/assets/images/avatar-4.jpg';
                            }
                            ?>
                            <img src="<?php echo $imagem_src; ?>" class="img-radius cover" height="50px" alt="User-Profile-Image">
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