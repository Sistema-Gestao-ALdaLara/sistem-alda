function ConfirmarMatricula(form, e) {

    e.stopPropagation();
    e.preventDefault();

    $("#custom_error").html("");


    $("#loading_ajax").show();

    $.ajax({
        type:"GET",
        url: "/acoes/conf_matricula.php",
        data: $(form).serialize(),
        dataType: "json",
        success: function(data){



            if(data.estado === "ok") {
                $("#exampleModalMatricula").modal("hide");
                setup(data);
                console.log(data);
                $("#loading_ajax").hide();
            } else {
                $("#exampleModalMatricula").modal("hide");
                $("#custom_error").html("<span class='text-danger'>"+data.texto+"</span>");



            }


        }, beforeSend: function(xhr){



        }, error: function(xhr, err){


            console.log("Erro", err);
            $("#loading_ajax").hide();
            $("#custom_error").html("<span class='text-danger'>Este código não foi encontrado!</span>");


        }
    });
}

function setup(data){

    let modal = $("#exampleModalVer");
    modal.find(".modal-title").html("Matrícula de " + data.c.nome_cand);


    var html = `
        <div class='text-center'>
        <img src="/fotos/${data.c.foto}"  style="width:100px; height: 100px; border-radius: 5px; object-fit: cover;">
        </div>  
        <br>
        
        <div class="row">
            <div class="col-md col-sm-12">
                Sala: ${data.sala} <br>
                Turma: ${data.turma} <br>
                Curso: ${data.curso} <br>
                Regime: ${data.regime} <br>
            </div>
        </div>  
    `;


    modal.find(".modal-body").html(html);
    modal.modal("show");


}

function confirmarInscricao(form, e) {
    e.stopPropagation();
    e.preventDefault();

    $("#loading_ajax").show();

    $.ajax({
        type:"GET",
        url: "/acoes/conf_inscricao.php",
        data: $(form).serialize(),
        dataType: "json",
        success: function(data){


            $("#exampleModalInscricao").modal("hide");
            setup2(data);
            console.log(data);
            $("#loading_ajax").hide();

        }, beforeSend: function(xhr){



        }, error: function(xhr, err){


            console.log("Erro", err);
            $("#loading_ajax").hide();

        }
    });
}

function setup2(data){

    let modal = $("#exampleModalVer");
    modal.find(".modal-title").html("Inscrição de " + data.c.nome_cand);


    var html = `
        <div class='text-center'>
  
        <h3>Foste pré selecionado para o curso ${data.curso}</h3>
                
        <img src="/fotos/${data.c.foto}"  style="width:100px; height: 100px; border-radius: 5px; object-fit: cover;">
        </div>  
        <br>
        
        <div class="row">
            <div class="col-md col-sm-12">
                <div class="form-group">
               
                    
                    <div class="form-input">
                        <label for="first_name" class="required">Nome Completo</label>
                        <input disabled required class="form-control" type="text" value="${data.c.nome_cand}" name="nome_completo" id="first_name" />
                    </div>
    
    
                    <div class="form-group">
                        <label>Data de Nascimento</label>
                        <input disabled required class="form-control" value="${data.c.data_nascimento}" name="data_nascimento" placeholder="Escolher data de nascimento" type="date">
                    </div>
    
    
                 
    
                    <div class="form-input">
                        <label for="company" class="required">Nº BI</label>
                        <input disabled class="form-control" value="${data.c.n_bi}" required type="text" name="n_bi" id="company" />
                    </div>
    
                 
    
    
                    <div class="form-input">
                        <label for="email" class="required">Endereço Electrônico</label>
                        <input disabled class="form-control" required type="email" value="${data.c.endereco_electronico}" name="endereco_electronico" id="email" />
                    </div>
                    <div class="form-input">
                        <label for="phone_number" class="required">Número de telefone</label>
                        <input disabled class="form-control" required type="number" value="${data.c.contacto}" min="0" name="contacto" id="phone_number" />
                    </div>
                    
    
                
    
    
                </div>
            </div>
        </div>  
    `;


    modal.find(".modal-body").html(html);
    modal.modal("show");


}