<?php

namespace App\Models;

use App\Utils\Auxiliar;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Selecao extends Model
{
    public $table = "selecao";
    public $timestamps = false;
    protected $fillable = [
        'ano_lectivo',
        'id_candidato',
        'usuario_id',
        'curso_id'
    ];

    public static function selecaoAutomatica($ano_lectivo, $candidato_id, $curso_id){
        return self::insert(['ano_lectivo' => $ano_lectivo,
                            'id_candidato' => $candidato_id,
                            'curso_id' => $curso_id]);
    }

    public static function listarSelecionados(){
        if(count(Aluno::idAlunosMatriculados()) > 0){
            return self::join('candidato', 'selecao.id_candidato', 'candidato.id')
                        ->join('inscricao', 'selecao.id_candidato', 'inscricao.candidato_id')
                        ->join('aluno', 'selecao.id_candidato', '<>', 'aluno.candidato_id')
                        ->where('selecao.ano_lectivo', session('id_ano_lectivo'))
                        ->whereNotIn('selecao.id_candidato', Aluno::idAlunosMatriculados())
                        ->select('candidato.*', 'inscricao.*', 'selecao.*')
                        ->distinct('inscricao.candidato_id')->get();
        }

        return self::join('candidato', 'selecao.id_candidato', 'candidato.id')
                    ->join('inscricao', 'selecao.id_candidato', 'inscricao.candidato_id')
                    ->where('selecao.ano_lectivo', session('id_ano_lectivo'))
                    ->get();
    }

    public static function listarPreSelecionados(){
        return self::join('candidato', 'selecao.id_candidato', 'candidato.id')
                    ->where('ano_lectivo', session('id_ano_lectivo'))
                    ->where('usuario_id', null)->get();
    }

    public static function idCandidatosSelecionados(){
        return self::join('candidato', 'selecao.id_candidato', 'candidato.id')
                    ->where('ano_lectivo', session('id_ano_lectivo'))
                    ->select('selecao.id_candidato')->get();
    }

    public static function selecionarCandidato($ano_lectivo, $candidato_id, $usuario_id, $curso_id){

        return self::insert([
                        'ano_lectivo' => $ano_lectivo,
                        'id_candidato' => $candidato_id,
                        'usuario_id' => $usuario_id,
                        'curso_id' => $curso_id,
                    ]);
        
    }

    public static function eliminarSelecao($candidato_id){
        return self::where('id_candidato', $candidato_id)
                    ->where('ano_lectivo', session('id_ano_lectivo'))->delete();
    }
}
