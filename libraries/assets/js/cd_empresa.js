document.getElementById('btn-cadastrar').addEventListener('click', function() {
    var empresa = document.getElementById('nome_empresa')
    var nif = document.getElementById('nif')
    var objecto_social = document.getElementById('objecto_social')
    var data_constituicao = document.getElementById('data_constituicao')
    var regime_fiscal = document.getElementById('regime_fiscal')
    var email = document.getElementById('email')
    var representante = document.getElementById('representante')
    var tel1 = document.getElementById('tel1')
    var tel2 = document.getElementById('tel2')
    var country = document.getElementById('pais')
    var endereco = document.getElementById('endereco')
    var capital = document.getElementById('capital_social')
    var tisl = document.getElementById('tisl')

    console.log(empresa.value)
    console.log(nif.value)
    console.log(objecto_social.value)
    console.log(data_constituicao.value)
    console.log(regime_fiscal.value)
    console.log(email.value)
    console.log(parseFloat(capital.value))
    console.log(tisl.value/100)

    $.ajax({
        method:'post',
        crossDomain:true,
        url:'process/cd_empresa.php',
        data:{
            nome_empresa:empresa.value,
            nif:nif.value,
            regime_fiscal:regime_fiscal.value,
            capital_social:capital.value,
            representante:representante.value,
            data_constituicao:data_constituicao.value,
            tisl:tisl.value,
            objecto_social:objecto_social.value,
            criar:true,
        },
        complete: (data) => {
            console.log(data.responseText)
        }
    })
})