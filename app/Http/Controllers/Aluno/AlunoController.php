<?php

namespace App\Http\Controllers\Aluno;

use App\Http\Controllers\Controller;
use App\Models\Aluno;
use App\Models\DisciplinaCurso;
use App\Models\Turma;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class AlunoController extends Controller
{
    public function index(){
        return view('school.classes_aluno');
    }

    public function boletimDeNotas($aluno_id, $trimestre_id){
        $aluno = Aluno::pegarAluno($aluno_id);
        $dados_turma = Turma::pegarTurma($aluno->turma_id);
        $disciplinas = DisciplinaCurso::disciplinasClasseCurso($dados_turma->curso_id, $dados_turma->classe_id);

        $contagem = 0;

        return Pdf::loadView('students.boletim', compact('aluno', 'trimestre_id', 'dados_turma', 'disciplinas', 'contagem'))->setPaper([0, 0, 600, 600], 'landscape')->stream();
    }

    public function relatorioDeNotas($aluno_id, $trimestre_id){
        $aluno = Aluno::pegarAluno($aluno_id);
        $dados_turma = Turma::pegarTurma($aluno->turma_id);
        $disciplinas = DisciplinaCurso::disciplinasClasseCurso($dados_turma->curso_id, $dados_turma->classe_id);

        $contagem = 0;

        return Pdf::loadView('students.relatorio-notas', compact('aluno', 'trimestre_id', 'dados_turma', 'disciplinas', 'contagem'))->setPaper([0, 0, 600, 600], 'landscape')->stream();
    }
}
