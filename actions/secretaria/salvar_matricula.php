<?php
require_once '../../database/conexao.php';

header('Content-Type: application/json');

try {
    $dados = $_POST;

    // Validações obrigatórias
    if (!isset($dados['id_aluno']) || empty($dados['id_aluno'])) {
        throw new Exception('Aluno é obrigatório.');
    }

    if (!isset($dados['ano_letivo']) || empty($dados['ano_letivo'])) {
        throw new Exception('Ano letivo é obrigatório.');
    }

    if (!isset($dados['id_curso']) || empty($dados['id_curso'])) {
        throw new Exception('Curso é obrigatório.');
    }

    // Verificar se já existe matrícula para este aluno no ano letivo
    $stmt = $conn->prepare("SELECT id_matricula FROM matricula WHERE aluno_id_aluno = ? AND ano_letivo = ?");
    $stmt->bind_param("ii", $dados['id_aluno'], $dados['ano_letivo']);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        throw new Exception('Este aluno já possui matrícula para o ano letivo selecionado.');
    }
    $stmt->close();

    // Preparando os dados opcionais corretamente
    $id_turma = isset($dados['id_turma']) && !empty($dados['id_turma']) ? $dados['id_turma'] : null;

    // Inserir matrícula
    $stmt = $conn->prepare("INSERT INTO matricula 
                           (aluno_id_aluno, curso_id_curso, turma_id_turma, ano_letivo, 
                            data_matricula) 
                           VALUES (?, ?, ?, ?, NOW())");

    // Alteração na ordem e tipo dos parâmetros de acordo com a estrutura
    $stmt->bind_param(
        "iiis", 
        $dados['id_aluno'],
        $dados['id_curso'],
        $id_turma,
        $dados['ano_letivo']
    );

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Matrícula registrada com sucesso.'
        ]);
    } else {
        throw new Exception('Erro ao registrar matrícula: ' . $stmt->error);
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
