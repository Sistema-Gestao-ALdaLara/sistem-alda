<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Disciplina extends Model
{
    public $table = "disciplinas";
    public $timestamps = false;
    protected $fillable = [
        'nome_disciplina'
    ];

    public static function cadastrarDisciplina($data){
        return $status = self::create($data);
    }

    public static function listarDisciplinas(){
        return self::all();
    }

    public static function pegarDisciplina($idDisciplina){
        return self::where('id', $idDisciplina)->first();
    }
}
