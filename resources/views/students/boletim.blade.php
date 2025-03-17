<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boletim de Notas do {{DB::table('trimestre')->where('id', $trimestre_id)->first()->nome_trimestre}} TRIMESTRE</title>
   
    
<style>
     table {
        font-family:  'Times New Roman', Times, serif;
        border-collapse: collapse;
        width: 100%;
    }
    
    td,th {
        border: 1px solid black;
        text-align: left;
        padding-left: 1px;
        
    }
    .line{
        text-align: center;
        
    }
    .img{
        display: inline-block;
        margin-left: 14%;
        margin-top: -15px;
    }

    footer{
        width: 100%;
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-start;
        align-items: flex-start;
        align-content: flex-start;
    }

    #data {
            /* float: left;  */
            float: left;
            position: fixed;
            width: 380px;
        }

    #coordenador {
        text-align: center;
        margin-left: -270;
        float: left;
        position: fixed;
        bottom: -10;
        width: 250px;
    }
    #director-turma {
        text-align: center;
        float: right;
        position: fixed;
        bottom: -10;
        width: 250px;
    }
  
</style>
    
</head>

<body>
    <br>
        <div class="img">
            <br>
              <div class="line">
                <img src='data:image/png;base64,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'/>
                <br>
                <br>
                    INSTITUTO MÉDIO INSDUTRIAL SIMIONE MUCUNE Nº 1119 <br>
                    SUBDIRECÇÃO PEDAGÓGICA <br>
                    CURSO: <b>{{mb_strtoupper(App\Models\Curso::pegarCurso($dados_turma->curso_id)->nome)}}</b><br>
                    <b>BOLETIM DE NOTAS DO {{DB::table('trimestre')->where('id', $trimestre_id)->first()->nome_trimestre}} TRIMESTRE / ANO LECTIVO {{session('ano_em_curso')}}</b>
                <br>
                <br>
                <br>
                
              </div>
        </div>
        <table>
            <thead>
                <tr>
                    <th rowspan="5"></th>
                    <th colspan="4" style="text-align: center">Dados do Aluno</th>
                </tr>
                <tr>
                    <td colspan="3">Nome: <b style="color: red">{{$aluno->nome_cand}}</b></td>
                    <td>Classe: <b>{{App\Models\Classe::pegarClasse($dados_turma->classe_id)->nome_classe}}</b></td>
                </tr>
                <tr>
                    <td>Nº de Processo: <b>{{$aluno->numero_processo}}</b></td>
                    <td colspan="2">Nº Convencional: <b>{{$aluno->numero_aluno}}</b></td>
                    <td>Turma: <b>{{$dados_turma->turma}}</b></td>
                </tr>
                <tr>
                    <th rowspan="2" style="text-align: center">Disciplinas</th>
                    <th colspan="2" style="text-align: center">Notas</th>
                    <th rowspan="2" style="text-align: center;">OBS</th>
                </tr>
                <tr>
                    <td style="text-align: center; width: 140px">COMPREENSÃO</td>
                    <td style="text-align: center">EXTENSÃO</td>
                </tr>
            </thead>
            <tbody>
               @foreach ($disciplinas as $disciplina)
                   <tr>
                       <td style="text-align: center">{{++$contagem}}</td>
                       <td>{{$disciplina->nome_disciplina}}</td>

                       @if (App\Models\ProvasTrimestre::buscarNotas($trimestre_id, session('aluno_id'), $disciplina->disciplina_id)->mt ?? "-" >= 10)
                           <td style="text-align: center; color: blue">{{App\Models\ProvasTrimestre::buscarNotas($trimestre_id, session('aluno_id'), $disciplina->disciplina_id)->mt ?? "-"}}</td>
                            <td style="text-align: center; color: blue">{{App\Utils\Auxiliar::notaPorExtenso(App\Models\ProvasTrimestre::buscarNotas($trimestre_id, session('aluno_id'), $disciplina->disciplina_id)->mt ?? "-")}}</td>
                       @elseif(App\Models\ProvasTrimestre::buscarNotas($trimestre_id, session('aluno_id'), $disciplina->disciplina_id)->mt ?? "-" != "-")
                           <td style="text-align: center; color: red">{{App\Models\ProvasTrimestre::buscarNotas($trimestre_id, session('aluno_id'), $disciplina->disciplina_id)->mt ?? "-"}}</td>
                            <td style="text-align: center; color: red">{{App\Utils\Auxiliar::notaPorExtenso(App\Models\ProvasTrimestre::buscarNotas($trimestre_id, session('aluno_id'), $disciplina->disciplina_id)->mt ?? "-")}}</td>
                        @else
                           <td style="text-align: center;">-</td>
                            <td style="text-align: center;"></td>
                       @endif
                       <td style="text-align: center; border-top: 1.2px transparent;"></td>
                   </tr>
               @endforeach
            </tbody>
        </table>
    </div>

    <footer>
        <div id="data">
            <br>
            &nbsp; LUANDA AOS ______/__________________/_______
        </div>

        <div id="coordenador">
            O COORDENADOR DO CURSO <br><br>
            _______________________________ <br>
            {{mb_strtoupper(App\Models\Coordenador::pegarCoordenadorDeCurso($dados_turma->curso_id)->nome)}}
        </div>

        <div id="director-turma">
            O DIRECTOR DE TURMA <br><br>
            _______________________________ <br> <br>
        </div>
    </footer>
    <script>
        
    </script>
</body>
</html>
