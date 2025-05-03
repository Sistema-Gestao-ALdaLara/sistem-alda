<?php
require_once '../../includes/common/permissoes.php';
verificarPermissao(['secretaria', 'diretor_geral', 'diretor_pedagogico', 'coordenador', 'professor', 'aluno']);
require_once '../../process/verificar_sessao.php';
require_once '../../database/conexao.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verifica se o usuário está logado
if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../../login.php');
    exit();
}

$title = "Meu Perfil";

// Obter dados do usuário logado
$id_usuario = $_SESSION['id_usuario'] ?? null;
if (!$id_usuario) {
    die("Erro: ID do usuário não encontrado na sessão.");
}

// Buscar dados do usuário no banco de dados
$usuario = [];
try {
    $query = "SELECT * FROM usuario WHERE id_usuario = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    $usuario = $result->fetch_assoc();
    
    if (!$usuario) {
        die("Erro: Usuário não encontrado no banco de dados.");
    }
} catch (Exception $e) {
    die("Erro ao buscar dados do usuário: " . $e->getMessage());
}

// Inicializar variáveis
$erro = null;
$sucesso = null;
$foto_perfil = $usuario['foto_perfil'] ?? null;

// Processar atualização do perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitizar inputs
    $nome = trim(filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING)) ?? '';
    $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL)) ?? '';
    $senha_atual = trim($_POST['senha_atual'] ?? '');
    $nova_senha = trim($_POST['nova_senha'] ?? '');
    $confirmar_senha = trim($_POST['confirmar_senha'] ?? '');

    // Validações
    if (empty($nome)) {
        $erro = "O nome é obrigatório.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = "E-mail inválido.";
    } else {
        // Verificar se o e-mail já existe
        try {
            $query_check = "SELECT id_usuario FROM usuario WHERE email = ? AND id_usuario != ?";
            $stmt_check = $conn->prepare($query_check);
            $stmt_check->bind_param("si", $email, $id_usuario);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();
            
            if ($result_check->num_rows > 0) {
                $erro = "Este e-mail já está em uso por outro usuário.";
            }
        } catch (Exception $e) {
            $erro = "Erro ao verificar e-mail: " . $e->getMessage();
        }

        // Processar upload de foto
        if (!isset($erro) && isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
            $file_info = $_FILES['foto_perfil'];
            $extensao = strtolower(pathinfo($file_info['name'], PATHINFO_EXTENSION));
            $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (in_array($extensao, $extensoes_permitidas)) {
                $diretorio = '../../uploads/perfil/';
                if (!file_exists($diretorio)) {
                    mkdir($diretorio, 0777, true);
                }
                
                $nome_arquivo = uniqid('perfil_') . '.' . $extensao;
                $caminho_completo = $diretorio . $nome_arquivo;
                
                if (move_uploaded_file($file_info['tmp_name'], $caminho_completo)) {
                    // Remove a foto antiga se existir
                    if (!empty($usuario['foto_perfil']) && strpos($usuario['foto_perfil'], 'default') === false) {
                        @unlink($diretorio . basename($usuario['foto_perfil']));
                    }
                    $foto_perfil = $nome_arquivo;
                } else {
                    $erro = "Erro ao fazer upload da foto.";
                }
            } else {
                $erro = "Formato de arquivo não suportado. Use JPG, PNG ou GIF.";
            }
        }
        
        // Verificar senha se for alterada
        if (!isset($erro) && (!empty($nova_senha) || !empty($confirmar_senha))) {
            if (empty($senha_atual)) {
                $erro = "Por favor, informe sua senha atual para alterar a senha.";
            } elseif ($nova_senha !== $confirmar_senha) {
                $erro = "A nova senha e a confirmação não coincidem.";
            } elseif (strlen($nova_senha) < 6) {
                $erro = "A senha deve ter pelo menos 6 caracteres.";
            } else {
                // Verificar senha atual
                $query_senha = "SELECT senha FROM usuario WHERE id_usuario = ?";
                $stmt_senha = $conn->prepare($query_senha);
                $stmt_senha->bind_param("i", $id_usuario);
                $stmt_senha->execute();
                $result_senha = $stmt_senha->get_result();
                $usuario_senha = $result_senha->fetch_assoc();
                
                if (!password_verify($senha_atual, $usuario_senha['senha'])) {
                    $erro = "Senha atual incorreta.";
                }
            }
        }
        
        // Atualizar no banco de dados
        if (!isset($erro)) {
            try {
                $query_update = "UPDATE usuario SET nome = ?, email = ?";
                $params = [$nome, $email];
                $types = "ss";
                
                if ($foto_perfil !== null) {
                    $query_update .= ", foto_perfil = ?";
                    $params[] = $foto_perfil;
                    $types .= "s";
                }
                
                if (!empty($nova_senha)) {
                    $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
                    $query_update .= ", senha = ?";
                    $params[] = $senha_hash;
                    $types .= "s";
                }
                
                $query_update .= " WHERE id_usuario = ?";
                $params[] = $id_usuario;
                $types .= "i";
                
                $stmt_update = $conn->prepare($query_update);
                $stmt_update->bind_param($types, ...$params);
                
                if ($stmt_update->execute()) {
                    $sucesso = "Perfil atualizado com sucesso!";
                    $_SESSION['nome'] = $nome;
                    $_SESSION['email'] = $email;
                    if ($foto_perfil !== null) {
                        $_SESSION['foto_perfil'] = $foto_perfil;
                    }
                    $usuario['nome'] = $nome;
                    $usuario['email'] = $email;
                    $usuario['foto_perfil'] = $foto_perfil;
                } else {
                    $erro = "Erro ao atualizar perfil. Tente novamente.";
                }
            } catch (Exception $e) {
                $erro = "Erro ao atualizar perfil: " . $e->getMessage();
            }
        }
    }
}

$tipo = $_SESSION['tipo_usuario'];
?>

<!DOCTYPE html>
<html lang="pt">

<?php require_once '../../includes/common/head.php'; ?>

<body>
    <?php require_once '../../includes/common/preloader.php'; ?>

    <div id="pcoded" class="pcoded">
        <div class="pcoded-overlay-box"></div>
        <div class="pcoded-container navbar-wrapper">

            <?php require_once "../../includes/$tipo/navbar.php"; ?>

            <!--sidebar-->
            <div class="pcoded-main-container">
                <div class="pcoded-wrapper">
                    <?php require_once "../../includes/$tipo/sidebar.php"; ?>

                    <div class="pcoded-content">
                        <div class="pcoded-inner-content">
                            <div class="main-body bg-img">
                                <div class="page-wrapper">
                                    <div class="page-body">
                                        <div class="row">
                                            <div class="col-12 mt-4">
                                                <div class="card">
                                                    <div class="card-header text-dark">
                                                        <h5 class="text-black mb-0">Editar Perfil</h5>
                                                        <?php echo $usuario['nome']; ?>
                                                    </div>
                                                    <div class="card-body">
                                                        <?php if (isset($erro)): ?>
                                                        <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
                                                        <?php elseif (isset($sucesso)): ?>
                                                        <div class="alert alert-success"><?= htmlspecialchars($sucesso) ?></div>
                                                        <?php endif; ?>
                                                        
                                                        <form id="formPerfil" method="POST" action="" enctype="multipart/form-data">
                                                            <div class="row">
                                                                <div class="col-md-4">
                                                                    <div class="text-center mb-4">
                                                                        <img id="previewFoto" src="<?= !empty($foto_perfil) ? "../../uploads/perfil/" . htmlspecialchars($foto_perfil) : '../../assets/images/default-profile.png' ?>" 
                                                                            class="rounded-circle img-thumbnail" width="200" height="200" alt="Foto de perfil">
                                                                        <div class="mt-3">
                                                                            <label for="foto_perfil" class="btn btn-primary btn-sm">
                                                                                <i class="feather icon-camera"></i> Alterar Foto
                                                                            </label>
                                                                            <input type="file" id="foto_perfil" name="foto_perfil" accept="image/*" style="display: none;" onchange="previewImage(this)">
                                                                            <?php if (!empty($foto_perfil)): ?>
                                                                            <button type="button" class="btn btn-danger btn-sm" onclick="removerFoto()">
                                                                                <i class="feather icon-trash"></i> Remover
                                                                            </button>
                                                                            <?php endif; ?>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <div class="form-group">
                                                                        <label>Tipo de Usuário</label>
                                                                        <input type="text" class="form-control" value="<?= ucfirst(str_replace('_', ' ', $usuario['tipo'] ?? '')) ?>" readonly>
                                                                    </div>
                                                                    
                                                                    <div class="form-group">
                                                                        <label>Status</label>
                                                                        <input type="text" class="form-control" value="<?= ucfirst($usuario['status']) ?>" readonly>
                                                                    </div>
                                                                </div>
                                                                
                                                                <div class="col-md-8">
                                                                    <div class="form-group">
                                                                        <label for="nome">Nome Completo *</label>
                                                                        <input type="text" class="form-control" id="nome" name="nome" 
                                                                            value="<?= htmlspecialchars($usuario['nome'] ?? 'ups') ?>" required>
                                                                    </div>
                                                                    
                                                                    <div class="form-group">
                                                                        <label for="email">E-mail *</label>
                                                                        <input type="email" class="form-control" id="email" name="email" 
                                                                            value="<?= htmlspecialchars($usuario['email']) ?>" required>
                                                                    </div>
                                                                    
                                                                    <div class="form-group">
                                                                        <label>Número do BI</label>
                                                                        <input type="text" class="form-control" value="<?= htmlspecialchars($usuario['bi_numero']) ?>" readonly>
                                                                    </div>
                                                                    
                                                                    <hr>
                                                                    
                                                                    <h5>Alterar Senha</h5>
                                                                    
                                                                    <div class="form-group">
                                                                        <label for="senha_atual">Senha Atual</label>
                                                                        <input type="password" class="form-control" id="senha_atual" name="senha_atual">
                                                                    </div>
                                                                    
                                                                    <div class="form-group">
                                                                        <label for="nova_senha">Nova Senha</label>
                                                                        <input type="password" class="form-control" id="nova_senha" name="nova_senha">
                                                                        <small class="form-text text-muted">Deixe em branco para manter a senha atual</small>
                                                                    </div>
                                                                    
                                                                    <div class="form-group">
                                                                        <label for="confirmar_senha">Confirmar Nova Senha</label>
                                                                        <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha">
                                                                    </div>
                                                                    
                                                                    <div class="form-group text-right">
                                                                        <button type="submit" class="btn btn-primary">
                                                                            <i class="feather icon-save"></i> Salvar Alterações
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </form>
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
        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('previewFoto').src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
        
        function removerFoto() {
            if (confirm('Tem certeza que deseja remover sua foto de perfil?')) {
                $.ajax({
                    url: '../../actions/remover_foto.php',
                    method: 'POST',
                    data: { id_usuario: <?= $id_usuario ?> },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#previewFoto').attr('src', '../../assets/images/default-profile.png');
                            $('#foto_perfil').val('');
                            alert(response.message);
                        } else {
                            alert('Erro: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('Erro na comunicação com o servidor');
                    }
                });
            }
        }
        
        $(document).ready(function() {
            $('#formPerfil').submit(function(e) {
                const novaSenha = $('#nova_senha').val();
                const confirmarSenha = $('#confirmar_senha').val();
                
                if (novaSenha || confirmarSenha) {
                    const senhaAtual = $('#senha_atual').val();
                    
                    if (!senhaAtual) {
                        alert('Por favor, informe sua senha atual para alterar a senha.');
                        e.preventDefault();
                        return false;
                    }
                    
                    if (novaSenha !== confirmarSenha) {
                        alert('A nova senha e a confirmação não coincidem.');
                        e.preventDefault();
                        return false;
                    }
                    
                    if (novaSenha.length < 6) {
                        alert('A senha deve ter pelo menos 6 caracteres.');
                        e.preventDefault();
                        return false;
                    }
                }
                
                return true;
            });
        });
    </script>
</body>
</html>