<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoUsuario extends Model
{
    public $table = "tipo_usuario";
    public $timestamps = false;
    protected $fillable = [
        'nome_usuario',
    ];

    public static function listarTipos(){
        return self::get()->take(3);
    }

    public static function pegarTipoUsuario($id_tipo_usuario){
        return self::where('id', $id_tipo_usuario)->first();
    }

}
