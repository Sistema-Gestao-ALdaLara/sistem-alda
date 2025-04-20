<?php
function verificarPermissao($permissoesPermitidas) {
    session_start();
    
    
    // Se não estiver logado, redireciona para login
    if (!isset($_SESSION['tipo_usuario'])) {
        $_SESSION['erro'] = "Você não tem permissão para acessar esta página.";
        echo $_SESSION['erro'];
        header("Location: ../../process/login.php");
        exit();
    }
    
    // Se não tiver permissão, redireciona para página de acesso negado
    if (!in_array($_SESSION['tipo_usuario'], $permissoesPermitidas)) {
        header("Location: ../../public/erro403.htm");
        exit();
    }
}
?>