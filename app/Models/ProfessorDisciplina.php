<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfessorDisciplina extends Model
{
    public $table = "professor_disciplina";
    public $timestamps = false;
    protected $fillable = [
        'usuario_id',
        'disciplina_id',
        'turma_id',
        'ano_lectivo',
    ];

    public static function cadastrarProfDisciplina($usuario_id, $id_disciplina, $id_turma){
        return self::insert([
                        'usuario_id' => $usuario_id,
                        'disciplina_id' => $id_disciplina,
                        'turma_id' => $id_turma,
                        'ano_lectivo' => session('id_ano_lectivo'),
                    ]);
     }

     public static function listarAssociacoes(){
        return self::join('users', 'professor_disciplina.usuario_id', 'users.id')
                   ->join('disciplinas', 'professor_disciplina.disciplina_id', 'disciplinas.id')
                   ->join('turma', 'professor_disciplina.turma_id', 'turma.id')
                   ->get();
    }

    public static function pegarProfDisciplinaDaTurma($id_disciplina, $id_turma){
        return self::join('users', 'professor_disciplina.usuario_id', 'users.id')
                   ->join('disciplinas', 'professor_disciplina.disciplina_id', 'disciplinas.id')
                   ->join('turma', 'professor_disciplina.turma_id', 'turma.id')
                   ->where('turma_id', $id_turma)
                   ->where('disciplina_id', $id_disciplina)
                   ->where('professor_disciplina.ano_lectivo', session('id_ano_lectivo'))
                   ->first();
    }
 
     public static function listarTurmas($id_usuario){
         return self::join('turma', 'professor_disciplina.turma_id', 'turma.id')
                    ->join('disciplinas', 'professor_disciplina.disciplina_id', 'disciplinas.id')
                    ->select('disciplina_id', 'turma_id', 'turma.turma')
                    ->where('professor_disciplina.usuario_id', $id_usuario)
                    ->where('professor_disciplina.ano_lectivo', session('id_ano_lectivo'))
                    ->get();
     }
}
