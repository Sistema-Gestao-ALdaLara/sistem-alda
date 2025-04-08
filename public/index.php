<?php
session_start();

if (!isset($_SESSION['tipo'])) {
    header('Location: login.php');
    exit;
}

switch ($_SESSION['tipo']) {
    case 'secretaria':
        header('Location: ../../pages/secretaria/dashboard.php');
        break;
    case 'coordenador':
        header('Location: ../../pages/coordenador/dashboard.php');
        break;
    case 'aluno':
        header('Location: ../../pages/aluno/dashboard.php');
        break;
    case 'professor':
        header('Location: ../../pages/professor/dashboard.php');
        break;
    case 'diretor_geral':
        header('Location: ../../pages/diretor_geral/dashboard.php');
        break;
    case 'diretor_pedagogico':
        header('Location: ../../pages/deritor_pedagogico/dashboard.php');
        break;
    default:
        echo 'Tipo de usuário inválido!';
        session_destroy();
        break;
}
exit;
