<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pauta extends Model
{
    public $table = "pauta";
    public $timesatamps = false;
    protected $fillable = [
        'trimestre_id',
        'turma_id',
        'ano_lectivo',
    ];

    public static function cadastrarPauta($trimestre_id, $turma_id){
        return self::insert([
            'trimestre_id' => $trimestre_id,
            'turma_id' => $turma_id,
            'ano_lectivo' => session('id_ano_lectivo'),
        ]);
    }
}
