<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnoLectivoAluno extends Model
{
    public $table = "ano_lectivo_aluno";
    public $timesatamps = false;
    protected $fillable = [
        'aluno_id',
        'numero_aluno',
        'turma_id',
        'status_inicio_ano',
        'status_fim_ano',
        'ano_lectivo'
    ];

    public static function cadastrarAluno($id_aluno, $numero_aluno, $id_turma, $status_inicio, $status_fim, $ano_lectivo){
        return self::insert([
                    'aluno_id' => $id_aluno,
                    'numero_aluno' => $numero_aluno,
                    'turma_id' => $id_turma,
                    'status_inicio_ano' => $status_inicio,
                    'status_fim_ano' => $status_fim,
                    'ano_lectivo' => $ano_lectivo
                ]);
    }

    public static function inserirStatusFimAno($aluno_id, $status)
    {
        return self::where('aluno_id', '=', $aluno_id)
                    ->where('ano_lectivo', '=', session('id_ano_lectivo'))
                    ->update([
                        'status_fim_ano' => $status
                    ]);
            }

    public static function listarAlunosDaTurma($id_turma){
        return self::join('aluno', 'ano_lectivo_aluno.aluno_id', 'aluno.id')
                    ->join('candidato', 'aluno.candidato_id', 'candidato.id')
                    ->join('status_ano', 'ano_lectivo_aluno.status_fim_ano', 'status_ano.id')
                    ->join('turma', 'ano_lectivo_aluno.turma_id', 'turma.id')
                    ->where('ano_lectivo_aluno.ano_lectivo', session('id_ano_lectivo'))
                    ->where('ano_lectivo_aluno.turma_id', $id_turma)
                    ->orderBy('candidato.nome_cand')
                    ->select('ano_lectivo_aluno.*', 'aluno.numero_processo', 'candidato.nome_cand', 'candidato.genero', 'candidato.data_nascimento', 'status_ano.nome_status')
                    ->get();
    }

    public static function actualizarListaDeAlunosDaTurma($id_turma){
        $alunos = self::listarAlunosDaTurma($id_turma);
        $contagem = 0;
        foreach($alunos as $aluno){
                self::where('id', $aluno->id)
                    ->update([
                        'numero_aluno' => ++$contagem
                    ]);
        }
    }
}
