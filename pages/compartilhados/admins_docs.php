<?php
require_once '../../includes/common/permissoes.php';
verificarPermissao(['secretaria', 'diretor_geral', 'diretor_pedagogico', 'coordenador', 'professor']);
require_once '../../process/verificar_sessao.php';
require_once '../../database/conexao.php';

$titulo = "Documentos Administrativos";

// Processar envio de novo documento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enviar_documento'])) {
    $titulo_documento = $conn->real_escape_string($_POST['titulo']);
    $descricao = $conn->real_escape_string($_POST['descricao']);
    $tipo = $conn->real_escape_string($_POST['tipo']);
    $visibilidade = $_POST['visibilidade'];
    $id_usuario = $_SESSION['id_usuario'];
    $data_publicacao = date('Y-m-d H:i:s');
    
    // Processar upload do arquivo
    $caminho_arquivo = '';
    if (isset($_FILES['arquivo']) && $_FILES['arquivo']['error'] === UPLOAD_ERR_OK) {
        $extensao = pathinfo($_FILES['arquivo']['name'], PATHINFO_EXTENSION);
        $nome_arquivo = uniqid() . '.' . $extensao;
        $diretorio_upload = '../../uploads/documentos/';
        
        if (!file_exists($diretorio_upload)) {
            mkdir($diretorio_upload, 0777, true);
        }
        
        $caminho_arquivo = $diretorio_upload . $nome_arquivo;
        
        if (!move_uploaded_file($_FILES['arquivo']['tmp_name'], $caminho_arquivo)) {
            $_SESSION['erro'] = "Erro ao fazer upload do arquivo.";
            header('Location: documentos.php');
            exit();
        }
        
        // Armazenar apenas o caminho relativo no banco
        $caminho_arquivo = '/uploads/documentos/' . $nome_arquivo;
    } else {
        $_SESSION['erro'] = "Por favor, selecione um arquivo para upload.";
        header('Location: documentos.php');
        exit();
    }
    
    // Validar dados
    if (empty($titulo_documento)) {
        $_SESSION['erro'] = "O título do documento é obrigatório.";
    } elseif (empty($tipo)) {
        $_SESSION['erro'] = "O tipo do documento é obrigatório.";
    } else {
        // Iniciar transação
        $conn->begin_transaction();
        
        try {
            // Inserir o documento no banco de dados
            $query = "INSERT INTO documentos_administrativos 
                     (titulo, descricao, caminho_arquivo, tipo, data_upload, data_publicacao, visibilidade, usuario_id_usuario) 
                     VALUES (?, ?, ?, ?, NOW(), ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssssssi", $titulo_documento, $descricao, $caminho_arquivo, $tipo, $data_publicacao, $visibilidade, $id_usuario);
            
            if ($stmt->execute()) {
                $documento_id = $conn->insert_id;
                
                // Processar destinatários se for visibilidade restrita
                if ($visibilidade === 'restrito') {
                    $destinatarios = $_POST['destinatarios'] ?? [];
                    
                    foreach ($destinatarios as $destino) {
                        // Parse do valor que está no formato "tipo:valor"
                        list($tipo_destino, $valor) = explode(':', $destino);
                        
                        $query_dest = "INSERT INTO documento_destinatarios 
                                      (documento_id, tipo_destino, usuario_id, turma_id, curso_id, tipo_usuario) 
                                      VALUES (?, ?, ?, ?, ?, ?)";
                        $stmt_dest = $conn->prepare($query_dest);
                        
                        // Definir os valores com base no tipo de destino
                        $usuario_id = null;
                        $turma_id = null;
                        $curso_id = null;
                        $tipo_usuario_val = null;
                        
                        switch ($tipo_destino) {
                            case 'usuario':
                                $usuario_id = $valor;
                                break;
                            case 'turma':
                                $turma_id = $valor;
                                break;
                            case 'curso':
                                $curso_id = $valor;
                                break;
                            case 'tipo_usuario':
                                $tipo_usuario_val = $valor;
                                break;
                        }
                        
                        $stmt_dest->bind_param("isiiis", $documento_id, $tipo_destino, $usuario_id, $turma_id, $curso_id, $tipo_usuario_val);
                        $stmt_dest->execute();
                    }
                }
                
                $conn->commit();
                $_SESSION['sucesso'] = "Documento enviado com sucesso!";
            } else {
                throw new Exception("Erro ao inserir o documento.");
            }
        } catch (Exception $e) {
            $conn->rollback();
            // Remover arquivo enviado em caso de erro
            if (!empty($caminho_arquivo) && file_exists('../../' . $caminho_arquivo)) {
                unlink('../../' . $caminho_arquivo);
            }
            $_SESSION['erro'] = "Erro ao enviar o documento. Por favor, tente novamente.";
        }
    }
    
    header('Location: documentos.php');
    exit();
}

// Obter documentos enviados pelo usuário atual
$query = "SELECT d.*, u.nome AS remetente 
          FROM documentos_administrativos d
          JOIN usuario u ON d.usuario_id_usuario = u.id_usuario
          WHERE d.usuario_id_usuario = ?
          ORDER BY d.data_upload DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $_SESSION['id_usuario']);
$stmt->execute();
$result = $stmt->get_result();
$documentos = $result->fetch_all(MYSQLI_ASSOC);

// Obter opções para destinatários
$turmas = [];
$cursos = [];
$usuarios = [];

if ($_SESSION['tipo_usuario'] === 'secretaria' || $_SESSION['tipo_usuario'] === 'diretor_geral' || $_SESSION['tipo_usuario'] === 'diretor_pedagogico') {
    // Obter turmas
    $query_turmas = "SELECT id_turma, nome FROM turma ORDER BY nome";
    $result_turmas = $conn->query($query_turmas);
    $turmas = $result_turmas->fetch_all(MYSQLI_ASSOC);
    
    // Obter cursos
    $query_cursos = "SELECT id_curso, nome FROM curso ORDER BY nome";
    $result_cursos = $conn->query($query_cursos);
    $cursos = $result_cursos->fetch_all(MYSQLI_ASSOC);
    
    // Obter usuários (limitado para não sobrecarregar)
    $query_usuarios = "SELECT id_usuario, nome, tipo FROM usuario WHERE status = 'ativo' ORDER BY nome";
    $result_usuarios = $conn->query($query_usuarios);
    $usuarios = $result_usuarios->fetch_all(MYSQLI_ASSOC);
}

$title = "Documentos Administrativos";
$tipo = $_SESSION['tipo_usuario'];
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <?php require_once '../../includes/common/head.php'; ?>
    <style>
        .card-body {
            padding: 20px;
        }
        .destinatarios-container {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }
        .destinatario-option {
            flex: 1 0 200px;
        }
        .documento-preview {
            max-width: 100%;
            max-height: 200px;
        }
        .tabs-container {
            margin-bottom: 20px;
        }
        .tab-content {
            padding: 15px;
            border: 1px solid #dee2e6;
            border-top: none;
            border-radius: 0 0 4px 4px;
        }
        .nav-tabs .nav-link.active {
            background-color: #fff;
            border-bottom: 1px solid #fff;
        }
    </style>
</head>

<body>
    <div id="pcoded" class="pcoded">
        <div class="pcoded-overlay-box"></div>
        <div class="pcoded-container navbar-wrapper">
            <?php require_once "../../includes/$tipo/navbar.php"; ?>

            <div class="pcoded-main-container">
                <div class="pcoded-wrapper">
                    <?php require_once "../../includes/$tipo/sidebar.php"; ?>
                    
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
                                            <!-- Card de Envio de Documentos -->
                                            <div class="col-md-12">
                                                <div class="card card-table">
                                                    <div class="card-header bg-primary text-white">
                                                        <h5>Enviar Novo Documento</h5>
                                                    </div>
                                                    <div class="card-body">
                                                        <form method="POST" action="" enctype="multipart/form-data" id="formDocumento">
                                                            <div class="form-group text-dark">
                                                                <label for="titulo">Título do Documento *</label>
                                                                <input type="text" class="form-control" id="titulo" name="titulo" required>
                                                            </div>
                                                            
                                                            <div class="form-group text-dark">
                                                                <label for="descricao">Descrição</label>
                                                                <textarea class="form-control" id="descricao" name="descricao" rows="3"></textarea>
                                                            </div>
                                                            
                                                            <div class="form-group text-dark">
                                                                <label for="tipo">Tipo de Documento *</label>
                                                                <select class="form-control" id="tipo" name="tipo" required>
                                                                    <option value="">Selecione o tipo...</option>
                                                                    <option value="Ata">Ata</option>
                                                                    <option value="Circular">Circular</option>
                                                                    <option value="Comunicado">Comunicado</option>
                                                                    <option value="Contrato">Contrato</option>
                                                                    <option value="Declaracao">Declaração</option>
                                                                    <option value="Edital">Edital</option>
                                                                    <option value="Formulario">Formulário</option>
                                                                    <option value="Informe">Informe</option>
                                                                    <option value="Ofício">Ofício</option>
                                                                    <option value="Plano">Plano</option>
                                                                    <option value="Portaria">Portaria</option>
                                                                    <option value="Relatorio">Relatório</option>
                                                                    <option value="Outro">Outro</option>
                                                                </select>
                                                            </div>
                                                            
                                                            <div class="form-group text-white">
                                                                <label for="arquivo">Arquivo *</label>
                                                                <input type="file" class="form-control-file" id="arquivo" name="arquivo" required>
                                                                <small class="form-text text-muted bg-white">Formatos aceitos: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, JPG, PNG</small>
                                                            </div>
                                                            
                                                            <div class="form-group text-white ml-4">
                                                                <label>Visibilidade *</label>
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="radio" name="visibilidade" id="visibilidade_publico" value="publico" checked>
                                                                    <label class="form-check-label" for="visibilidade_publico">
                                                                        Público (todos os usuários podem ver)
                                                                    </label>
                                                                </div>
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="radio" name="visibilidade" id="visibilidade_restrito" value="restrito">
                                                                    <label class="form-check-label" for="visibilidade_restrito">
                                                                        Restrito (somente destinatários selecionados podem ver)
                                                                    </label>
                                                                </div>
                                                            </div>
                                                            
                                                            <div id="destinatariosSection" style="display: none;">
                                                                <div class="form-group text-dark">
                                                                    <label>Destinatários</label>
                                                                    
                                                                    <ul class="nav nav-tabs" id="destinatariosTab" role="tablist">
                                                                        <?php if (!empty($turmas)): ?>
                                                                        <li class="nav-item">
                                                                            <a class="nav-link active" id="turmas-tab" data-toggle="tab" href="#turmas" role="tab">Turmas</a>
                                                                        </li>
                                                                        <?php endif; ?>
                                                                        
                                                                        <?php if (!empty($cursos)): ?>
                                                                        <li class="nav-item">
                                                                            <a class="nav-link" id="cursos-tab" data-toggle="tab" href="#cursos" role="tab">Cursos</a>
                                                                        </li>
                                                                        <?php endif; ?>
                                                                        
                                                                        <?php if (!empty($usuarios)): ?>
                                                                        <li class="nav-item">
                                                                            <a class="nav-link" id="usuarios-tab" data-toggle="tab" href="#usuarios" role="tab">Usuários</a>
                                                                        </li>
                                                                        <li class="nav-item">
                                                                            <a class="nav-link" id="tipos-tab" data-toggle="tab" href="#tipos" role="tab">Tipos de Usuário</a>
                                                                        </li>
                                                                        <?php endif; ?>
                                                                    </ul>
                                                                    
                                                                    <div class="tab-content" id="destinatariosTabContent">
                                                                        <?php if (!empty($turmas)): ?>
                                                                        <div class="tab-pane fade show active" id="turmas" role="tabpanel">
                                                                            <div class="destinatarios-container">
                                                                                <?php foreach ($turmas as $turma): ?>
                                                                                <div class="destinatario-option">
                                                                                    <div class="checkbox-fade fade-in-primary">
                                                                                        <label>
                                                                                            <input type="checkbox" name="destinatarios[]" value="turma:<?= $turma['id_turma'] ?>">
                                                                                            <span class="cr"><i class="cr-icon feather icon-check"></i></span>
                                                                                            <span><?= htmlspecialchars($turma['nome']) ?></span>
                                                                                        </label>
                                                                                    </div>
                                                                                </div>
                                                                                <?php endforeach; ?>
                                                                            </div>
                                                                        </div>
                                                                        <?php endif; ?>
                                                                        
                                                                        <?php if (!empty($cursos)): ?>
                                                                        <div class="tab-pane fade" id="cursos" role="tabpanel">
                                                                            <div class="destinatarios-container">
                                                                                <?php foreach ($cursos as $curso): ?>
                                                                                <div class="destinatario-option">
                                                                                    <div class="checkbox-fade fade-in-primary">
                                                                                        <label>
                                                                                            <input type="checkbox" name="destinatarios[]" value="curso:<?= $curso['id_curso'] ?>">
                                                                                            <span class="cr"><i class="cr-icon feather icon-check"></i></span>
                                                                                            <span><?= htmlspecialchars($curso['nome']) ?></span>
                                                                                        </label>
                                                                                    </div>
                                                                                </div>
                                                                                <?php endforeach; ?>
                                                                            </div>
                                                                        </div>
                                                                        <?php endif; ?>
                                                                        
                                                                        <?php if (!empty($usuarios)): ?>
                                                                        <div class="tab-pane fade" id="usuarios" role="tabpanel">
                                                                            <div class="destinatarios-container">
                                                                                <?php foreach ($usuarios as $usuario): ?>
                                                                                <div class="destinatario-option">
                                                                                    <div class="checkbox-fade fade-in-primary">
                                                                                        <label>
                                                                                            <input type="checkbox" name="destinatarios[]" value="usuario:<?= $usuario['id_usuario'] ?>">
                                                                                            <span class="cr"><i class="cr-icon feather icon-check"></i></span>
                                                                                            <span><?= htmlspecialchars($usuario['nome']) ?> (<?= $usuario['tipo'] ?>)</span>
                                                                                        </label>
                                                                                    </div>
                                                                                </div>
                                                                                <?php endforeach; ?>
                                                                            </div>
                                                                        </div>
                                                                        
                                                                        <div class="tab-pane fade" id="tipos" role="tabpanel">
                                                                            <div class="destinatarios-container">
                                                                                <div class="destinatario-option">
                                                                                    <div class="checkbox-fade fade-in-primary">
                                                                                        <label>
                                                                                            <input type="checkbox" name="destinatarios[]" value="tipo_usuario:diretor_geral">
                                                                                            <span class="cr"><i class="cr-icon feather icon-check"></i></span>
                                                                                            <span>Diretores Gerais</span>
                                                                                        </label>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="destinatario-option">
                                                                                    <div class="checkbox-fade fade-in-primary">
                                                                                        <label>
                                                                                            <input type="checkbox" name="destinatarios[]" value="tipo_usuario:diretor_pedagogico">
                                                                                            <span class="cr"><i class="cr-icon feather icon-check"></i></span>
                                                                                            <span>Diretores Pedagógicos</span>
                                                                                        </label>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="destinatario-option">
                                                                                    <div class="checkbox-fade fade-in-primary">
                                                                                        <label>
                                                                                            <input type="checkbox" name="destinatarios[]" value="tipo_usuario:coordenador">
                                                                                            <span class="cr"><i class="cr-icon feather icon-check"></i></span>
                                                                                            <span>Coordenadores</span>
                                                                                        </label>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="destinatario-option">
                                                                                    <div class="checkbox-fade fade-in-primary">
                                                                                        <label>
                                                                                            <input type="checkbox" name="destinatarios[]" value="tipo_usuario:professor">
                                                                                            <span class="cr"><i class="cr-icon feather icon-check"></i></span>
                                                                                            <span>Professores</span>
                                                                                        </label>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="destinatario-option">
                                                                                    <div class="checkbox-fade fade-in-primary">
                                                                                        <label>
                                                                                            <input type="checkbox" name="destinatarios[]" value="tipo_usuario:aluno">
                                                                                            <span class="cr"><i class="cr-icon feather icon-check"></i></span>
                                                                                            <span>Alunos</span>
                                                                                        </label>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="destinatario-option">
                                                                                    <div class="checkbox-fade fade-in-primary">
                                                                                        <label>
                                                                                            <input type="checkbox" name="destinatarios[]" value="tipo_usuario:secretaria">
                                                                                            <span class="cr"><i class="cr-icon feather icon-check"></i></span>
                                                                                            <span>Secretaria</span>
                                                                                        </label>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            
                                                            <button type="submit" name="enviar_documento" class="btn btn-primary">
                                                                <i class="feather icon-upload"></i> Enviar Documento
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Lista de Documentos Enviados -->
                                            <div class="col-md-12 mt-4">
                                                <div class="card card-table">
                                                    <div class="card-header">
                                                        <h5 class="text-white mb-0">Meus Documentos Enviados</h5>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="table-responsive">
                                                            <table class="table table-hover">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Título</th>
                                                                        <th>Tipo</th>
                                                                        <th>Visibilidade</th>
                                                                        <th>Data</th>
                                                                        <th>Ações</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php if (empty($documentos)): ?>
                                                                    <tr>
                                                                        <td colspan="5" class="text-center">Nenhum documento encontrado</td>
                                                                    </tr>
                                                                    <?php else: ?>
                                                                    <?php foreach ($documentos as $documento): ?>
                                                                    <tr>
                                                                        <td><?= htmlspecialchars($documento['titulo']) ?></td>
                                                                        <td><?= htmlspecialchars($documento['tipo']) ?></td>
                                                                        <td><?= $documento['visibilidade'] === 'publico' ? 'Público' : 'Restrito' ?></td>
                                                                        <td><?= date('d/m/Y H:i', strtotime($documento['data_upload'])) ?></td>
                                                                        <td>
                                                                            <a href="<?= $documento['caminho_arquivo'] ?>" class="btn btn-info btn-sm" target="_blank">
                                                                                <i class="feather icon-download"></i> Baixar
                                                                            </a>
                                                                            <button class="btn btn-secondary btn-sm" data-toggle="modal" data-target="#modalDocumento<?= $documento['id_documento'] ?>">
                                                                                <i class="feather icon-info"></i> Detalhes
                                                                            </button>  
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
                                <!-- Modal para visualização de detalhes -->
                                <?php foreach ($documentos as $documento): ?>
                                <div class="modal fade" id="modalDocumento<?= $documento['id_documento'] ?>" tabindex="-1" role="dialog" aria-hidden="true">
                                    <div class="modal-dialog modal-lg" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header bg-primary text-white">
                                                <h5 class="modal-title"><?= htmlspecialchars($documento['titulo']) ?></h5>
                                                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <p><strong>Enviado por:</strong> <?= htmlspecialchars($documento['remetente']) ?></p>
                                                <p><strong>Tipo:</strong> <?= htmlspecialchars($documento['tipo']) ?></p>
                                                <p><strong>Visibilidade:</strong> <?= $documento['visibilidade'] === 'publico' ? 'Público' : 'Restrito' ?></p>
                                                <p><strong>Data de Envio:</strong> <?= date('d/m/Y H:i', strtotime($documento['data_upload'])) ?></p>
                                                <?php if (!empty($documento['descricao'])): ?>
                                                <p><strong>Descrição:</strong> <?= htmlspecialchars($documento['descricao']) ?></p>
                                                <?php endif; ?>
                                                
                                                <hr>
                                                
                                                <div class="text-center">
                                                    <?php 
                                                    $extensao = pathinfo($documento['caminho_arquivo'], PATHINFO_EXTENSION);
                                                    $extensoes_imagem = ['jpg', 'jpeg', 'png', 'gif'];
                                                    if (in_array(strtolower($extensao), $extensoes_imagem)): ?>
                                                        <img src="<?= $documento['caminho_arquivo'] ?>" class="documento-preview img-fluid" alt="Prévia do documento">
                                                    <?php else: ?>
                                                        <i class="feather icon-file-text" style="font-size: 100px;"></i>
                                                        <p>Visualização não disponível para este tipo de arquivo</p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <a href="<?= $documento['caminho_arquivo'] ?>" class="btn btn-primary" target="_blank">
                                                    <i class="feather icon-download"></i> Baixar Documento
                                                </a>
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require_once '../../includes/common/js_imports.php'; ?>
    
    <script>
        // Mostrar/ocultar seção de destinatários com base na visibilidade
        document.querySelectorAll('input[name="visibilidade"]').forEach(el => {
            el.addEventListener('change', function() {
                const destinatariosSection = document.getElementById('destinatariosSection');
                destinatariosSection.style.display = this.value === 'restrito' ? 'block' : 'none';
            });
        });

        // Validação do formulário
        document.getElementById('formDocumento').addEventListener('submit', function(e) {
            const visibilidadeRestrito = document.getElementById('visibilidade_restrito').checked;
            const destinatarios = document.querySelectorAll('input[name="destinatarios[]"]:checked');
            
            if (visibilidadeRestrito && destinatarios.length === 0) {
                alert('Para documentos restritos, selecione pelo menos um destinatário.');
                e.preventDefault();
                return false;
            }
            
            return true;
        });

        // Ativar tabs
        $('#destinatariosTab a').on('click', function (e) {
            e.preventDefault();
            $(this).tab('show');
        });
    </script>
</body>
</html>