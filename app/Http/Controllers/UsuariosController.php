<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;

class UsuariosController extends Controller
{
    public function index(){
        //return view('school.gerir_inscritos');
    }

    public function adicionarUsuario(Request $request){
        
        $regras = [
            'nome' => 'required',
            'email'=>'required|email|max:255',
            'senha' => 'required|min:4',
        ];
        $mensagens = [
            'nome.required' => 'Insira o nome',
            'email.required' => 'Insira o email',
            'email.email' => 'Insira um email valido',
            'senha.required'=> 'Insira a senha',
            'senha.min:4'=> 'A senha tem de ter no mínimo 8 caracteres',
        ];

        //Helper::errorApis($request->except('_token'),$regras,$mensagens);

        return Usuario::addUser($request->nome, $request->email, $request->senha, $request->tipo_usuario);
        
    }

    public function editarUsuario($idUsuario, Request $request){
        
        $regras = [
            'email'=>'required|email|max:255',
            'senha' => 'required|min:8',
        ];
        $mensagens = [
            'email.required' => 'Informe o seu email',
            'email.email' => 'Informe um email valido',
            'senha.required'=> 'Informe a sua senha',
            'senha.min:8'=> 'A senha tem de ter no mínimo 8 caracteres',
        ];

        Helper::errorApis($request->except('_token'),$regras,$mensagens);

        return Usuario::updateUser($request->nome, $request->email, $request->senha, $idUsuario);
        
    }

    public function listarTodosUsuarios(){
        $usuarios = json_encode(Usuario::getAllUsers());

        return $usuarios;
    }

    public function pegarUsuarioPorId($idUsuario){
        $usuario = json_encode(Usuario::getUserById($idUsuario));

        return $usuario;
    }

    public function pegarUsuario($campo, $valor){
        $usuario = json_encode(Usuario::getUser("{$campo}", $valor));

        return $usuario;
    }

    public function eliminarUsuario($idUsuario){
        Usuario::deleteUser($idUsuario);

        return response('success', 200);
    }
}
