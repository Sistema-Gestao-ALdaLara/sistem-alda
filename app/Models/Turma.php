<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Turma extends Model
{
    public $table = "turma";
    public $timestamps = false;
    protected $fillable = [
        'ano_lectivo',
        'sala_id',
        'curso_id',
        'classe_id',
        'periodo_id',
        'turma',
    ];

    public static function cadastrarTurma($ano_lectivo, $id_sala, $id_curso, $id_classe, $id_periodo, $turma){
            return  self::insert([
                        'ano_lectivo' => $ano_lectivo,
                        'sala_id' => $id_sala,
                        'curso_id' => $id_curso,
                        'classe_id' => $id_classe,
                        'periodo_id' => $id_periodo,
                        'turma' => $turma
                    ]);
    }

    public static function listarTurmas(){
        return self::join('sala', 'turma.sala_id', 'sala.id')
                    ->join('curso', 'turma.curso_id', 'curso.id')
                    ->join('classe', 'turma.classe_id', 'classe.id')
                    ->join('periodo', 'turma.periodo_id', 'periodo.id')
                    ->select('turma.*', 'curso_id', 'classe_id', 'periodo_id', 'sala_id')
                    ->where('turma.ano_lectivo', session('id_ano_lectivo'))
                    ->orderBy('turma')->get();
    }

    //função para pegar dados de uma turma de um determinado curso
    public static function listarTurmasDoCurso($id_curso){
        return self::join('sala', 'turma.sala_id', 'sala.id')
                    ->join('curso', 'turma.curso_id', 'curso.id')
                    ->join('classe', 'turma.classe_id', 'classe.id')
                    ->join('periodo', 'turma.periodo_id', 'periodo.id')
                    ->select('turma.*', 'curso_id', 'classe_id', 'periodo_id', 'sala_id')
                    ->where('turma.ano_lectivo', session('id_ano_lectivo'))
                    ->where('turma.curso_id', $id_curso)->get();
    }

    //função para pegar dados de uma turma de uma classe e de um determinado curso
    public static function listarTurmasDaClasseDoCurso($id_classe, $id_curso){
        return self::join('sala', 'turma.sala_id', 'sala.id')
                    ->join('curso', 'turma.curso_id', 'curso.id')
                    ->join('classe', 'turma.classe_id', 'classe.id')
                    ->join('periodo', 'turma.periodo_id', 'periodo.id')
                    ->where('turma.curso_id', $id_curso)
                    ->where('turma.classe_id', $id_classe)
                    ->select('turma.*')
                    ->orderBy('turma')->get();
    }

    public static function pegarTurma($id_turma){
        return self::where('id', $id_turma)->first();
    }

    public static function eliminarTurma($id_turma){
        return self::where('id', $id_turma)->delete();
    }

    public static function retornaLetraTurma($id_turma){
        $nome = self::pegarTurma($id_turma)->turma;
        $array = str_split($nome);

        $letra = (strlen($nome) == 6) ? $array[4] : $array[3];

        return $letra;
    }
}
