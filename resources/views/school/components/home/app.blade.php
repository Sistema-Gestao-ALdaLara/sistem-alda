<!doctype html>
<html class="no-js" lang="zxx">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>IMISM</title>
    <meta name="description" content="">
    <link rel="apple-touch-icon" sizes="180x180" href="/images/logosimione.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/images/logosimione.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/images/logosimione.png">

    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="apple-touch-icon" href="apple-touch-icon.png">
    <link rel="stylesheet" href="{{asset('assets/css/bootstrap.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/normalize.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/font-awesome.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/icomoon.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/animate.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/prettyPhoto.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/owl.carousel.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/owl.theme.default.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/transitions.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/main.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/color.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/responsive.css')}}">
    <script src="{{asset('assets/js/vendor/modernizr-2.8.3-respond-1.4.2.min.js')}}"></script>
    <script src="{{asset('assets/js/vendor/jquery-library.js')}}"></script>
    <script src="{{asset('assets/js/vendor/bootstrap.min.js')}}"></script>
    <script src="{{asset('assets/js/mapclustering/data.json')}}"></script>
    <script src="{{asset('assets/js/mapclustering/markerclusterer.min.js')}}"></script>
    <script src="{{asset('assets/js/mapclustering/infobox.js')}}"></script>
    <script src="{{asset('assets/js/mapclustering/map.js')}}"></script>
    <script src="{{asset('assets/js/owl.carousel.min.js')}}"></script>
    <script src="{{asset('assets/js/isotope.pkgd.js')}}"></script>
    <script src="{{asset('assets/js/prettyPhoto.js')}}"></script>
    <script src="{{asset('assets/js/countdown.js')}}"></script>
    <script src="{{asset('assets/js/collapse.js')}}"></script>
    <script src="{{asset('assets/js/moment.js')}}"></script>
    <script src="{{asset('assets/js/gmap3.js')}}"></script>
    <script src="{{asset('assets/js/main.js')}}"></script>
    <script src="{{asset('assets/js/alert.js')}}"></script>
    <script src="{{asset('assets/js/sweetalert2.all.min.js')}}"></script>
    <script src="{{asset('assets/js/inscricao_matricula.js')}}"></script>

</head>

<body class="tg-home tg-homefour">
    <!--[if lt IE 8]>
<p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
<![endif]-->
    <!--************************************
        Wrapper Start
*************************************-->
    <div id="tg-wrapper" class="tg-wrapper">
        <!--************************************
            Header Start
    *************************************-->
    @include('home.includes.navbar')
        <!--************************************
            Header End
    *************************************-->
    @include('home.components.modals')   
    <div style="display: none; z-index: 9999999999" id="loading_ajax" class="loading">Loading&#8230;</div>
    @include('home.components.inscricao')
        <script>
            jQuery(document).ready(function($) {

                /* The following adds/removes classes to <html> accordingly */

                $('.modalAbre').on('show.bs.modal', function(e) {
                    $('html').addClass('modal-open');
                })

                $('.modalAbre').on('hide.bs.modal', function(e) {
                    $('html').removeClass('modal-open');
                })

            });


            function adicionarInscricao(isto, e) {

                e.preventDefault();
                e.stopPropagation();

                var form = $(isto);
                var action = $(isto).attr("action");


                $.ajax({
                    type: "POST",
                    url: action,
                    data: form.serialize(),
                    dataType: "json",
                    success: function(data) {

                        $("#err").html("");

                        if (data["estado"] === "ok") {

                            $(isto).find("input").val("");

                            Swal.fire({
                                title: 'Inscrição feita com sucesso!',
                                icon: 'success',
                                confirmButtonColor: '#3085d6',
                                cancelButtonColor: '#d33',
                                confirmButtonText: 'Ok'
                            }).then((result) => {
                                window.location.href = window.location.href
                            });

                        } else {

                            Swal.fire(
                                '',
                                data["texto"],
                                'error'
                            );

                        }



                    },
                    beforeSend: function(xhr) {

                        $("#err").html("" +
                            "" +
                            "<div align='center' class='p-3'>" +
                            "<h5>Carregando...</h5>" +
                            "</div>" +
                            "");

                    },
                    error: function(xhr) {

                        $("#err").html("" +
                            "" +
                            "<div align='center' class='p-3'>" +
                            "<h5>Ocorreu um erro</h5>" +
                            "<p class='text-danger'>Certifica-te que tens acesso a internet</p>" +
                            "</div>" +
                            "");

                    }
                });


            }


            function verificaCV(cv) {
                var fileExtension = cv.split('.').pop();
                return fileExtension === 'pdf';
            }

            function verificaCert(cert) {
                var fileExtension = cert.split('.').pop();
                return fileExtension === 'pdf';
            }


            function uploadBI(e) {




                var botao = $("#enviarCv");
                var area = $("#cv");

                var file_data = e.target.files[0];

                if (file_data.size > 1024 * 1024 * 5) {

                    Swal.fire(
                        '',
                        "O tamanho deste ficheiro é muito grande, por favor escolhe outro",
                        'error'
                    );

                    return;
                }

                if (!verificaCV(file_data.name)) {

                    Swal.fire(
                        '',
                        "O tipo de ficheiro não é suportado, só é permitido o formato pdf",
                        'error'
                    );

                    return;
                }


                var reader = new FileReader();

                area.html("<img src='/images/loader.gif'><br><span id='per'>0%</span>");


                var form_data = new FormData();

                form_data.append("arquivo", file_data);

                $.ajax({
                    url: "/acoes/upload_bi.php",
                    cache: false,
                    contentType: false,
                    processData: false,
                    async: false,
                    data: form_data,
                    type: 'post',
                    dataType: "json",
                    xhr: function() {

                        var xhrobj = new window.XMLHttpRequest();
                        console.log(xhrobj);

                        if (xhrobj.upload) {
                            xhrobj.upload.addEventListener('progress', function(event) {

                                    var percent = 0;
                                    var position = event.loaded || event.position;
                                    var total = event.total || e.totalSize;
                                    if (event.lengthComputable) {

                                        percent = Math.ceil(position / total * 100);
                                        if (percent == 100) {

                                        } else {

                                            // $("#addFotoArea").find("#per").html(percent+"%");
                                            console.log(percent);

                                        }

                                    }
                                },
                                false);
                        }

                        return xhrobj;
                    },
                    success: function(data) {
                        // display image
                        area.html("<div class='media'>" +
                            "<img src='{{asset('assets/images/pdf.png')}}' width='50' height='50' class='mr-3'>" +
                            "<div class='media-body'>" +
                            "" + file_data.name + "<br>" +
                            "<span class='text-success'>Arquivo enviado com sucesso <i class='fa fa-check'></i></span>" +
                            "</div>" +
                            "</div><br>");
                        botao.show();

                        $("#append_cv").html("<input type='hidden' value='" + data["nome"] + "' name='cv'>");

                    }
                });

                e.stopPropagation();
                e.preventDefault();
            }



            function uploadCert(e) {

                var botao = $("#enviarCert");
                var area = $("#cert");

                var file_data = e.target.files[0];

                if (file_data.size > 1024 * 1024 * 5) {

                    Swal.fire(
                        '',
                        "O tamanho deste ficheiro é muito grande, por favor escolhe outro",
                        'error'
                    );

                    return;
                }

                if (!verificaCert(file_data.name)) {

                    Swal.fire(
                        '',
                        "O tipo de ficheiro não é suportado, só é permitido o formato pdf",
                        'error'
                    );

                    return;
                }


                var reader = new FileReader();

                area.html("<img src='/images/loader.gif'><br><span id='per'>0%</span>");


                var form_data = new FormData();

                form_data.append("arquivo", file_data);

                $.ajax({
                    url: "/acoes/upload_certificado.php",
                    cache: false,
                    contentType: false,
                    processData: false,
                    async: false,
                    data: form_data,
                    type: 'post',
                    dataType: "json",
                    xhr: function() {

                        var xhrobj = new window.XMLHttpRequest();
                        console.log(xhrobj);

                        if (xhrobj.upload) {
                            xhrobj.upload.addEventListener('progress', function(event) {

                                    var percent = 0;
                                    var position = event.loaded || event.position;
                                    var total = event.total || e.totalSize;
                                    if (event.lengthComputable) {

                                        percent = Math.ceil(position / total * 100);
                                        if (percent == 100) {

                                        } else {

                                            // $("#addFotoArea").find("#per").html(percent+"%");
                                            console.log(percent);

                                        }

                                    }
                                },
                                false);
                        }

                        return xhrobj;
                    },
                    success: function(data) {
                        // display image
                        area.html("<div class='media'>" +
                            "<img src='{{asset('assets/images/pdf.png')}}' width='50' height='50' class='mr-3'>" +
                            "<div class='media-body'>" +
                            "" + file_data.name + "<br>" +
                            "<span class='text-success'>Arquivo enviado com sucesso <i class='fa fa-check'></i></span>" +
                            "</div>" +
                            "</div><br>");
                        botao.show();

                        $("#append_cert").html("<input type='hidden' value='" + data["nome"] + "' name='cert'>");

                    }
                });

                e.stopPropagation();
                e.preventDefault();
            }




            function uploadFoto(e) {

                var foto = $("#fotoAdd");
                var area = $("#addFotoArea");

                var file_data = e.target.files[0];


                if (file_data.size > 1024 * 1024 * 5) {

                    Swal.fire(
                        '',
                        "O tamanho desta imagem é muito grande, por favor escolhe outra",
                        'error'
                    );

                    return;
                }

                if (!verificaImagem(file_data.name)) {

                    Swal.fire(
                        '',
                        "O tipo de ficheiro não é suportado, só é permitido os formatos png, jpg e jfif",
                        'error'
                    );

                    return;
                }

                var reader = new FileReader();

                foto.hide();
                area.html("<img src='{{asset('assets/images/loader.gif')}}'><br><span id='per'>0%</span>");

                reader.onload = function(ev) {
                    var output = document.getElementById('fotoAdd');
                    output.src = reader.result;
                };

                reader.readAsDataURL(e.target.files[0]);

                var form_data = new FormData();

                form_data.append("foto", file_data);

                $.ajax({
                    url: "/acoes/upload_foto.php",
                    cache: false,
                    contentType: false,
                    processData: false,
                    async: false,
                    data: form_data,
                    type: 'post',
                    dataType: "json",
                    xhr: function() {

                        var xhrobj = new window.XMLHttpRequest();

                        if (xhrobj.upload) {
                            xhrobj.upload.addEventListener('progress', function(event) {

                                    var percent = 0;
                                    var position = event.loaded || event.position;
                                    var total = event.total || e.totalSize;
                                    if (event.lengthComputable) {

                                        percent = Math.ceil(position / total * 100);
                                        if (percent == 100) {

                                        } else {

                                            $("#addFotoArea").find("#per").html(percent + "%");
                                            console.log(percent);

                                        }

                                    }
                                },
                                false);
                        }

                        return xhrobj;
                    },
                    success: function(data) {
                        // display image
                        area.html("");
                        foto.show();

                        $("#addFotoArea").append("<input type='hidden' value='" + data["nome"] + "' name='foto'>");

                    }
                });

                e.stopPropagation();
                e.preventDefault();
            }






            function verificaImagem(nome) {
                var fileExtension = nome.split('.').pop();
                return fileExtension === 'jpg' || fileExtension === 'jpeg' || fileExtension === 'png' || fileExtension ===
                    "jfif" || fileExtension === "JPG" || fileExtension === "PNG" || fileExtension === "JPEG";
            }

            function check_multifile_logo(file) {
                var extension = file.substr((file.lastIndexOf('.') + 1))
                if (extension === 'jpg' || extension === 'jpeg' || extension === 'gif' || extension === 'png' || extension ===
                    'bmp') {
                    return true;
                } else {
                    return false;
                }
            }


            function abreDialog(tipo) {

                if (tipo === 1) {
                    $("#carregar_foto").click();
                } else if (tipo === 2) {
                    $("#carregar_bi").click();
                } else if (tipo === 3) {
                    $("#carregar_cert").click();
                }
            }
        </script>

        <main id="tg-main" class="tg-main tg-haslayout">
          @yield('content')
        </main>
        <!--************************************
        Main End
*************************************-->
        <!--************************************
    Footer Start
  *************************************-->
        @include('home.includes.footer')
        <!--************************************
        Footer End
*************************************-->
    </div>
    <!--************************************
        Wrapper End
*************************************-->

</body>


</html>
