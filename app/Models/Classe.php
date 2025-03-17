<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classe extends Model
{
    public $table = "classe";
    public $timestamps = false;
    protected $fillable = [
        'nome_classe',
    ];

    public static function listarClasses(){
        return self::all();
    }

    public static function pegarClasse($id_classe){
        return self::where('id', $id_classe)->first();
    }

    public static function retornaNumeroClasse($id_classe){
        $nome_classe = self::where('id', $id_classe)->select('nome_classe')->first()->nome_classe;

        return explode('ª', $nome_classe)[0];
    }
}
