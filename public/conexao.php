<?php
$servername = "localhost";  // Ou IP do servidor MySQL
$username = "root";         // Usuário do MySQL
$password = "";             // Senha do MySQL
$dbname = "escoladb";       // Nome do banco de dados

// Criar conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
    // Não revelar detalhes do erro em produção
    //die("Erro ao conectar ao sistema. Contate o administrador.");
}
?>

