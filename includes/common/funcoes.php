<?php 
    require_once '../../database/conexao.php';
    
    function obterFotoPerfil1($id_usuario) {
        global $conn;
        $query = "SELECT foto_perfil FROM usuario WHERE id_usuario = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        $usuario1 = $result->fetch_assoc();
        
        // Retorna a foto do perfil ou uma imagem padrão se não houver
        return $usuario1['foto_perfil'] ?? '/assets/images/avatar-default.png';
    }
    
    function formatarDataRelativa($data) {
        $agora = new DateTime();
        $data_comunicado = new DateTime($data);
        $intervalo = $agora->diff($data_comunicado);
        
        if ($intervalo->y > 0) {
            return $intervalo->y . ' ano' . ($intervalo->y > 1 ? 's' : '') . ' atrás';
        } elseif ($intervalo->m > 0) {
            return $intervalo->m . ' mês' . ($intervalo->m > 1 ? 'es' : '') . ' atrás';
        } elseif ($intervalo->d > 0) {
            return $intervalo->d . ' dia' . ($intervalo->d > 1 ? 's' : '') . ' atrás';
        } elseif ($intervalo->h > 0) {
            return $intervalo->h . ' hora' . ($intervalo->h > 1 ? 's' : '') . ' atrás';
        } elseif ($intervalo->i > 0) {
            return $intervalo->i . ' minuto' . ($intervalo->i > 1 ? 's' : '') . ' atrás';
        } else {
            return 'Agora mesmo';
        }
    }