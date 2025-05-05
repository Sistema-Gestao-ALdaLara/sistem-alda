<li class="header-notification">
    <div class="dropdown-primary dropdown">
        <div class="dropdown-toggle" data-toggle="dropdown">
            <i class="feather icon-bell"></i>
            <?php
            // Consulta para contar comunicados relevantes para o usuário atual
            $tipo_usuario1 = $_SESSION['tipo_usuario'];
            
            $query = "SELECT COUNT(DISTINCT c.id_comunicado) AS total
                      FROM comunicado c
                      JOIN comunicado_destinatario cd ON c.id_comunicado = cd.comunicado_id
                      WHERE cd.tipo_destinatario = ? OR cd.tipo_destinatario = 'todos'";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $tipo_usuario1);
            $stmt->execute();
            $result = $stmt->get_result();
            $total_comunicados1 = $result->fetch_assoc()['total'];
            
            if ($total_comunicados1 > 0): ?>
                <span class="badge bg-c-pink"><?= $total_comunicados1 ?></span>
            <?php endif; ?>
        </div>
        <ul class="show-notification notification-view dropdown-menu" data-dropdown-in="fadeIn" data-dropdown-out="fadeOut">
            <li>
                <h6>Comunicados</h6>
                <?php if ($total_comunicados1 > 0): ?>
                    <label class="label label-danger"><?= $total_comunicados1 ?></label>
                <?php endif; ?>
            </li>
            
            <?php
            // Consulta para obter os comunicados mais recentes para o usuário
            $query = "SELECT c.*, u.nome AS remetente
                      FROM comunicado c
                      JOIN usuario u ON c.usuario_id_usuario = u.id_usuario
                      JOIN comunicado_destinatario cd ON c.id_comunicado = cd.comunicado_id
                      WHERE cd.tipo_destinatario = ? OR cd.tipo_destinatario = 'todos'
                      GROUP BY c.id_comunicado
                      ORDER BY c.data DESC
                      LIMIT 5";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $tipo_usuario1);
            $stmt->execute();
            $result = $stmt->get_result();
            $comunicado1s1 = $result->fetch_all(MYSQLI_ASSOC);
            ?>
            
            <?php if (!empty($comunicado1s1)): ?>
                <?php foreach ($comunicado1s1 as $comunicado1): ?>
                    <li>
                        <div class="media">
                            <img class="d-flex align-self-center img-radius" 
                                src="<?= obterFotoPerfil1($comunicado1['usuario_id_usuario']) ?>" 
                                alt="Foto do remetente">
                            <div class="media-body">
                                <h5 class="notification-user"><?= htmlspecialchars($comunicado1['remetente']) ?></h5>
                                <p class="notification-msg"><?= htmlspecialchars(substr(strip_tags($comunicado1['titulo']), 0, 50)) ?></p>
                                <span class="notification-time"><?= formatarDataRelativa($comunicado1['data']) ?></span>
                            </div>
                        </div>
                    </li>
                <?php endforeach; ?>
                <li class="text-center">
                    <a href="../compartilhados/visualizar_comunicados.php" class="text-primary">Ver todos os comunicados</a>
                </li>
            <?php else: ?>
                <li class="text-center">
                    <p>Nenhum comunicado disponível</p>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</li>
