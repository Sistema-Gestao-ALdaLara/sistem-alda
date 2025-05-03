<?php
$senha_plana = "secretaria";
$senha_hash = password_hash($senha_plana, PASSWORD_DEFAULT);

echo $senha_hash;
?>
