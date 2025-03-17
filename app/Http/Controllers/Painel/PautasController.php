<?php

namespace App\Http\Controllers\Painel;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\InserirActualizarNota;
use App\Models\Aluno;
use App\Models\AnoLectivoAluno;
use App\Models\Disciplina;
use App\Models\DisciplinaCurso;
use App\Models\MediaTrimestral;
use App\Models\NotaFinalDisciplina;
use App\Models\Pauta;
use App\Models\ProvasTrimestre;
use App\Models\Turma;
use Barryvdh\DomPDF\Facade\Pdf as FacadePdf;
use Barryvdh\DomPDF\PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Validator;

class PautasController extends Controller
{
    public function turma($turma_id, $disciplina_id = null){
        $disciplina = null;
        if($disciplina_id != null)
            $disciplina = Disciplina::pegarDisciplina($disciplina_id);
        $turma = Turma::pegarTurma($turma_id);
        $alunos = Aluno::listarAlunosdaTurma($turma_id);

        self::mudarTrimestre();
        return view('school.lista_alunos_turma', compact('turma', 'disciplina', 'alunos'));
    }

    public function adicionarNotas(InserirActualizarNota $request){
        try {    
            $mac = round(str_replace(',', '.', $request->mac));
            $npp = round(str_replace(',', '.', $request->npp));
            $npt = round(str_replace(',', '.', $request->npt));

            $media = round(($mac + $npp + $npt) / 3);

            $aluno = Aluno::pegarAluno($request->aluno_id);

            if (ProvasTrimestre::verificaNotas(session('trimestre_id'), $request->aluno_id, $request->disciplina_id)){
                ProvasTrimestre::actualizarNotas(session('trimestre_id'), $mac, $npp, $npt, $media, $request->aluno_id, $request->disciplina_id);
            }else{
                ProvasTrimestre::inserirNotas($mac, $npp, $npt, $media, $request->aluno_id, $request->disciplina_id);
            }

            return \redirect()->back()->with('success', "Notas do aluno <b>".$aluno->nome_cand."</b> adicionadas com sucesso");
                
        } catch (\Throwable $e) {
            dd($e->getMessage());
            return \redirect()->back()->withInput($request->all())->withErrors("Aconteceu um erro ao adicionar N
            notas do aluno <b>".$aluno->nome_cand."</b>. Se o erro pesistir contacte um assistente técnico.");
        }

    }

    public static function mudarTrimestre()
    {
        $dados = DB::table('pauta')
        ->where('ano_lectivo', '=', session('id_ano_lectivo'))
        ->orderByDesc('date_create')
        ->first();

        if (is_null($dados)) {
            session()->forget('fim_ano_lectivo');
            session()->put('nome_trimestre', 'I º');
            session()->put('trimestre_id', 1);
        } else if ($dados->trimestre_id == 1) {
            session()->forget('fim_ano_lectivo');
            session()->put('nome_trimestre', 'II º');
            session()->put('trimestre_id', 2);
        } else if ($dados->trimestre_id == 2) {
            session()->forget('fim_ano_lectivo');
            session()->put('nome_trimestre', 'III º');
            session()->put('trimestre_id', 3);
        } else if ($dados->trimestre_id == 3) {
            session()->put('trimestre_id', 3);
            session()->put('fim_ano_lectivo', true);
        }
    }

    public function trimestre(){
        self::mudarTrimestre();

        return view('school.trimestre');
    }


    public function fecharTrimestre(){
        try{
            DB::beginTransaction();
            $turmas = Turma::listarTurmas();
            $validacao_notas = [];
            foreach ($turmas as $turma) {
                if (count(AnoLectivoAluno::listarAlunosDaTurma($turma->id)) > 0) {
                    Pauta::cadastrarPauta(session('trimestre_id'), $turma->id);
                }
            }

            if(session('trimestre_id') == 3){
                foreach ($turmas as $turma) {
                    if (count(AnoLectivoAluno::listarAlunosDaTurma($turma->id)) > 0) {
                        $dados_turma = Turma::pegarTurma($turma->id);
                        $alunos = AnoLectivoAluno::listarAlunosDaTurma($turma->id);
                        $disciplinas = DisciplinaCurso::disciplinasClasseCurso($dados_turma->curso_id, $dados_turma->classe_id);

                        foreach ($alunos as $aluno) {
                            foreach ($disciplinas as $disciplina) {

                                $mt1 = 0;
                                $mt2 = 0;
                                $mt3 = 0;
                                if ($media1 = ProvasTrimestre::buscarNotas(1, $aluno->aluno_id, $disciplina->disciplina_id)->mt ?? 0) {
                                    $mt1 = round(str_replace(',', '.', $media1));
                                }
                                if ($media2 = ProvasTrimestre::buscarNotas(2, $aluno->aluno_id, $disciplina->disciplina_id)->mt ?? 0) {
                                    $mt2 = round(str_replace(',', '.', $media2));
                                }
                                if ($media3 = ProvasTrimestre::buscarNotas(3, $aluno->aluno_id, $disciplina->disciplina_id)->mt ?? 0){
                                    $mt3 = round(str_replace(',', '.', $media3));
                                }

                                if($dados_turma->classe_id == 3){
                                    $media_final = round((0.6 * ($mt1 + $mt2)) +  (0.4 * $mt3));
                                }else{
                                    $media_final = round(($mt1 + $mt2 + $mt3) / 3);
                                }

                                if(MediaTrimestral::verificaNotas($aluno->aluno_id, $disciplina->disciplina_id)){
                                    MediaTrimestral::actualizarMedias($aluno->aluno_id, $disciplina->disciplina_id, $mt1, $mt2, $mt3, $media_final);
                                }else{
                                    MediaTrimestral::inserirMediasTrimestrais($aluno->aluno_id, $disciplina->disciplina_id, $mt1, $mt2, $mt3, $media_final);
                                }
                            }
                        }



                    }
                }

                foreach ($alunos as $aluno) {
                    foreach ($disciplinas as $disciplina) {
                        $media_final = MediaTrimestral::retornaNotaFinal($aluno->aluno_id, $disciplina->disciplina_id);
        
                        if ($media_final >= 10) {
                            array_push($validacao_notas, ["nota"=>1, "aluno_id"=>$aluno->aluno_id, "disciplina_id"=>$disciplina->disciplina_id, "classe_id"=>$turma->classe_id, "disciplina_recurso"=>DisciplinaCurso::disciplinaDaClasseCurso($turma->classe_id, $turma->curso_id, $disciplina->disciplina_id)->disciplina_recurso]);
                        } else {
                            array_push($validacao_notas, ["nota"=>0, "aluno_id"=>$aluno->aluno_id, "disciplina_id"=>$disciplina->disciplina_id, "classe_id"=>$turma->classe_id, "disciplina_recurso"=>DisciplinaCurso::disciplinaDaClasseCurso($turma->classe_id, $turma->curso_id, $disciplina->disciplina_id)->disciplina_recurso]);
                        }
                    }
                    $notas = \collect($validacao_notas);
                    
                    self::statusFimAno($notas, $aluno->aluno_id, $turma->classe_id);
                }

            }

            DB::commit();

            return redirect()->back()->with('success', "Trimestre Fechado.");
        } catch(\Throwable $e){
            return $e->getMessage();
            return \redirect()->back()->withErrors("Aconteceu um erro ao Fechar o trimestre. Se o erro pesistir contacte um assistente técnico.");
        }
    }

    public function voltarTrimestre(){
        DB::table('pauta')
            ->where('ano_lectivo', session('id_ano_lectivo'))->delete();
        
        return redirect()->back()->with('success', "A Escola está agora no Iº Trimestre");
    }

    public static function statusFimAno($notas, $aluno_id, $classe_id)
    {
        $max_recurso = 5;
        $max_sem_recurso = 3;
        if (MediaTrimestral::verificaSeDesistiu($aluno_id)) {
            return AnoLectivoAluno::inserirStatusFimAno($aluno_id, 5);
        }
        
        $disciplinas_recurso = $notas->where('aluno_id', '=', $aluno_id)
                                ->where('disciplina_recurso', 1)
                                ->where('nota', 0)->count();

        $disciplinas_sem_recurso = $notas->where('aluno_id', '=', $aluno_id)
                                ->where('disciplina_recurso', 0)
                                ->where('nota', 0)->count();

        if ($disciplinas_sem_recurso > $max_sem_recurso OR $disciplinas_recurso > $max_recurso) {
            return AnoLectivoAluno::inserirStatusFimAno($aluno_id, 4);
        }

        if ($disciplinas_recurso > 0) {
            return AnoLectivoAluno::inserirStatusFimAno($aluno_id, 7);
        }

        NotaFinalDisciplina::inserirNotasFinais($aluno_id, $classe_id);

        return AnoLectivoAluno::inserirStatusFimAno($aluno_id, 3);
    }


    public function gerarPautaTrimestral($turma_id)
    {   
        $turma = Turma::pegarTurma($turma_id);
        $alunos = AnoLectivoAluno::listarAlunosDaTurma($turma_id);

        $disciplinas = DisciplinaCurso::disciplinasClasseCurso($turma->curso_id, $turma->classe_id);

        //return FacadePdf::loadView('school.prints.pauta_trimestral', compact('turma', 'disciplinas', 'alunos', 'turma_id'))->setPaper('a3', 'landscape')->stream();

        return view('school.prints.pauta_trimestral', compact('turma', 'disciplinas', 'alunos', 'turma_id'));

    }

    public function gerarMiniPautaSingular($turma_id, $disciplina_id)
    {   
        $turma = Turma::pegarTurma($turma_id);
        $alunos = AnoLectivoAluno::listarAlunosDaTurma($turma_id);

        $disciplina = DisciplinaCurso::disciplinaDaClasseCurso($turma->classe_id, $turma->curso_id, $disciplina_id);

        return view('school.prints.mini_pauta', compact('turma', 'disciplina_id', 'disciplina', 'alunos', 'turma_id'));

    }

    public function gerarMiniPautas($turma_id)
    {   
        $turma = Turma::pegarTurma($turma_id);
        $alunos = AnoLectivoAluno::listarAlunosDaTurma($turma_id);

        $disciplinas = DisciplinaCurso::disciplinasClasseCurso($turma->curso_id, $turma->classe_id);

        return view('school.prints.mini-pautas', compact('turma', 'disciplinas', 'alunos', 'turma_id'));

    }

    public function gerarPautaFinal($turma_id)
    {   
        $turma = Turma::pegarTurma($turma_id);
        $alunos = AnoLectivoAluno::listarAlunosDaTurma($turma_id);

        $disciplinas = DisciplinaCurso::disciplinasClasseCurso($turma->curso_id, $turma->classe_id);

        return view('school.prints.pauta_final', compact('turma', 'disciplinas', 'alunos', 'turma_id'));

    }
}
