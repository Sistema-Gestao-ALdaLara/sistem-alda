<?php
session_start();
require_once '../../database/conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Inicializa variável de erro
    $erro = '';
    
    // Validação básica dos inputs
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL) ?? '';
    $senha = $_POST['password'] ?? '';
    
    if (empty($email) || empty($senha)) {
        $erro = "Por favor, preencha todos os campos";
    } else {
        // Busca o usuário no banco de dados (AGORA SELECIONANDO TODOS OS CAMPOS NECESSÁRIOS)
        $stmt = $conn->prepare("SELECT id_usuario, nome, email, senha, tipo, status FROM usuario WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $usuario = $result->fetch_assoc();
            
            // Verifica a senha
            if (password_verify($senha, $usuario['senha'])) {
                // Verifica se o usuário está ativo
                if ($usuario['status'] === 'ativo') {
                    // Cria a sessão
                    $_SESSION['id_usuario'] = $usuario['id_usuario'];
                    $_SESSION['nome_usuario'] = $usuario['nome'];
                    $_SESSION['tipo_usuario'] = $usuario['tipo'];
                    $_SESSION['email'] = $usuario['email'];
                    
                    // Redireciona conforme o tipo de usuário (CAMINHOS CORRIGIDOS)
                    $redirect = match($usuario['tipo']) {
                        'diretor_geral' => '../../pages/diretor_geral/dashboard.php',
                        'diretor_pedagogico' => '../../pages/diretor_pedagogico/dashboard.php',
                        'coordenador' => '../../pages/coordenador/dashboard.php',
                        'professor' => '../../pages/professor/dashboard.php',
                        'secretaria' => '../../pages/secretaria/dashboard.php',
                        'aluno' => '../../pages/aluno/dashboard.php',
                        default => '../../public/index.php'
                    };
                    
                    header("Location: $redirect");
                    exit();
                } else {
                    $erro = "Sua conta está inativa. Entre em contato com o administrador.";
                }
            } else {
                $erro = "Email ou senha incorretos.";
            }
        } else {
            $erro = "Email ou senha incorretos.";
        }
    }
    
    // Se houver erro, volta para a página de login com mensagem
    $_SESSION['erro_login'] = $erro;
    header("Location: ../../process/login.php");
    exit();
}