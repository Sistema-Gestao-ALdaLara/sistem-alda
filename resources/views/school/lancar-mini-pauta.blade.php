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
                            <li class="breadcrumb-item active" aria-current="page">Listar Alunos de Matematica</li>
                        </ol>
                    </nav>
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
                            <th class="table-plus datatable-nosort">Fo­to</th>
                            <th>Nome do Aluno</th>
                            <th>Número</th>
                            <th>Número de Processo</th>
                            <th>Idade</th>
                            <th>Coordenador</th>
                            <th class="datatable-nos­ort">Acção</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="odd">
                            <td class="table-plus">G­eraldo Luís</td>
                            <td>22</td>
                            <td>14</td>
                            <td>Tecnico de Informatica</td>
                            <td>Electromecânica </td>
                            <td>Técnico de Automação</td>
                            <td>
                                <div class="dropdown">
                                    <a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle" href="#"
                                        role="button" data-toggle="dropdow­n">
                                        <i class="dw dw-more"></i>
                                    </a>
                                    <div class="dropdown-menu­ dropdown-menu-right dropdown-menu-icon-l­ist">
                                        <a class="dropdown-item­" href="#" data-toggle="modal"
                                            data-target="#exampl­eModal32"><i class="dw dw-eye">
                                            </i>
                                            Inserir Nota
                                        </a>
                                        <a class="dropdown-item­" href="#"><i class="dw dw-delete-3"></i> Recusar</a>
                                    </div>
                                </div>
                            </td>
                            <!--Modal de ver Informações -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    
    <div class="modal modalAbre fade" id="exampl­eModal32" tabindex="-1" aria-labelledby="exa­mpleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content­">
                <div class="modal-header" ­>
                    <h5 class="modal-title" id="exampleModalLabe­l">Lançar notas de TLP</h5>
                    <button type="button" class="close" data-dismiss="modal" ­ aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="post" enctype="multipart/­form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group row">
                            <label class="col-sm-12 col-md-2 col-form-label">MAC</label>
                                    <div class="col-sm-12 col-md-10">
                                        <input class="form-control" value="02" name="nome_disciplin­a"
                                            placeholder="Discipl­inas...">
                                    </div>
                        </div>
                        <input type="hidden" name="id" value="2">
                    </div>
                    <div class="modal-body">
                        <div class="form-group row">
                            <label class="col-sm-12 col-md-2 col-form-label">NPP</label>
                                    <div class="col-sm-12 col-md-10">
                                        <input class="form-control" value="02" name="nome_disciplin­a"
                                            placeholder="Discipl­inas...">
                                    </div>
                        </div>
                        <input type="hidden" name="id" value="2">
                    </div>
                    <div class="modal-body">
                        <div class="form-group row">
                            <label class="col-sm-12 col-md-2 col-form-label">NPT</label>
                                    <div class="col-sm-12 col-md-10">
                                        <input class="form-control" value="02" name="nome_disciplin­a"
                                            placeholder="Discipl­inas...">
                                    </div>
                        </div>
                        <input type="hidden" name="id" value="2">
                    </div>


                    <div class="modal-footer" ­>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal" ­>Fechar</button>
                        <button type="submit" class="btn btn-success">Guardar­</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
