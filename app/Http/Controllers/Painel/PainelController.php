<?php

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Painel\PautasController;
use App\Models\Coordenador;
use App\Models\Pauta;
use App\Models\ProfessorDisciplina;
use App\Models\Turma;
use Illuminate\Http\Request;

class PainelController extends Controller
{
    public function index(){

        //dd(Coordenador::pegarCoordenador(2));
        PautasController::mudarTrimestre();
        //dd(ProfessorDisciplina::listarTurmas(2));
        return view('school.index');
    }

}
