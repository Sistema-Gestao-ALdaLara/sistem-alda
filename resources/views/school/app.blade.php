<!DOCTYPE html>
<html>

<head>
    <!-- Basic Page Info -->
    <meta charset="utf-8">
    <title>Página Inicial</title>

    <!-- Site favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="/images/logosimione.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/images/logosimione.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/images/logosimione.png">

    <!-- Mobile Specific Metas -->
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <!-- CSS -->
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/vendors/styles/core.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/vendors/styles/icon-font.min.css') }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('admin/src/plugins/jvectormap/jquery-jvectormap-2.0.3.css') }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('admin/src/plugins/jquery-asColorPicker/dist/css/asColorPicker.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <!-- CSS -->
    <!-- switchery css -->
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/src/plugins/switchery/switchery.min.css') }}">
    <!-- bootstrap-tagsinput css -->
    <link rel="stylesheet" type="text/css"
        href="{{ asset('admin/src/plugins/bootstrap-tagsinput/bootstrap-tagsinput.css') }}">
    <!-- bootstrap-touchspin css -->
    <link rel="stylesheet" type="text/css"
        href="{{ asset('admin/src/plugins/bootstrap-touchspin/jquery.bootstrap-touchspin.css') }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('admin/src/plugins/datatables/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('admin/src/plugins/datatables/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/vendors/styles/style.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/src/plugins/sweetalert2/sweetalert2.css') }}">
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());

        gtag('config', 'UA-119386393-1');
    </script>


</head>

<body>

    <div class="header">

        <div class="header-left">
            <div class="menu-icon dw dw-menu"></div>
            <div class="mr-auto">
                <style>
                    .cor {
                        color: #ff8400;
                    }

                    .corHora {
                        color: #f2bf14;
                    }

                </style>

                <SPAN class="corHora" ID="Clock"> 00:00:00</SPAN>

                <script LANGUAGE="JavaScript">
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
                </script>
            </div>

        </div>
        @include('school.includes.navbar')
        @include('school.includes.left-bar')
        <div class="mobile-menu-overlay"></div>

        <div class="main-container">
            <div class="xs-pd-20-10 pd-ltr-20">
                @if ($errors->any())
                    @foreach ($errors->all() as $error)
                        <div class="alert alert-danger" role="alert">
                            <span>{!! $error !!}</span>
                        </div>
                    @endforeach
                @endif

                @if ($sms = Session::get('success'))
                    <div class="alert alert-success" role="alert">
                        {!! $sms !!}
                    </div>
                @endif
                @yield('content')
                <div class="footer-wrap pd-20 mb-20 card-box">
                    2021-2022 - INSTITUTO MÉDIO INDUSTRIAL SIMIONE MUCUNE
                </div>
            </div>
        </div>
        <!-- js -->

        <script src="{{ asset('admin/vendors/scripts/core.js') }}"></script>
        <script src="{{ asset('admin/vendors/scripts/script.min.js') }}"></script>
        <script src="{{ asset('admin/vendors/scripts/process.js') }}"></script>
        <script src="{{ asset('admin/vendors/scripts/layout-settings.js') }}"></script>
        <script src="{{ asset('admin/src/plugins/jQuery-Knob-master/jquery.knob.min.js') }}"></script>
        <script src="{{ asset('admin/src/plugins/highcharts-6.0.7/code/highcharts.js') }}"></script>
        <script src="{{ asset('admin/src/plugins/highcharts-6.0.7/code/highcharts-more.js') }}"></script>
        <script src="{{ asset('admin/src/plugins/jvectormap/jquery-jvectormap-2.0.3.min.js') }}"></script>
        <script src="{{ asset('admin/src/plugins/jvectormap/jquery-jvectormap-world-mill-en.js') }}"></script>
        <script src="{{ asset('admin/vendors/scripts/dashboard2.js') }}"></script>
        <script src="{{ asset('admin/src/plugins/jquery-asColor/dist/jquery-asColor.js') }}"></script>
        <script src="{{ asset('admin/src/plugins/jquery-asGradient/dist/jquery-asGradient.js') }}"></script>
        <script src="{{ asset('admin/src/plugins/jquery-asColorPicker/jquery-asColorPicker.js') }}"></script>
        <script src="{{ asset('admin/vendors/scripts/colorpicker.js') }}"></script>
        <script src="{{ asset('admin/vendors/scripts/highchart-setting.js') }}"></script>

        <script src="{{ asset('admin/src/plugins/datatables/js/jquery.dataTables.min.js') }}"></script>
        <script src="{{ asset('admin/src/plugins/datatables/js/dataTables.bootstrap4.min.js') }}"></script>
        <script src="{{ asset('admin/src/plugins/datatables/js/dataTables.responsive.min.js') }}"></script>
        <script src="{{ asset('admin/src/plugins/datatables/js/responsive.bootstrap4.min.js') }}"></script>
        <!-- buttons for Export datatable -->
        <script src="{{ asset('admin/src/plugins/datatables/js/dataTables.buttons.min.js') }}"></script>
        <script src="{{ asset('admin/src/plugins/datatables/js/buttons.bootstrap4.min.js') }}"></script>
        <script src="{{ asset('admin/src/plugins/datatables/js/buttons.print.min.js') }}"></script>
        <script src="{{ asset('admin/src/plugins/datatables/js/buttons.html5.min.js') }}"></script>
        <script src="{{ asset('admin/src/plugins/datatables/js/buttons.flash.min.js') }}"></script>
        <script src="{{ asset('admin/src/plugins/datatables/js/pdfmake.min.js') }}"></script>
        <script src="{{ asset('admin/src/plugins/datatables/js/vfs_fonts.js') }}"></script>
        <!-- Datatable Setting js -->
        <script src="{{ asset('admin/vendors/scripts/datatable-setting.js') }}"></script>
        <!-- add sweet alert js & css in footer -->
        <script src="{{ asset('admin/src/plugins/sweetalert2/sweetalert2.all.js') }}"></script>
        <script src="{{ asset('admin/src/plugins/sweetalert2/sweet-alert.init.js') }}"></script>


        <!-- switchery js -->
        <script src="{{ asset('admin/src/plugins/switchery/switchery.min.js') }}"></script>
        <!-- bootstrap-tagsinput js -->
        <script src="{{ asset('admin/src/plugins/bootstrap-tagsinput/bootstrap-tagsinput.js') }}"></script>
        <!-- bootstrap-touchspin js -->
        <script src="{{ asset('admin/src/plugins/bootstrap-touchspin/jquery.bootstrap-touchspin.js') }}"></script>
        <script src="{{ asset('admin/vendors/scripts/advanced-components.js') }}"></script>


        <script src="{{ asset('admin/src/plugins/apexcharts/apexcharts.min.js') }}"></script>
        <script src="{{ asset('admin/vendors/scripts/dashboard.js') }}"></script>
        <script src="{{ asset('admin/src/scripts/sweetalert2.all.min.js') }}"></script>
        <script src="{{ asset('admin/src/scripts/alert.js') }}"></script>

        @stack('js')
</body>

</html>
