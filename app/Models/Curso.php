<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Curso extends Model
{
    public $table = "curso";
    public $timestamps = false;
    protected $fillable = [
        'nome',
        'sigla',
        'descricao',
        'foto',
        'usuario_id',
    ];

    public static function cadastrarCurso($data){
        if(is_array($data)){
            $status = self::create($data);
            if($status){
                return true;
            }
            return false;
        }
    }

    public static function listarCursos(){
        return self::all();
    }

    public static function pegarCurso($id_curso){
        return self::where('id', $id_curso)->first();
    }

    public static function apagarCurso($id_curso){
        return self::where('id', $id_curso)->delete();
    }

}
