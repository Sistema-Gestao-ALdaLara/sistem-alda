@extends('school.app')
@section('content')
    <div class="min-height-200px">
        <div class="page-header">
            <div class="row">
                <div class="col-md-6 col-sm-12">
                    <div class="title">
                        <h4>Gerir Matrícula</h4>
                    </div>
                    <nav aria-label="breadcrumb" role="navigation">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home.painel') }}">Página Inicial</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Alunos Matriculados</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <!-- Simple Datatable start -->
        <div class="card-box mb-30">
            <br>
            <div class="pb-20">
                <table id="data-table" class=" table stripe hover nowrap">
                    <thead>
                        <tr>
                            <th class="table-plus datatable-nosort">Nome</th>
                            <th>Nº Processo</th>
                            <th>Idade</th>
                            <th>Curso</th>
                            <th>Usuário</th>
                            <th>Data da Matrícula</th>
                            <th class="datatable-nosort">Acção</th>
                        </tr>
                    </thead>
                    <tbody id="tbody">
                        @foreach ($matriculados as $aluno)
                            <tr>
                                <td class="table-plus">{{ $aluno->nome_cand }}</td>
                                <td>{{ $aluno->numero_processo }}</td>
                                <td>{{ App\Utils\Auxiliar::diffYear($aluno->data_nascimento) }}</td>
                                <td>{{ App\Models\Curso::pegarCurso($aluno->curso_id)->nome }}</td>
                                <td>{{ App\Models\Usuario::retornaNomeUsuario($aluno->usuario_id) }}
                                </td>
                                <td>{{ date_format(date_create($aluno->data_matricula), 'd-m-Y H:i') }}</td>
                                <td>
                                    <div class="dropdown">
                                        <a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle" href="#"
                                            role="button" data-toggle="dropdown">
                                            <i class="dw dw-more"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                                            <a class="dropdown-item" href="#" data-toggle="modal"
                                                data-target="#exampleModal"><i
                                                    class="dw dw-eye"></i> Mais
                                                Informações</a>
                                        @if(session('tipo_usuario') == 1)
                                            <a class="dropdown-item" href="#" data-toggle="modal" data-target="#ModalTurma{{$aluno->id}}"><i
                                                    class="dw dw-eye"></i> Definir Turma</a>
                                        @endif
                                        </div>
                                    </div>
                                </td>


                                {{-- MODAL PARA MATRICULAR ALUNO --}}
                                <div class="modal fade" id="ModalTurma{{$aluno->id}}" tabindex="-1"
                                    aria-labelledby="ModalTurmaLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="ModalTurmaLabel">Definir Turma para o Aluno <b>{{$aluno->nome_cand}}</b>
                                                </h5>
                                                <button type="button" class="close" data-dismiss="modal"
                                                    aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>

                                            <form id="add-turma" method="POST" action="{{route('aluno.definir-turma')}}">
                                                @csrf
                                                <div class="modal-body">

                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-md-2 col-form-label">Escolher
                                                            Turma</label>
                                                        <div class="col-sm-12 col-md-10">
                                                            <select name="turma_id" class="form-control">
                                                                @foreach((App\Models\Turma::listarTurmasDaClasseDoCurso(1, $aluno->curso_id)) as $turma)
                                                                    <option value="{{$turma->id}}">{{$turma->turma}}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <input type="hidden" name="aluno_id" value="{{$aluno->id}}">
                                                    <input type="hidden" name="candidato_id" value="{{$aluno->candidato_id}}">

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
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>


        </div>
        <!-- Simple Datatable End -->

    @endsection
