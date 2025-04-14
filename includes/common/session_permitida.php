<?php
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

// Define quais tipos de usuário podem acessar esta página
$tipos_permitidos = ['secretaria', 'diretor_geral']; // Exemplo: apenas secretária e diretor

if (!in_array($_SESSION['tipo_usuario'], $tipos_permitidos)) {
    header("Location: acesso-negado.php"); // Ou redirecione para o dashboard do usuário
    exit();
}
?>