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
        'dashboard-dg.htm' => ['diretor_geral'],
        'dashboard-dp.htm' => ['diretor_pedagogico'],
        'dashboard-cc.htm' => ['coordenador'],
        'dashboard-prf.htm' => ['professor'],
        'dashboard-sc.htm' => ['secretaria'],
        'dashboard-aln.htm' => ['aluno']
    ];

    // Verifica se a página atual requer um tipo específico de usuário
    if (array_key_exists($pagina_atual, $tipos_permitidos)) {
        if (!in_array($_SESSION['tipo_usuario'], $tipos_permitidos[$pagina_atual])) {
            // Redireciona para o dashboard correspondente ao tipo de usuário
            $redirect = match($_SESSION['tipo_usuario']) {
                'diretor_geral' => 'dashboard-dg.htm',
                'diretor_pedagogico' => 'dashboard-dp.htm',
                'coordenador' => 'dashboard-cc.htm',
                'professor' => 'dashboard-prf.htm',
                'secretaria' => 'dashboard-sc.htm',
                'aluno' => 'dashboard-aln.htm',
                default => 'index.php'
            };
            header("Location: $redirect");
            exit();
        }
    }

?>