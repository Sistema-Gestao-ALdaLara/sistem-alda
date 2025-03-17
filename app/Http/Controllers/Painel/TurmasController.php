<?php

namespace App\Http\Controllers\Painel;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Classe;
use App\Models\Curso;
use App\Models\Periodo;
use App\Models\Turma;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TurmasController extends Controller
{
    public function index(){

        $turmas = Turma::listarTurmas();

        return view('school.turmas', compact('turmas'));
    }

    public function adicionarTurma(Request $request){

        $regas = [
            'curso_id' => 'required',
            'sala_id' => 'required',
            'turma' => 'required',
            'classe_id' => 'required',
            'periodo_id' => 'required',
        ];

        $mensagens = [
            'turma.required' => 'Preencha o campo Nome',
            'classe_id.required' => 'Preencha o campo Sigla',
        ];

        /*if(is_object(Turma::getCurso('nome', $request->nome))){
            array_push($mensagens, 'Já existe um curso com este nome');
            return Helper::returnApi($mensagens, 500);
        }*/

        $validator = Validator::make($request->except('_token'), $regas, $mensagens);

        if ($validator->fails()) {
            $message = '';
            $mensagens_l = json_decode(json_encode($validator->messages()), true);
            foreach ($mensagens_l as $msg) {
                $message .= $msg[0].'<br>';
            }
            return Helper::returnApi($message, 500);
         }
         
        $turma = Curso::pegarCurso($request->curso_id)->sigla.
                 Classe::retornaNumeroClasse($request->classe_id).$request->turma.Periodo::retornaAbreviacao($request->periodo_id);
        
        $status = Turma::cadastrarTurma($request->ano_lectivo, $request->sala_id, $request->curso_id, $request->classe_id, $request->periodo_id, $turma);
        if($status){
            return Helper::returnApi("Turma adicionada com sucesso", 200);
        }
    }
}
