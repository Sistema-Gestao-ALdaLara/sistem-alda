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
                            @if ($disciplina == null)
                                <li class="breadcrumb-item active" aria-current="page">Alunos da Turma {{$turma->turma}}</li>
                            @else
                                <li class="breadcrumb-item active" aria-current="page">Lançar Notas de {{$disciplina->nome_disciplina}} da Turma {{$turma->turma}}</li>
                            @endif
                            
                        </ol>
                    </nav><br>

                    @if($disciplina != null)
                        <a href="{{ route('mini-pauta.singular', [$turma->id, $disciplina->id]) }}" class="btn btn-info" target="_blank">Ver Mini Pauta</a>
                    @endif

                    @if($disciplina == null)
                        @if (App\Models\Coordenador::pegarCoordenador(session('id_usuario')) != null)
                            <a href="{{ route('mini-pauta.colectiva', [$turma->id]) }}" class="btn btn-info" target="_blank">Mini Pautas</a>
                        @endif

                        @if (App\Models\Coordenador::pegarCoordenador(session('id_usuario')) != null OR session('tipo_usuario') == 1)
                            <a href="{{ route('pauta.trimestral', [$turma->id]) }}" class="btn btn-info" target="_blank">Ver Pauta trimestral</a>
                        @endif

                        @if (App\Models\Coordenador::pegarCoordenador(session('id_usuario')) != null AND session('trimestre_id') >= 3 or session('tipo_usuario') == 1 AND session('trimestre_id') >= 3)
                            <a href="{{ route('pauta-final', [$turma->id]) }}" class="btn btn-info" target="_blank">Pauta Final</a>
                        @endif
                    @endif
                    
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
                            <th style="width: 20px">Nº</th>
                            <th>Nome do Aluno</th>
                            <th>Nº de Processo</th>
                            <th>Idade</th>
                             <th style="text-align: center;">Acção</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($alunos as $aluno)
                        <tr class="odd">
                            <td>{{$aluno->numero_aluno}}</td>
                            <td class="table-plus">{{$aluno->nome_cand}}</td>
                            <td>{{$aluno->numero_processo}}</td>
                            <td>{{App\Utils\Auxiliar::diffYear($aluno->data_nascimento)}}</td>
                            <td style="text-align: center;">
                                @if($disciplina != null AND session('trimestre_id') < 3)
                                <a class="" href="#" data-toggle="modal" data-target="#notas{{$aluno->aluno_id}}"><i class="dw dw-edit"></i> Lançar Notas</a>
                                @endif
                                    
                            </td>
                            <!--Modal de ver Informações -->

                            @if($disciplina != null)
                            {{-- MODAL PARA LANÇAR NOTAS DO ALUNO --}}
                            <div class="modal fade" id="notas{{$aluno->aluno_id}}" tabindex="-1"
                                aria-labelledby="ModalTurmaLabel" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="ModalTurmaLabel">Lançar Notas de {{$disciplina->nome_disciplina}} do {{session('nome_trimestre')}} Trimestre</b>
                                            </h5>
                                            <button type="button" class="close" data-dismiss="modal"
                                                aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>

                                        <form id="add-nota" method="POST" action="{{route('pauta.lancar-notas')}}">
                                            @csrf
                                            <div class="modal-body">
                                                <div class="form-group row">
                                                    <label class="col-sm-12 col-md-2 col-form-label">MAC</label>
                                                    <div class="col-sm-12 col-md-10">
                                                        <input name="mac" class="form-control" value="{{App\Models\ProvasTrimestre::buscarNotas(session('trimestre_id'), $aluno->aluno_id, $disciplina->id, $turma->id)->mac ?? ''}}">
                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label class="col-sm-12 col-md-2 col-form-label">NPP</label>
                                                    <div class="col-sm-12 col-md-10">
                                                        <input name="npp" class="form-control" value="{{App\Models\ProvasTrimestre::buscarNotas(session('trimestre_id'), $aluno->aluno_id, $disciplina->id, $turma->id)->npp ?? ''}}">
                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label class="col-sm-12 col-md-2 col-form-label">NPT</label>
                                                    <div class="col-sm-12 col-md-10">
                                                        <input name="npt" class="form-control" value="{{App\Models\ProvasTrimestre::buscarNotas(session('trimestre_id'), $aluno->aluno_id, $disciplina->id, $turma->id)->npt ?? ''}}">
                                                    </div>
                                                </div>

                                                <input type="hidden" name="aluno_id" value="{{$aluno->aluno_id}}">
                                                <input type="hidden" name="disciplina_id" value="{{$disciplina->id}}">
                                                <input type="hidden" name="turma_id" value="{{$turma->id}}">

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
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>        
    </div>
@endsection
