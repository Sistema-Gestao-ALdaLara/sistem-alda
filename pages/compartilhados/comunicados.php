<?php
require_once '../../includes/common/permissoes.php';
verificarPermissao(['secretaria', 'diretor_geral', 'diretor_pedagogico']);
require_once '../../process/verificar_sessao.php';
require_once '../../database/conexao.php';

$titulo = "Envio de Comunicados";

// Processar envio de novo comunicado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enviar_comunicado'])) {
    $titulo_comunicado = $conn->real_escape_string($_POST['titulo']);
    $mensagem = $_POST['mensagem'];
    $destinatarios = isset($_POST['destinatarios']) ? $_POST['destinatarios'] : [];
    $id_usuario = $_SESSION['id_usuario'];
    
    // Sanitizar a mensagem mantendo a formatação HTML
    $mensagem = htmlspecialchars_decode($mensagem);
    $mensagem = $conn->real_escape_string($mensagem);
    
    // Validar dados
    if (empty($titulo_comunicado)) {
        $_SESSION['erro'] = "O título do comunicado é obrigatório.";
    } elseif (empty($mensagem)) {
        $_SESSION['erro'] = "A mensagem do comunicado é obrigatória.";
    } elseif (empty($destinatarios)) {
        $_SESSION['erro'] = "Selecione pelo menos um grupo de destinatários.";
    } else {
        // Iniciar transação
        $conn->begin_transaction();
        
        try {
            // Inserir o comunicado no banco de dados
            $query = "INSERT INTO comunicado (titulo, mensagem, usuario_id_usuario) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssi", $titulo_comunicado, $mensagem, $id_usuario);
            
            if ($stmt->execute()) {
                $comunicado_id = $conn->insert_id;
                
                // Inserir os destinatários
                foreach ($destinatarios as $tipo) {
                    // Verificar se é "todos" para inserir todos os tipos
                    if ($tipo === 'todos') {
                        $tipos_usuarios = ['diretor_geral', 'diretor_pedagogico', 'coordenador', 'professor', 'aluno', 'secretaria'];
                        foreach ($tipos_usuarios as $tipo_usuario) {
                            $query_dest = "INSERT INTO comunicado_destinatario (comunicado_id, tipo_destinatario) VALUES (?, ?)";
                            $stmt_dest = $conn->prepare($query_dest);
                            $stmt_dest->bind_param("is", $comunicado_id, $tipo_usuario);
                            $stmt_dest->execute();
                        }
                    } else {
                        $query_dest = "INSERT INTO comunicado_destinatario (comunicado_id, tipo_destinatario) VALUES (?, ?)";
                        $stmt_dest = $conn->prepare($query_dest);
                        $stmt_dest->bind_param("is", $comunicado_id, $tipo);
                        $stmt_dest->execute();
                    }
                }
                
                $conn->commit();
                $_SESSION['sucesso'] = "Comunicado enviado com sucesso!";
            } else {
                throw new Exception("Erro ao inserir o comunicado.");
            }
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['erro'] = "Erro ao enviar o comunicado. Por favor, tente novamente.";
        }
    }
    
    header('Location: comunicados.php');
    exit();
}

// Obter comunicados enviados
$query = "SELECT c.*, u.nome AS remetente, 
                 GROUP_CONCAT(DISTINCT cd.tipo_destinatario SEPARATOR ', ') AS destinatarios
          FROM comunicado c
          JOIN usuario u ON c.usuario_id_usuario = u.id_usuario
          LEFT JOIN comunicado_destinatario cd ON c.id_comunicado = cd.comunicado_id
          GROUP BY c.id_comunicado
          ORDER BY c.data DESC";
$result = $conn->query($query);
$comunicados = $result->fetch_all(MYSQLI_ASSOC);

// Função para traduzir os tipos de destinatários
function traduzirDestinatarios($tipos) {
    $traducao = [
        'diretor_geral' => 'Diretor Geral',
        'diretor_pedagogico' => 'Diretor Pedagógico',
        'coordenador' => 'Coordenadores',
        'professor' => 'Professores',
        'aluno' => 'Alunos',
        'secretaria' => 'Secretaria',
        'todos' => 'Todos'
    ];
    
    if (empty($tipos)) {
        return 'Nenhum destinatário específico';
    }
    
    $tipos_array = explode(', ', $tipos);
    $traduzidos = array_map(function($tipo) use ($traducao) {
        return $traducao[$tipo] ?? $tipo;
    }, $tipos_array);
    
    return implode(', ', $traduzidos);
}

$title = "Comunicados";
$tipo = $_SESSION['tipo_usuario'];
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <?php require_once '../../includes/common/head.php'; ?>
    <!-- TinyMCE Local - Ajustado para sua estrutura -->
    <script src="/js/tinymce/tinymce.min.js"></script>

    <style>
        .tox-tinymce {
            border-radius: 4px !important;
            border: 1px solid #ced4da !important;
        }
        .card-body {
            padding: 20px;
        }
        .comunicado-mensagem {
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }
        .comunicado-mensagem img {
            max-width: 100%;
            height: auto;
        }
        .destinatarios-container {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }
        .destinatario-option {
            flex: 1 0 200px;
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
                                            <!-- Card de Envio de Comunicados -->
                                            <div class="col-md-12">
                                                <div class="card card-table">
                                                    <div class="card-header bg-primary text-white">
                                                        <h5>Enviar Novo Comunicado</h5>
                                                    </div>
                                                    <div class="card-body">
                                                        <form method="POST" action="" id="formComunicado">
                                                            <div class="form-group text-dark">
                                                                <label for="titulo">Título do Comunicado *</label>
                                                                <input type="text" class="form-control" id="titulo" name="titulo" required>
                                                            </div>
                                                            
                                                            <div class="form-group text-dark">
                                                                <label for="mensagem">Mensagem *</label>
                                                                <textarea class="form-control" id="mensagem" name="mensagem" rows="10"></textarea>
                                                            </div>
                                                            
                                                            <div class="form-group">
                                                                <label>Destinatários *</label>
                                                                <div class="destinatarios-container">
                                                                    <div class="destinatario-option">
                                                                        <div class="checkbox-fade fade-in-primary">
                                                                            <label>
                                                                                <input type="checkbox" name="destinatarios[]" value="aluno">
                                                                                <span class="cr"><i class="cr-icon feather icon-check"></i></span>
                                                                                <span>Alunos</span>
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="destinatario-option">
                                                                        <div class="checkbox-fade fade-in-primary">
                                                                            <label>
                                                                                <input type="checkbox" name="destinatarios[]" value="professor">
                                                                                <span class="cr"><i class="cr-icon feather icon-check"></i></span>
                                                                                <span>Professores</span>
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="destinatario-option">
                                                                        <div class="checkbox-fade fade-in-primary">
                                                                            <label>
                                                                                <input type="checkbox" name="destinatarios[]" value="coordenador">
                                                                                <span class="cr"><i class="cr-icon feather icon-check"></i></span>
                                                                                <span>Coordenadores</span>
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="destinatario-option">
                                                                        <div class="checkbox-fade fade-in-primary">
                                                                            <label>
                                                                                <input type="checkbox" name="destinatarios[]" value="secretaria">
                                                                                <span class="cr"><i class="cr-icon feather icon-check"></i></span>
                                                                                <span>Secretaria</span>
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="destinatario-option">
                                                                        <div class="checkbox-fade fade-in-primary">
                                                                            <label>
                                                                                <input type="checkbox" name="destinatarios[]" value="diretor_geral">
                                                                                <span class="cr"><i class="cr-icon feather icon-check"></i></span>
                                                                                <span>Diretor Geral</span>
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="destinatario-option">
                                                                        <div class="checkbox-fade fade-in-primary">
                                                                            <label>
                                                                                <input type="checkbox" name="destinatarios[]" value="diretor_pedagogico">
                                                                                <span class="cr"><i class="cr-icon feather icon-check"></i></span>
                                                                                <span>Diretor Pedagógico</span>
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="destinatario-option">
                                                                        <div class="checkbox-fade fade-in-primary">
                                                                            <label>
                                                                                <input type="checkbox" name="destinatarios[]" value="todos">
                                                                                <span class="cr"><i class="cr-icon feather icon-check"></i></span>
                                                                                <span>Todos</span>
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            
                                                            <button type="submit" name="enviar_comunicado" class="btn btn-primary">
                                                                <i class="feather icon-send"></i> Enviar Comunicado
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Lista de Comunicados Enviados -->
                                            <div class="col-md-12 mt-4">
                                                <div class="card card-table">
                                                    <div class="card-header">
                                                        <h5 class="text-white mb-0">Histórico de Comunicados</h5>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="table-responsive">
                                                            <table class="table table-hover">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Título</th>
                                                                        <th>Prévia</th>
                                                                        <th>Remetente</th>
                                                                        <th>Destinatários</th>
                                                                        <th>Data/Hora</th>
                                                                        <th>Ações</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php if (empty($comunicados)): ?>
                                                                    <tr>
                                                                        <td colspan="6" class="text-center">Nenhum comunicado encontrado</td>
                                                                    </tr>
                                                                    <?php else: ?>
                                                                    <?php foreach ($comunicados as $comunicado): ?>
                                                                    <tr>
                                                                        <td><?= htmlspecialchars($comunicado['titulo']) ?></td>
                                                                        <td><?= substr(strip_tags($comunicado['mensagem']), 0, 100) . (strlen($comunicado['mensagem']) > 100 ? '...' : '') ?></td>
                                                                        <td><?= htmlspecialchars($comunicado['remetente']) ?></td>
                                                                        <td><?= traduzirDestinatarios($comunicado['destinatarios'] ?? '') ?></td>
                                                                        <td><?= date('d/m/Y H:i', strtotime($comunicado['data'])) ?></td>
                                                                        <td>
                                                                            <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#modalComunicado<?= $comunicado['id_comunicado'] ?>">
                                                                                <i class="feather icon-eye"></i> Ver
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
                                <!-- Modal para visualização completa -->
                                <?php foreach ($comunicados as $comunicado): ?>
                                <div class="modal fade" id="modalComunicado<?= $comunicado['id_comunicado'] ?>" tabindex="-1" role="dialog" aria-hidden="true">
                                    <div class="modal-dialog modal-lg" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header bg-primary text-white">
                                                <h5 class="modal-title"><?= htmlspecialchars($comunicado['titulo']) ?></h5>
                                                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <p><strong>Enviado por:</strong> <?= htmlspecialchars($comunicado['remetente']) ?></p>
                                                <p><strong>Para:</strong> <?= traduzirDestinatarios($comunicado['destinatarios'] ?? '') ?></p>
                                                <p><strong>Data:</strong> <?= date('d/m/Y H:i', strtotime($comunicado['data'])) ?></p>
                                                <hr>
                                                <div class="comunicado-mensagem">
                                                    <?= $comunicado['mensagem'] ?>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
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
        // Inicialização do TinyMCE com caminhos locais
        tinymce.init({
            selector: '#mensagem',
            height: 300,
            menubar: false,
            plugins: [
                'advlist autolink lists link image charmap print preview anchor',
                'searchreplace visualblocks code fullscreen',
                'insertdatetime media table paste code help wordcount'
            ],
            toolbar: 'undo redo | formatselect | bold italic backcolor | ' +
                     'alignleft aligncenter alignright alignjustify | ' +
                     'bullist numlist outdent indent | removeformat | help',
            skin_url: '/js/tinymce/skins/ui/oxide',
            content_css: '/js/tinymce/skins/content/default/content.min.css',
            images_upload_url: '/upload.php',
            automatic_uploads: true,
            paste_data_images: false
        });

        // Validação do formulário
        document.getElementById('formComunicado').addEventListener('submit', function(e) {
            let message = tinymce.get('mensagem').getContent().trim();
            
            if (!message) {
                alert('Por favor, preencha a mensagem do comunicado.');
                e.preventDefault();
                return false;
            }
            
            const destinatarios = document.querySelectorAll('input[name="destinatarios[]"]:checked');
            if (destinatarios.length === 0) {
                alert('Selecione pelo menos um grupo de destinatários.');
                e.preventDefault();
                return false;
            }
            
            return true;
        });

        // Quando selecionar "Todos", desmarcar os outros
        document.querySelector('input[value="todos"]').addEventListener('change', function(e) {
            if (e.target.checked) {
                document.querySelectorAll('input[name="destinatarios[]"]:not([value="todos"])').forEach(el => {
                    el.checked = false;
                });
            }
        });

        // Quando selecionar qualquer outro, desmarcar "Todos"
        document.querySelectorAll('input[name="destinatarios[]"]:not([value="todos"])').forEach(el => {
            el.addEventListener('change', function(e) {
                if (e.target.checked) {
                    document.querySelector('input[value="todos"]').checked = false;
                }
            });
        });
    </script>
</body>
</html>