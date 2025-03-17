@extends('school.app')
@section('content')
    <div class="min-height-200px">
        <div class="page-header">
            <div class="row">
                <div class="col-md-6 col-sm-12">
                    <div class="title">
                        <h4>Gerir Inscrição</h4>
                    </div>
                    <nav aria-label="breadcrumb" role="navigation">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home.painel') }}">Página Inicial</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Candidatos Seleccionados</li>
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
                            <th>Idade</th>
                            <th>Média</th>
                            <th>Curso</th>
                            <th>Usuário</th>
                            <th>Data de Seleção</th>
                            <th class="datatable-nosort">Acção</th>
                        </tr>
                    </thead>
                    <tbody id="tbody">
                        @foreach ($selecionados as $candidato)
                            <tr>
                                <td class="table-plus">{{ $candidato->nome_cand }}</td>
                                <td>{{ App\Utils\Auxiliar::diffYear($candidato->data_nascimento) }}</td>
                                <td>{{ $candidato->media_final }}</td>
                                <td>{{ App\Models\Curso::pegarCurso($candidato->curso_id)->nome }}</td>
                                <td>{{ App\Models\Usuario::retornaNomeUsuario($candidato->usuario_id) ?? 'Pré-Seleccionado' }}
                                </td>
                                <td>{{ date_format(date_create($candidato->data_selecao), 'd-m-Y H:i') }}</td>
                                <td>
                                    <div class="dropdown">
                                        <a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle" href="#"
                                            role="button" data-toggle="dropdown">
                                            <i class="dw dw-more"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                                            <a class="dropdown-item" href="#" data-toggle="modal"
                                                data-target="#exampleModal{{ $candidato->candidato_id }}"><i
                                                    class="dw dw-eye"></i> Mais
                                                Informações</a>

                                            @if(session('tipo_usuario') == 2)
                                                <a class="dropdown-item" href="{{route('candidato.matricular', [$candidato->candidato_id])}}"><i
                                                    class="dw dw-eye"></i> Matricular</a>
                                            @endif
                                        </div>
                                    </div>
                                </td>




                                <!--Modal de ver Informações -->

                                <!-- Modal -->
                                <div class="modal fade" id="exampleModal{{ $candidato->candidato_id }}" tabindex="-1"
                                    aria-labelledby="exampleModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <form method="post" enctype="multipart/form-data">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="exampleModalLabel">Perfil de
                                                        {{ $candidato->nome_cand }}
                                                    </h5>
                                                    <button type="button" class="close" data-dismiss="modal"
                                                        aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div id="recusar" class="modal-body">
                                                    <div class="media">
                                                        <img src="{{ asset('storage/candidato/img/' . $candidato->foto) }}"
                                                            width="100" height="100" style="object-fit: cover"
                                                            class="rounded-circle mr-3">
                                                        <div class="media-body">
                                                            {{ $candidato->nome_cand }}<br>
                                                            {{ $candidato->contacto }}<br>
                                                            {{ App\Utils\Auxiliar::diffYear($candidato->data_nascimento) }}
                                                            anos de idade<br>

                                                        </div>
                                                    </div>
                                                </div>

                                                <br>
                                                <ul class="list-group">
                                                    <li class="list-group-item active bg-primary border-0">Mais Informações
                                                    </li>
                                                    <li class="list-group-item">BI:
                                                        <b><span>{{ $candidato->n_bi }}</span></b> </li>
                                                    <li class="list-group-item">Data de validade do BI:
                                                        <b><span>{{ App\Utils\Auxiliar::retornaDataValidadeBI($candidato->data_emissao_bi) }}</span></b>
                                                    </li>
                                                    <li class="list-group-item">Gênero:
                                                        <b><span>{{ App\Utils\Auxiliar::retornaNomeGenero($candidato->genero) }}</span></b>
                                                    </li>
                                                    <li class="list-group-item">Endereço Electrônico:
                                                        <b><span>{{ $candidato->endereco_electronico }}</span></b>
                                                    </li>
                                                    <li class="list-group-item">Data de Nascimento:
                                                        <b><span>{{ date_format(date_create($candidato->data_nascimento), 'd-m-Y') }}</span></b>
                                                    </li>
                                                    <li class="list-group-item">Média de Matemática:
                                                        <b><span>{{ $candidato->mat_nota }}</span></b>
                                                    </li>
                                                    <li class="list-group-item">Média de Química:
                                                        <b><span>{{ $candidato->quim_nota }}</span></b> </li>
                                                    <li class="list-group-item">Média de Física:
                                                        <b><span>{{ $candidato->fis_nota }}</span></b> </li>
                                                    <li class="list-group-item">Média de Língua Portuguesa:
                                                        <b><span>{{ $candidato->lingua_nota }}</span></b>
                                                    </li>
                                                    <li class="list-group-item">Média Nuclear:
                                                        <b><span>{{ $candidato->media_final }}</span></b> </li>
                                                    <li class="list-group-item">Morada Actual:
                                                        <b><span>{{ $candidato->morada_actual }}</span></b> </li>
                                                    <li class="list-group-item">Nome do Pai:
                                                        <b><span>{{ $candidato->nome_pai }}</span></b>
                                                    </li>
                                                    <li class="list-group-item">Nome da Mãe:
                                                        <b><span>{{ $candidato->nome_mae }}</span></b>
                                                    </li>
                                                    <li class="list-group-item">Contacto do Encarregado:
                                                        <b><span>{{ $candidato->contacto_encarregado }}</span></b>
                                                    </li>
                                                    <li class="list-group-item"> Cursos Escolhidos:
                                                        <b>
                                                            <p>1ª Opção:
                                                                {{ App\Models\Curso::pegarCurso($candidato->curso1)->nome }}
                                                            </p>
                                                            <p>2ª Opção:
                                                                {{ App\Models\Curso::pegarCurso($candidato->curso2)->nome }}
                                                            </p>
                                                            <p>3ª Opção:
                                                                {{ App\Models\Curso::pegarCurso($candidato->curso3)->nome }}
                                                            </p>
                                                        </b>
                                                    </li>

                                                </ul>

                                                <div class="row text-center mt-4 mb-4">
                                                    <div class="col">
                                                        <a target="_blank"
                                                            href="{{ asset('storage/candidato/pdfs/' . $candidato->upload_cert) }}"
                                                            class="btn btn-outline-success">Ver Certificado de
                                                            Habilitação</a>
                                                    </div>
                                                    <div class="col">
                                                        <a target="_blank"
                                                            href="{{ asset('storage/candidato/pdfs/' . $candidato->upload_bi) }}"
                                                            class="btn btn-outline-success">Ver Bilhete de Identidade</a>
                                                    </div>
                                                </div>

                                                <div class="modal-footer">
                                                    <button type="submit" class="btn btn-secondary"
                                                        data-dismiss="modal">Fechar</button>

                                                    @if (session('tipo_usuario') == 1)
                                                        <a href="#" class="btn btn-danger" data-toggle="modal" data-dismiss="modal" data-target="#eliminar{{$candidato->candidato_id}}" >Remover Selecionado</a>
                                                    @endif

                                                    @if(session('tipo_usuario') == 2)
                                                        <a href="javascript:void(0);" data-dismiss="modal" class="btn btn-success" data-toggle="modal" data-target="#exampleMatricula{{$candidato->candidato_id}}">Matricular</a>
                                                    @endif

                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>


                                {{-- MODAL PARA Eliminar CANDIDATO --}}
                                <div id="eliminar{{$candidato->candidato_id}}" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
                                    <div class="modal-dialog modal-md">
                                        <div class="modal-content">
                                            <div class="modal-body p-4">
                                                <div class="text-center">
                                                    <i class="dripicons-warning h1 text-warning"></i>
                                                    <h4 class="mt-2"> Deseja mesmo remover o Candidato {{$candidato->nome_cand}} da lista de seleccionados?</h4>
                                                        <a href="#" class="btn btn-primary" data-dismiss="modal">Cancelar</a>
                                                        <a href="{{ route('candidato.selecionado.remover', $candidato->candidato_id)}}" class="btn btn-danger my-2">Sim, Eliminar</a>
                                                </div>
                                            </div>
                                        </div><!-- /.modal-content -->
                                    </div><!-- /.modal-dialog -->
                                </div>
                                <!--Modal de Adicionar coordenador termina aqui -->

                                {{-- MODAL PARA MATRICULAR ALUNO --}}
                                <div class="modal fade" id="exampleMatricula{{$candidato->candidato_id}}" tabindex="-1"
                                    aria-labelledby="exampleMatriculaLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="exampleMatriculaLabel">Formulário Matricular Candidato
                                                </h5>
                                                <button type="button" class="close" data-dismiss="modal"
                                                    aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>


                                            <form enctype="multipart/form-data" id="add-matricula">
                                                @csrf
                                                <div class="modal-body">

                                                    <div class="form-group row">
                                                        <label class="col-sm-12 col-md-2 col-form-label">Escolher
                                                            Turma</label>
                                                        <div class="col-sm-12 col-md-10">
                                                            <select name="turma_id" class="form-control">
                                                                @foreach (App\Models\Turma::listarTurmasDoCurso($candidato->curso1) as $turma)
                                                                    <option value="{{$turma->id}}">{{$turma->turma}}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <input type="hidden" name="candidato_id" value="{{$candidato->candidato_id}}">
                                                    <input type="hidden" name="usuario_id" value="{{session('id_usuario')}}">
                                                    <input type="hidden" name="">
                                                    <input type="hidden" name="">
                                                    <input type="hidden" name="">
                                                    <input type="hidden" name="">


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
    @push('js')
    <script>
        /*
        $("#add-matricula").submit(function(e){
            e.preventDefault();
            $.ajax({
                type: "POST",
                url: "{{route('candidato.matricular')}}",
                data: $("#add-matricula").serialize(),
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
                    setTimeout(function(){
                        window.location.href = "{{route('candidato.selecionados')}}"}, 2500
                    );
                }
            });
        }); **/
    </script>
    @endpush
    @endsection
