<?php
require_once '../../includes/common/permissoes.php';
verificarPermissao(['professor']);
require_once '../../process/verificar_sessao.php';
require_once '../../database/conexao.php';

// Obter informações do professor
$id_usuario = $_SESSION['id_usuario'];
$sql_professor = "SELECT p.id_professor, c.nome as nome_curso, c.id_curso 
                 FROM professor p
                 JOIN curso c ON p.curso_id_curso = c.id_curso
                 WHERE p.usuario_id_usuario = ?";
$stmt = $conn->prepare($sql_professor);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
$professor = $result->fetch_assoc();

if (!$professor) {
    die("Acesso negado ou professor não encontrado.");
}

$id_professor = $professor['id_professor'];
$id_curso = $professor['id_curso'];

// Obter disciplinas do professor
$sql_disciplinas = "SELECT d.id_disciplina, d.nome
                   FROM disciplina d
                   JOIN professor_tem_disciplina ptd ON d.id_disciplina = ptd.disciplina_id_disciplina
                   WHERE ptd.professor_id_professor = ?
                   ORDER BY d.nome";
$stmt = $conn->prepare($sql_disciplinas);
$stmt->bind_param("i", $id_professor);
$stmt->execute();
$disciplinas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Obter turmas do curso
$sql_turmas = "SELECT id_turma, nome, classe 
              FROM turma 
              WHERE curso_id_curso = ?
              ORDER BY classe, nome";
$stmt = $conn->prepare($sql_turmas);
$stmt->bind_param("i", $id_curso);
$stmt->execute();
$turmas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Processar envio do formulário
$mensagem = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? '';
    $descricao = $_POST['descricao'] ?? '';
    $disciplina_id = $_POST['disciplina_id'] ?? '';
    $classe = $_POST['classe'] ?? '';
    $destino_tipo = $_POST['destino_tipo'] ?? '';
    $turmas_selecionadas = $_POST['turmas'] ?? [];
    
    // Validar dados
    if (empty($nome) || empty($destino_tipo) || empty($_FILES['arquivo'])) {
        $mensagem = '<div class="alert alert-danger">Preencha todos os campos obrigatórios!</div>';
    } else {
        // Processar upload do arquivo
        $diretorio = "../../uploads/materiais/";
        if (!file_exists($diretorio)) {
            mkdir($diretorio, 0777, true);
        }
        
        $nomeArquivo = uniqid() . '_' . basename($_FILES['arquivo']['name']);
        $caminhoCompleto = $diretorio . $nomeArquivo;
        
        if (move_uploaded_file($_FILES['arquivo']['tmp_name'], $caminhoCompleto)) {
            try {
                $conn->begin_transaction();
                
                // Inserir material no banco
                $sql_material = "INSERT INTO materiais_apoio 
                                (nome, descricao, caminho_arquivo, disciplina_id, curso_id, classe, usuario_id_upload) 
                                VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql_material);
                $disciplina_id = $disciplina_id ?? null;
                $classe = $classe ?? null;

                $stmt->bind_param("sssiiss", 
                    $nome, 
                    $descricao, 
                    $caminhoCompleto, 
                    $disciplina_id,
                    $id_curso,
                    $classe,
                    $id_usuario
                );
                $stmt->execute();
                $material_id = $conn->insert_id;
                
                // Inserir destinatários
                if ($destino_tipo == 'turma' && !empty($turmas_selecionadas)) {
                    foreach ($turmas_selecionadas as $turma_id) {
                        $sql_destino = "INSERT INTO material_destinatario 
                                       (material_id, tipo_destino, turma_id) 
                                       VALUES (?, 'turma', ?)";
                        $stmt = $conn->prepare($sql_destino);
                        $stmt->bind_param("ii", $material_id, $turma_id);
                        $stmt->execute();
                    }
                } elseif ($destino_tipo == 'curso') {
                    $sql_destino = "INSERT INTO material_destinatario 
                                   (material_id, tipo_destino, curso_id) 
                                   VALUES (?, 'curso', ?)";
                    $stmt = $conn->prepare($sql_destino);
                    $stmt->bind_param("ii", $material_id, $id_curso);
                    $stmt->execute();
                } elseif ($destino_tipo == 'classe' && $classe) {
                    $sql_destino = "INSERT INTO material_destinatario 
                                   (material_id, tipo_destino, classe) 
                                   VALUES (?, 'classe', ?)";
                    $stmt = $conn->prepare($sql_destino);
                    $stmt->bind_param("is", $material_id, $classe);
                    $stmt->execute();
                }
                
                $conn->commit();
                $mensagem = '<div class="alert alert-success">Material enviado com sucesso!</div>';
                
                // Redirecionar após 2 segundos
                echo '<script>
                    setTimeout(function() {
                        window.location.href = "materiais.php";
                    }, 2000);
                </script>';
            } catch (Exception $e) {
                $conn->rollback();
                unlink($caminhoCompleto); // Remove o arquivo em caso de erro
                $mensagem = '<div class="alert alert-danger">Erro ao enviar material: ' . $e->getMessage() . '</div>';
            }
        } else {
            $mensagem = '<div class="alert alert-danger">Erro ao fazer upload do arquivo!</div>';
        }
    }
}

$title = "Enviar Material de Apoio";
?>

<!DOCTYPE html>
<html lang="pt">

<?php require_once '../../includes/common/head.php'; ?>

<body>
    <?php require_once '../../includes/common/preloader.php'; ?>

    <div id="pcoded" class="pcoded">
        <div class="pcoded-overlay-box"></div>
        <div class="pcoded-container navbar-wrapper">

            <?php require_once '../../includes/professor/navbar.php'; ?>

            <div class="pcoded-main-container">
                <div class="pcoded-wrapper">
                    <?php require_once '../../includes/professor/sidebar.php'; ?>
                    
                    <div class="pcoded-content">
                        <div class="pcoded-inner-content">
                            <div class="main-body bg-img">
                                <div class="page-wrapper">

                                    <div class="page-body">
                                        <div class="card card-table">
                                            <div class="card-header">
                                                <h5>Enviar Novo Material de Apoio</h5>
                                            </div>
                                            <div class="card-block">
                                                <?php echo $mensagem; ?>
                                                
                                                <form method="post" enctype="multipart/form-data">
                                                    <div class="form-group row">
                                                        <label class="col-sm-2 col-form-label">Nome do Material*</label>
                                                        <div class="col-sm-10">
                                                            <input type="text" class="form-control" name="nome" required>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-group row">
                                                        <label class="col-sm-2 col-form-label">Descrição</label>
                                                        <div class="col-sm-10">
                                                            <textarea class="form-control" name="descricao" rows="3"></textarea>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-group row">
                                                        <label class="col-sm-2 col-form-label">Disciplina</label>
                                                        <div class="col-sm-10">
                                                            <select class="form-control" name="disciplina_id">
                                                                <option value="">-- Selecione --</option>
                                                                <?php foreach ($disciplinas as $disciplina): ?>
                                                                    <option value="<?= $disciplina['id_disciplina'] ?>">
                                                                        <?= htmlspecialchars($disciplina['nome']) ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                            <small class="text-muted">Deixe em branco para material geral do curso</small>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-group row">
                                                        <label class="col-sm-2 col-form-label">Classe</label>
                                                        <div class="col-sm-10">
                                                            <select class="form-control" name="classe">
                                                                <option value="10ª">-- Todas as Classes --</option>
                                                                <option value="10ª">10ª Classe</option>
                                                                <option value="11ª">11ª Classe</option>
                                                                <option value="12ª">12ª Classe</option>
                                                                <option value="13ª">13ª Classe</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-group row">
                                                        <label class="col-sm-2 col-form-label">Destino*</label>
                                                        <div class="col-sm-10">
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input" type="radio" name="destino_tipo" id="destino_curso" value="curso" checked>
                                                                <label class="form-check-label" for="destino_curso">
                                                                    Todo o Curso
                                                                </label>
                                                            </div>
                                                            
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input" type="radio" name="destino_tipo" id="destino_classe" value="classe">
                                                                <label class="form-check-label" for="destino_classe">
                                                                    Classe Específica (selecionada acima)
                                                                </label>
                                                            </div>
                                                            
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input" type="radio" name="destino_tipo" id="destino_turma" value="turma">
                                                                <label class="form-check-label" for="destino_turma">
                                                                    Turmas Específicas
                                                                </label>
                                                            </div>
                                                            
                                                            <div id="turmas_container" class="border p-3 mt-2" style="display: none;">
                                                                <h6>Selecione as Turmas:</h6>
                                                                <?php foreach ($turmas as $turma): ?>
                                                                    <div class="form-check ml-4">
                                                                        <input class="form-check-input" type="checkbox" name="turmas[]" value="<?= $turma['id_turma'] ?>" id="turma_<?= $turma['id_turma'] ?>">
                                                                        <label class="form-check-label" for="turma_<?= $turma['id_turma'] ?>">
                                                                            <?= htmlspecialchars($turma['nome']) ?> (<?= $turma['classe'] ?>)
                                                                        </label>
                                                                    </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-group row">
                                                        <label class="col-sm-2 col-form-label">Arquivo*</label>
                                                        <div class="col-sm-10">
                                                            <input type="file" class="form-control" name="arquivo" required>
                                                            <small class="text-muted">Formatos aceitos: PDF, DOC, PPT, XLS, ZIP, RAR, imagens</small>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-group row">
                                                        <div class="col-sm-10 offset-sm-2">
                                                            <button type="submit" class="btn btn-primary">
                                                                <i class="feather icon-upload"></i> Enviar Material
                                                            </button>
                                                            <a href="materiais.php" class="btn btn-secondary">
                                                                <i class="feather icon-x"></i> Cancelar
                                                            </a>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require_once '../../includes/common/js_imports.php'; ?>
    
    <script>
        // Mostrar/ocultar turmas quando selecionar o tipo de destino
        document.querySelectorAll('input[name="destino_tipo"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const turmasContainer = document.getElementById('turmas_container');
                turmasContainer.style.display = this.value === 'turma' ? 'block' : 'none';
            });
        });
    </script>
</body>
</html>