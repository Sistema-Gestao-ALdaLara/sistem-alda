<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coordenador extends Model
{
    public $table = "coordenador";
    public $timestamps = false;
    protected $fillable = [
        'curso_id',
        'usuario_id'
    ];

    public static function cadastrarCoordenador($idCurso, $idUsuario){
            return  self::insert([
                        'curso_id' => $idCurso,
                        'usuario_id' => $idUsuario
                    ]);
    }

    public static function pegarCoordenador($usuario_id){
        return self::join('users', 'coordenador.usuario_id', 'users.id')
                    ->join('curso', 'coordenador.curso_id', 'curso.id')
                    ->where('coordenador.usuario_id', $usuario_id)->first();
    }

    public static function pegarCoordenadorDeCurso($curso_id){
        return self::join('users', 'coordenador.usuario_id', 'users.id')
                    ->join('curso', 'coordenador.curso_id', 'curso.id')
                    ->select('users.nome')
                    ->where('coordenador.curso_id', $curso_id)->first();
    }
}
