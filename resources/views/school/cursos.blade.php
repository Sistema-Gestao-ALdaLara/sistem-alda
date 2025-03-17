@extends('school.app')
@section('content')
    <div class="container">
        <div class="page-header">
            <div class="row">
                <div class="col-md-6 col-sm-12">
                    <div class="title">
                        <h4>Gerir Cursos</h4>
                    </div>
                    <nav aria-label="breadcrumb" role="navigation">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home.painel')}}">Página Inicial</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Gerir Cursos</li>
                        </ol>
                    </nav>
                </div>

                <div class="col-md-6 col-sm-12 text-right">
                    <a href="javascript:void(0);" class="btn btn-primary" data-toggle="modal"
                        data-target="#exampleModal">Adicionar Curso</a>
                </div>
            </div>
        </div>

        <!-- Simple Datatable start -->
        <div class="card-box mb-30">
            <br>
            <div class="pb-20">
                <table id="data-table" class="table stripe hover nowrap">
                    <thead>
                        <tr>
                            <th class="table-plus datatable-nosort">Foto</th>
                            <th>Nome</th>
                            <th>Sigla</th>
                            <th>Usuário</th>
                            <th>Coordenador</th>
                            <th>Data de Criação</th>
                            <th class="datatable-nosort">Acção</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($cursos as $curso)

                        <tr class="odd">
                            <td class="table-plus"><img src="{{asset('storage/curso/'.$curso->foto) ?? 'Nenhuma'}}"></td>
                            <td>{{$curso->nome}}</td>
                            <td>{{$curso->sigla}}</td>
                            <td>{{App\Models\Usuario::retornaNomeUsuario($curso->usuario_id)}}</td>
                            <td>{{App\Models\Funcionario::retornaCoordenador($curso->id)->nome ?? "Sem Coordenador"}}</td>
                            <td>{{date_format(date_create($curso->date_create) ,"d-m-Y")}}</td>
                            <td>
                                <div class="dropdown">
                                    <a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle" href="#"
                                        role="button" data-toggle="dropdown">
                                        <i class="dw dw-more"></i>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                                        <a class="dropdown-item" href="#" data-toggle="modal"
                                            data-target="#exampleCoordenador{{$curso->id}}"><i class="dw dw-eye">
                                            </i> Adicionar Coordenador</a>

                                        <a class="dropdown-item" href="#" data-toggle="modal"
                                            data-target="#exampleModal32"><i class="dw dw-eye">
                                            </i> Editar</a>

                                        <a class="dropdown-item" href="#"><i class="dw dw-delete-3"></i> Eliminar</a>
                                    </div>
                                </div>
                            </td>

                            {{-- MODAL PARA ADICIONAR COORDENADOR DO CURSO --}}
                            <div class="modal fade" id="exampleCoordenador{{$curso->id}}" tabindex="-1"
                                aria-labelledby="exampleCoordenadorLabel" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="exampleCoordenadorLabel">Adicionar Coordenador do Curso {{$curso->nome}}
                                            </h5>
                                            <button type="button" class="close" data-dismiss="modal"
                                                aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>


                                        <form id="add-coordenador">
                                            @csrf
                                            <div class="modal-body">
                                                <input type="hidden" name="curso_id" value="{{$curso->id}}">
                                                <div class="form-group row">
                                                    <label class="col-sm-12 col-md-2 col-form-label">Escolher
                                                        Professor</label>
                                                    <div class="col-sm-12 col-md-10">
                                                        <select name="usuario_id" class="form-control">
                                                            @foreach ($professores as $prof)
                                                                <option value="{{$prof->id}}">{{$prof->nome}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>


                                            </div>

                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-dismiss="modal">Fechar</button>
                                                <button type="submit" class="btn btn-success">Guardar</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <!--Modal de Adicionar coordenador termina aqui -->
                            @endforeach
                            <!--Modal de ver Informações -->
                    </tbody>
                </table>
            </div>
        </div>



        <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Formulário Adicionar Curso</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>


                    <form enctype="multipart/form-data" id="add-curso">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group row">
                                <label class="col-sm-12 col-md-2 col-form-label">Carregar Fotografia</label>
                                <div class="col-sm-12 col-md-10">
                                    <input class="form-control" name="nome" type="file">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-sm-12 col-md-2 col-form-label">Nome</label>
                                <div class="col-sm-12 col-md-10">
                                    <input class="form-control" name="nome" type="text" placeholder="Nome...">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-sm-12 col-md-2 col-form-label">Abreviação</label>
                                <div class="col-sm-12 col-md-10">
                                    <input class="form-control" name="sigla" type="text" placeholder="Abreviação do Nome do Curso">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-sm-12 col-md-2 col-form-label">Descrição</label>
                                <div class="col-sm-12 col-md-10">
                                    <textarea class="form-control" name="descricao" id="" ></textarea>
                                </div>
                            </div>

                            <input type="hidden" name="usuario_id" value = "{{session('id_usuario')}}">

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
    $("#add-curso").submit(function(e){
        e.preventDefault();
        $.ajax({
            type: "POST",
            url: "{{route('cursos.adicionar')}}",
            data: $("#add-curso").serialize(),
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
    });

    $("#add-coordenador").submit(function(e){
        e.preventDefault();
        $.ajax({
            type: "POST",
            url: "{{route('coordenador.adicionar')}}",
            data: $("#add-coordenador").serialize(),
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

    function getTeacher(id){
        $.ajax({
        type: "GET",
        url: "curso/"+id,
        dataType: "json",
        success: function (response) {
            if(response.status == 500){
                return ModalAlerta('alert', response.message, 1);
            }
            if(response.status == 404){
                return ModalAlerta('alert', response.message, 1);
            }
            if(response.status == 200){
                //return ModalAlerta('info', response.message, 2);
                let val_teach = response.data
                $("#file_code").val(val_teach.photo)
                $("#id_teach").val(val_teach.id_teacher)
                $("#sexo").find("option[value=" + val_teach.sexo + "]").attr("selected", true);
                //$("#turm_choice").find("option[value=" + val_teach.id_turm + "]").attr("selected", true);
                $("#name_teacher").val(val_teach.name_teacher)
                //$("#sexo").attr('value', val_teach.sexo)
                //$("#sexo").html(returnSexo(val_teach.sexo))
                $("#contacto").val(val_teach.contacto)
                $("#agent").val(val_teach.agent_number)
                $("#bi_code").val(val_teach.number_bi)
                $("#turm").attr('value', val_teach.id_turm);
                $("#turm").html(val_teach.name_class +' - '+val_teach.name_turm + ' | ' + val_teach.name_teacher )
                $("#edit-teacher").modal('show')
            }
        }
      });
    }
    
</script>
@endpush
@endsection
