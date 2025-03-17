@extends('school.app')
@section('content')
<div class="container">
    <div class="page-header">
        <div class="row">
            <div class="col-md-6 col-sm-12">
                <div class="title">
                    <h4>Gerir Vagas</h4>
                </div>
                <nav aria-label="breadcrumb" role="navigation">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index">Página Inicial</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Gerir Vagas</li>
                    </ol>
                </nav>
            </div>
            
            <div class="col-md-6 col-sm-12 text-right">
                <a href="javascript:void(0);" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal">Adicionar Vagas</a>
            </div>

        </div>
    </div>

        <div class="pd-20 card-box mb-30">
            <ul class="list-group list-group-flush">
                @foreach ($vagas as $vaga)
                    <a id="vaga1" href="javascript:void(0);" data-toggle="modal" data-target="#editar-vaga{{$vaga->id}}" class="list-group-item list-group-item-action">{{$vaga->nome}}<br>

                        <div class="float-left">Ano Lectivo: {{$vaga->nome_ano}}</div>
                        <div class="float-right">Vagas: {{$vaga->numero_vagas}}</div>
                    </a>

                <!--Modal Para Editar Vaga-->
                <div class="modal modalAbre fade" id="editar-vaga{{$vaga->id}}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel">Editar Vagas do Curso {{$vaga->nome}}</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <form method="POST" action="{{ route('vagas.editar')}}">
                                @csrf
                                <div class="modal-body">
                                    
                                    <input type="hidden" name="curso_id" value="{{$vaga->curso_id}}">
                
                                    <div class="form-group row">
                                        <label class="col-sm-12 col-md-2 col-form-label">Número de vagas</label>
                                        <div class="col-sm-12 col-md-10">
                                            <input class="form-control" name="numero_vagas" type="number" placeholder="Número de vagas do Curso..." value="{{$vaga->numero_vagas}}">
                                        </div>
                                    </div>
                
                                    <input type="hidden" name="ano_lectivo" value="{{session('id_ano_lectivo')}}">
                
                                </div>
                
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-success">Actualizar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <!--Modal de editar vagas termina aqui -->
            @endforeach
            </ul>
        </div>

    <div style="width: 200px; " class="page-header">Total de Vagas: {{$total_vagas}}</div>
</div>


<!-- Modal de editar vagas começa aqui -->
<div class="modal modalAbre fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Formulário Adicionar Vagas</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="{{ route('vagas.adicionar')}}">
                @csrf
                <div class="modal-body">
                        
                    <div class="form-group row">
                        <label class="col-sm-12 col-md-2 col-form-label">Curso</label>
                            <div class="col-sm-12 col-md-10">
                            <select class="form-control" name="curso_id" >
                                <option value="">Escolher Curso</option>
                                @foreach ($cursos as $curso)
                                    <option value="{{$curso->id}}">{{$curso->nome}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-12 col-md-2 col-form-label">Nome da Disciplinas</label>
                        <div class="col-sm-12 col-md-10">
                            <input class="form-control" name="numero_vagas" type="number" placeholder="Número de vagas do Curso...">
                        </div>
                    </div>

                    <input type="hidden" name="ano_lectivo" value="{{session('id_ano_lectivo')}}">

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                    <button type="submit" class="btn btn-success">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!--Modal de editar vagas termina aqui -->

@endsection