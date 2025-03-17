<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MediaTrimestral extends Model
{
    public $table = "media_trimestral";
    public $timestamps = false;
    protected $fillable = [
        'aluno_id',
        'disciplina_id',
        'mt1',
        'mt2',
        'mt3',
        'media_final',
        'nota_votada',
        'nota_recurso',
        'ano_lectivo',
    ];

    public static function inserirMediasTrimestrais($aluno_id, $disciplina_id, $mt1, $mt2, $mt3, $media_final){
        return self::insert([
            'aluno_id' => $aluno_id,
            'disciplina_id' => $disciplina_id,
            'mt1' => $mt1,
            'mt2' => $mt2,
            'mt3' => $mt3,
            'media_final' => $media_final,
            'ano_lectivo' => session('id_ano_lectivo'),
        ]);
    }

    public static function actualizarMedias($aluno_id, $disciplina_id, $mt1, $mt2, $mt3, $media_final){
        return self::where('aluno_id', $aluno_id)
                    ->where('disciplina_id', $disciplina_id)
                    ->where('ano_lectivo', session('id_ano_lectivo'))
                    ->update([
                        'mt1' => $mt1,
                        'mt2' => $mt2,
                        'mt3' => $mt3,
                        'media_final' => $media_final,
                    ]);
    }

    public static function verificaNotas($aluno_id, $disciplina_id){
        $notas = self::where('aluno_id', $aluno_id)
                     ->where('disciplina_id', $disciplina_id)
                     ->where('ano_lectivo', session('id_ano_lectivo'))
                     ->count();

        if($notas > 0){
            return true;
        }

        return false;
    }

    public static function buscarMediasFinais($aluno_id, $disciplina_id, $ano_lectivo = null){
        return self::where('aluno_id', $aluno_id)
                     ->where('disciplina_id', $disciplina_id)
                     ->where('ano_lectivo', $ano_lectivo == null ? session('id_ano_lectivo') : $ano_lectivo)
                     ->first();
    }

    public static function retornaNotaFinal($aluno_id, $disciplina_id, $ano_lectivo = null){
        $notas = self::buscarMediasFinais($aluno_id, $disciplina_id, $ano_lectivo);

        if ($notas->nota_votada != null){
            return $notas->nota_votada;
        }elseif($notas->recurso != null){
            return $notas->nota_votada;
        }

        return $notas->media_final;
    }

    public static function buscarMediasFinaisAluno($aluno_id, $ano_lectivo = null){
        return self::where('aluno_id', $aluno_id)
                     ->where('ano_lectivo', $ano_lectivo == null ? session('id_ano_lectivo') : $ano_lectivo)
                     ->get();
    }

    public static function verificaSeDesistiu($aluno_id)
    {
        $contagem = [
            self::where('aluno_id', '=', $aluno_id)
                ->where('ano_lectivo', '=', session('id_ano_lectivo'))
                ->where('mt1', 0)->count(),

            self::where('aluno_id', '=', $aluno_id)
                ->where('ano_lectivo', '=', session('id_ano_lectivo'))
                ->where('mt2', 0)->count(),

            self::where('aluno_id', '=', $aluno_id)
                ->where('ano_lectivo', '=', session('id_ano_lectivo'))
                ->where('mt3', 0)->count(),
        ];

        if (array_sum($contagem) >= 22) {
            return true;
        }

        return false;
    }
}
