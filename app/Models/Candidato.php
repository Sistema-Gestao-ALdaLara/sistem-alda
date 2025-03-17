<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

use function PHPUnit\Framework\isEmpty;
use function PHPUnit\Framework\isType;

class Candidato extends Model
{
    public $table = "candidato";
    public $timestamps = false;
    protected $fillable = [
        'nome_cand',
        'n_bi',
        'data_emissao_bi',
        'genero',
        'contacto',
        'contacto_encarregado',
        'endereco_electronico',
        'data_nascimento',
        'nome_mae',
        'nome_pai',
        'morada_actual',
        'foto'
    ];

    public static function cadastrarCandidato($nome, $bi, $data_emissao_bi, $genero, $contacto, $contacto_encarregado, $email, $data_nascimento, $nome_mae, $nome_pai, $morada, $foto){
        
        return self::insertGetId([
                    'nome_cand' => $nome,
                    'n_bi' => $bi,
                    'data_emissao_bi' => $data_emissao_bi,
                    'genero' => $genero,
                    'contacto' => $contacto,
                    'contacto_encarregado' => $contacto_encarregado,
                    'endereco_electronico' => $email,
                    'data_nascimento' => $data_nascimento,
                    'nome_mae' => $nome_mae,
                    'nome_pai' => $nome_pai,
                    'morada_actual' => $morada,
                    'foto' => $foto
                ]);

    }

    public static function listarCandidatosPendentes(){
        if(count(Selecao::listarSelecionados()) > 0){
            return self::join('inscricao', 'candidato.id', '=', 'inscricao.candidato_id')
                    ->join('selecao', 'candidato.id', '<>', 'selecao.id_candidato')
                    ->where('inscricao.ano_lectivo', session('id_ano_lectivo'))
                    ->whereNotIn('candidato.id', Selecao::idCandidatosSelecionados())
                    ->select('candidato.*', 'inscricao.*')
                    ->distinct('inscricao.candidato_id')->get();
        }

        return self::join('inscricao', 'candidato.id', '=', 'inscricao.candidato_id')
                    ->where('inscricao.ano_lectivo', session('id_ano_lectivo'))
                    ->get();
    }

    public static function pegarCandidato($candidato_id){
        return self::join('inscricao', 'candidato.id', 'inscricao.candidato_id')
                    ->where('candidato.id', $candidato_id)->first();
    }

    public static function pegarCandidatoPor($campo, $valor){
        return self::where($campo, $valor)->first();
    }

    public static function eliminarCandidatoPendente($candidato_id){
        try {
            DB::beginTransaction();

            Inscricao::eliminarInscricao($candidato_id);
            self::where('id', $candidato_id)->delete();

            DB::commit();

            return true;
        } catch (\Throwable $e) {
            return $e->getMessage();
        }
    }
}
