<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vaga extends Model
{
    public $table = "vagas";
    public $timestamps = false;
    protected $fillable = [
        'numero_vagas',
        'ano_lectivo',
        'curso_id',
    ];

    public static function cadastrarVaga($dados){
        if(is_array($dados))
            return self::create($dados);
     }
 
     public static function listarVagas(){
         return self::join('ano_lectivo', 'vagas.ano_lectivo', 'ano_lectivo.id')
                    ->join('curso', 'vagas.curso_id', 'curso.id')
                    ->get();
     }

     public static function contaTotalVagas(){
         return self::sum('numero_vagas');
     }

    public static function retornaNumeroVagasDoCurso($idCurso){
        return self::join('curso', 'vagas.curso_id', 'curso.id')
                    ->where('vagas.ano_lectivo', session('id_ano_lectivo'))
                    ->where('vagas.curso_id', $idCurso)
                    ->first()->numero_vagas;
    }     

    public static function editarVaga($idCurso, $numero_vagas){
        return self::where('ano_lectivo', session('id_ano_lectivo'))
                    ->where('curso_id', $idCurso)
                    ->update([
                        'numero_vagas' => $numero_vagas
                    ]);
    }

     public static function verificaVaga($idCurso){
        $numero_vagas = self::join('curso', 'vagas.curso_id', 'curso.id')
                    ->where('vagas.ano_lectivo', session('id_ano_lectivo'))
                    ->where('vagas.curso_id', $idCurso)
                    ->first()->numero_vagas ?? 0;

        return ($numero_vagas > 0) ? true : false ;
        
     }

     public static function retiraUmaVaga($idCurso){
        $numero_vagas = self::where('vagas.ano_lectivo', session('id_ano_lectivo'))
                            ->where('vagas.curso_id', $idCurso)
                            ->first()->numero_vagas;

        return self::where('vagas.ano_lectivo', session('id_ano_lectivo'))
                    ->where('vagas.curso_id', $idCurso)
                    ->update(['numero_vagas' => --$numero_vagas]);
     }
}
