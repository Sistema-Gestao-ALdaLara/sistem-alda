<div class="left-side-bar">
    <div class="brand-logo">
        <a href="./index">
            <img width="50" height="40" src="{{asset('admin/imagens/logosimione.png')}}" alt="" class="dark-logo">
            <img width="50" height="40" src="{{asset('admin/imagens/logosimione.png')}}" alt="" class="light-logo">
            <div class="ml-2" style="font-size: 30px; font-family: Algerian">
                IMISM
            </div>
        </a>
        <div class="close-sidebar" data-toggle="left-sidebar-close">
            <i class="ion-close-round"></i>
        </div>
    </div>
    <div class="menu-block customscroll">
        <div class="sidebar-menu">
            <ul id="accordion-menu">

                <li>
                    <a href="/" class="dropdown-toggle no-arrow">
                        <span class="micon dw dw-house-1"></span><span class="mtext">Página Inicial</span>
                    </a>
                </li>
               
                @if (session('tipo_usuario') == 1 or session('tipo_usuario') == 2)
                <li class="dropdown">
                    <a href="javascript:;" class="dropdown-toggle">
                        <span class="micon dw dw-user"></span><span class="mtext">Inscrição &
                            Seleção</span>
                    </a>
                    <ul class="submenu">
                        <li><a href="{{route('candidato.inscritos')}}">Cand. Pendentes</a></li>
                        <li><a href="{{route('candidato.selecionados')}}">Cand. Seleccionados</a></li>
                    </ul>
                </li>

                <li>
                    <a href="{{route('aluno.matriculados')}}" class="dropdown-toggle no-arrow">
                        <span class="micon dw dw-file-12"></span><span class="mtext">Matrícula</span>
                    </a>
                </li>
                @endif

                @if (session('tipo_usuario') == 1)

                <li>
                    <a href="{{route('home.trimestre')}}" class="dropdown-toggle no-arrow">
                        <span class="micon dw dw-enter-1"></span><span class="mtext">Trimestre</span>
                    </a>
                </li>

                <li class="dropdown">
                    <a href="javascript:;" class="dropdown-toggle">
                        <span class="micon dw dw-diagram"></span><span class="mtext">Pautas</span>
                    </a>

                    <ul class="submenu">
                        @foreach (App\Models\Curso::listarCursos() as $curso)
                        <li class="dropdown">
                            <a href="javascript:;" class="dropdown-toggle">
                                <span class="micon dw dw-diagram"></span><span class="mtext">{{$curso->nome}}</span>
                            </a>
                            <ul class="submenu">
                                @foreach (App\Models\Turma::listarTurmasDoCurso($curso->id) as $turma)
                                    <li><a href="{{route('pauta.listar-alunos', [$turma->id])}}">{{$turma->turma}}</a></li>
                                @endforeach
                            </ul>
                        </li>
                        @endforeach
                    </ul>
                </li>

                <li>
                    <a href="{{route('home.vagas')}}" class="dropdown-toggle no-arrow">
                        <span class="micon dw dw-enter-1"></span><span class="mtext">Gerir Vagas</span>
                    </a>
                </li>

                <li>
                    <a href="{{route('home.discplinas')}}" class="dropdown-toggle no-arrow">
                        <span class="micon dw dw-book2"></span><span class="mtext">Gerir Disciplinas</span>
                    </a>
                </li>

                <li>
                    <a href="{{route('home.cursos')}}" class="dropdown-toggle no-arrow">
                        <span class="micon dw dw-book2"></span><span class="mtext">Gerir Cursos</span>
                    </a>
                </li>

                <li class="dropdown">
                    <a href="javascript:;" class="dropdown-toggle">
                        <span class="micon dw dw-user"></span><span class="mtext">Associar</span>
                    </a>
                    <ul class="submenu">
                        <li><a href="{{route('home.disciplina-curso')}}">Disciplina & Curso</a></li>
                        <li><a href="{{route('home.professor-disciplina')}}">Professores & Disciplinas</a></li>
                    </ul>
                </li>


                <li>
                    <a href="{{route('home.salas')}}" class="dropdown-toggle no-arrow">
                        <span class="micon dw dw-edit"></span><span class="mtext">Gerir Salas</span>
                    </a>
                </li>


                <li>
                    <a href="{{route('home.turmas')}}" class="dropdown-toggle no-arrow">
                        <span class="micon dw dw-id-card2"></span><span class="mtext">Gerir Turmas</span>
                    </a>
                </li>
                
                @endif


                
                @if (session('tipo_usuario') == 4)
                <li class="dropdown">
                    <a href="javascript:;" class="dropdown-toggle">
                        <span class="micon dw dw-user"></span><span class="mtext">Boletim</span>
                    </a>
                    <ul class="submenu">
                        @if(session('trimestre_id') >= 1)
                            <li>
                                <a target="_blank" href="{{route('boletim', [session('aluno_id'), 1])}}" class="dropdown-toggle no-arrow">
                                    <span class="micon dw dw-id-card2"></span><span class="mtext">Boletim do 1º Trimestre </span>
                                </a>
                            </li>
                        @endif
                        @if(session('trimestre_id') >= 2)
                            <li>
                                <a target="_blank" href="{{route('boletim', [session('aluno_id'), 2])}}" class="dropdown-toggle no-arrow">
                                    <span class="micon dw dw-id-card2"></span><span class="mtext">Boletim do 2º Trimestre </span>
                                </a>
                            </li>
                        @endif
                        @if(session('trimestre_id') >= 3)
                            <li>
                                <a target="_blank" href="{{route('boletim',  [session('aluno_id'), 3])}}" class="dropdown-toggle no-arrow">
                                    <span class="micon dw dw-id-card2"></span><span class="mtext">Boletim do 3º Trimestre </span>
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
                
                <li class="dropdown">
                    <a href="javascript:;" class="dropdown-toggle">
                        <span class="micon dw dw-user"></span><span class="mtext">Relatório de Notas</span>
                    </a>
                    <ul class="submenu">
                        @if(session('trimestre_id') >= 1)
                            <li>
                                <a target="_blank" href="{{route('relatorio-notas', [session('aluno_id'), 1])}}" class="dropdown-toggle no-arrow">
                                    <span class="micon dw dw-id-card2"></span><span class="mtext">Notas do 1º Trimestre </span>
                                </a>
                            </li>
                        @endif
                        @if(session('trimestre_id') >= 2)
                            <li>
                                <a target="_blank" href="{{route('relatorio-notas', [session('aluno_id'), 2])}}" class="dropdown-toggle no-arrow">
                                    <span class="micon dw dw-id-card2"></span><span class="mtext">Notas do 2º Trimestre </span>
                                </a>
                            </li>
                        @endif
                        @if(session('trimestre_id') >= 3)
                            <li>
                                <a target="_blank" href="{{route('relatorio-notas',  [session('aluno_id'), 3])}}" class="dropdown-toggle no-arrow">
                                    <span class="micon dw dw-id-card2"></span><span class="mtext">Notas do 3º Trimestre </span>
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
                
                    @if (session('trimestre_id') >= 3)
                        <li>
                            <a href="{{ route('pauta-final', [session('turma_id')]) }}" class="dropdown-toggle no-arrow" target="_blank"><span class="micon dw dw-id-card2"></span><span class="mtext">Pauta Final </span>
                            </a>
                        </li>
                    @endif
                @endif

                @if (session('tipo_usuario') == 3)
                <li class="dropdown">
                    <a href="javascript:;" class="dropdown-toggle">
                        <span class="micon dw dw-diagram"></span><span class="mtext">Turmas</span>
                    </a>
                    <ul class="submenu">
                        @foreach (App\Models\ProfessorDisciplina::listarTurmas(session('id_usuario')) as $turma)
                            <li><a href="{{route('pauta.listar-alunos', [$turma->turma_id, $turma->disciplina_id])}}">{{$turma->turma}}</a></li>
                        @endforeach
                    </ul>
                </li>
                @endif

                @if (($coordenador = App\Models\Coordenador::pegarCoordenador(session('id_usuario'))) != null)
                <li class="dropdown">
                    <a href="javascript:;" class="dropdown-toggle">
                        <span class="micon dw dw-diagram"></span><span class="mtext">Turmas do Curso</span>
                    </a>
                    <ul class="submenu">
                        @foreach (App\Models\Turma::listarTurmasDoCurso($coordenador->curso_id) as $turma)
                            <li><a href="{{route('pauta.listar-alunos', [$turma->id])}}">{{$turma->turma}}</a></li>
                        @endforeach
                    </ul>
                </li>
                @endif

                @if(session('tipo_usuario') == 1)
                <li>
                    <a href=" {{route('home.funcionarios')}} " class="dropdown-toggle no-arrow">
                        <span class="micon dw dw-add-user"></span><span class="mtext">Gerir
                            Funcionários</span>
                    </a>
                </li>
                
                @endif
            </ul>
        </div>
    </div>
</div>