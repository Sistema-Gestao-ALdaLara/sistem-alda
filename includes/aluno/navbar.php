<?php require_once '../../includes/common/funcoes.php'; ?>
<nav class="navbar header-navbar pcoded-header">
    <div class="navbar-wrapper">

        <div class="navbar-logo">
            <a class="mobile-menu" id="mobile-collapse" href="#!">
                <i class="feather icon-menu"></i>
            </a>
            <a href="dashboard.htm">
                <img class="img-fluid" src="../../public/libraries/assets/images/logo.png" height="50px" width="50px" alt="Theme-Logo"> <span class="font-italic font-weight-bold text-uppercase text-warning text-center">ALUNO |Alda Lara</span>
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
                <li class="header-notification">
                    <div class="dropdown-primary dropdown">
                        <div class="dropdown-toggle" data-toggle="dropdown">
                            <i class="feather icon-bell"></i>
                            <span class="badge bg-c-pink"><?php
                                // Consulta para obter comunicados relevantes para o usuário atual
                                $query = "SELECT c.*, u.nome AS remetente 
                                        FROM comunicado c
                                        JOIN usuario u ON c.usuario_id_usuario = u.id_usuario
                                        ORDER BY c.data DESC
                                        LIMIT 5";
                                $result = $conn->query($query);
                                $total_comunicados = $result->num_rows;
                                ?>
                                <?php if ($total_comunicados > 0): ?>
                                    <?= $total_comunicados ?>
                                <?php endif; ?></span>
                        </div>
                        <ul class="show-notification notification-view dropdown-menu" data-dropdown-in="fadeIn" data-dropdown-out="fadeOut">
                            <li>
                                <h6>Comunicados</h6>
                                <?php
                                // Consulta para obter comunicados relevantes para o usuário atual
                                $query = "SELECT c.*, u.nome AS remetente 
                                        FROM comunicado c
                                        JOIN usuario u ON c.usuario_id_usuario = u.id_usuario
                                        ORDER BY c.data DESC
                                        LIMIT 5";
                                $result = $conn->query($query);
                                $total_comunicados = $result->num_rows;
                                ?>
                                <?php if ($total_comunicados > 0): ?>
                                    <label class="label label-danger"><?= $total_comunicados ?> novo(s)</label>
                                <?php endif; ?>
                            </li>
                            
                            <?php if ($total_comunicados > 0): ?>
                                <?php while ($comunicado = $result->fetch_assoc()): ?>
                                    <li>
                                        <div class="media">
                                            <img class="d-flex align-self-center img-radius" 
                                                src="<?= obterFotoPerfil($comunicado['usuario_id_usuario']) ?>" 
                                                alt="Foto do remetente">
                                            <div class="media-body">
                                                <h5 class="notification-user"><?= htmlspecialchars($comunicado['remetente']) ?></h5>
                                                <p class="notification-msg"><?= htmlspecialchars(substr(strip_tags($comunicado['titulo']), 0, 50)) ?></p>
                                                <span class="notification-time"><?= formatarDataRelativa($comunicado['data']) ?></span>
                                            </div>
                                        </div>
                                    </li>
                                <?php endwhile; ?>
                                <li class="text-center">
                                    <a href="comunicados.php" class="text-primary">Ver todos os comunicados</a>
                                </li>
                            <?php else: ?>
                                <li class="text-center">
                                    <p>Nenhum comunicado disponível</p>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </li>
                <li class="user-profile header-notification">
                    <div class="dropdown-primary dropdown">
                        <div class="dropdown-toggle" data-toggle="dropdown">
                            <img src="../../public/libraries/assets/images/avatar-4.jpg" class="img-radius" alt="User-Profile-Image">
                            <span><?php echo htmlspecialchars($_SESSION['nome_usuario']); ?></span>
                            <i class="feather icon-chevron-down"></i>
                        </div>
                        <ul class="show-notification profile-notification dropdown-menu" data-dropdown-in="fadeIn" data-dropdown-out="fadeOut">
                            <li>
                                <a href="user-profile.htm">
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