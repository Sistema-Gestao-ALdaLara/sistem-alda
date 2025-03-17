<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aluno extends Model
{
    public $table = "aluno";
    public $timesatamps = false;
    protected $fillable = [
        'candidato_id',
        'usuario_id',
        'user_id',
        'numero_processo',
    ];

    public static function cadastrarAluno($id_candidato, $id_usuario, $id_user, $numero_processo){
        return self::insert([
                    'candidato_id' => $id_candidato,
                    'usuario_id' => $id_usuario,
                    'user_id' => $id_user,
                    'numero_processo' => $numero_processo,
                ]);
    }

    public static function listarMatriculados(){
        return self::join('candidato', 'aluno.candidato_id', 'candidato.id')
                    ->join('selecao', 'aluno.candidato_id', 'selecao.id_candidato')
                    ->join('curso', 'selecao.curso_id', 'curso.id')
                    ->where('selecao.ano_lectivo', session('id_ano_lectivo'))
                    ->select('candidato.*', 'aluno.*', 'selecao.ano_lectivo', 'selecao.curso_id')
                    ->get();
    }

    public static function listarAlunosdaTurma($id_turma){
        return self::join('candidato', 'aluno.candidato_id', 'candidato.id')
                    ->join('selecao', 'aluno.candidato_id', 'selecao.id_candidato')
                    ->join('ano_lectivo_aluno', 'aluno.id', 'ano_lectivo_aluno.aluno_id')
                    ->join('curso', 'selecao.curso_id', 'curso.id')
                    ->select('candidato.*', 'aluno.*', 'selecao.*', 'ano_lectivo_aluno.aluno_id', 'ano_lectivo_aluno.turma_id', 'ano_lectivo_aluno.numero_aluno')
                    ->where('turma_id', $id_turma)
                    ->where('ano_lectivo_aluno.ano_lectivo', session('id_ano_lectivo'))
                    ->orderBy('ano_lectivo_aluno.numero_aluno')
                    ->get();
    }

    public static function pegarAluno($aluno_id){
        return self::join('candidato', 'aluno.candidato_id', 'candidato.id')
                    ->join('selecao', 'aluno.candidato_id', 'selecao.id_candidato')
                    ->join('ano_lectivo_aluno', 'aluno.id', 'ano_lectivo_aluno.aluno_id')
                    ->join('curso', 'selecao.curso_id', 'curso.id')
                    ->select('candidato.*', 'aluno.*', 'selecao.*', 'ano_lectivo_aluno.aluno_id', 'ano_lectivo_aluno.turma_id', 'ano_lectivo_aluno.numero_aluno')
                    ->where('aluno_id', $aluno_id)
                    ->where('ano_lectivo_aluno.ano_lectivo', session('id_ano_lectivo'))
                    ->first();
    }

    public static function pegarUsuarioAluno($email){
        return self::join('candidato', 'aluno.candidato_id', 'candidato.id')
                    ->join('selecao', 'aluno.candidato_id', 'selecao.id_candidato')
                    ->join('users', 'candidato.endereco_electronico', 'users.email')
                    ->join('ano_lectivo_aluno', 'aluno.id', 'ano_lectivo_aluno.aluno_id')
                    ->join('curso', 'selecao.curso_id', 'curso.id')
                    ->select('candidato.*', 'aluno.*', 'selecao.*', 'ano_lectivo_aluno.aluno_id', 'ano_lectivo_aluno.turma_id', 'ano_lectivo_aluno.numero_aluno')
                    ->where('users.email', $email)
                    ->where('ano_lectivo_aluno.ano_lectivo', session('id_ano_lectivo'))
                    ->first();
    }

    public static function ultimoNumeroProcesso(){
        return self::select('numero_processo')->orderByDesc('id')->first()->numero_processo ?? null;
    }

    public static function idAlunosMatriculados(){
        return self::join('candidato', 'aluno.candidato_id', 'candidato.id')
                    ->join('selecao', 'aluno.candidato_id', 'selecao.id_candidato')
                    ->where('ano_lectivo', session('id_ano_lectivo'))
                    ->select('aluno.candidato_id')->get();
    }

    public static function pegarNotasDoAluno($aluno_id, $trimestre_id = null){
        return self::join('provas_trimestre', 'provas_trimestre.aluno_id', '=', 'aluno.id')
                    ->join('disciplinas', 'disciplina.id', '=', 'provas_trimestre.disciplina_id')
                    ->join('disciplina_curso', 'disciplina_curso.disciplina_id', '=', 'disciplinas.id')
                    ->join('trimestre', 'trimestre.id', '=', 'provas_trimestre.trimestre_id')
                    ->join('candidato', 'candidato.id', '=', 'aluno.candidato_id')
                    ->join('ano_lectivo_aluno', 'ano_lectivo_aluno.aluno_id', '=', 'provas_trimestre.aluno_id')
                    ->where('provas_trimestre.ano_lectivo', '=', session('id_ano_lectivo'))
                    //->where('provas_trimestre.trimestre_id', $trimestre_id)
                    //->where('provas_trimestre.aluno_id', $aluno_id)
                    ->select( "mac", "npp", "npt", "mt", "numero_aluno", "provas_trimestre.trimestre_id", "provas_trimestre.aluno_id", "provas_trimestre.disciplina_id", 'nome_trimestre', "provas_trimestre.ano_lectivo", "nome_cand", "nome_disciplina", "numero_processo")->orderBy('numero_aluno') ->get();
    }
}
