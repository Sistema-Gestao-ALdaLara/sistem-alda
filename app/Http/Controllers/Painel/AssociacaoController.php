<?php

namespace App\Http\Controllers\Painel;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Classe;
use App\Models\Curso;
use App\Models\Disciplina;
use App\Models\DisciplinaCurso;
use App\Models\Funcionario;
use App\Models\ProfessorDisciplina;
use App\Models\Turma;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Validator;

class AssociacaoController extends Controller
{
    public function professorDisciplina(){
        $professores = Funcionario::listarProfessores();
        $disciplinas = Disciplina::listarDisciplinas();
        $turmas = Turma::listarTurmas();
        $associacoes = ProfessorDisciplina::listarAssociacoes();

        return view('school.professor-disciplina', compact('professores', 'disciplinas', 'turmas', 'associacoes'));
    }

    public function disciplinaCurso(){

        $cursos = Curso::listarCursos();
        $disciplinas = Disciplina::listarDisciplinas();
        $classes = Classe::listarClasses();
        $associacoes = DisciplinaCurso::listarAssociacoes();

        return view('school.disciplina-curso', compact('cursos', 'disciplinas', 'classes', 'associacoes'));
    }

    public function adicionarDisciplinaCurso(Request $request){
        $regas = [
            'curso_id' => 'required',
            'classe_id' => 'required',
            'disciplina_id' => 'required',
        ];

        $mensagens = [
            'curso_id.required' => 'Escolha um curso',
            'classe_id.required' => 'Escolha uma classe',
            'disciplina_id.required' => 'Escolha uma disciplina',
        ];

        $validacao = Validator::make($request->except('_token'), $regas, $mensagens);

        if ($validacao->fails()) {
            $mensagem = '';
            $mensagens_l = json_decode(json_encode($validacao->messages()), true);
            foreach ($mensagens_l as $msg) {
                $mensagem .= $msg[0].'<br>';
            }
            return Helper::returnApi($mensagem, 500);
         }
         
         $status = DisciplinaCurso::cadastrarCursoDisciplina($request->except('_token'));
         if($status){
            return Helper::returnApi("Associação concluída com sucesso", 200);
         }
    }

    public function adicionarProfDisciplina(Request $request){
        $regas = [
            'usuario_id' => 'required',
            'disciplina_id' => 'required',
            'turma_id' => 'required',
        ];

        $mensagens = [
            'usuario_id.required' => 'Selecione um professor',
            'disciplina_id.required' => 'Escolha uma disciplina',
            'turma_id.required' => 'Escolha uma turma',
        ];

        $validacao = Validator::make($request->except('_token'), $regas, $mensagens);

        if ($validacao->fails()) {
            $mensagem = '';
            $mensagens_l = json_decode(json_encode($validacao->messages()), true);
            foreach ($mensagens_l as $msg) {
                $mensagem .= $msg[0].'<br>';
            }
            return Helper::returnApi($mensagem, 500);
         }
         
         $status = ProfessorDisciplina::cadastrarProfDisciplina($request->usuario_id, $request->disciplina_id, $request->turma_id);
         if($status){
            return Helper::returnApi("Associação concluída com sucesso", 200);
         }
    }
}
