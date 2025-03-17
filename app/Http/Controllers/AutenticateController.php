<?php

namespace App\Http\Controllers;

use App\Models\Aluno;
use App\Models\AnoLectivoAluno;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AutenticateController extends Controller
{
    public function index(){
        return view('index');
    }

    public function entrar(Request $request){

        $request->validate([
            'email'=>'required|email|max:255',
            'senha' => 'required'
        ],[
            'email.required' => 'Insira o seu email',
            'email.email' => 'Insira um email válido',
            'senha.required'=> 'Insira a sua senha'
        ]);
        $vars = [
            'email'=> $request->email,
            'password'=> $request->senha
        ]; 

        if(Auth::attempt($vars)){
            session()->put('tipo_usuario',Auth::user()->tipo_usuario);

            $user = DB::table('users')->where('email', $request->email)->first();

            $year = DB::table('ano_lectivo')->orderByDesc('id')->first();
            session()->put('email', $user->email);
            session()->put('nome_usuario', $user->nome);
            session()->put('id_usuario', $user->id);
            session()->put('tipo_usuario', $user->tipo_usuario);
            session()->put('ano_em_curso', $year->nome_ano);
            session()->put('id_ano_lectivo', $year->id);

            if($user->tipo_usuario == 4){
                $aluno = Aluno::pegarUsuarioAluno($user->email);
                session()->put('aluno_id', $aluno->aluno_id);
                session()->put('turma_id', $aluno->turma_id);
            }

            return redirect()->route('home.painel');    
        }

        return redirect()->back()->withInput($request->all())->withErrors(['Email ou Senha Incorrectos']);
    }

    public function logout(Request $request){
        
        session()->flush();

        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();
    
        return redirect('/login');
    }
}
