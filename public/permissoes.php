<?php
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['tipo_usuario'])) {
    header("Location: /auth/auth-normal-sign-in.php"); // Redireciona para login
    exit();
}

// Função para verificar permissões
function verificarPermissao($permissoesPermitidas) {
    if (!in_array($_SESSION['tipo_usuario'], $permissoesPermitidas)) {
        header("Location: /erro403.php"); // Página de acesso negado
        exit();
    }
}
?>
