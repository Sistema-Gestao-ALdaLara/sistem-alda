@extends('school.app')
@section('content')
    <div class="container">
        <div class="page-header">
            <div class="row">
                <div class="col-md-6 col-sm-12">
                    <div class="title">
                        <h4>Associar Professor & Disciplina</h4>
                    </div>
                    <nav aria-label="breadcrumb" role="navigation">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index">Página Inicial</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Professor & Disciplina</li>
                        </ol>
                    </nav>
                </div>

                <div class="col-md-6 col-sm-12 text-right">
                    <a href="javascript:void(0);" class="btn btn-primary" data-toggle="modal"
                        data-target="#exampleModal">Adicionar Associação</a>
                </div>
            </div>
        </div>

        @foreach ($associacoes as $associacao)
                <a id="turma14" href="javascript:void(0);" data-toggle="modal" data-target="#exampleModal14"
                    class="list-group-item list-group-item-action">
                    <span>Professor: {{$associacao->nome}}</span><br>
                    <span>Turma: {{$associacao->turma}}</span><br>
                    <span>Disciplina: {{App\Models\Disciplina::pegarDisciplina($associacao->disciplina_id)->nome_disciplina}}</span><br>
                </a><br>
        @endforeach

        <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Formulário Adicionar Associação</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>


                    <form enctype="multipart/form-data" id="add-associacao">
                        @csrf
                        <div class="modal-body">

                            <div class="form-group row">
                                <label class="col-sm-12 col-md-2 col-form-label">Professor</label>
                                <div class="col-sm-12 col-md-10">
                                    <select class="custom-select2 form-control" name="usuario_id"
                                    style="width: 100%; height: 38px;">
                                    <option value="" >Escolher Professor</option>
                                    @foreach ($professores as $prof)
                                        <option value="{{$prof->id}}">{{$prof->nome}}</option>
                                    @endforeach

                                </select>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-sm-12 col-md-2 col-form-label">Disciplina</label>
                                <div class="col-sm-12 col-md-10">
                                    <select class="custom-select2 form-control" name="disciplina_id"
                                    style="width: 100%; height: 38px;">
                                    <option value="">Escolher Disciplina</option>
                                    @foreach ($disciplinas as $disciplina)
                                        <option value="{{$disciplina->id}}">{{$disciplina->nome_disciplina}}</option>
                                    @endforeach

                                </select>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-sm-12 col-md-2 col-form-label">Turmas</label>
                                <div class="col-sm-12 col-md-10">
                                    <select class="custom-select2 form-control" name="turma_id"
                                    style="width: 100%; height: 38px;">
                                    <option value="">Escolher Turma</option>
                                    @foreach ($turmas as $turma)
                                        <option value="{{$turma->id}}">{{$turma->turma}}</option>
                                    @endforeach

                                </select>
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
        <!--Modal de Adicionar coordenador termina aqui -->

    </div>



@push('js')
<script>

$("#add-associacao").submit(function(e){
        e.preventDefault();
        $.ajax({
            type: "POST",
            url: "{{route('professor-disciplina.adicionar')}}",
            data: $("#add-associacao").serialize(),
            dataType: "json",
            success: function (response) {
                console.log(response)
                if(response.status == 500){
                    ModalAlerta('error',response.register,3)
                }
                if(response.status == 200){
                    ModalAlerta('confirm',response.register,1)
                }
            },
            error: function(error){
                ModalAlerta('error',error,3)
            }
        });
    })        
</script>
@endpush
@endsection