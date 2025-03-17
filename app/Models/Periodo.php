<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Periodo extends Model
{
    public $table = "periodo";
    public $timestamps = false;
    protected $fillable = [
        'nome',
    ];

    public static function listarPeriodos(){
        return self::all();
    }

    public static function pegarPeriodo($id_periodo){
        return self::where('id', $id_periodo)->first();
    }

    public static function retornaAbreviacao($id_periodo){
        $nome_periodo = self::where('id', $id_periodo)->select('nome')->first()->nome;

        switch ($nome_periodo) {
            case 'Tarde':
                return 'T';
                break;
            
            case 'Manhã':
                return 'M';
                break;

            case 'Noite':
                return 'N';
                break;
        }
    }
}
