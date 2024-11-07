<!DOCTYPE html>
<html class="loading" lang="en">
<!-- BEGIN : Head-->

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <meta name="description" content="Sistem Informasi Akuntansi Perusahaan Versi 2">
    <meta name="keywords" content="SIAP V2, Sistem Informasi Akuntansi Perusahaan Versi 2">
    <meta name="author" content="PIXINVENT">
    <title>{{ $app_nama }} - Versi {{ $app_versi }}</title>
    <link rel="shortcut icon" type="image/x-icon" href="template/Apex6/app-assets/img/logo-sarana-jaya.png">
    <link rel="shortcut icon" type="image/png" href="template/Apex6/app-assets/img/logo-sarana-jaya.png">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-touch-fullscreen" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <link href="https://fonts.googleapis.com/css?family=Rubik:300,400,500,700,900%7CMontserrat:300,400,500,600,700,800,900" rel="stylesheet">
    <!-- BEGIN VENDOR CSS-->
    <!-- font icons-->
    <link rel="stylesheet" type="text/css" href="template/Apex6/app-assets/fonts/feather/style.min.css">
    <link rel="stylesheet" type="text/css" href="template/Apex6/app-assets/fonts/simple-line-icons/style.css">
    <link rel="stylesheet" type="text/css" href="template/Apex6/app-assets/fonts/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="template/Apex6/app-assets/vendors/css/perfect-scrollbar.min.css">
    <link rel="stylesheet" type="text/css" href="template/Apex6/app-assets/vendors/css/prism.min.css">
    <link rel="stylesheet" type="text/css" href="template/Apex6/app-assets/vendors/css/switchery.min.css">
    <!-- END VENDOR CSS-->
    <!-- BEGIN APEX CSS-->
    <link rel="stylesheet" type="text/css" href="template/Apex6/app-assets/css/bootstrap.css">
    <link rel="stylesheet" type="text/css" href="template/Apex6/app-assets/css/bootstrap-extended.css">
    <link rel="stylesheet" type="text/css" href="template/Apex6/app-assets/css/colors.css">
    <link rel="stylesheet" type="text/css" href="template/Apex6/app-assets/css/components.css">
    <link rel="stylesheet" type="text/css" href="template/Apex6/app-assets/css/themes/layout-dark.css">
    <link rel="stylesheet" href="template/Apex6/app-assets/css/plugins/switchery.css">
    <link href="plugins/alertify/themes/alertify.core.css" rel="stylesheet">
    <link href="plugins/alertify/themes/alertify.default.css" rel="stylesheet" >
    <!-- END APEX CSS-->
    <!-- BEGIN Page Level CSS-->
    <link rel="stylesheet" href="template/Apex6/app-assets/css/pages/authentication.css">
    <!-- END Page Level CSS-->
    <!-- BEGIN: Custom CSS-->
    <link rel="stylesheet" type="text/css" href="template/Apex6/assets/css/style.css">
    <!-- END: Custom CSS-->
</head>
<!-- END : Head-->

<!-- BEGIN : Body-->

<body class="vertical-layout vertical-menu 1-column auth-page navbar-sticky blank-page" data-menu="vertical-menu" data-col="1-column">
    <!-- ////////////////////////////////////////////////////////////////////////////-->
    <div class="wrapper">
        <div class="main-panel">
            <!-- BEGIN : Main Content-->
            <div class="main-content">
                <div class="content-overlay"></div>
                <div class="content-wrapper">
                    <!--Login Page Starts-->
                    <section id="login" class="auth-height">
                        <div class="row full-height-vh m-0">
                            <div class="col-12 d-flex align-items-center justify-content-center">
                                <div class="card overflow-hidden">
                                    <div class="card-content">
                                        <div class="card-body auth-img">
                                            <div class="row m-0">
                                                <div class="col-lg-6 d-none d-lg-flex justify-content-center align-items-center auth-img-bg p-3">
                                                    <img src="template/Apex6/app-assets/img/gallery/login.png" alt="" class="img-fluid" width="300" height="230">
                                                </div>
                                                <div class="col-lg-6 col-12 px-4 py-3">
                                                    <h4 class="mb-2 card-title">SIAP V2</h4>
                                                    <p>Selamat datang,<br>masukan akun pengguna Anda.</p>
                                                    <form id="form-ruh" name="form-ruh" onsubmit="return false">
                                                        <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                                                        <input type="text" class="form-control mb-3" placeholder="Username" id="username" name="username">
                                                        <input type="password" class="form-control mb-3" placeholder="Password" id="password" name="password">
                                                        <select class="form-control mb-3" id="tahun" name="tahun">
                                                        </select>
                                                        <div class="d-flex justify-content-between flex-sm-row flex-column">
                                                            <button type="submit" id="submit" class="btn btn-primary">Masuk</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                    <!--Login Page Ends-->
                </div>
            </div>
            <!-- END : End Main Content-->
        </div>
    </div>
    <!-- ////////////////////////////////////////////////////////////////////////////-->

    <!-- BEGIN VENDOR JS-->
    <script src="template/Apex6/app-assets/vendors/js/vendors.min.js"></script>
    <script src="template/Apex6/app-assets/vendors/js/switchery.min.js"></script>
    <!-- BEGIN VENDOR JS-->
    <!-- BEGIN PAGE VENDOR JS-->
    <!-- END PAGE VENDOR JS-->
    <!-- BEGIN APEX JS-->
    <script src="template/Apex6/app-assets/js/core/app-menu.js"></script>
    <script src="template/Apex6/app-assets/js/core/app.js"></script>
    <script src="template/Apex6/app-assets/js/notification-sidebar.js"></script>
    <script src="template/Apex6/app-assets/js/customizer.js"></script>
    <script src="template/Apex6/app-assets/js/scroll-top.js"></script>
    <script src="plugins/alertify/lib/alertify.min.js"></script>
    <!-- END APEX JS-->
    <!-- BEGIN PAGE LEVEL JS-->
    <!-- END PAGE LEVEL JS-->
    <!-- BEGIN: Custom CSS-->
    <script src="template/Apex6/assets/js/scripts.js"></script>
    <!-- END: Custom CSS-->

    <script>
        $(document).ready(function(){

            setTimeout(function(){
                $("#username").focus();
            },1000);

            function doBounce(element, times, distance, speed) {
                for(i = 0; i < times; i++) {
                    element.animate({marginTop: '-='+distance},speed)
                        .animate({marginTop: '+='+distance},speed);
                }        
            }

            $.get('auth/tahun', function(result){
                if(result){
                    $('#tahun').html(result);
                }
            });
            
            //login         
            $('#submit').click(function(){
            
                //bouncing for awhile...
                doBounce($('#login'), 3, '10px', 100);
            
                $(this).prop('disabled',true);
                $(this).html('<span class="loading">Sedang proses.....</span>');
                var lanjut=true;
                if($('#username').val()==''){
                    lanjut=false;
                }
                if($('#password').val()==''){
                    lanjut=false;
                }
				if($('#tahun').val()==''){
                    lanjut=false;
                }
                if(lanjut==true){
                    var url="auth";
                    var data=$('#form-ruh').serialize();
                    $.ajax({
                        url:'auth',
                        data:data,
                        method:'POST',
                        success:function(result){
                            alertify.log(result.message);
                            $('#submit').html('Masuk');
                            $('#submit').prop('disabled', false);
                            if(result.success){    
                                window.location.href='./';
                            }
                        },
                        error:function(result){
                            alertify.log(result.message);
                            $('#submit').html('Masuk');
                            $('#submit').prop('disabled', false);
                        }
                    });
                }
                else{
                    alertify.log('Kolom username/password tidak dapat dikosongkan!');
                    $('#submit').html('Masuk');
                    $('#submit').prop('disabled', false);
                }
                
            });

        });
    </script>

</body>
<!-- END : Body-->

</html>