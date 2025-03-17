<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProvasTrimestre extends Model
{
    public $table = "provas_trimestre";
    public $timesatamps = false;
    protected $fillable = [
        'trimestre_id',
        'mac',
        'npp',
        'npt',
        'mt',
        'ano_lectivo',
        'aluno_id',
        'usuario_id',
        'disciplina_id',
    ];

    public static function inserirNotas($mac, $npp, $npt, $mt, $aluno_id, $disciplina_id){
        return self::insert([
                        'trimestre_id' => session('trimestre_id'),
                        'mac' => $mac,
                        'npp' => $npp,
                        'npt' => $npt,
                        'mt' => $mt,
                        'ano_lectivo' => session('id_ano_lectivo'),
                        'aluno_id' => $aluno_id,
                        'usuario_id' => session('id_usuario'),
                        'disciplina_id' => $disciplina_id,
                    ]);
    }

    public static function actualizarNotas($trimestre_id, $mac, $npp, $npt, $mt, $aluno_id, $disciplina_id){
        return self::where('trimestre_id', $trimestre_id)
                    ->where('aluno_id', $aluno_id)
                    ->where('disciplina_id', $disciplina_id)
                    ->where('ano_lectivo', session('id_ano_lectivo'))
                    ->update([
                        'mac' => $mac,
                        'npp' => $npp,
                        'npt' => $npt,
                        'mt' => $mt,
                    ]);
    }

    public static function verificaNotas($trimestre_id, $aluno_id, $disciplina_id){
        $notas = self::where('trimestre_id', $trimestre_id)
            ->where('aluno_id', $aluno_id)
            ->where('disciplina_id', $disciplina_id)
            ->where('ano_lectivo', session('id_ano_lectivo'))
            ->count();

        if($notas > 0){
            return true;
        }

        return false;
    }

    public static function apagarNotas($trimestre_id, $aluno_id, $disciplina_id){
        return self::where('trimestre_id', $trimestre_id)
                    ->where('aluno_id', $aluno_id)
                    ->where('disciplina_id', $disciplina_id)
                    ->where('ano_lectivo', session('id_ano_lectivo'))
                    ->delete();
    }

    public static function buscarNotas($trimestre_id, $aluno_id, $disciplina_id){
        return self::where('trimestre_id', $trimestre_id)
                    ->where('aluno_id', $aluno_id)
                    ->where('disciplina_id', $disciplina_id)
                    ->where('ano_lectivo', session('id_ano_lectivo'))
                    ->first();
    }

    public static function buscarMediaTrimestral($trimestre_id, $aluno_id, $disciplina_id){
        return self::where('trimestre_id', $trimestre_id)
                    ->where('aluno_id', $aluno_id)
                    ->where('disciplina_id', $disciplina_id)
                    ->where('ano_lectivo', session('id_ano_lectivo'))
                    ->select('mt')
                    ->first()->mt ?? null;
    }
}
