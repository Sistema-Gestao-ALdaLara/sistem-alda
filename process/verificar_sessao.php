<?php
    // Verifica se o usuário está logado
    if (!isset($_SESSION['id_usuario'])) {
        // Redireciona para a página de login se não estiver logado
        header("Location: login.php");
        exit();
    }

    // Verifica se o tipo de usuário tem permissão para acessar a página atual
    $pagina_atual = basename($_SERVER['PHP_SELF']);
    $tipos_permitidos = [
        '../../pages/diretor_geral/dashboard.php' => ['diretor_geral'],
        '../../pages/diretor_pedagogico/dashboard.php' => ['diretor_pedagogico'],
        '../../pages/coordenador/dashboard.php' => ['coordenador'],
        '../../pages/professor/dashboard.php' => ['professor'],
        '../../pages/secretaria/dashboard.php' => ['secretaria'],
        '../../pages/aluno/dashboard.php' => ['aluno']
    ];

    // Verifica se a página atual requer um tipo específico de usuário
    if (array_key_exists($pagina_atual, $tipos_permitidos)) {
        if (!in_array($_SESSION['tipo_usuario'], $tipos_permitidos[$pagina_atual])) {
            // Redireciona para o dashboard correspondente ao tipo de usuário
            $redirect = match($_SESSION['tipo_usuario']) {
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
        }
    }

?>