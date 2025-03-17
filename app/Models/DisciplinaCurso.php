<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisciplinaCurso extends Model
{
    public $table = "disciplina_curso";
    public $timestamps = false;
    protected $fillable = [
        'curso_id',
        'classe_id',
        'disciplina_id',
        'disciplina_recurso',
    ];

    public static function cadastrarCursoDisciplina($dados){
        if(is_array($dados))
            return self::create($dados);
     }
 
     public static function listarAssociacoes(){
         return self::join('curso', 'disciplina_curso.curso_id', 'curso.id')
                    ->join('classe', 'disciplina_curso.classe_id', 'classe.id')
                    ->join('disciplinas', 'disciplina_curso.disciplina_id', 'disciplinas.id')
                    ->get();
     }

     public static function disciplinasClasseCurso($curso_id, $classe_id){
        return self::join('curso', 'disciplina_curso.curso_id', 'curso.id')
                   ->join('classe', 'disciplina_curso.classe_id', 'classe.id')
                   ->join('disciplinas', 'disciplina_curso.disciplina_id', 'disciplinas.id')
                   ->where('classe_id', $classe_id)
                   ->where('curso_id', $curso_id)
                   ->get();
    }
    

    public static function disciplinaDaClasseCurso($classe_id, $curso_id, $disciplina_id){
        return self::join('curso', 'disciplina_curso.curso_id', 'curso.id')
                   ->join('classe', 'disciplina_curso.classe_id', 'classe.id')
                   ->join('disciplinas', 'disciplina_curso.disciplina_id', 'disciplinas.id')
                   ->where('classe_id', $classe_id)
                   ->where('curso_id', $curso_id)
                   ->where('disciplina_id', $disciplina_id)
                   ->first();
    }
}
