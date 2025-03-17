<?php

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfessorController extends Controller
{
    public function index(){
        return view('school.lista_turmas_professor');
    }

    public function listaAlunos($id_turma = null){
        return view('school.lista_alunos_turma');
    }
}
