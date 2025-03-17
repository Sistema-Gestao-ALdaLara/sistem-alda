@extends('school.app')
@section('content')
<div class="container">
    <div class="page-header">
        <div class="row">
            <div class="col-md-6 col-sm-12">
                <div class="title">
                    <h4>Gerir Disciplinas</h4>
                </div>
                <nav aria-label="breadcrumb" role="navigation">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index">Página Inicial</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Gerir Disciplinas</li>
                    </ol>
                </nav>
            </div>

            <div class="col-md-6 col-sm-12 text-right">
                <a href="javascript:void(0);" class="btn btn-primary" data-toggle="modal" data-target="#exampleModalAdicionar">Adicionar Disciplinas</a>
            </div>

        </div>
    </div>

    <div class="pd-20 card-box mb-30">

        <ul class="list-group list-group-flush">

                <!--Modal de editar vagas termina aqui -->                
                @foreach ($disciplinas as $disciplina)
                    <a id="sala1" href="javascript:void(0);" data-toggle="modal" data-target="#disciplina" class="list-group-item list-group-item-action">
                        Nome da Disciplina: <span class="nome1">{{$disciplina->nome_disciplina}}</span> <br>
                    </a>

                <!-- Editar Disciplina-->
                <div class="modal modalAbre fade" id="#disciplina" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel">Editar Disciplina</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <form method="POST" action="{{ route('disciplinas.actualizar')}}">
                                @csrf
                                <div class="modal-body">
                                    <div class="form-group row">
                                        <label class="col-sm-12 col-md-2 col-form-label">Nome da Disciplinas</label>
                                        <div class="col-sm-12 col-md-10">
                                            <input class="form-control" name="nome_disciplina" placeholder="Disciplinas..." value="{{$disciplina->nome_disciplina}}">
                                        </div>
                                    </div>
                                </div>
                
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-success">Actualizar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <!-------->
            @endforeach
        </ul>
    </div>
</div>



<div class="modal modalAbre fade" id="exampleModalAdicionar" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Formulário Adicionar Disciplina</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form  method="POST" action="{{ route('disciplinas.adicionar')}}">
                @csrf
                <div class="modal-body">
                    <div class="form-group row">
                        <label class="col-sm-12 col-md-2 col-form-label">Nome da Disciplinas</label>
                        <div class="col-sm-12 col-md-10">
                            <input class="form-control" name="nome_disciplina" placeholder="Disciplinas...">
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                    <button type="submit" class="btn btn-success">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('js')
    <script>
        /*
        $("#add-disciplina").submit(function(e){
            e.preventDefault();
            $.ajax({
                type: "POST",
                url: "{{route('disciplinas.adicionar')}}",
                data: $("#add-disciplina").serialize(),
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
        */
    </script>
@endpush
@endsection