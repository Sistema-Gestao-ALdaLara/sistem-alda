<?php
    require_once '../../includes/common/permissoes.php';
    verificarPermissao(['aluno']);
    require_once '../../process/verificar_sessao.php';
    require_once '../../database/conexao.php';

    $title = "Aluno";

    // Obter dados do aluno
    $id_usuario = $_SESSION['id_usuario'];
    $dados_aluno = [];
    $turma_aluno = '';
    $curso_aluno = '';
    $ano_letivo = '';
    $media_geral = 0;
    $presenca_percentual = 0;
    $ultimas_notas = [];

    // Dados básicos do aluno
    $sql = "SELECT a.*, u.nome, u.foto_perfil, u.email, t.nome as nome_turma, c.nome as nome_curso, 
                   m.ano_letivo as ano_letivo_matricula
            FROM aluno a 
            JOIN usuario u ON a.usuario_id_usuario = u.id_usuario 
            JOIN turma t ON a.turma_id_turma = t.id_turma 
            JOIN curso c ON a.curso_id_curso = c.id_curso
            JOIN matricula m ON m.aluno_id_aluno = a.id_aluno
            WHERE u.id_usuario = ? AND m.status_matricula = 'ativa'";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    $dados_aluno = $result->fetch_assoc();
    $stmt->close();

    if ($dados_aluno) {
        $turma_aluno = $dados_aluno['nome_turma'];
        $curso_aluno = $dados_aluno['nome_curso'];
        $ano_letivo = $dados_aluno['ano_letivo_matricula'] ?? $dados_aluno['ano_letivo'];
        
        // Calcular média geral
        $sql_media = "SELECT AVG(nota) as media 
                      FROM nota 
                      WHERE aluno_id_aluno = ?";
        $stmt_media = $conn->prepare($sql_media);
        $stmt_media->bind_param("i", $dados_aluno['id_aluno']);
        $stmt_media->execute();
        $result_media = $stmt_media->get_result();
        $media = $result_media->fetch_assoc();
        $media_geral = $media['media'] ? round($media['media'], 1) : 0;
        $stmt_media->close();
        
        // Calcular percentual de presença
        $sql_presenca = "SELECT 
                            (SUM(CASE WHEN presenca = 'presente' THEN 1 ELSE 0 END) / COUNT(*)) * 100 as percentual 
                         FROM frequencia_aluno 
                         WHERE aluno_id_aluno = ?";
        $stmt_presenca = $conn->prepare($sql_presenca);
        $stmt_presenca->bind_param("i", $dados_aluno['id_aluno']);
        $stmt_presenca->execute();
        $result_presenca = $stmt_presenca->get_result();
        $presenca = $result_presenca->fetch_assoc();
        $presenca_percentual = $presenca['percentual'] ? round($presenca['percentual'], 0) : 0;
        $stmt_presenca->close();
        
        // Obter últimas notas
        $sql_notas = "SELECT n.nota, d.nome as disciplina, n.data, n.tipo_avaliacao 
                      FROM nota n 
                      JOIN disciplina d ON n.disciplina_id_disciplina = d.id_disciplina 
                      WHERE n.aluno_id_aluno = ? 
                      ORDER BY n.data DESC 
                      LIMIT 5";
        $stmt_notas = $conn->prepare($sql_notas);
        $stmt_notas->bind_param("i", $dados_aluno['id_aluno']);
        $stmt_notas->execute();
        $result_notas = $stmt_notas->get_result();
        while ($row = $result_notas->fetch_assoc()) {
            $ultimas_notas[] = $row;
        }
        $stmt_notas->close();
    }
?>

<!DOCTYPE html>
<html lang="pt">

<?php require_once '../../includes/common/head.php'; ?>

<body>
    <?php require_once '../../includes/common/preloader.php'; ?>

    <div id="pcoded" class="pcoded">
        <div class="pcoded-overlay-box"></div>
        <div class="pcoded-container navbar-wrapper">

            <?php require_once '../../includes/aluno/navbar.php'; ?>

            <!--sidebar-->
            <div class="pcoded-main-container">
                <div class="pcoded-wrapper">
                    <?php require_once '../../includes/aluno/sidebar.php'; ?>
                    
                    <div class="pcoded-content">
                        <div class="pcoded-inner-content">
                            <div class="main-body bg-img">
                                <div class="page-wrapper">

                                    <div class="page-body">
                                        <div class="row">
                                            <!-- Card de Perfil -->
                                            <div class="col-xl-4 col-md-6">
                                                <div class="card user-card">
                                                    <div class="card-block text-center">
                                                        <div class="user-image">
                                                            <img src="<?php echo $dados_aluno['foto_perfil'] ? '../../uploads/perfil/'.$dados_aluno['foto_perfil'] : '../../public/libraries/assets/images/avatar-4.jpg'; ?>" 
                                                                 class="img-radius" id="profile-pic" width="100" height="100" alt="Foto do Aluno">
                                                        </div>
                                                        <h4 class="m-t-15 text-uppercase"><?php echo htmlspecialchars($dados_aluno['nome'] ?? $_SESSION['nome_usuario']); ?></h4>
                                                        <p class="text-muted">Curso: <?php echo htmlspecialchars($curso_aluno); ?></p>
                                                        <p class="text-muted">Turma: <?php echo htmlspecialchars($turma_aluno); ?></p>
                                                        <p class="text-muted">Ano Letivo: <?php echo htmlspecialchars($dados_aluno['ano_letivo_matricula'] ?? ''); ?></p>
                                                        <button class="btn btn-light btn-sm" onclick="document.getElementById('file-input').click();">
                                                            <i class="feather icon-camera"></i> Alterar Foto
                                                        </button>
                                                        <input type="file" id="file-input" class="d-none" accept="image/*" onchange="validateImage()">
                                                    </div>
                                                    <div class="card-footer">
                                                        <p class="text-muted m-b-0">
                                                            <i class="feather icon-clock f-14 m-r-10"></i> Última atualização: <?php echo date('d/m/Y'); ?>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        
                                            <!-- Acesso Rápido -->
                                            <div class="col-xl-8 col-md-6">
                                                <div class="row">
                                                    <a href="disciplinas-aln.php" class="col-6 col-md-4 d-block text-decoration-none text-reset">
                                                        <div class="card bg-c-yellow">
                                                            <div class="card-block text-center">
                                                                <i class="feather icon-book f-30 text-white"></i>
                                                                <h6 class="text-white m-t-10">Minhas Disciplinas</h6>
                                                            </div>
                                                            <div class="card-footer bg-c-blue">
                                                                <p class="text-white m-b-0">
                                                                    <i class="feather icon-arrow-right f-14 m-r-10"></i> Acessar Disciplinas
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </a>
                                                    <a href="notas-aln.php" class="col-6 col-md-4 d-block text-decoration-none text-reset">
                                                        <div class="card bg-c-green">
                                                            <div class="card-block text-center">
                                                                <i class="feather icon-bar-chart f-30 text-white"></i>
                                                                <h6 class="text-white m-t-10">Minhas Notas</h6>
                                                            </div>
                                                            <div class="card-footer bg-c-yellow">
                                                                <p class="text-white m-b-0">
                                                                    <i class="feather icon-arrow-right f-14 m-r-10"></i> Ver Notas Recentes
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </a>
                                                    <a href="calendario-aln.php" class="col-6 col-md-4 d-block text-decoration-none text-reset">
                                                        <div class="card bg-c-blue">
                                                            <div class="card-block text-center">
                                                                <i class="feather icon-calendar f-30 text-white"></i>
                                                                <h6 class="text-white m-t-10">Calendário Escolar</h6>
                                                            </div>
                                                            <div class="card-footer bg-c-green">
                                                                <p class="text-white m-b-0">
                                                                    <i class="feather icon-arrow-right f-14 m-r-10"></i> Ver Eventos
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </a>
                                                </div>
                                            </div>
                                        
                                            <!-- Notas e Frequência -->
                                            <div class="col-xl-3 col-md-6">
                                                <div class="card bg-c-yellow update-card">
                                                    <div class="card-block">
                                                        <div class="row align-items-end">
                                                            <div class="col-8">
                                                                <h4 class="text-white"><?php echo $media_geral; ?>%</h4>
                                                                <h6 class="text-white m-b-0">
                                                                    <i class="feather icon-star"></i> Média Geral
                                                                </h6>
                                                            </div>
                                                            <div class="col-4 text-right">
                                                                <i class="feather icon-star text-white" style="font-size: 40px;"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card-footer">
                                                        <p class="text-white m-b-0">
                                                            <i class="feather icon-clock text-white f-14 m-r-10"></i> Atualizado hoje
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        
                                            <div class="col-xl-3 col-md-6">
                                                <div class="card bg-c-green update-card">
                                                    <div class="card-block">
                                                        <div class="row align-items-end">
                                                            <div class="col-8">
                                                                <h4 class="text-white"><?php echo $presenca_percentual; ?>%</h4>
                                                                <h6 class="text-white m-b-0">
                                                                    <i class="feather icon-check-circle"></i> Presença
                                                                </h6>
                                                            </div>
                                                            <div class="col-4 text-right">
                                                                <i class="feather icon-check-circle text-white" style="font-size: 40px;"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card-footer">
                                                        <p class="text-white m-b-0">
                                                            <i class="feather icon-clock text-white f-14 m-r-10"></i> Atualizado hoje
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        
                                            <!-- Últimas Notas -->
                                            <div class="col-xl-6">
                                                <div class="card card-table">
                                                    <div class="card-header">
                                                        <h5><i class="feather icon-award"></i> Últimas Notas</h5>
                                                    </div>
                                                    <div class="card-body">
                                                        <table class="table table-hover">
                                                            <thead>
                                                                <tr>
                                                                    <th>Disciplina</th>
                                                                    <th>Nota</th>
                                                                    <th>Tipo</th>
                                                                    <th>Data</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php if (count($ultimas_notas) > 0): ?>
                                                                    <?php foreach ($ultimas_notas as $nota): ?>
                                                                        <tr>
                                                                            <td><?php echo htmlspecialchars($nota['disciplina']); ?></td>
                                                                            <td>
                                                                                <?php 
                                                                                    $badge_class = '';
                                                                                    if ($nota['nota'] >= 15) {
                                                                                        $badge_class = 'badge-success';
                                                                                    } elseif ($nota['nota'] >= 10) {
                                                                                        $badge_class = 'badge-warning';
                                                                                    } else {
                                                                                        $badge_class = 'badge-danger';
                                                                                    }
                                                                                ?>
                                                                                <span class="badge <?php echo $badge_class; ?>"><?php echo $nota['nota']; ?></span>
                                                                            </td>
                                                                            <td><?php echo htmlspecialchars($nota['tipo_avaliacao']); ?></td>
                                                                            <td><?php echo date('d/m/Y', strtotime($nota['data'])); ?></td>
                                                                        </tr>
                                                                    <?php endforeach; ?>
                                                                <?php else: ?>
                                                                    <tr>
                                                                        <td colspan="4" class="text-center">Nenhuma nota registrada ainda</td>
                                                                    </tr>
                                                                <?php endif; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <div class="card-footer">
                                                        <p class="text-muted m-b-0">
                                                            <i class="feather icon-clock f-14 m-r-10"></i> Atualizado em <?php echo date('d/m/Y H:i'); ?>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require_once '../../includes/common/js_imports.php'; ?>

    <script>
        function validateImage() {
            let fileInput = document.getElementById('file-input');
            let profilePic = document.getElementById('profile-pic');
        
            if (fileInput.files.length > 0) {
                let file = fileInput.files[0];
        
                // Verifica o tipo de arquivo
                if (!file.type.startsWith('image/')) {
                    alert("Por favor, envie uma imagem válida.");
                    return;
                }
        
                // Verifica o tamanho do arquivo (máximo 2MB)
                if (file.size > 2 * 1024 * 1024) {
                    alert("A imagem deve ter no máximo 2MB.");
                    return;
                }
        
                // Mostra a imagem selecionada
                let reader = new FileReader();
                reader.onload = function(e) {
                    profilePic.src = e.target.result;
                    
                    // Aqui você pode adicionar código para enviar a imagem para o servidor
                    // Exemplo com AJAX:
                    /*
                    let formData = new FormData();
                    formData.append('foto_perfil', file);
                    formData.append('id_usuario', <?php echo $id_usuario; ?>);
                    
                    fetch('../../process/upload_foto_perfil.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Foto atualizada com sucesso!');
                        } else {
                            alert('Erro ao atualizar foto: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Erro ao enviar foto');
                    });
                    */
                };
                reader.readAsDataURL(file);
            }
        }
    </script>
</body>
</html>