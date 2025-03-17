@extends('school.app')
@section('content')
<div class="container">
    <div class="page-header">
        <div class="row">
            <div class="col-md-6 col-sm-12">
                <div class="title">
                    <h4>Gerir Salas</h4>
                </div>
                <nav aria-label="breadcrumb" role="navigation">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index">Página Inicial</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Gerir Salas</li>
                    </ol>
                </nav>
            </div>

            <div class="col-md-6 col-sm-12 text-right">
                <a href="javascript:void(0);" class="btn btn-primary" data-toggle="modal" data-target="#exampleModalAdicionar">Adicionar Sala</a>
            </div>

        </div>
    </div>


    <div class="pd-20 card-box mb-30">

        <ul class="list-group list-group-flush">



                <!-- Modal de editar vagas começa aqui -->

                <div class="modal modalAbre fade" id="exampleModalEditar2" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel">Formulário Editar Sala</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <form  method="post"  enctype="multipart/form-data">
                                @csrf
                                <div class="modal-body">
                                    <div class="form-group row">
                                        <label class="col-sm-12 col-md-2 col-form-label">Referência</label>
                                        <div class="col-sm-12 col-md-10">
                                            <input class="form-control" value="02" name="refe" placeholder="Referência...">
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-sm-12 col-md-2 col-form-label">Capacidade</label>
                                        <div class="col-sm-12 col-md-10">
                                            <input class="form-control" value="30" min="0" name="capacidade" type="number" placeholder="Capacidade...">
                                        </div>
                                    </div>
                                    <input type="hidden" name="sala_id" value="2">
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
                
                

                <table id="data-table" class="table stripe hover nowrap">
                    <thead>
                        <tr>
                            <th class="table-plus datatable-nosort">Referência</th>
                            <th>Capacidade</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($salas as $sala)

                        <tr class="odd">
                            <td class="table-plus">{{$sala->refe}}</td>
                            <td>{{$sala->capacidade}}</td>
                            @if(1 == 0)
                            <td>
                                <div class="dropdown">
                                    <a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle" href="#"
                                        role="button" data-toggle="dropdown">
                                        <i class="dw dw-more"></i>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                                        <a class="dropdown-item" href="#" data-toggle="modal"
                                            data-target="#exampleModal32"><i class="dw dw-eye">
                                            </i> Editar</a>

                                        <a class="dropdown-item" href="#"><i class="dw dw-delete-3"></i> Eliminar</a>
                                    </div>
                                </div>
                            </td>
                            @endif
                            @endforeach
                            <!--Modal de ver Informações -->
                    </tbody>
                </table>



                {{-- comment 
                @foreach ($salas as $sala)
                    <a id="sala1" href="javascript:void(0);" data-toggle="modal" data-target="#exampleModal1" class="list-group-item list-group-item-action">
                        Referência: <span class="nome1">{{$sala->refe}}</span> &nbsp 
                        Capacidade: <span class="nome1">{{$sala->capacidade}}</span>
                    </a>
                @endforeach
                --}}



                <!-- Modal de actualização-->
                <div class="modal fade" id="exampleModal1" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel">Informações sobre sala: 01</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <ul class="list-group">
                                    <li class="list-group-item">  Referência: <b><span class="nome1">01</span></b> </li>
                                    <li class="list-group-item">  Capacidade: <b><span>52</span></b> </li>

                                </ul>
                            </div>

                            <div class="modal-footer">
                                <button type="submit" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                                <button type="submit" class="btn btn-danger" data-dismiss="modal" onclick="eliminarSala(1)">Eliminar</button>
                                <button type="button" class="btn btn-primary" data-dismiss="modal" data-toggle="modal" data-target="#exampleModalEditar1">Editar</button>
                            </div>
                        </div>
                    </div>
                </div>
                
        </ul>

    </div>

</div>


<div class="modal modalAbre fade" id="exampleModalAdicionar" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Formulário Adicionar Sala</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form  enctype="multipart/form-data" id="add-sala">
                @csrf
                <div class="modal-body">

                    <div class="form-group row">
                        <label class="col-sm-12 col-md-2 col-form-label">Referência</label>
                        <div class="col-sm-12 col-md-10">
                            <input class="form-control" name="refe" placeholder="Referência...">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-12 col-md-2 col-form-label">Capacidade</label>
                        <div class="col-sm-12 col-md-10">
                            <input class="form-control" min="0" name="capacidade" type="number" placeholder="Capacidade...">
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
        $("#add-sala").submit(function(e){
            e.preventDefault();
            $.ajax({
                type: "POST",
                url: "{{route('home.store.sala')}}",
                data: $("#add-sala").serialize(),
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