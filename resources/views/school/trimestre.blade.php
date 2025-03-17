@extends('school.app')
@section('content')
    <div class="container">
        <div class="page-header">
            <div class="row">
                <div class="col-md-6 col-sm-12">
                    <div class="title">
                        <h4>Listar Alunos</h4>
                    </div>
                    <nav aria-label="breadcrumb" role="navigation">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index">Página Inicial</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Configurações do Trimestre</li>
                        </ol>
                    </nav><br>
                    @if (!session('fim_ano_lectivo'))
                        <a href="#" data-toggle="modal" data-target="#fechar" class="btn btn-info">Fechar {{session('nome_trimestre'). " Trimestre"}}</a><br>
                    @else
                        Fim do Ano Lectivo <br>
                    @endif

                    @if (session('trimestre_id') > 1 AND session('tipo_usuario') == 1)
                        <br><a href="#" data-toggle="modal" data-target="#voltar" class="btn btn-info">Voltar para o Iº Trimestre</a>
                    @endif
                </div>
            </div>
        </div>


<div id="fechar" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-body p-4">
                <div class="text-center">
                    <i class="dripicons-warning h1 text-warning"></i>
                    <h4 class="mt-2"> Quando fechar o trimestre
                        todos os alunos que não
                        lhes foi atribuído nota em uma disciplina terá 0 valores e o sistema passará automaticamente
                        para o
                        próximo trimestre.</h4>
                    <form action=" {{ route('trimestre.fechar') }} " method="post">
                        @csrf
                        <a href="#" class="btn btn-success" data-dismiss="modal">Cancelar</a>
                        <a href="{{route('trimestre.fechar')}}" class="btn btn-warning my-2">Sim, fechar trimestre</a>
                    </form>
                </div>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>


<div id="voltar" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-body p-4">
                <div class="text-center">
                    <i class="dripicons-warning h1 text-warning"></i>
                    <h4 class="mt-2"> Pretende mesmo voltar?</h4>
                    <form action=" {{ route('trimestre.voltar') }} " method="post">
                        @csrf
                        <a href="#" class="btn btn-success" data-dismiss="modal">Cancelar</a>
                        <a href="{{route('trimestre.voltar')}}" class="btn btn-warning my-2">Sim, voltar</a>
                    </form>
                </div>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>


    </div>

@endsection