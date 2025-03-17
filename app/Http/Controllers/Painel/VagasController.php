<?php

namespace App\Http\Controllers\Painel;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Curso;
use App\Models\Vaga;
use Illuminate\Http\Request;
use Validator;

class VagasController extends Controller
{
    public function index(){
        $cursos = Curso::listarCursos();
        $vagas = Vaga::listarVagas();
        $total_vagas = Vaga::contaTotalVagas();

        return view('school.vagas', compact('cursos', 'vagas', 'total_vagas'));
    }

    public function definirVaga(Request $request){
        $request->validate([
            'numero_vagas' => 'required',
            'curso_id' => 'required',
        ], [
            'numero_vagas.required' => 'Insira o número de vagas.',
            'numero_vagas.int' => 'O número de vagas deve ser número inteiro',
            'curso_id.required' => 'Escolha um curso.',
        ]);

        $status = Vaga::cadastrarVaga($request->except('_token'));

        if($status){
            return redirect()->back()->with('success', "Vaga definida com sucesso");
        }

        return redirect()->back()->withErrors("Aconteceu um erro ao definir vaga.");
    }

    public function actualizarVaga(Request $request){
        $request->validate([
            'numero_vagas' => 'required|integer',
        ], [
            'numero_vagas.required' => 'Insira o número de vagas',
            'numero_vagas.integer' => 'O Número de vagas deve ser um número inteiro',
        ]);
        
         
        $status = Vaga::editarVaga($request->curso_id, $request->numero_vagas);

        if($status){
            return redirect()->back()->with('success', "Número de Vagas actualizada com sucesso");
        }

        return redirect()->back()->withErrors("Aconteceu um erro ao actualizar o número de vagas.");
    }
}
