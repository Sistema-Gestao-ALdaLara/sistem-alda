@extends('school.app')
@section('content')
    <div class="container">

        <div class="page-header">
            <div class="row">
                <div class="col-md-6 col-sm-12">
                    <div class="title">
                        <h4>Gerir Turmas</h4>
                    </div>
                    <nav aria-label="breadcrumb" role="navigation">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index">Página Inicial</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Gerir Turmas</li>
                        </ol>
                    </nav>
                </div>

                <div class="col-md-6 col-sm-12 text-right">
                    <a href="javascript:void(0);" class="btn btn-primary" data-toggle="modal"
                        data-target="#exampleModalAdicionar">Adicionar Turma</a>
                </div>

            </div>
        </div>

        <div class="pd-20 card-box mb-30">

               @foreach ($turmas as $turma)
                    
                <a id="turma14" href="javascript:void(0);" data-toggle="modal" data-target="#Modal{{$turma->id}}"
                    class="list-group-item list-group-item-action">
                    <span class="nome14">{{$turma->turma}}</span> <br>
                    <small>Ano: {{App\Models\AnoLectivo::pegarAno($turma->ano_lectivo)->nome_ano}}</small>
                </a>


                <!-- Modal -->
                <div class="modal fade" id="Modal{{$turma->id}}" tabindex="-1" aria-labelledby="exampleModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel">Informações sobre a turma: {{$turma->turma}}</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <ul class="list-group">
                                    <li class="list-group-item"> Classe: <b><span class="nome14">{{App\Models\Classe::pegarClasse($turma->classe_id)->nome_classe}}</span></b> </li>
                                    <li class="list-group-item"> Turma: <b><span class="nome14">{{App\Models\Turma::retornaLetraTurma($turma->id)}}</span></b> </li>
                                    <li class="list-group-item"> Período: <b><span class="nome14">{{App\Models\Periodo::pegarPeriodo($turma->periodo_id)->nome}}</span></b> </li>
                                    <li class="list-group-item"> Ano Lectivo: <b><span>{{App\Models\AnoLectivo::pegarAno($turma->ano_lectivo)->nome_ano}}</span></b> </li>
                                    <li class="list-group-item"> Curso: <b><span>{{App\Models\Curso::pegarCurso($turma->curso_id)->nome}}</span></b>
                                    </li>
                                    <li class="list-group-item"> Sala: <b><span>{{App\Models\Sala::pegarSala($turma->sala_id)->refe}}</span></b> </li>

                                </ul>
                            </div>

                            <div class="modal-footer">
                                <button type="submit" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                                <button type="submit" class="btn btn-danger" data-dismiss="modal"
                                    onclick="eliminarTurma(14)">Eliminar</button>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Modal de editar vagas começa aqui -->

                <div class="modal modalAbre fade" id="ModalEditar{{$turma->id}}" tabindex="-1"
                    aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel">Formulário Editar Turma</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <form onsubmit="editarTurma(this,14,event)" method="post" action="/acoes/editar_turma"
                                enctype="multipart/form-data">
                                <div class="modal-body">

                                    <div class="form-group row">
                                        <label class="col-sm-12 col-md-2 col-form-label">Ano Lectivo</label>
                                        <div class="col-sm-12 col-md-10">
                                            <input required value="32323" class="form-control" min="2000"
                                                name="ano_lectivo" type="number" placeholder="Ano Lectivo...">
                                        </div>
                                    </div>


                                    <div class="form-group row">
                                        <label class="col-sm-12 col-md-2 col-form-label">Curso</label>
                                        <div class="col-sm-12 col-md-10">
                                            <select class="custom-select2 form-control" name="curso_id"
                                                style="width: 100%; height: 38px;">
                                                <option value="1">Tecnico de Informatica</option>
                                                <option value="2">Tecnico de Energia Renovavel</option>
                                                <option value="6">Electromecânica</option>
                                                <option value="7">Técnico de Automação</option>
                                                <option value="10">MatFisica</option>
                                                <option value="11">12211212</option>

                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-sm-12 col-md-2 col-form-label">Classe</label>
                                        <div class="col-sm-12 col-md-10">
                                            <select class="custom-select2 form-control" name="classe"
                                                style="width: 100%; height: 38px;">
                                                <option value="10">10ª Classe</option>
                                                <option value="11">11ª Classe</option>
                                                <option value="12">12ª Classe</option>
                                                <option value="13">13ª Classe</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-sm-12 col-md-2 col-form-label">Período</label>
                                        <div class="col-sm-12 col-md-10">
                                            <select class="custom-select2 form-control" name="periodo"
                                                style="width: 100%; height: 38px;">
                                                <option value="M">Manhã</option>
                                                <option value="T">Tarde</option>
                                                <option value="N">Noite</option>

                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-sm-12 col-md-2 col-form-label">Turma</label>
                                        <div class="col-sm-12 col-md-10">
                                            <select class="custom-select2 form-control" name="turma"
                                                style="width: 100%; height: 38px;">
                                                <option value="A">A</option>
                                                <option value="B">B</option>
                                                <option value="C">C</option>
                                                <option value="D">D</option>
                                                <option value="E">E</option>
                                                <option value="F">F</option>
                                                <option value="G">G</option>
                                                <option value="H">H</option>
                                            </select>
                                        </div>
                                    </div>


                                    <div class="form-group row">
                                        <label class="col-sm-12 col-md-2 col-form-label">Sala</label>
                                        <div class="col-sm-12 col-md-10">
                                            <select class="custom-select2 form-control" name="sala_id"
                                                style="width: 100%; height: 38px;">
                                                <option value="1">01</option>
                                                <option value="2">02</option>
                                                <option value="3">03</option>
                                                <option value="4">04</option>
                                                <option value="5">05</option>
                                                <option value="6">06</option>

                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-12 col-md-2 col-form-label">Sala</label>
                                        <div class="col-sm-12 col-md-10">
                                            <select class="custom-select2 form-control" name="sala_id"
                                                style="width: 100%; height: 38px;">
                                                <option selected value="1">01</option>
                                                <option value="2">02</option>
                                                <option value="3">03</option>
                                                <option value="4">04</option>
                                                <option value="5">05</option>
                                                <option value="6">06</option>

                                            </select>
                                        </div>
                                    </div>

                                    <input type="hidden" name="turma_id" value="14">

                                </div>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                                    <button type="submit" class="btn btn-success">Guardar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
            @endforeach
        </div>
    </div>



    <div class="modal modalAbre fade" id="exampleModalAdicionar" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Formulário Adicionar Turma</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <form enctype="multipart/form-data" id="add-turma">
                    @csrf
                    <div class="modal-body">

                        <div class="form-group row">
                            <label class="col-sm-12 col-md-2 col-form-label">Ano Lectivo</label>
                            <div class="col-sm-12 col-md-10">
                                <input required class="form-control" disabled type="text"
                                     value="{{session('ano_em_curso')}}">
                            </div>
                            <input type="hidden" name="ano_lectivo" value="{{session('id_ano_lectivo')}}">
                        </div>


                        <div class="form-group row">
                            <label class="col-sm-12 col-md-2 col-form-label">Curso</label>
                            <div class="col-sm-12 col-md-10">
                                <select class="custom-select2 form-control" name="curso_id"
                                    style="width: 100%; height: 38px;">
                                    <option>Escolha o Curso</option>
                                    @foreach (App\Models\Curso::listarCursos() as $curso)
                                        <option value="{{$curso->id}}">{{$curso->nome}}</option>
                                    @endforeach

                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-12 col-md-2 col-form-label">Classe</label>
                            <div class="col-sm-12 col-md-10">
                                <select class="custom-select2 form-control" name="classe_id"
                                    style="width: 100%; height: 38px;">
                                    <option>Escolha a Classe</option>
                                    @foreach (App\Models\Classe::listarClasses() as $classe)
                                        <option value="{{$classe->id}}">{{$classe->nome_classe}} Classe</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-12 col-md-2 col-form-label">Turma</label>
                            <div class="col-sm-12 col-md-10">
                                <select class="custom-select2 form-control" name="turma"
                                    style="width: 100%; height: 38px;">
                                    <option value="A">A</option>
                                    <option value="B">B</option>
                                    <option value="C">C</option>
                                    <option value="D">D</option>
                                    <option value="E">E</option>
                                    <option value="F">F</option>
                                    <option value="G">G</option>
                                    <option value="H">H</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-12 col-md-2 col-form-label">Período</label>
                            <div class="col-sm-12 col-md-10">
                                <select class="custom-select2 form-control" name="periodo_id"
                                    style="width: 100%; height: 38px;">
                                    <option>Escolha o Período</option>
                                    @foreach (App\Models\Periodo::listarPeriodos() as $periodo)
                                        <option value="{{$periodo->id}}">{{$periodo->nome}}</option>
                                    @endforeach

                                </select>
                            </div>
                        </div>


                        <div class="form-group row">
                            <label class="col-sm-12 col-md-2 col-form-label">Sala</label>
                            <div class="col-sm-12 col-md-10">
                                <select class="custom-select2 form-control" name="sala_id"
                                    style="width: 100%; height: 38px;">
                                    <option>Escolha a Sala</option>
                                    @foreach (App\Models\Sala::listarSalas() as $sala)
                                        <option value="{{$sala->id}}">{{$sala->refe}}</option>
                                    @endforeach

                                </select>
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

$("#add-turma").submit(function(e){
        e.preventDefault();
        $.ajax({
            type: "POST",
            url: "{{route('turmas.adicionar')}}",
            data: $("#add-turma").serialize(),
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
