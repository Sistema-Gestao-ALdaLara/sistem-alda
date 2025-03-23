<?php
function listarCursosETurmas($tipo_usuario) {
    // Simulação de cursos e turmas (depois isso pode vir do banco de dados)
    $cursos = [
        "Ciências Exatas" => ["10º A", "10º B", "11º A"],
        "Informática" => ["11º B", "12º A"],
        "Gestão" => ["12º B", "13º A"]
    ];

    echo '<div class="tab-content">';
    foreach ($cursos as $curso => $turmas) {
        echo '<div class="tab-pane" id="' . strtolower(str_replace(' ', '_', $curso)) . '" role="tabpanel">';
        echo "<h5>📚 $curso</h5>";

        foreach ($turmas as $turma) {
            echo "<h6>Turma: $turma</h6>";
            echo '<table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Exemplo Nome</td>
                            <td>exemplo@email.com</td>
                            <td><span class="badge badge-success">Ativo</span></td>
                            <td>
                                <button class="btn btn-sm btn-primary"><i class="feather icon-edit"></i></button>
                                <button class="btn btn-sm btn-danger"><i class="feather icon-trash"></i></button>
                            </td>
                        </tr>
                    </tbody>
                  </table>';
        }

        echo '</div>';
    }
    echo '</div>';
}
?>
