<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sala extends Model
{
    public $table = "sala";
    public $timestamps = false;
    protected $fillable = [
        'refe',
        'capacidade'
    ];

    public static function cadastrarSala($data){
        if(is_array($data)){
            $status = self::create($data);
            if($status){
                return true;
            }
            return false;
        }
    }

    public static function listarSalas(){
        return self::all();
    }

    public static function pegarSala($id_sala){
        return self::where('id', $id_sala)->first();
    }

    public static function verificaEspaco($id_sala){
        $capacidade = self::where('capacidade', $id_sala)
                            ->first()->capacidade;

        return ($capacidade > 0) ? true : false ;
        
     }
}
