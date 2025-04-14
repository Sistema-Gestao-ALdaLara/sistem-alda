<?php
session_start();
require_once 'conexao.php';

// Habilita erros para depuração
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verifica se o usuário está logado como admin (opcional)
// if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'admin') {
//     die("Acesso negado. Esta página é apenas para testes.");
// }

// Verifica se o email foi enviado via GET
$email = filter_input(INPUT_GET, 'email', FILTER_SANITIZE_EMAIL) ?? '';

if (empty($email)) {
    die("Por favor, forneça um email via parâmetro GET: get_user_info.php?email=seu@email.com");
}

// Busca o usuário no banco de dados
try {
    $stmt = $conn->prepare("SELECT * FROM usuario WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        die("Nenhum usuário encontrado com o email: " . htmlspecialchars($email));
    }
    
    $usuario = $result->fetch_assoc();
    
    // Esconde a senha hash por segurança
    unset($usuario['senha']);
    
} catch (Exception $e) {
    die("Erro ao buscar usuário: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informações do Usuário</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; max-width: 800px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <h1>Informações do Usuário</h1>
    <p>Email pesquisado: <strong><?= htmlspecialchars($email) ?></strong></p>
    
    <?php if ($usuario): ?>
        <h2 class="success">Usuário encontrado!</h2>
        <table>
            <thead>
                <tr>
                    <th>Campo</th>
                    <th>Valor</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuario as $campo => $valor): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($campo) ?></strong></td>
                        <td><?= htmlspecialchars($valor) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="error">Nenhum usuário encontrado.</p>
    <?php endif; ?>
    
    <hr>
    <h3>Teste de Autenticação</h3>
    <form action="login.php" method="POST">
        <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
        <p>Você pode testar o login deste usuário:</p>
        <label for="password">Senha:</label>
        <input type="password" id="password" name="password" required>
        <button type="submit">Testar Login</button>
    </form>
</body>
</html>