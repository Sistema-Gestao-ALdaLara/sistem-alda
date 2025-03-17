<?php

namespace App\Http\Controllers\Painel;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Disciplina;
use Illuminate\Http\Request;
use Validator;

class DisciplinaController extends Controller
{
    public function index(){
        $disciplinas = Disciplina::listarDisciplinas();

        return view('school.disciplinas', compact('disciplinas'));
    }

    public function adicionarDisciplina(Request $request){

        $request->validate([[
            'nome_disciplina' => 'required',
        ],[
            'nome_disciplina.required' => 'Preencha o campo Nome',
        ]]);
        
        $status = Disciplina::cadastrarDisciplina($request->except('_token'));
        if($status){
            return redirect()->back()->with('sucess', "Disciplina adicionada com sucesso");
        }
    }
}
