<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inscricao extends Model
{
    public $table = "inscricao";
    public $timestamps = false;
    protected $fillable = [
        'candidato_id',
        'media_final',
        'mat_nota',
        'quim_nota',
        'fis_nota',
        'lingua_nota',
        'upload_bi',
        'upload_cert',
        'curso1',
        'curso2',
        'curso3',
        'ano_lectivo',
    ];

    public static function cadastrarInscricao($idCandidato, $media_final, $media_matematica, $media_quimica, $media_fisica, $media_portugues, $pdf_bi, $pdf_certificado, $curso_1, $curso_2, $curso_3, $ano_lectivo){
        return self::insert([
            'candidato_id' => $idCandidato,
            'media_final' => $media_final,
            'mat_nota' => $media_matematica,
            'quim_nota' => $media_quimica,
            'fis_nota' => $media_fisica,
            'lingua_nota' => $media_portugues,
            'upload_bi' => $pdf_bi,
            'upload_cert' => $pdf_certificado,
            'curso1' => $curso_1,
            'curso2' => $curso_2,
            'curso3' => $curso_3,
            'ano_lectivo' => $ano_lectivo,
        ]);
    }

    public static function pegarInscrito($idCandidato){
        return self::join('candidato', 'inscricao.candidato_id', 'candidato.id')
                    ->where('inscricao.ano_lectivo', session('id_ano_lectivo'))
                    ->where('candidato_id', $idCandidato)->first();
    }

    public static function eliminarInscricao($idCandidato){
        return self::where('candidato_id', $idCandidato)->delete();
    }

    public static function listarInscritos(){
        return self::join('candidato', 'inscricao.candidato_id', 'candidato.id')
                    ->where('inscricao.ano_lectivo', session('id_ano_lectivo'))
                    ->get();
    }

}
