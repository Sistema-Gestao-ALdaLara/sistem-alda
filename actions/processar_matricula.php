<?php
require_once '../includes/common/permissoes.php';
verificarPermissao(['secretaria', 'diretor_geral', 'diretor_pedagogico']);
require_once '../database/conexao.php';

header('Content-Type: application/json');

// Validar e sanitizar dados
$nome = $conn->real_escape_string($_POST['nome']);
$bi_numero = $conn->real_escape_string($_POST['bi_numero']);
$email = $conn->real_escape_string($_POST['email']);
$senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
$data_nascimento = $_POST['data_nascimento'];
$genero = $_POST['genero'];
$naturalidade = $conn->real_escape_string($_POST['naturalidade']);
$nacionalidade = $conn->real_escape_string($_POST['nacionalidade']);
$municipio = $conn->real_escape_string($_POST['municipio']);
$nome_encarregado = $conn->real_escape_string($_POST['nome_encarregado']);
$contacto_encarregado = $conn->real_escape_string($_POST['contacto_encarregado']);
$id_turma = intval($_POST['id_turma']);
$ano_letivo = intval($_POST['ano_letivo']);

// Processar upload do comprovativo
$comprovativo_path = '';
if (isset($_FILES['comprovativo'])) {
    $uploadDir = '../../uploads/comprovativos/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $comprovativo_name = uniqid() . '_' . basename($_FILES['comprovativo']['name']);
    $comprovativo_path = $uploadDir . $comprovativo_name;
    
    if (!move_uploaded_file($_FILES['comprovativo']['tmp_name'], $comprovativo_path)) {
        echo json_encode(['success' => false, 'message' => 'Erro ao fazer upload do comprovativo']);
        exit();
    }
}

// Processar upload da foto de perfil (se existir)
$foto_perfil_path = null;
if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] == UPLOAD_ERR_OK) {
    $uploadDir = '../../uploads/alunos/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $foto_name = uniqid() . '_' . basename($_FILES['foto_perfil']['name']);
    $foto_perfil_path = $uploadDir . $foto_name;
    
    if (!move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $foto_perfil_path)) {
        echo json_encode(['success' => false, 'message' => 'Erro ao fazer upload da foto de perfil']);
        exit();
    }
}

// Iniciar transação
$conn->begin_transaction();

try {
    // 1. Criar usuário
    $stmt = $conn->prepare("INSERT INTO usuario 
        (nome, email, senha, bi_numero, tipo, status, foto_perfil) 
        VALUES (?, ?, ?, ?, 'aluno', 'ativo', ?)");
    $stmt->bind_param("sssss", $nome, $email, $senha, $bi_numero, $foto_perfil_path);
    $stmt->execute();
    $id_usuario = $conn->insert_id;
    
    // 2. Criar aluno
    $stmt = $conn->prepare("INSERT INTO aluno 
        (data_nascimento, genero, naturalidade, nacionalidade, municipio, 
        nome_encarregado, contacto_encarregado, usuario_id_usuario, 
        turma_id_turma) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssii", $data_nascimento, $genero, $naturalidade, 
        $nacionalidade, $municipio, $nome_encarregado, $contacto_encarregado, 
        $id_usuario, $id_turma);
    $stmt->execute();
    $id_aluno = $conn->insert_id;
    
    // 3. Gerar número de matrícula automático
    $stmt = $conn->prepare("SELECT MAX(id_matricula) as ultimo_id FROM matricula WHERE ano_letivo = ?");
    $stmt->bind_param("i", $ano_letivo);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $sequencial = $row['ultimo_id'] ? $row['ultimo_id'] + 1 : 1;
    $numero_matricula = 'AL-' . $ano_letivo . '-' . str_pad($sequencial, 4, '0', STR_PAD_LEFT);
    
    // 4. Criar matrícula
    $stmt = $conn->prepare("INSERT INTO matricula 
        (ano_letivo, numero_matricula, data_matricula, 
        turma_id_turma, aluno_id_aluno, status_matricula, comprovativo_pagamento) 
        VALUES (?, ?, NOW(), ?, ?, 'ativa', ?)");
    $stmt->bind_param("issss", $ano_letivo, $numero_matricula, $id_turma, $id_aluno, $comprovativo_path);
    $stmt->execute();
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => "Matrícula registrada com sucesso! Número: $numero_matricula"
    ]);
} catch (Exception $e) {
    $conn->rollback();
    
    // Remover arquivos enviados em caso de erro
    if (!empty($comprovativo_path) && file_exists($comprovativo_path)) {
        unlink($comprovativo_path);
    }
    if (!empty($foto_perfil_path) && file_exists($foto_perfil_path)) {
        unlink($foto_perfil_path);
    }
    
    echo json_encode([
        'success' => false,
        'message' => "Erro ao registrar matrícula: " . $e->getMessage()
    ]);
}