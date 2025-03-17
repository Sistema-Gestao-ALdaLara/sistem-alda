<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class Usuario extends Model
{
    public $table = "users";
    public $timestamps = false;
    protected $fillable = [
        'nome',
        'email',
        'tipo_usuario',
        'password',
    ];

    public static function cadastrarUsuario($nome, $email, $senha, $tipo_usuario){
        $usuario = Usuario::where('email', $email)->first();
        if (is_object($usuario)) {
            return false;
        }
        return self::insertGetId([
                        'nome' => $nome,
                        'email' => $email,
                        'password' => Hash::make($senha),
                        'tipo_usuario' => $tipo_usuario,
                    ]);
    }

    public static function actualizarUsuario($nome, $email, $senha, $id_usuario){
        return self::where('id', $id_usuario)
                    ->update([
                        'nome' => $nome,
                        'email' => $email,
                        'senha' => Hash::make($senha)
                    ]);
    }

    public static function listarUsuarios(){
        return self::all();
    }

    public static function pegarUsuarioPorId($id_usuario){
        return self::where('id', $id_usuario)->first();
    }

    public static function pegarUsuario($campo, $valor){
        return self::where($campo, $valor)->first();
    }

    public static function eliminarUsuario($id_usuario){
        return self::where('id', $id_usuario)->delete();
    }

    public static function retornaNomeUsuario($id_usuario){
        return self::where('id', $id_usuario)->select('nome')->first()->nome ?? null;
    }

}
