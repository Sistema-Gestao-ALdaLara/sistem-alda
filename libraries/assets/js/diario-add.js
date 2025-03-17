var n = 1

function btn_delete(elem) {
    document.getElementById("sub-conta-" + elem.attributes[1].nodeValue).remove();
    n -= 1;
}

function add_conta() {
    n += 1;
    document.getElementById("contas-all").innerHTML +=
        `
<div id="sub-conta-#${n}" style="margin-top:-25px">
<br>
<div class="form-group row">
    <div class="col-sm-1">
        <div class="col-sm-12">
            <select name="select" class="form-control"
                name="regima_fiscal" id="regime_fiscal">
                <option value="Janeiro">Janeiro</option>
                <option value="Fevereiro">Fevereiro</option>
                <option value="Março">Março</option>
                <option value="Abril">Abril</option>
                <option value="Maio">Maio</option>
                <option value="Junho">Junho</option>
                <option value="Julho">Julho</option>
                <option value="Agosto">Agosto</option>
                <option value="Setembro">Setembro</option>
                <option value="Outubro">Outubro</option>
                <option value="Novembro">Novembro</option>
                <option value="Dezembro">Dezembro</option>
            </select>
        </div>
    </div>   

    <div class="col-sm-1">
        <div class="col-sm-12">
            <select name="select" class="form-control"
                name="regima_fiscal" id="regime_fiscal">
                <option value="Janeiro">2024</option>
                <option value="Fevereiro">2023</option>
                <option value="Março">2022</option>
                <option value="Abril">2021</option>
                <option value="Maio">2020</option>
            </select>
        </div>
    </div>

    <div class="col-sm-1">
        <div class="col-sm-12">
            <select name="select" class="form-control" name="dia" id="dia">
                <option value="01">01</option>
                <option value="02">02</option>
                <option value="03">03</option>
                <option value="04">04</option>
                <option value="05">05</option>
                <option value="06">06</option>
                <option value="07">07</option>
                <option value="08">08</option>
                <option value="09">09</option>
                <option value="10">10</option>
                <option value="11">11</option>
                <option value="12">12</option>
                <option value="13">13</option>
                <option value="14">14</option>
                <option value="15">15</option>
                <option value="16">16</option>
                <option value="17">17</option>
                <option value="18">18</option>
                <option value="19">19</option>
                <option value="20">20</option>
                <option value="21">21</option>
                <option value="22">22</option>
                <option value="23">23</option>
                <option value="24">24</option>
                <option value="25">25</option>
                <option value="26">26</option>
                <option value="27">27</option>
                <option value="28">28</option>
                <option value="29">29</option>
                <option value="30">30</option>
                <option value="31">31</option>
            </select>
        </div>
    </div>

    <div class="col-sm-3">
        <div class="col-sm-12">
            <select name="select" class="form-control" name="regima_fiscal" id="regime_fiscal">
                <option value="opt1">11 - IMOBILIZAÇÕES CORPÓREAS</option>
                <option value="R.Geral">12 - IMOBILIZAÇÕES INCORPÓREAS</option>
                <option value="R.Simplificado">13 - INVESTIMENTOS FINANCEIROS</option>
                <option value="R.Simplificado">14 - IMOBILIZAÇÕES EM CURSO</option>
                <option value="R.Simplificado">18 - AMORTIZAÇÕES ACUMULADAS</option>
                <option value="R.Simplificado">19 - PROVISÕES PARA INVESTIMENTOS FINANCEIROS</option>
                <option value="R.Simplificado">21 - COMPRAS</option>
                <option value="R.Simplificado">22 - MATÉRIAS-PRIMAS SUBSIDIÁRIAS E DE CONSUMO</option>
                <option value="R.Simplificado">35 - ENTIDADES PARTICIPANTES E PARTICIPADAS</option>
                <option value="R.Simplificado">43 - DEPÓSITOS À ORDEM</option>
                <option value="R.Simplificado">44 - OUTROS DEPÓSITOS</option>
                <option value="R.Simplificado">45 - CAIXA</option>
                <option value="R.Simplificado">48 - CONTA TRANSITÓRIA</option>
                <option value="R.Simplificado">49 - PROVISÕES PARA APLICAÇÕES DE TESOURARIA</option>
                <option value="R.Simplificado">51 - CAPITAL</option>
            </select>
        </div>
    </div>    

    <div class="col-sm-2">
        <div class="col-sm-12">
            <select name="select" class="form-control" name="regima_fiscal" id="regime_fiscal">
                <option value="opt1">111002_VENDAS GASOLEO</option>
                <option value="R.Geral">111003_VENDAS GASOLINA</option>
                <option value="R.Simplificado">111003_VENDA PAPEL</option>
                <option value="opt1">121002_MONITOR</option>
                <option value="R.Geral">121003_CANETAS</option>
                <option value="R.Simplificado">121003_COMPUTADOR</option>
                <option value="opt1">121002_MESA DE ESCRITORIO</option>
                <option value="R.Geral">121003_CADERNOS</option>
                <option value="R.Simplificado">3511_ESTADO</option>
                <option value="R.Simplificado">3512_C/SUBSCRIÇÃO</option>
                <option value="R.Simplificado">3514101_WILSON FONSECA DA SILVA</option>
                <option value="R.Simplificado">3514102_LUIZA PASCOAL</option>
                <option value="R.Simplificado">3514103_ÁLGIO PASCOAL</option>
                <option value="R.Simplificado">3514104_SUAMI PASCOAL</option>
                <option value="R.Simplificado">3514105_LENGUE PASCOAL</option>
            </select>
        </div>
    </div>

    <div class="col-sm-2">
        <div class="col-sm-12">
            <input type="text"
                class="form-control autonumber"
                data-a-sep="." data-a-dec=","
                placeholder="0,00">
        </div>
    </div>    

    <div class="col-sm-2">
        <div class="col-sm-12">
            <input type="text"
                class="form-control autonumber"
                data-a-sep="." data-a-dec=","
                placeholder="0,00">
        </div>
    </div>

    <div class="col-sm-1">
        <div class="c">
            <a class="btn btn-danger" value="#${n}"
                onclick="btn_delete(this)">
                <i class="icofont icofont-trash"></i>
            </a>
        </div>
    </div>

</div>

</div>
`;
}