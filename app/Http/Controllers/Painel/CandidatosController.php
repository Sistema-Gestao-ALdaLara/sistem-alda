<?php

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Controller;
use App\Mail\EmailCredenciais;
use App\Models\Aluno;
use App\Models\AnoLectivo;
use App\Models\AnoLectivoAluno;
use App\Models\Candidato;
use App\Models\Curso;
use App\Models\Inscricao;
use App\Models\NumeroProcesso;
use App\Models\Sala;
use App\Models\Selecao;
use App\Models\Turma;
use App\Models\User;
use App\Models\Usuario;
use App\Models\Vaga;
use App\Utils\Auxiliar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class CandidatosController extends Controller
{
    public function CandidatosPendentes(){
        $candidatos_pendentes = Candidato::listarCandidatosPendentes();

        return view('school.gerir_inscritos', compact('candidatos_pendentes'));
    }

    public function alunosMatriculados(){
        $matriculados = Aluno::listarMatriculados();

        return view('school.gerir-matriculados', compact('matriculados'));
    }

    public function CandidatosSelecionados(){
        $selecionados = Selecao::listarSelecionados();

        return view('school.gerir-selecionados', compact('selecionados'));
    }

    public function selecionarCandidato(Request $request){
        try {
            DB::beginTransaction();
            if(!Vaga::verificaVaga($request->curso_id)){
                return redirect()->route('candidato.inscritos')->withErrors("Não há vaga para o curso <strong>".
                        Curso::pegarCurso($request->curso_id)->nome. "</strong>. Por favor, selecione o aluno em outro curso desejado.");
            }

            Selecao::selecionarCandidato(session('id_ano_lectivo'), $request->candidato_id, $request->usuario_id, $request->curso_id);

            Vaga::retiraUmaVaga($request->curso_id);
            DB::commit();

            return redirect()->route('candidato.inscritos')->with('success', "Candidato <strong>".
                    Candidato::pegarCandidato($request->candidato_id)->nome_cand. "</strong> Seleccionado com sucesso.");
        } catch (\Throwable $e) {
            return $e->getMessage();
        }
    }


    public static function matricularCandidato($candidato_id){
        try {
            DB::beginTransaction();
            $candidato = Candidato::pegarCandidato($candidato_id);
            $senha = Auxiliar::gerarSenha($candidato->nome_cand, $candidato->data_nascimento);
            
            $user_id = Usuario::cadastrarUsuario($candidato->nome_cand, $candidato->endereco_electronico, $senha, 4);
            $numero_processo = Aluno::ultimoNumeroProcesso();
            if($numero_processo == null){
                $numero_processo = NumeroProcesso::retornaNumeroProcesso();
            }
            ++$numero_processo;
            Aluno::cadastrarAluno($candidato_id, session('id_usuario'), $user_id, $numero_processo);

            $usuario = User::findOrFail($user_id);
            Mail::to($usuario->email)->send(new EmailCredenciais($usuario, $senha));

            DB::commit();

            return redirect()->route('candidato.selecionados')->with('success', "Aluno <strong>".
                Candidato::pegarCandidato($candidato_id)->nome_cand. "</strong> Matriculado com sucesso.");            

        } catch (\Throwable $e) {
            return ($e->getMessage());
        }
    }

    public static function definirTurmaDoAluno(Request $request){
        try {
            DB::beginTransaction();
            $turma = Turma::pegarTurma($request->turma_id);
            $sala = Sala::pegarSala($turma->sala_id);
            $numero_de_alunos = count(AnoLectivoAluno::listarAlunosDaTurma($request->turma_id));

            if($sala->capacidade - $numero_de_alunos == 0){
                return redirect()->route('aluno.matriculados')->withErrors("A turma <strong>".$turma->turma."</strong> já está cheia seu olho do no dinheiro."); 
            }

            AnoLectivoAluno::cadastrarAluno($request->aluno_id, 1, $request->turma_id, 1, null, session('id_ano_lectivo'));

            AnoLectivoAluno::actualizarListaDeAlunosDaTurma($request->turma_id);

            DB::commit();

            return redirect()->route('aluno.matriculados')->with('success', "Aluno <strong>".
                Candidato::pegarCandidato($request->candidato_id)->nome_cand. "</strong> foi adicionado na turma <strong>".Turma::pegarTurma($request->turma_id)->turma."</strong> com sucesso."); 
        } catch (\Throwable $e) {
            dd($e->getMessage());
        }
    }

    public static function eliminarInscrito($candidato_id){
        try{
            DB::beginTransaction();

            $candidato = Candidato::pegarCandidato($candidato_id);

            unlink('storage/candidato/pdfs/'.$candidato->upload_bi);
            unlink('storage/candidato/pdfs/'.$candidato->upload_cert);
            unlink('storage/candidato/img/'.$candidato->foto);

            Candidato::eliminarCandidatoPendente($candidato_id);

            DB::commit();

            return redirect()->back()->with('success', "Candidato eliminado com sucesso");

        } catch(\Throwable $e){
            return redirect()->back()->withErrors("Aconteceu um erro ao eliminar o candidato.");
        }
    }

    public static function removerSelecionado($candidato_id){
        if(Selecao::eliminarSelecao($candidato_id)){
            return redirect()->back()->with('success', "Candidato removido da lista dos selecionados com sucesso");
        }

        return redirect()->back()->withErrors("Aconteceu um erro ao remover o candidato.");
    }
}
