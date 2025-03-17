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
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/normalize.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/icomoon.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/animate.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/prettyPhoto.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/owl.carousel.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/owl.theme.default.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/transitions.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/color.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive.css') }}">
    <script src="{{ asset('assets/js/vendor/modernizr-2.8.3-respond-1.4.2.min.js') }}"></script>
    <script src="{{ asset('assets/js/vendor/jquery-library.js') }}"></script>
    <script src="{{ asset('assets/js/vendor/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/mapclustering/data.json') }}"></script>
    <script src="{{ asset('assets/js/mapclustering/markerclusterer.min.js') }}"></script>
    <script src="{{ asset('assets/js/mapclustering/infobox.js') }}"></script>
    <script src="{{ asset('assets/js/mapclustering/map.js') }}"></script>
    <script src="{{ asset('assets/js/owl.carousel.min.js') }}"></script>
    <script src="{{ asset('assets/js/isotope.pkgd.js') }}"></script>
    <script src="{{ asset('assets/js/prettyPhoto.js') }}"></script>
    <script src="{{ asset('assets/js/countdown.js') }}"></script>
    <script src="{{ asset('assets/js/collapse.js') }}"></script>
    <script src="{{ asset('assets/js/moment.js') }}"></script>
    <script src="{{ asset('assets/js/gmap3.js') }}"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>
    <script src="{{ asset('assets/js/alert.js') }}"></script>
    <script src="{{ asset('assets/js/sweetalert2.all.min.js') }}"></script>
    <script src="{{ asset('assets/js/inscricao_matricula.js') }}"></script>

</head>

<body class="tg-home tg-homefour">
    <!--[if lt IE 8]>
<p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
<![endif]-->
    <!--****************Wrapper Start*********************-->

    <div id="tg-wrapper" class="tg-wrapper">
        <!--*****************Header Start*******************-->

        @include('home.includes.navbar')
        <!--****************Header End*******************-->

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


            





            
        </script>

        <main id="tg-main" class="tg-main tg-haslayout">
            @yield('content')
        </main>
        <!--**************Main End ***************-->
        
        <!--******************Footer Start***********************-->

        @include('home.includes.footer')

        <!--******************Footer End********************-->

    </div>
    <!--***************Wrapper End*********************-->

    <script>
        function consultarBi(data){
            $.ajax({
                type: "GET",
                url: "/pegar-dados-candidato/" + data.value,
                dataType: "json",
                success: function (response) {
                    if(response.error == 500){
                        return ModalAlerta('alert', response.message, 1);
                    }
                    $("#nome_cand").val(response.FIRST_NAME + " " +response.LAST_NAME)
                    $("#nome_pai").val(response.FATHER_FIRST_NAME + " " + response.FATHER_LAST_NAME)
                    $("#nome_mae").val(response.MOTHER_FIRST_NAME + " " + response.MOTHER_LAST_NAME)
                    $("#provincia").val(response.BIRTH_PROVINCE_NAME)
                    $("#data_nascimento").val(response.BIRTH_DATE)
                    $("#data_emissao_bi").val(response.ISSUE_DATE)
                    $("#municipio").val(response.BIRTH_MUNICIPALITY_NAME)
                    $("#genero").html('<option value="M">'+response.GENDER_NAME+'</option>')
                    $("#local_emissao").val("Direcção Nacional de Identificação")
                    console.log(response);
                },
                error: function (error){
                    console.log(error);
                }
            });
        }
        
        
    </script>

</body>
</html>
