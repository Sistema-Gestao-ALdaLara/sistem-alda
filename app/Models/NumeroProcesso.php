<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NumeroProcesso extends Model
{
    public $table = "numero_processo";
    public $timesatamps = false;
    protected $fillable = [
        'ultimo_numero',
        'data_criacao'
    ];

    public static function inserirNumeroProcesso($numero_processo){
        return self::insert(['ultimo_numero' => $numero_processo]);
    }

    public static function retornaNumeroProcesso(){
        return self::select('ultimo_numero')->first()->ultimo_numero ?? null;
    }
}
