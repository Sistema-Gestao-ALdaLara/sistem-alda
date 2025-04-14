<?php
$senha_plana = "diretoraldalara";
$senha_hash = password_hash($senha_plana, PASSWORD_DEFAULT);

echo $senha_hash;
?>
