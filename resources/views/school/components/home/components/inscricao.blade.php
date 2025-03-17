<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form onsubmit="adicionarInscricao(this,event)" method="post" class="register-form" id="register-form"
            enctype="multipart/form-data" action="../acoes/cadastrar_inscricao.php">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Formulário de Inscrição</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                    <div class="hidden">
                        <input onchange="uploadFoto(event);" hidden id="carregar_foto" accept="image/*" multiple="false"
                            type="file">
                        <input onchange="uploadBI(event);" hidden id="carregar_bi" accept="application/pdf"
                            multiple="false" type="file">
                        <input onchange="uploadCert(event);" hidden id="carregar_cert" accept="application/pdf"
                            multiple="false" type="file">
                    </div>

                    <div class="text-center">
                        <div id="addFotoArea"></div>
                        <img id="fotoAdd" style="cursor: pointer;" onclick="abreDialog(1);"
                            src="{{ asset('assets/images/authors/1.jpg') }}" width="150" height="150"
                            class="rounded-circle">
                        <br><label>Carregar fotografia (Tipo Passe)</label>
                    </div>
                    <br>


                    <div class="row">
                        <div class="col-md col-sm-12">

                            <div class="row mb-3">
                                <div class="form-group">
                                    <div class=" col-sm-6">
                                        <label for="company" class="required">Nº BI</label>
                                        <input class="form-control" required type="text" minlength="14" maxlength="14"
                                            name="n_bi" id="company" placeholder="Número do BI" />
                                    </div>

                                    <div class=" col-sm-6">
                                        <label for="first_name" class="required">Nome Completo</label>
                                        <input required class="form-control" type="text" name="nome_completo"
                                            id="first_name" placeholder="Nome completo" />
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="form-group">



                                    <div class="col-sm-6">
                                        <label for="company" class="required">Nome do Pai</label>
                                        <input class="form-control" required type="text" name="nome_pai" id="company"
                                            placeholder="Nome do Pai" />
                                    </div>


                                    <div class=" col-sm-6">
                                        <label for="company" class="required">Nome da Mãe</label>
                                        <input class="form-control" required type="text" name="nome_mae" id="company"
                                            placeholder="Nome da Mãe" />
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="form-group">
                                    <div class=" col-sm-6">
                                        <label>Data de Nascimento</label>
                                        <input required class="form-control" name="data_nascimento"
                                            placeholder="Escolher data de nascimento" type="date">
                                    </div>

                                    <div class="col-sm-6">
                                        <label>Data de Validade</label>
                                        <input class="form-control" name="data_validade_bi"
                                            placeholder="Escolher data de validade" type="date">
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="form-group">
                                    <div class=" col-sm-6">
                                        <label for="email" class="required">Endereço Electrônico / Email</label>
                                        <input class="form-control" required type="email" name="endereco_electronico"
                                            id="email" placeholder="Email" />
                                    </div>
                                    <div class="col-sm-6">
                                        <label for="phone_number" class="required">Número de telefone</label>
                                        <input class="form-control" required type="number" minlength="9" min="0"
                                            maxlength="9" name="contacto" id="phone_number"
                                            placeholder="Número de Telefone" />
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="sel1">Genero</label>
                                <select class="form-control" id="sel1" name="genero">
                                    <option value="">Escolher Genero</option>
                                    <option value="Masculino">Masculino</option>
                                    <option value="Feminino">Feminino</option>
                                </select>
                            </div>


                        </div>
                    </div>


                    <div class="row mb-3">
                        <div class="form-group">
                            <div class=" col-sm-6">
                                <label for="phone_number" class="required">Nº Telefone do Encarregado
                                    de Educação</label>
                                <input class="form-control" minlength="9" required type="number" maxlength="9" min="0"
                                    name="contacto_encarregado" placeholder="Contacto do Encarregado" />
                            </div>

                            <div class="col-sm-6">
                                <label class="required">Morada Actual</label>
                                <input class="form-control" required type="text" name="morada_actual"
                                    placeholder="Morada Atual" />
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="form-group">
                            <div class=" col-sm-6">

                                <label class="required">Média da Classificação Final</label>
                                <input class="form-control" required type="number" min="0" name="media_final"
                                    placeholder="Media de Classificação Final" />
                            </div>

                            <div class="col-sm-6">
                                <label class="required">Nota de Língua Portuguesa</label>
                                <input class="form-control" required type="number" min="0" name="lingua_nota"
                                    placeholder="Nota de Língua Portuguesa" />
                            </div>

                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="form-group">
                            <div class=" col-sm-6">
                                <label class="required">Nota de Matemática</label>
                                <input class="form-control" required type="number" min="0" name="mat_nota"
                                    placeholder="Nota de Matemática " />
                            </div>

                            <div class="col-sm-6">
                                <label class="required">Nota de Química</label>
                                <input class="form-control" required type="number" min="0" name="quim_nota"
                                    placeholder="Nota de Química" />
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="form-group">
                            <div class=" col-sm-6">
                                <label>Nota de Física</label>
                                <input class="form-control" required type="number" min="0" name="fis_nota"
                                    placeholder="Nota de Física" />
                            </div>
                            <div class="col-sm-6 form-group">
                                <label>Selecionar 1º curso desejado</label>
                                <select class="form-control" name="curso1">
                                    <option value="1">Tecnico de Informatica</option>

                                    <option value="2">Tecnico de Energia Renovavel</option>

                                    <option value="6">Electromecânica</option>

                                    <option value="7">Técnico de Automação</option>

                                    <option value="10">MatFisica</option>

                                </select>
                            </div>

                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-6 form-group">
                            <label>Selecionar 2º curso desejado</label>
                            <select class="form-control" name="curso2">

                                <option value="1">Tecnico de Informatica</option>

                                <option value="2">Tecnico de Energia Renovavel</option>

                                <option value="6">Electromecânica</option>

                                <option value="7">Técnico de Automação</option>

                                <option value="10">MatFisica</option>

                            </select>
                        </div>

                        <div class="col-sm-6 form-group">
                            <label>Selecionar 3º curso desejado</label>
                            <select class="form-control" name="curso3">

                                <option value="1">Tecnico de Informatica</option>

                                <option value="2">Tecnico de Energia Renovavel</option>

                                <option value="6">Electromecânica</option>

                                <option value="7">Técnico de Automação</option>

                                <option value="10">MatFisica</option>

                            </select>
                        </div>

                    </div>
                    <div class="row">
                        <div class="form-group">

                            <div class="col-md-6">
                                <label class="col-md-6">PDF do BI</label>
                                <div id="cv"></div>
                                <button type="button" onclick="abreDialog(2);" id="enviarCv"
                                    class="btn btn-primary">Carregar BI</button>
                            </div>
                            <div class="col-md-6">
                                <label class="col-md-6">PDF do Certificado</label>
                                <div id="cert"></div>
                                <button type="button" onclick="abreDialog(3);" id="enviarCert"
                                    class="btn btn-primary">Carregar certificado</button>
                            </div>
                        </div>
                    </div>
                    <div id="append_foto">
                    </div>
                    <div id="append_cv">
                    </div>
                    <div id="append_cert">
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
