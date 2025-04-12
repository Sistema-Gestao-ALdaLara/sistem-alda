<?php
    require_once '../../includes/common/permissoes.php';
    verificarPermissao(['secretaria']);
    require_once '../../process/verificar_sessao.php';
    require_once '../../database/conexao.php';

    $titulo = "Documentos Administrativos";
    
    // Processar upload de documento
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['documento'])) {
        $titulo_doc = $conn->real_escape_string($_POST['titulo']);
        $descricao = $conn->real_escape_string($_POST['descricao']);
        $tipo = $conn->real_escape_string($_POST['tipo']);
        $id_usuario = $_SESSION['id_usuario'];
        
        // Diretório de upload
        $upload_dir = '../../uploads/documentos/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Processar arquivo
        $file_name = $_FILES['documento']['name'];
        $file_tmp = $_FILES['documento']['tmp_name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Extensões permitidas
        $allowed_ext = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png'];
        
        if (in_array($file_ext, $allowed_ext)) {
            // Renomear arquivo para evitar conflitos
            $new_file_name = uniqid('doc_', true) . '.' . $file_ext;
            $file_path = $upload_dir . $new_file_name;
            
            if (move_uploaded_file($file_tmp, $file_path)) {
                // Inserir no banco de dados
                $query = "INSERT INTO documentos_administrativos 
                          (titulo, descricao, caminho_arquivo, tipo, usuario_id_usuario) 
                          VALUES (?, ?, ?, ?, ?)";
                
                $stmt = $conn->prepare($query);
                $stmt->bind_param("ssssi", $titulo_doc, $descricao, $file_path, $tipo, $id_usuario);
                
                if ($stmt->execute()) {
                    $_SESSION['sucesso'] = "Documento enviado com sucesso!";
                } else {
                    $_SESSION['erro'] = "Erro ao salvar informações do documento no banco de dados.";
                }
            } else {
                $_SESSION['erro'] = "Erro ao fazer upload do arquivo.";
            }
        } else {
            $_SESSION['erro'] = "Tipo de arquivo não permitido. Formatos aceitos: " . implode(', ', $allowed_ext);
        }
        
        header('Location: documentos.php');
        exit();
    }
    
    // Processar exclusão de documento
    if (isset($_GET['excluir'])) {
        $id = intval($_GET['excluir']);
        
        // Primeiro, obter o caminho do arquivo
        $query = "SELECT caminho_arquivo FROM documentos_administrativos WHERE id_documento = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $documento = $result->fetch_assoc();
            
            // Excluir o arquivo físico
            if (file_exists($documento['caminho_arquivo'])) {
                unlink($documento['caminho_arquivo']);
            }
            
            // Excluir do banco de dados
            $query = "DELETE FROM documentos_administrativos WHERE id_documento = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                $_SESSION['sucesso'] = "Documento excluído com sucesso!";
            } else {
                $_SESSION['erro'] = "Erro ao excluir documento do banco de dados.";
            }
        } else {
            $_SESSION['erro'] = "Documento não encontrado.";
        }
        
        header('Location: documentos.php');
        exit();
    }
    
    // Obter documentos
    $query = "SELECT d.*, u.nome AS usuario_nome 
              FROM documentos_administrativos d
              JOIN usuario u ON d.usuario_id_usuario = u.id_usuario
              ORDER BY d.data_upload DESC";
    $result = $conn->query($query);
    $documentos = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt">

<?php require_once '../../includes/common/head.php'; ?>

<body>
    <?php require_once '../../includes/common/preloader.php'; ?>

    <div id="pcoded" class="pcoded">
        <div class="pcoded-overlay-box"></div>
        <div class="pcoded-container navbar-wrapper">

            <?php require_once '../../includes/secretaria/navbar.php'; ?>

            <!--sidebar-->
            <div class="pcoded-main-container">
                <div class="pcoded-wrapper">
                    <?php require_once '../../includes/secretaria/sidebar.php'; ?>
                    <div class="pcoded-content">
                        <div class="pcoded-inner-content">
                            <div class="main-body bg-img">
                                <div class="page-wrapper">

                                    <div class="page-body">
                                        <!-- Mensagens de feedback -->
                                        <?php if (isset($_SESSION['sucesso'])): ?>
                                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                                            <?= $_SESSION['sucesso'] ?>
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <?php unset($_SESSION['sucesso']); endif; ?>
                                        
                                        <?php if (isset($_SESSION['erro'])): ?>
                                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                            <?= $_SESSION['erro'] ?>
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <?php unset($_SESSION['erro']); endif; ?>
                                        
                                        <div class="row">
                                            <!-- Card de Upload de Documentos -->
                                            <div class="col-md-12">
                                                <div class="card card-table">
                                                    <div class="card-header bg-primary text-white">
                                                        <h5>Enviar Novo Documento</h5>
                                                    </div>
                                                    <div class="card-body">
                                                        <form method="POST" action="" enctype="multipart/form-data">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label for="titulo">Título do Documento *</label>
                                                                        <input type="text" class="form-control" id="titulo" name="titulo" required>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label for="tipo">Tipo de Documento *</label>
                                                                        <select class="form-control" id="tipo" name="tipo" required>
                                                                            <option value="">Selecione...</option>
                                                                            <option value="ata">Ata</option>
                                                                            <option value="oficio">Ofício</option>
                                                                            <option value="declaracao">Declaração</option>
                                                                            <option value="contrato">Contrato</option>
                                                                            <option value="relatorio">Relatório</option>
                                                                            <option value="outro">Outro</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="form-group">
                                                                <label for="descricao">Descrição</label>
                                                                <textarea class="form-control" id="descricao" name="descricao" rows="3"></textarea>
                                                            </div>
                                                            
                                                            <div class="form-group">
                                                                <label for="documento">Arquivo *</label>
                                                                <div class="custom-file">
                                                                    <input type="file" class="custom-file-input" id="documento" name="documento" required>
                                                                    <label class="custom-file-label" for="documento">Escolher arquivo...</label>
                                                                </div>
                                                                <div class="bg-primary text-white p-2">
                                                                    <small class="form-text">
                                                                        <span class="text-dark">FORMATOS ACEITOS:</span> PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, JPG, JPEG, PNG
                                                                    </small>
                                                                </div>
                                                            </div>
                                                            
                                                            <button type="submit" class="btn btn-primary">
                                                                <i class="feather icon-upload"></i> Enviar Documento
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Lista de Documentos -->
                                            <div class="col-md-12 mt-4">
                                                <div class="card card-table">
                                                    <div class="card-header">
                                                        <h5 class="text-white mb-0">Documentos Administrativos</h5>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="table-responsive">
                                                            <table class="table table-hover table-custom">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Título</th>
                                                                        <th>Tipo</th>
                                                                        <th>Descrição</th>
                                                                        <th>Enviado por</th>
                                                                        <th>Data</th>
                                                                        <th>Ações</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php if (empty($documentos)): ?>
                                                                    <tr>
                                                                        <td colspan="6" class="text-center">Nenhum documento encontrado</td>
                                                                    </tr>
                                                                    <?php else: ?>
                                                                    <?php foreach ($documentos as $doc): ?>
                                                                    <tr>
                                                                        <td><?= htmlspecialchars($doc['titulo']) ?></td>
                                                                        <td>
                                                                            <?php 
                                                                            $badge_class = '';
                                                                            switch($doc['tipo']) {
                                                                                case 'ata': $badge_class = 'badge-primary'; break;
                                                                                case 'oficio': $badge_class = 'badge-secondary'; break;
                                                                                case 'declaracao': $badge_class = 'badge-success'; break;
                                                                                case 'contrato': $badge_class = 'badge-danger'; break;
                                                                                case 'relatorio': $badge_class = 'badge-warning'; break;
                                                                                default: $badge_class = 'badge-info';
                                                                            }
                                                                            ?>
                                                                            <span class="badge <?= $badge_class ?>">
                                                                                <?= ucfirst($doc['tipo']) ?>
                                                                            </span>
                                                                        </td>
                                                                        <td><?= htmlspecialchars($doc['descricao']) ?></td>
                                                                        <td><?= htmlspecialchars($doc['usuario_nome']) ?></td>
                                                                        <td><?= date('d/m/Y H:i', strtotime($doc['data_upload'])) ?></td>
                                                                        <td>
                                                                            <div class="btn-group btn-group-sm">
                                                                                <a href="<?= $doc['caminho_arquivo'] ?>" target="_blank" class="btn btn-info">
                                                                                    <i class="feather icon-eye"></i>
                                                                                </a>
                                                                                <a href="<?= $doc['caminho_arquivo'] ?>" download class="btn btn-success">
                                                                                    <i class="feather icon-download"></i>
                                                                                </a>
                                                                                <button class="btn btn-danger" onclick="confirmarExclusao(<?= $doc['id_documento'] ?>)">
                                                                                    <i class="feather icon-trash"></i>
                                                                                </button>
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                    <?php endforeach; ?>
                                                                    <?php endif; ?>
                                                                </tbody>
                                                            </table>
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
    </div>

    <?php require_once '../../includes/common/js_imports.php'; ?>
    
    <script>
        // Mostrar nome do arquivo selecionado
        document.querySelector('.custom-file-input').addEventListener('change', function(e) {
            var fileName = document.getElementById("documento").files[0].name;
            var nextSibling = e.target.nextElementSibling;
            nextSibling.innerText = fileName;
        });
        
        // Confirmar exclusão de documento
        function confirmarExclusao(id) {
            if (confirm('Tem certeza que deseja excluir este documento?\nEsta ação não pode ser desfeita.')) {
                window.location.href = 'documentos.php?excluir=' + id;
            }
        }
    </script>
</body>
</html>