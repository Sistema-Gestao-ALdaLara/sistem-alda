<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnoLectivo extends Model
{
    public $table = "ano_lectivo";
    public $timestamps = false;
    protected $fillable = [
        'nome_ano',
    ];

    public static function pegarAno($id_ano){
        return self::where('id', $id_ano)->first();
    }

    public static function retornaAnoActual(){
        return self::orderByDesc('id')->first();
    }
}
