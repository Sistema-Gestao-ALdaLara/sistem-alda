@extends('school.app')
@section('content')
    <div class="container">
        <div class="page-header">
            <div class="row">
                <div class="col-md-6 col-sm-12">
                    <div class="title">
                        <h4>Gerir Funcionários</h4>
                    </div>
                    <nav aria-label="breadcrumb" role="navigation">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{route('home.painel')}}">Página Inicial</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Gerir Funcionários</li>
                        </ol>
                    </nav>
                </div>

                <div class="col-md-6 col-sm-12 text-right">
                    <a href="javascript:void(0);" class="btn btn-primary" data-toggle="modal"
                        data-target="#funcionario">Adicionar Funcionário</a>
                </div>
            </div>
        </div>

        <div class="page-header">
            <div class="row">
                <div class="col-md-12 col-sm-12">
                    <div align="center">
                        <div class="p-2">
                            <form action="" method="get">
                                <div class="d-flex bd-highlight">
                                    <div class="p-2 flex-grow-1 bd-highlight">
                                        <input value="" type="text" name="query"
                                            placeholder="Pesquisar funcionários por nome, contacto, nº BI, email..."
                                            class="form-control">
                                    </div>
                                    <div class="p-2 bd-highlight">
                                        <input type="submit" value="Pesquisar" class="btn btn-outline-primary"
                                            name="pesquisar">
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="contact-directory-list">
            <ul class="row">
                @foreach ($funcionarios as $funcionario)
                    <li class="col-xl-4 col-lg-4 col-md-6 col-sm-12" id="usuario2">
                        <div class="contact-directory-box">
                            <div class="contact-dire-info text-center">
                                <div class="contact-avatar">
                                    <span>
                                        <img style="object-fit: cover;" src="{{asset('storage/funcionario/'.$funcionario->foto)}}" alt="">
                                    </span>
                                </div>
                                <div class="contact-name">
                                    <h4>{{$funcionario->nome}}</h4>
                                </div>
                                <div class="contact-skill">
                                    <span class="badge badge-pill">{{$funcionario->telefone}}</span>
                                </div>
                                <div class="profile-sort-desc">{{$funcionario->email}}</div>
                            </div>
                            <div class="view-contact">
                                <a href="javascript:void(0);" data-toggle="modal" data-target="#editar-funcionario{{$funcionario->usuario_id}}">Editar</a>
                            </div>
                        </div>
                    </li>

                <!-- Modal de Editar funcionário começa aqui -->
                <div class="modal modalAbre fade" id="editar-funcionario{{$funcionario->usuario_id}}" tabindex="-1" aria-labelledby="exampleModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel">Formulário Editar Funcionário {{$funcionario->nome_usuario}}</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <form enctype="multipart/form-data" method="POST" action="{{route('funcionario.adicionar')}}">
                                @csrf
                                <div class="modal-body">
                                    <div class="form-group row">
                                        <label class="col-sm-12 col-md-2 col-form-label">Carregar Fotografia</label>
                                        <div class="col-sm-12 col-md-10">
                                            <input class="form-control" type="file" name="foto">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-12 col-md-2 col-form-label">Nome Completo</label>
                                        <div class="col-sm-12 col-md-10">
                                            <input required class="form-control" type="text" name="nome_usuario"
                                                placeholder="Nome Completo..." value="{{old('nome_usuario') ?? $funcionario->nome_usuario}}">
                                        </div>
                                    </div>
            
                                    <div class="form-group row">
                                        <label class="col-sm-12 col-md-2 col-form-label">Data de Nascimento</label>
                                        <div class="col-sm-12 col-md-10">
                                            <input required class="form-control" type="date" name="data_nascimento" value="{{old('data_nascimento') ?? $funcionario->data_nascimento}}">
                                        </div>
                                    </div>
            
                                    <div class="form-group row">
                                        <label class="col-sm-12 col-md-2 col-form-label">Endereço</label>
                                        <div class="col-sm-12 col-md-10">
                                            <input required class="form-control" type="text" name="endereco"
                                                placeholder="Endereço do funcionário..." value="{{old('endereco') ?? $funcionario->endereco}}">
                                        </div>
                                    </div>
            
                                    <div class="form-group row">
                                        <label class="col-sm-12 col-md-2 col-form-label">Nacionalidade</label>
                                        <div class="col-sm-12 col-md-10">
                                            <input required class="form-control" type="text" name="nacionalidade"
                                                placeholder="Nacionalidade do Funcionário..." value="{{old('nacionalidade') ?? $funcionario->nacionalidade}}">
                                        </div>
                                    </div>
            
                                    <div class="form-group row">
                                        <label class="col-sm-12 col-md-2 col-form-label">Província</label>
                                        <div class="col-sm-12 col-md-10">
                                            <input required class="form-control" type="text" name="provincia"
                                                placeholder="Natural de..." value="{{old('provincia') ?? $funcionario->provincia}}">
                                        </div>
                                    </div>
            
                                    <div class="form-group row">
                                        <label class="col-sm-12 col-md-2 col-form-label">Nº BI</label>
                                        <div class="col-sm-12 col-md-10">
                                            <input required class="form-control" type="text" name="numero_bi" placeholder="Nº BI..." value="{{old('numero_bi') ?? $funcionario->numero_bi}}">
                                        </div>
                                    </div>
            
                                    <div class="form-group row">
                                        <label class="col-sm-12 col-md-2 col-form-label">Gênero</label>
                                        <div class="col-sm-12 col-md-10">
                                            <div class="custom-control custom-radio mb-5">
                                                <input checked required value="Masculino" type="radio" id="customRadio1" name="sexo"
                                                    class="custom-control-input">
                                                <label class="custom-control-label" for="customRadio1">Masculino</label>
                                            </div>
            
                                            <div class="custom-control custom-radio mb-5">
                                                <input required value="Feminino" type="radio" id="customRadio2" name="sexo"
                                                    class="custom-control-input">
                                                <label class="custom-control-label" for="customRadio2">Feminino</label>
                                            </div>
                                        </div>
                                    </div>
            
                                    <div class="form-group row">
                                        <label class="col-sm-12 col-md-2 col-form-label">Tipo de Funcionário</label>
                                        <div class="col-sm-12 col-md-10">
                                            <select class="custom-select2 form-control" name="tipo_usuario"
                                                style="width: 100%; height: 38px;">
                                                <option value="" >Escolha o tipo de Funcionário</option>
                                                @foreach ($tipos_de_usuarios as $tipo)
                                                    <option value="{{$tipo->id}}">{{$tipo->nome_usuario}}</option>
                                                @endforeach
            
                                            </select>
                                        </div>
                                    </div>
            
                                    <div class="form-group row">
                                        <label class="col-sm-12 col-md-2 col-form-label">Telefone</label>
                                        <div class="col-sm-12 col-md-10">
                                            <input required class="form-control" min="0" type="tel" name="telefone"
                                                placeholder="Telefone..." value="{{old('telefone') ?? $funcionario->telefone}}">
                                        </div>
                                    </div>
            
                                    <div class="form-group row">
                                        <label class="col-sm-12 col-md-2 col-form-label">Email</label>
                                        <div class="col-sm-12 col-md-10">
                                            <input required class="form-control" type="email" name="email" placeholder="Email..." value="{{old('email') ?? $funcionario->email}}">
                                        </div>
                                    </div>
            
                                    <div class="form-group row">
                                        <label class="col-sm-12 col-md-2 col-form-label">Palavra-passe</label>
                                        <div class="col-sm-12 col-md-10">
                                            <input required class="form-control" type="password" name="senha"
                                                placeholder="Palavra-passe..." value="{{old('senha') ?? $funcionario->senha}}">
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
                <!--Modal de Editar funcionário termina aqui -->
                @endforeach
            </ul>
        </div>
    </div>



    <!-- Modal de Adicionar funcionario começa aqui -->
    <div class="modal fade" id="funcionario" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Formulário Adicionar Funcionário</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form enctype="multipart/form-data" method="POST" action="{{route('funcionario.adicionar')}}">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group row">
                            <label class="col-sm-12 col-md-2 col-form-label">Carregar Fotografia</label>
                            <div class="col-sm-12 col-md-10">
                                <input class="form-control" type="file" name="foto">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-12 col-md-2 col-form-label">Nome Completo</label>
                            <div class="col-sm-12 col-md-10">
                                <input required class="form-control" type="text" name="nome_usuario"
                                    placeholder="Nome Completo..." value="{{old('nome_usuario') ?? ''}}">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-12 col-md-2 col-form-label">Data de Nascimento</label>
                            <div class="col-sm-12 col-md-10">
                                <input required class="form-control" type="date" name="data_nascimento" value="{{old('data_nascimento') ?? ''}}">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-12 col-md-2 col-form-label">Endereço</label>
                            <div class="col-sm-12 col-md-10">
                                <input required class="form-control" type="text" name="endereco"
                                    placeholder="Endereço do funcionário..." value="{{old('endereco') ?? ''}}">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-12 col-md-2 col-form-label">Nacionalidade</label>
                            <div class="col-sm-12 col-md-10">
                                <input required class="form-control" type="text" name="nacionalidade"
                                    placeholder="Nacionalidade do Funcionário..." value="{{old('nacionalidade') ?? ''}}">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-12 col-md-2 col-form-label">Província</label>
                            <div class="col-sm-12 col-md-10">
                                <input required class="form-control" type="text" name="provincia"
                                    placeholder="Natural de..." value="{{old('provincia') ?? ''}}">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-12 col-md-2 col-form-label">Nº BI</label>
                            <div class="col-sm-12 col-md-10">
                                <input required class="form-control" type="text" name="numero_bi" placeholder="Nº BI..." value="{{old('numero_bi') ?? ''}}">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-12 col-md-2 col-form-label">Gênero</label>
                            <div class="col-sm-12 col-md-10">
                                <div class="custom-control custom-radio mb-5">
                                    <input checked required value="Masculino" type="radio" id="customRadio1" name="sexo"
                                        class="custom-control-input">
                                    <label class="custom-control-label" for="customRadio1">Masculino</label>
                                </div>

                                <div class="custom-control custom-radio mb-5">
                                    <input required value="Feminino" type="radio" id="customRadio2" name="sexo"
                                        class="custom-control-input">
                                    <label class="custom-control-label" for="customRadio2">Feminino</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-12 col-md-2 col-form-label">Tipo de Funcionário</label>
                            <div class="col-sm-12 col-md-10">
                                <select class="custom-select2 form-control" name="tipo_usuario"
                                    style="width: 100%; height: 38px;">
                                    <option value="" >Escolha o tipo de Funcionário</option>
                                    @foreach ($tipos_de_usuarios as $tipo)
                                        <option value="{{$tipo->id}}">{{$tipo->nome_usuario}}</option>
                                    @endforeach

                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-12 col-md-2 col-form-label">Telefone</label>
                            <div class="col-sm-12 col-md-10">
                                <input required class="form-control" min="0" type="tel" name="telefone"
                                    placeholder="Telefone..." value="{{old('telefone') ?? ''}}">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-12 col-md-2 col-form-label">Email</label>
                            <div class="col-sm-12 col-md-10">
                                <input required class="form-control" type="email" name="email" placeholder="Email..." value="{{old('email') ?? ''}}">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-12 col-md-2 col-form-label">Palavra-passe</label>
                            <div class="col-sm-12 col-md-10">
                                <input required class="form-control" type="password" name="senha"
                                    placeholder="Palavra-passe..." value="{{old('senha') ?? ''}}">
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
@endsection
