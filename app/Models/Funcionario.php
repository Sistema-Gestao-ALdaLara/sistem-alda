<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Funcionario extends Model
{
    public $table = "dado_usuario";
    public $timestamps = false;
    protected $fillable = [
        'numero_bi',
        'nome_usuario',
        'telefone',
        'foto',
        'sexo',
        'usuario_id',
        'data_nascimento',
        'endereco',
        'nacionalidade',
        'provincia',
    ];

    public static function cadastrarFuncionario($bi, $nome, $telefone, $foto, $sexo, $idUsuario, $data_nascimento, $endereco, $nacionalidade, $provincia){
       return self::insert([
                    'numero_bi' => $bi,
                    'nome_usuario' => $nome,
                    'telefone' => $telefone,
                    'foto' => $foto,
                    'sexo' => $sexo,
                    'usuario_id' => $idUsuario,
                    'data_nascimento' => $data_nascimento,
                    'endereco' => $endereco,
                    'nacionalidade' => $nacionalidade,
                    'provincia' => $provincia,
                ]);
    }

    public static function actualizarFuncionario($bi, $nome, $telefone, $foto, $sexo, $idUsuario, $data_nascimento, $endereco, $nacionalidade, $provincia){
        return self::insert([
                     'numero_bi' => $bi,
                     'nome_usuario' => $nome,
                     'telefone' => $telefone,
                     'foto' => $foto,
                     'sexo' => $sexo,
                     'usuario_id' => $idUsuario,
                     'data_nascimento' => $data_nascimento,
                     'endereco' => $endereco,
                     'nacionalidade' => $nacionalidade,
                     'provincia' => $provincia,
                 ]);
     }

    public static function listarFuncionarios(){
        return self::join('users', 'dado_usuario.usuario_id', 'users.id')->get();
    }

    public static function pegarFuncionario($id_usuario){
        return self::join('users', 'dado_usuario.usuario_id', 'users.id')
                    ->where('usuario_id', $id_usuario)->first();
    }

    public static function listarProfessores(){
        return self::join('users', 'dado_usuario.usuario_id', 'users.id')
                    ->where('tipo_usuario', 3)->get();
    }

    public static function retornaCoordenador($idCurso){
        return self::join('users', 'dado_usuario.usuario_id', 'users.id')
                    ->join('coordenador', 'dado_usuario.usuario_id', 'coordenador.usuario_id')
                    ->join('curso', 'coordenador.curso_id', 'curso.id')
                    ->where('curso.id', $idCurso)
                    ->select('dado_usuario.*', 'users.*')->first();
    }
}
