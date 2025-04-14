<?php
// Conectar ao banco de dados
$conn = new mysqli("localhost", "root", "", "escoladb");

// Verificar conexão
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

// Buscar os últimos registros de matrícula e transferência
$sql = "SELECT id, nome_aluno, turma, tipo, data FROM registros ORDER BY data DESC LIMIT 10";
$result = $conn->query($sql);
?>

<div class="card mb-4">
    <div class="card-header">
        <h5>Últimos Registros (Matrículas e Transferências)</h5>
    </div>
    <div class="card-block table-border-style">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Aluno</th>
                        <th>Turma</th>
                        <th>Tipo</th>
                        <th>Data</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <th scope="row"><?php echo $row['id']; ?></th>
                            <td><?php echo $row['nome_aluno']; ?></td>
                            <td><?php echo $row['turma']; ?></td>
                            <td><?php echo $row['tipo']; ?></td>
                            <td><?php echo date("d/m/Y", strtotime($row['data'])); ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php $conn->close(); ?>
