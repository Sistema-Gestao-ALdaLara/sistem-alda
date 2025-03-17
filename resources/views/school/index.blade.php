@extends('school.app')
@section('content')
<div class="card-box pd-20 height-100-p mb-30">
    <div class="row align-items-center">
        <div class="col-md-4">
            @if (session('tipo_usuario') == 4)
                <img src='{{asset("storage/candidato/img/".App\Models\Candidato::pegarCandidatoPor("endereco_electronico", session('email'))->foto)}}' width="200" height="100"
            style="border-radius: 3px 3px 3px 3px; object-fit: cover; " alt="">
            @else
                <img src='{{asset("storage/funcionario/".App\Models\Funcionario::pegarFuncionario(session('id_usuario'))->foto)}}' width="200" height="100"
            style="border-radius: 3px 3px 3px 3px; object-fit: cover; " alt="Foto de perfil do Usuário">
            @endif
        </div>
        <div class="col-md-8">
            <h4 class="font-20 weight-500 mb-10 text-capitalize">
                Seja Bem-vindo <div class="weight-600 font-30 text-blue">{{session('nome_usuario')}}</div>
                <br>
            </h4>
            <p class="font-18 max-width-600">Sistema de Gestão do Instituto Médio Industrial Simione Mucune
            </p>
        </div>
    </div>
</div>
@endsection