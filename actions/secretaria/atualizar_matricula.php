<?php
require_once '../../includes/common/permissoes.php';
verificarPermissao(['secretaria', 'diretor_geral', 'diretor_pedagogico']);
require_once '../../database/conexao.php';

header('Content-Type: application/json');

// Verificar se é uma atualização (deve ter ID da matrícula)
if (!isset($_POST['id_matricula']) || !is_numeric($_POST['id_matricula'])) {
    echo json_encode(['success' => false, 'message' => 'ID da matrícula não fornecido ou inválido.']);
    exit;
}

$id_matricula = (int)$_POST['id_matricula'];

// Validar e sanitizar dados
$nome = $conn->real_escape_string($_POST['nome']);
$bi_numero = $conn->real_escape_string($_POST['bi_numero']);
$email = $conn->real_escape_string($_POST['email']);
$data_nascimento = $_POST['data_nascimento'];
$genero = $_POST['genero'];
$naturalidade = $conn->real_escape_string($_POST['naturalidade']);
$nacionalidade = $conn->real_escape_string($_POST['nacionalidade']);
$municipio = $conn->real_escape_string($_POST['municipio']);
$nome_encarregado = $conn->real_escape_string($_POST['nome_encarregado']);
$contacto_encarregado = $conn->real_escape_string($_POST['contacto_encarregado']);
$id_turma = intval($_POST['id_turma']);
$ano_letivo = intval($_POST['ano_letivo']);

// Verificar se foi enviada nova senha (opcional na atualização)
$senha = isset($_POST['senha']) && !empty($_POST['senha']) ? 
          password_hash($_POST['senha'], PASSWORD_DEFAULT) : null;

// Processar upload do novo comprovativo (opcional)
$comprovativo_path = null;
if (isset($_FILES['comprovativo']) && $_FILES['comprovativo']['error'] === UPLOAD_ERR_OK) {
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

// Processar upload da nova foto de perfil (opcional)
$foto_perfil_path = null;
if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
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
    // 1. Obter IDs necessários para a atualização
    $query = "SELECT m.aluno_id_aluno, a.usuario_id_usuario 
              FROM matricula m
              JOIN aluno a ON m.aluno_id_aluno = a.id_aluno
              WHERE m.id_matricula = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_matricula);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Matrícula não encontrada.");
    }
    
    $dados = $result->fetch_assoc();
    $id_aluno = $dados['aluno_id_aluno'];
    $id_usuario = $dados['usuario_id_usuario'];
    
    // 2. Atualizar usuário
    $query_usuario = "UPDATE usuario SET 
                      nome = ?, 
                      email = ?, 
                      bi_numero = ?";
    
    // Adicionar senha se foi fornecida
    if ($senha) {
        $query_usuario .= ", senha = ?";
    }
    
    // Adicionar foto de perfil se foi fornecida
    if ($foto_perfil_path) {
        $query_usuario .= ", foto_perfil = ?";
    }
    
    $query_usuario .= " WHERE id_usuario = ?";
    
    $stmt = $conn->prepare($query_usuario);
    
    // Montar os parâmetros dinamicamente
    $params = [$nome, $email, $bi_numero];
    $types = "sss";
    
    if ($senha) {
        $params[] = $senha;
        $types .= "s";
    }
    
    if ($foto_perfil_path) {
        $params[] = $foto_perfil_path;
        $types .= "s";
    }
    
    $params[] = $id_usuario;
    $types .= "i";
    
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    
    // 3. Atualizar aluno
    $stmt = $conn->prepare("UPDATE aluno SET 
                           data_nascimento = ?, 
                           genero = ?, 
                           naturalidade = ?, 
                           nacionalidade = ?, 
                           municipio = ?, 
                           nome_encarregado = ?, 
                           contacto_encarregado = ?, 
                           turma_id_turma = ?
                           WHERE id_aluno = ?");
    $stmt->bind_param("sssssssii", $data_nascimento, $genero, $naturalidade, 
                     $nacionalidade, $municipio, $nome_encarregado, 
                     $contacto_encarregado, $id_turma, $id_aluno);
    $stmt->execute();
    
    // 4. Atualizar matrícula
    $query_matricula = "UPDATE matricula SET 
                       ano_letivo = ?, 
                       turma_id_turma = ?";
    
    // Adicionar comprovativo se foi fornecido
    if ($comprovativo_path) {
        $query_matricula .= ", comprovativo_pagamento = ?";
    }
    
    $query_matricula .= " WHERE id_matricula = ?";
    
    $stmt = $conn->prepare($query_matricula);
    
    if ($comprovativo_path) {
        $stmt->bind_param("iisi", $ano_letivo, $id_turma, $comprovativo_path, $id_matricula);
    } else {
        $stmt->bind_param("iii", $ano_letivo, $id_turma, $id_matricula);
    }
    
    $stmt->execute();
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => "Matrícula atualizada com sucesso!"
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
        'message' => "Erro ao atualizar matrícula: " . $e->getMessage()
    ]);
}
?>