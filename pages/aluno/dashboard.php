<?php
    require_once '../../includes/common/permissoes.php';
    verificarPermissao(['aluno']);
    require_once '../../process/verificar_sessao.php';

    $title = "Aluno";
?>

<!DOCTYPE html>
<html lang="pt">

<?php require_once '../../includes/common//head.php'; ?>

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
                                                            <img src="../../public/libraries/assets/images/avatar-4.jpg" class="img-radius" id="profile-pic" width="100" height="100" alt="Foto do Aluno">
                                                        </div>
                                                        <h4 class="m-t-15 text-uppercase"><?php echo htmlspecialchars($_SESSION['nome_usuario']); ?></h4>
                                                        <p class="text-muted">Curso: Tecnico Informatica</p>
                                                        <p class="text-muted">Turma: I10AM</p>
                                                        <button class="btn btn-light btn-sm" onclick="document.getElementById('file-input').click();">
                                                            <i class="feather icon-camera"></i> Alterar Foto
                                                        </button>
                                                        <input type="file" id="file-input" class="d-none" accept="image/*" onchange="validateImage()">
                                                    </div>
                                                    <div class="card-footer">
                                                        <p class="text-muted m-b-0">
                                                            <i class="feather icon-clock f-14 m-r-10"></i> Última atualização: Hoje
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        
                                            <!-- Acesso Rápido -->
                                            <div class="col-xl-8 col-md-6">
                                                <div class="row">
                                                    <!--/aluno/disciplinas.php-->
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
                                                    <a href="/aluno/notas.php" class="col-6 col-md-4 d-block text-decoration-none text-reset">
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
                                                    <a href="/aluno/calendario.php" class="col-6 col-md-4 d-block text-decoration-none text-reset">
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
                                                                <h4 class="text-white">85%</h4>
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
                                                                <h4 class="text-white">92%</h4>
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
                                                            <i class="feather icon-clock text-white f-14 m-r-10"></i> Atualizado ontem
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
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td>Matemática</td>
                                                                    <td><span class="badge badge-success">15.0</span></td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Português</td>
                                                                    <td><span class="badge badge-warning">17.5</span></td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Fisica</td>
                                                                    <td><span class="badge badge-danger">13.0</span></td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <div class="card-footer">
                                                        <p class="text-muted m-b-0">
                                                            <i class="feather icon-clock f-14 m-r-10"></i> Atualizado recentemente
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
        
                // Simulando uma IA que valida rostos (apenas uma verificação básica)
                let fileName = file.name.toLowerCase();
                if (!fileName.includes("face") && !fileName.includes("selfie")) {
                    alert("A foto precisa ser do seu rosto!");
                    return;
                }
        
                // Atualiza a imagem do perfil (simulação)
                let reader = new FileReader();
                reader.onload = function(e) {
                    profilePic.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        }
    </script>

    <!-- Script para Validação da Foto -->
    <script>
        function validateImage() {
            let fileInput = document.getElementById('file-input');
            let profilePic = document.getElementById('profile-pic');
        
            if (fileInput.files.length > 0) {
                let file = fileInput.files[0];
        
                if (!file.type.startsWith('image/')) {
                    alert("Por favor, envie uma imagem válida.");
                    return;
                }
        
                let reader = new FileReader();
                reader.onload = function(e) {
                    profilePic.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        }
    </script>
</body>

</html>
