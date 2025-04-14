<?php
// Restringe a página apenas para secretaria e diretor
require_once 'auth.php'; // Verifica login

$tipos_permitidos = ['secretaria', 'diretor_geral'];
if (!in_array($_SESSION['tipo_usuario'], $tipos_permitidos)) {
    header("Location: dashboard-" . $_SESSION['tipo_usuario'] . ".htm");
    exit();
}
?>

<!DOCTYPE html>
<html>
<!-- Restante do seu HTML -->