<?php 
    require_once '../../database/conexao.php';
    function obterFotoPerfil($id_usuario) {
    global $conn;
    $query = "SELECT foto_perfil FROM usuario WHERE id_usuario = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return !empty($row['foto_perfil']) ? 
               '../../uploads/perfil/' . $row['foto_perfil'] : 
               '../../public/libraries/assets/images/avatar-4.jpg';
    }
    
    return '../../public/libraries/assets/images/avatar-4.jpg';
}

function formatarDataRelativa($data) {
    $now = new DateTime();
    $dataComunicado = new DateTime($data);
    $interval = $now->diff($dataComunicado);
    
    if ($interval->y > 0) {
        return $interval->y . ' ano(s) atrás';
    } elseif ($interval->m > 0) {
        return $interval->m . ' mês(es) atrás';
    } elseif ($interval->d > 0) {
        return $interval->d . ' dia(s) atrás';
    } elseif ($interval->h > 0) {
        return $interval->h . ' hora(s) atrás';
    } elseif ($interval->i > 0) {
        return $interval->i . ' minuto(s) atrás';
    } else {
        return 'agora mesmo';
    }
}