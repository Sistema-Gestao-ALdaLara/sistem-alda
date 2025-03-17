<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class NotaFinalDisciplina extends Model
{
    public $table = "nota_final_disciplina";
    public $timestamps = false;
    protected $fillable = [
        'aluno_id',
        'disciplina_id',
        'classe_id',
        'mfd',
    ];

    public static function inserirNotasFinais($aluno_id, $classe_id)
    {
        try{
            DB::beginTransaction();
        
            $notas = MediaTrimestral::buscarMediasFinaisAluno($aluno_id);


            foreach ($notas as $nota) {
                dd($nota);
                /*self::insert([
                        'aluno_id' => $aluno_id,
                        'disciplina_id' => $nota->disciplina_id,
                        'classe_id' => $classe_id,
                        'mfd' => $mfd,
                ]);*/
            }

            DB::commit();
        } catch(\Throwable $e){
            return $e->getMessage();
        }
    }
}
