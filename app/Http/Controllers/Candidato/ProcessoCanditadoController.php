<?php

namespace App\Http\Controllers\Candidato;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProcessoCandidato;
use App\Models\AnoLectivo;
use App\Models\Candidato;
use App\Models\Curso;
use App\Models\Inscricao;
use App\Models\Selecao;
use App\Models\Vaga;
use App\Utils\Auxiliar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;

class ProcessoCanditadoController extends Controller
{
    public function inscrever(ProcessoCandidato $request){

        if ((Auxiliar::diffYear($request->date_emissao_bi) >= 5)) {
            return redirect()->back()->withErrors('O bilhete de Identidade já expirou')
                ->withInput($request->all());
        }

        try {
            DB::beginTransaction();

            $ano_lectivo = AnoLectivo::retornaAnoActual()->id;
            $nome_imagem = Auxiliar::uploadImagem($request->file('foto'), 'storage/candidato/img');
            $idCandidato = Candidato::cadastrarCandidato($request->nome_cand,
                                                        $request->n_bi, $request->data_emissao_bi, $request->genero, $request->contacto, $request->contacto_encarregado, $request->endereco_electronico, $request->data_nascimento, $request->nome_mae, $request->nome_pai, $request->morada_actual, $nome_imagem);
            $pdf_bi = Auxiliar::uploadFicheiro($request->file('pdf_bi'), 'storage/candidato/pdfs');
            $pdf_certificado = Auxiliar::uploadFicheiro($request->file('pdf_certificado'), 'storage/candidato/pdfs');
            Inscricao::cadastrarInscricao($idCandidato, $request->media_final,
                                                        $request->mat_nota, $request->quim_nota, $request->fis_nota, $request->lingua_nota, $pdf_bi, $pdf_certificado, $request->curso1, $request->curso2, $request->curso3, $ano_lectivo);
            DB::commit();

            self::selecionarCandidatoAutomaticamente($request->data_nascimento, $request->media_final, $request->curso1, $request->curso2, $request->curso3, $idCandidato, $ano_lectivo);


            return redirect()->route('home.index')->with('success', "A sua inscrição foi feita com sucesso");
         } catch (\Throwable $e) {
             return ($e->getMessage());
             return redirect()->back()->withErrors("$e->getMessage()");
         }
    }

    public static function selecionarCandidatoAutomaticamente($data_nascimento, $media_final, $curso1, $curso2, $curso3, $idCandidato, $ano_lectivo){
        if (Auxiliar::diffYear($data_nascimento) <=14 AND $media_final >= 14) {
            DB::beginTransaction();

            if(Vaga::verificaVaga($curso1)){
                $idCurso = $curso1;
            }else if(Vaga::verificaVaga($curso2)){
                $idCurso = $curso2;
            }else if(Vaga::verificaVaga($curso3)){
                $idCurso = $curso3;
            }else{
                return;
            }
            Selecao::selecaoAutomatica($ano_lectivo, $idCandidato, $idCurso);
            Vaga::retiraUmaVaga($idCurso);

            DB::commit();
        }
    }

    public function pegarDadosBi($numero_bi)
    {
        $url = 'https://api.gov.ao/consultarBI/v2/?bi='.$numero_bi;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $dados = json_decode(curl_exec($ch), true);
        if (count($dados)) {
            $dados_bi = [
                'FIRST_NAME' => ucwords(mb_strtolower($dados[0]['FIRST_NAME'])),
                'LAST_NAME' => ucwords(mb_strtolower($dados[0]['LAST_NAME'])),
                'GENDER_NAME' => ucwords(mb_strtolower($dados[0]['GENDER_NAME'])),
                'BIRTH_DATE' => Auxiliar::formatData($dados[0]['BIRTH_DATE']),
                'FATHER_FIRST_NAME' => ucwords(mb_strtolower($dados[0]['FATHER_FIRST_NAME'])),
                'FATHER_LAST_NAME' => ucwords(mb_strtolower($dados[0]['FATHER_LAST_NAME'])),
                'MOTHER_FIRST_NAME' => ucwords(mb_strtolower($dados[0]['MOTHER_FIRST_NAME'])),
                'MOTHER_LAST_NAME' => ucwords(mb_strtolower($dados[0]['MOTHER_LAST_NAME'])),
                'BIRTH_PROVINCE_NAME' => ucwords(mb_strtolower($dados[0]['BIRTH_PROVINCE_NAME'])),
                'BIRTH_MUNICIPALITY_NAME' => ucwords(mb_strtolower($dados[0]['BIRTH_MUNICIPALITY_NAME'])),
                'ISSUE_DATE' => Auxiliar::formatData($dados[0]['ISSUE_DATE']),
                'RESIDENCE_COUNTRY_NAME' => ucwords(mb_strtolower($dados[0]['RESIDENCE_COUNTRY_NAME'])),
            ];
        }
        if (isset($dados_bi)) {
            return response()->json($dados_bi);
        }

        return response()->json([
            'error' => 500,
            'message' => 'BI não encontrado',
        ]);
    }
}
