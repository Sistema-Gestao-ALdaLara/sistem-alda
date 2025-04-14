<?php
    require_once '../../includes/common/permissoes.php';
    verificarPermissao(['secretaria']);
    require_once '../../process/verificar_sessao.php';
    require_once '../../database/conexao.php';

    $titulo = "Envio de Comunicados";
    
    // Processar envio de novo comunicado
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enviar_comunicado'])) {
        $titulo_comunicado = $conn->real_escape_string($_POST['titulo']);
        // Usa htmlspecialchars para segurança, mas preserva quebras de linha
        $mensagem = nl2br(htmlspecialchars($_POST['mensagem'], ENT_QUOTES, 'UTF-8'));
        $destinatarios = isset($_POST['destinatarios']) ? $_POST['destinatarios'] : [];
        $id_usuario = $_SESSION['id_usuario'];
        
        // Validar dados
        if (empty($titulo_comunicado)) {
            $_SESSION['erro'] = "O título do comunicado é obrigatório.";
        } elseif (empty($_POST['mensagem'])) { // Valida o POST original, não o processado
            $_SESSION['erro'] = "A mensagem do comunicado é obrigatória.";
        } elseif (empty($destinatarios)) {
            $_SESSION['erro'] = "Selecione pelo menos um grupo de destinatários.";
        } else {
            // Inserir o comunicado no banco de dados
            $query = "INSERT INTO comunicado (titulo, mensagem, usuario_id_usuario) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssi", $titulo_comunicado, $mensagem, $id_usuario);
            
            if ($stmt->execute()) {
                $_SESSION['sucesso'] = "Comunicado enviado com sucesso!";
            } else {
                $_SESSION['erro'] = "Erro ao enviar o comunicado. Por favor, tente novamente.";
            }
        }
        
        header('Location: comunicados.php');
        exit();
    }
    
    // Restante do seu código PHP...
?>

<!DOCTYPE html>
<html lang="pt">
<!-- ... (cabeçalho igual) ... -->

<body>
    <!-- ... (preloader, navbar, sidebar igual) ... -->

    <div class="page-body">
        <!-- ... (mensagens de feedback igual) ... -->
        
        <div class="row">
            <!-- Card de Envio de Comunicados - Modificado -->
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5>Enviar Novo Comunicado</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="" id="formComunicado">
                            <!-- Título (igual) -->
                            
                            <div class="form-group">
                                <label for="mensagem">Mensagem *</label>
                                <!-- Textarea principal que sempre funciona -->
                                <textarea class="form-control" id="mensagem" name="mensagem" rows="10" required></textarea>
                                <small class="form-text text-muted">
                                    Dicas de formatação: Use *negrito*, _itálico_ e [br] para quebra de linha
                                </small>
                            </div>
                            
                            <!-- Destinatários (igual) -->
                            
                            <button type="submit" name="enviar_comunicado" class="btn btn-primary">
                                <i class="feather icon-send"></i> Enviar Comunicado
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- ... (restante do código igual) ... -->
        </div>
    </div>

    <?php require_once '../../includes/common/js_imports.php'; ?>
    
    <!-- Script modificado para funcionar offline -->
    <script>
        // Tenta carregar o CKEditor com fallback
        function loadCKEditor() {
            return new Promise((resolve, reject) => {
                if (typeof CKEDITOR !== 'undefined') {
                    resolve();
                    return;
                }

                // Tenta carregar o CKEditor
                const script = document.createElement('script');
                script.src = 'https://cdn.ckeditor.com/4.25.1/standard/ckeditor.js';
                script.onload = resolve;
                script.onerror = function() {
                    console.log("CKEditor não carregado - usando editor simples");
                    document.getElementById('mensagem').placeholder += "\n(Editor avançado indisponível - usando modo texto simples)";
                    resolve(); // Resolve mesmo falhando
                };
                document.head.appendChild(script);
            });
        }

        // Inicialização do formulário
        document.addEventListener('DOMContentLoaded', function() {
            loadCKEditor().then(() => {
                if (typeof CKEDITOR !== 'undefined') {
                    CKEDITOR.replace('mensagem', {
                        // Configurações do editor...
                        height: 200,
                        removePlugins: 'exportpdf' // Remove funcionalidade que não funciona offline
                    });
                }
            });

            // Validação universal (funciona com ou sem CKEditor)
            document.getElementById('formComunicado').addEventListener('submit', function(e) {
                let mensagem;
                
                if (typeof CKEDITOR !== 'undefined' && CKEDITOR.instances.mensagem) {
                    mensagem = CKEDITOR.instances.mensagem.getData().trim();
                } else {
                    mensagem = document.getElementById('mensagem').value.trim();
                }
                
                if (!mensagem) {
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
        });
    </script>
</body>
</html>