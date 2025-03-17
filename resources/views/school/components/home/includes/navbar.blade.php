<header id="tg-header" class="tg-header tg-headervtwo tg-headervthree tg-haslayout">
    <div class="tg-topbar">
        <div class="container">
            <div class="row">
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                    <ul class="tg-addressinfo">
                        <li>
                            <i class="icon-map-marker"></i>
                            <address>Bairro Prenda, Rua dos Funantes</address>
                        </li>
                        <li>

                            <div class="tg-leftbox">
                                <i class="icon-clock"></i>
                                <script LANGUAGE="JAVASCRIPT">
                                    var now = new Date();
                                    var mName = now.getMonth() + 1;
                                    var dName = now.getDay() + 1;
                                    var dayNr = now.getDate();
                                    var yearNr = now.getYear();
                                    if (dName == 1) {
                                        Day = "Domingo";
                                    }
                                    if (dName == 2) {
                                        Day = "Segunda-feira";
                                    }
                                    if (dName == 3) {
                                        Day = "Terça-feira";
                                    }
                                    if (dName == 4) {
                                        Day = "Quarta-feira";
                                    }
                                    if (dName == 5) {
                                        Day = "Quinta-feira";
                                    }
                                    if (dName == 6) {
                                        Day = "Sexta-feira";
                                    }
                                    if (dName == 7) {
                                        Day = "Sábado";
                                    }
                                    if (mName == 1) {
                                        Month = "Janeiro";
                                    }
                                    if (mName == 2) {
                                        Month = "Fevereiro";
                                    }
                                    if (mName == 3) {
                                        Month = "Março";
                                    }
                                    if (mName == 4) {
                                        Month = "Abril";
                                    }
                                    if (mName == 5) {
                                        Month = "Maio";
                                    }
                                    if (mName == 6) {
                                        Month = "Junho";
                                    }
                                    if (mName == 7) {
                                        Month = "Julho";
                                    }
                                    if (mName == 8) {
                                        Month = "Agosto";
                                    }
                                    if (mName == 9) {
                                        Month = "Setembro";
                                    }
                                    if (mName == 10) {
                                        Month = "Outubro";
                                    }
                                    if (mName == 11) {
                                        Month = "Novembro";
                                    }
                                    if (mName == 12) {
                                        Month = "Dezembro";
                                    }
                                    if (yearNr < 2000) {
                                        Year = 1900 + yearNr;
                                    } else {
                                        Year = yearNr;
                                    }
                                    var todaysDate = (" <font class='cor'> " + Day + ", " + dayNr + " " + Month + " " + Year + "</font>");

                                    document.write('  ' + todaysDate);




                                    //-->
                                </script>


                                <style>
                                    .cor {
                                        color: #ff8400;
                                    }

                                    .corHora {
                                        color: #f2bf14;
                                    }

                                </style>

                                <SPAN class="corHora" ID="Clock">| 00:00:00</SPAN>

                                <script LANGUAGE="JavaScript">
                                    <!--
                                    var Elem = document.getElementById("Clock");

                                    function Horario() {
                                        var Hoje = new Date();
                                        var Horas = Hoje.getHours();
                                        if (Horas < 10) {
                                            Horas = "0" + Horas;
                                        }
                                        var Minutos = Hoje.getMinutes();
                                        if (Minutos < 10) {
                                            Minutos = "0" + Minutos;
                                        }
                                        var Segundos = Hoje.getSeconds();
                                        if (Segundos < 10) {
                                            Segundos = "0" + Segundos;
                                        }
                                        Elem.innerHTML = "| " + Horas + ":" + Minutos + ":" + Segundos;
                                    }
                                    window.setInterval("Horario()", 1000);
                                    //
                                    -->
                                </script>
                            </div>
                        </li>
                        <li>
                            <i class="icon-phone-handset"></i>
                            <span>222 737 234</span>
                        </li>

                    </ul>

                    <div class="tg-themedropdown tg-languagesdropdown">
                        <a href="/admin/index" class="tg-btndropdown">
                            <span><i class="fa fa-user"></i></span>
                            <span>Área Admin</span>
                        </a>

                    </div>

                </div>
            </div>
        </div>
    </div>
    <div class="clearfix"></div>
    <div class="container">
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <div class="tg-navigationarea">
                    <strong class="tg-logo"><a href="index"><img src="{{asset('assets/images/200%20x%2060(1)(1).png')}}"
                                alt="Instituto Médio Industrial Simione Mucune"></a></strong>
                    <div class="tg-navigationandsearch">
                        <nav id="tg-nav" class="tg-nav">
                            <div class="navbar-header">
                                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse"
                                    data-target="#tg-navigation" aria-expanded="false">
                                    <span class="sr-only">Toggle navigation</span>
                                    <span class="icon-bar"></span>
                                    <span class="icon-bar"></span>
                                    <span class="icon-bar"></span>
                                </button>
                            </div>
                            <div id="tg-navigation" class="collapse navbar-collapse tg-navigation">
                                <ul>
                                    <li>
                                        <a href="./index">Início</a>
                                    </li>
                                    <li>
                                        <a href="cursos">Cursos</a>
                                    </li>
                                    <li>
                                        <a href="eventos">Eventos</a>
                                    </li>

                                    <li><a href="noticias">Notícias</a></li>
                                    <li><a href="admissao">Admissão</a></li>
                                    <li><a data-toggle="modal" data-target="#exampleModal"
                                            href="javascript:void(0);">Inscrever-se</a></li>


                                    <li class="menu-item-has-children">
                                        <a href="javascript:void(0);">Verificar</a>
                                        <ul class="sub-menu">
                                            <li><a data-toggle="modal" data-target="#exampleModalInscricao"
                                                    href="javascript:void(0);">Inscrição</a></li>
                                            <li><a data-toggle="modal" data-target="#exampleModalMatricula"
                                                    href="#">Matrícula</a></li>
                                        </ul>
                                    </li>

                                    <li>
                                        <a href="contactos">Contacte-nos</a>
                                    </li>

                                    <li class="menu-item-has-children">
                                        <a href="javascript:void(0);">Sobre</a>
                                        <ul class="sub-menu">
                                            <li class="menu-item-has-children">
                                                <a href="javascript:void(0);">Sobre IMISM</a>
                                                <ul class="sub-menu">
                                                    <li><a href="mensagemdirector">Mensagem do Director</a></li>
                                                    <li><a href="nossoobjectivo">Nosso Objectivo</a></li>
                                                    <li><a href="nossaidentidade">Nossa Identidade</a></li>
                                                    <li><a href="nossamissaoevisao.php">Nossa Missão &amp;
                                                            Visão</a></li>
                                                    <li><a href="nossahistoria">Nossa História</a></li>
                                                </ul>
                                            </li>
                                            <li class="menu-item-has-children">
                                                <a href="javascript:void(0);">Outros</a>
                                                <ul class="sub-menu">
                                                    <li><a href="galeria">Galeria</a></li>
                                                </ul>
                                            </li>
                                        </ul>
                                    </li>
                                </ul>
                            </div>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>