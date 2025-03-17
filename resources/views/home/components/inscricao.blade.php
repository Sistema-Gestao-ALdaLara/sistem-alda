<!-- Modal -->
{{-- comment --}} <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">

<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">

        <form class="register-form" id="add-inscricao"
            enctype="multipart/form-data" action="{{route('candidato.inscrever')}}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Formulário de Inscrição</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                    <div class="text-center">

                        <div class="input-group mb-3">
                            <div class="custom-file">
                                <input type="file" name="foto" class="custom-file-input" id="inputGroupFile02" accept="image/*">
                                <label class="custom-file-label" for="inputGroupFile02"
                                    aria-describedby="inputGroupFileAddon02">Escolher arquivo</label>
                            </div>
                            <div class="input-group-append">
                                <span class="input-group-text" id="inputGroupFileAddon02">Upload</span>
                            </div>
                        </div>
                        <br><label>Carregar fotografia (Tipo Passe)</label>
                    </div>
                    <br>


                    <div class="row">
                        <div class="col-md col-sm-12">

                            <div class="row mb-3">
                                <div class="form-group">
                                    <div class=" col-sm-4">
                                        <label for="company" class="required">Nº BI</label>
                                        <input class="form-control" required type="text" minlength="14" maxlength="14"
                                            name="n_bi" id="n_bi" placeholder="Número do BI" onBlur="consultarBi(this)" />
                                    </div>

                                    <div class=" col-sm-4">
                                        <label for="first_name" class="required">Nome Completo</label>
                                        <input required class="form-control" type="text" name="nome_cand"
                                             placeholder="Nome completo" id="nome_cand" />
                                    </div>

                                    <div class="col-sm-4">
                                        <label for="company" class="required">Nome do Pai</label>
                                        <input class="form-control" type="text" name="nome_pai" id="nome_pai"
                                            placeholder="Nome do Pai" />
                                    </div>

                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="form-group">


                                    <div class=" col-sm-4">
                                        <label for="company" class="required">Nome da Mãe</label>
                                        <input class="form-control" type="text" name="nome_mae" 
                                            placeholder="Nome da Mãe" id="nome_mae" />
                                    </div>

                                    <div class=" col-sm-4">
                                        <label>Data de Nascimento</label>
                                        <input class="form-control" name="data_nascimento"
                                            placeholder="Escolher data de nascimento" type="date" id="data_nascimento">
                                    </div>

                                    <div class="col-sm-4">
                                        <label>Data de Emissão do B.I</label>
                                        <input class="form-control" name="data_emissao_bi"
                                            placeholder="Escolher data de validade" type="date" id="data_emissao_bi">
                                    </div>

                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="form-group">
                                    <div class=" col-sm-4">
                                        <label for="email" class="required">Email</label>
                                        <input class="form-control" type="email" name="endereco_electronico"
                                            id="email" placeholder="Email" />
                                    </div>
                                    <div class="col-sm-4">
                                        <label for="phone_number" class="required">Número de telefone</label>
                                        <input class="form-control" type="number" minlength="9" min="0" maxlength="9"
                                            name="contacto" id="phone_number" placeholder="Número de Telefone" />
                                    </div>

                                    <div class="col-sm-4">
                                        <label for="sel1">Gênero</label>
                                        <select class="form-control" id="genero" name="genero">
                                            <option value="">Escolher Genero</option>
                                            <option value="M">Masculino</option>
                                            <option value="F">Feminino</option>
                                        </select>
                                    </div>

                                </div>
                            </div>

                        </div>
                    </div>


                    <div class="row mb-3">
                        <div class="form-group">
                            <div class=" col-sm-4">
                                <label for="phone_number" class="required">Nº Telefone do Encarregado</label>
                                <input class="form-control" minlength="9" type="number" maxlength="9" min="0"
                                    name="contacto_encarregado" placeholder="Contacto do Encarregado" />
                            </div>

                            <div class="col-sm-4">
                                <label class="required">Morada Actual</label>
                                <input class="form-control" type="text" name="morada_actual"
                                    placeholder="Morada Atual" />
                            </div>

                            <div class=" col-sm-4">

                                <label class="required">Média da Classificação Final</label>
                                <input class="form-control" required type="number" min="0" name="media_final"
                                    placeholder="Media de Classificação Final" />
                            </div>
                        </div>
                    </div>


                    <div class="row mb-3">
                        <div class="form-group">

                            <div class="col-sm-3">
                                <label class="required">Nota de Língua Portuguesa</label>
                                <input class="form-control" required type="number" min="0" name="lingua_nota"
                                    placeholder="Média Final de Língua Portuguesa" />
                            </div>

                            <div class=" col-sm-3">
                                <label class="required">Nota de Matemática</label>
                                <input class="form-control" required type="number" min="0" name="mat_nota"
                                    placeholder="Média Final de Matemática " />
                            </div>

                            <div class="col-sm-3">
                                <label class="required">Nota de Química</label>
                                <input class="form-control" required type="number" min="0" name="quim_nota"
                                    placeholder="Média Final de Química" />
                            </div>

                            <div class=" col-sm-3">
                                <label>Nota de Física</label>
                                <input class="form-control" required type="number" min="0" name="fis_nota"
                                    placeholder="Média Final de Física" />
                            </div>

                        </div>
                    </div>

                    <div class="row mb-3 mt-3">
                        <div class="col-sm-4 form-group">
                            <label>Selecionar 1º curso desejado</label>
                            <select class="form-control" name="curso1">
                                <option value="">Escolher Curso</option>
                                @foreach (App\Models\Curso::listarCursos() as $curso)
                                    <option value="{{$curso->id}}">{{$curso->nome}}</option>
                                @endforeach
                            </select>
                        </div>

                    
                        <div class="col-sm-4 form-group">
                            <label>Selecionar 2º curso desejado</label>
                            <select class="form-control" name="curso2">
                                <option value="">Escolher Curso</option>
                                @foreach (App\Models\Curso::listarCursos() as $curso)
                                    <option value="{{$curso->id}}">{{$curso->nome}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-sm-4 form-group">
                            <label>Selecionar 3º curso desejado</label>
                            <select class="form-control" name="curso3">
                                <option value="">Escolher Curso</option>
                                @foreach (App\Models\Curso::listarCursos() as $curso)
                                    <option value="{{$curso->id}}">{{$curso->nome}}</option>
                                @endforeach
                            </select>
                        </div>

                    </div>

                    <div class="row">
                        <div class="form-group">

                            <div class="col-md-6">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="bi">Upload do B.I</span>
                                </div>
                                <div class="custom-file">
                                    <input type="file" name="pdf_bi" class="custom-file-input" id="pdf_bi" aria-describedby="bi">
                                    <label class="custom-file-label" for="pdf_bi">Escolher arquivo</label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="pdf_certificado">Upload do Certificado</span>
                                </div>
                                <div class="custom-file">
                                    <input type="file" name="pdf_certificado" class="custom-file-input" id="pdf_certf" aria-describedby="pdf_certificado" accept="pdf">
                                    <label class="custom-file-label" for="pdf_certf">Escolher arquivo</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                    <button type="submit" class="btn btn-primary">Inscrever-se</button>
                </div>
            </div>
        </form>
    </div>
</div>



<script>
    /*$("#add-inscricao").submit(function(e){
        e.preventDefault();
        $.ajax({
            type: "POST",
            url: "{{route('candidato.inscrever')}}",
            data: $("#add-inscricao").serialize(),
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
                ModalAlerta('error',error[0],3);
            }
        });
    })
    */
    
</script>