<?php

namespace App\Http\Controllers\Painel;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Coordenador;
use App\Models\Curso;
use App\Models\Funcionario;
use Validator;
use Illuminate\Http\Request;

class CursosController extends Controller
{
    public function index(){
        $professores = Funcionario::listarProfessores();
        $cursos = Curso::listarCursos();

        return view('school.cursos', compact('cursos', 'professores'));
    }

    public function adicionarCurso(Request $request){

        $regas = [
            'nome' => 'required',
            'sigla' => 'required',
            'descricao' => '',
            //'foto' => 'file',
        ];

        $mensagens = [
            'nome.required' => 'Preencha o campo Nome',
            'sigla.required' => 'Preencha o campo Sigla',
        ];


        $validator = Validator::make($request->except('_token'), $regas, $mensagens);

        if ($validator->fails()) {
            $message = '';
            $mensagens_l = json_decode(json_encode($validator->messages()), true);
            foreach ($mensagens_l as $msg) {
                $message .= $msg[0].'<br>';
            }
            return Helper::returnApi($message, 500);
         }
         
         $status = Curso::addCurso($request->except('_token'));
         if($status){
            return Helper::returnApi("Curso adicionado com sucesso", 200);
         }
    }

    public function adicionarCoordenador(Request $request){

        $regas = [
            'curso_id' => 'required',
            'usuario_id' => 'required',
        ];

        $mensagens = [
            'curso_id.required' => 'Escolha o Curso',
            'usuario_id.required' => 'Escolha o Corrdenador',
        ];

        $validator = Validator::make($request->except('_token'), $regas, $mensagens);

        if ($validator->fails()) {
            $message = '';
            $mensagens_l = json_decode(json_encode($validator->messages()), true);
            foreach ($mensagens_l as $msg) {
                $message .= $msg[0].'<br>';
            }
            return Helper::returnApi($message, 500);
         }
         
         $status = Coordenador::cadastrarCoordenador($request->curso_id, $request->usuario_id);
         if($status){
            return Helper::returnApi("Coordenador adicionado com sucesso", 200);
         }
    }
}
