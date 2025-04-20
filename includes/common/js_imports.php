
<!-- Required Jquery -->
<script data-cfasync="false" src="../../public/libraries/bower_components/cloudflare/email-decode.min.js"></script>
<script type="text/javascript" src="../../public/libraries/bower_components/jquery/js/jquery.min.js"></script>
<script type="text/javascript" src="../../public/libraries/bower_components/jquery-ui/js/jquery-ui.min.js"></script>
<script type="text/javascript" src="../../public/libraries/bower_components/popper.js/js/popper.min.js"></script>
<script type="text/javascript" src="../../public/libraries/bower_components/bootstrap/js/bootstrap.min.js"></script>
<!-- jquery slimscroll js -->
<script type="text/javascript" src="../../public/libraries/bower_components/jquery-slimscroll/js/jquery.slimscroll.js"></script>
<!-- modernizr js -->
<script type="text/javascript" src="../../public/libraries/bower_components/modernizr/js/modernizr.js"></script>
<!-- Chart js -->
<script type="text/javascript" src="../../public/libraries/bower_components/chart.js/js/Chart.js"></script>
<!-- amchart js -->
<script src="../../public/libraries/assets/pages/widget/amchart/amcharts.js"></script>
<script src="../../public/libraries/assets/pages/widget/amchart/serial.js"></script>
<script src="../../public/libraries/assets/pages/widget/amchart/light.js"></script>
<script src="../../public/libraries/assets/js/jquery.mCustomScrollbar.concat.min.js"></script>
<script type="text/javascript" src="../../public/libraries//assets/js/SmoothScroll.js"></script>
<script src="../../public/libraries//assets/js/pcoded.min.js"></script>
<!-- custom js -->
<script src="../../public/libraries//assets/js/vartical-layout.min.js"></script>
<script type="text/javascript" src="../../public/libraries/assets/pages/dashboard/custom-dashboard.js"></script>
<script type="text/javascript" src="../../public/libraries/assets/js/script.min.js"></script>

<!-- Warning Section Ends -->
    <!-- Required Jquery -->
    <script type="text/javascript" src="../public/libraries/bower_components/jquery/js/jquery.min.js"></script>
    <script type="text/javascript" src="../public/libraries/bower_components/jquery-ui/js/jquery-ui.min.js"></script>
    <script type="text/javascript" src="../public/libraries/bower_components/popper.js/js/popper.min.js"></script>
    <script type="text/javascript" src="../public/libraries/bower_components/bootstrap/js/bootstrap.min.js"></script>
    <!-- jquery slimscroll js -->
    <script type="text/javascript" src="../public/libraries/bower_components/jquery-slimscroll/js/jquery.slimscroll.js"></script>
    <!-- modernizr js -->
    <script type="text/javascript" src="../public/libraries/bower_components/modernizr/js/modernizr.js"></script>
    <script type="text/javascript" src="../public/libraries/bower_components/modernizr/js/css-scrollbars.js"></script>
    <!-- i18next.min.js -->
    <script type="text/javascript" src="../public/libraries/bower_components/i18next/js/i18next.min.js"></script>
    <script type="text/javascript" src="../public/libraries/bower_components/i18next-xhr-backend/js/i18nextXHRBackend.min.js"></script>
    <script type="text/javascript" src="../public/libraries/bower_components/i18next-browser-languagedetector/js/i18nextBrowserLanguageDetector.min.js"></script>
    <script type="text/javascript" src="../public/libraries/bower_components/jquery-i18next/js/jquery-i18next.min.js"></script>
    <script type="text/javascript" src="../public/libraries/assets/js/common-pages.js"></script>
   
   <script>
        setInterval(function() {
            $.ajax({
                url: '../../api/check_comunicados.php',
                method: 'GET',
                success: function(data) {
                    if (data.novos > 0) {
                        $('.notification-label').text(data.novos).show();
                    } else {
                        $('.notification-label').hide();
                    }
                }
            });
        }, 300000); // 5 minutos
    </script>
