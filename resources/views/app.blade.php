<!DOCTYPE html>
<html ng-app="spa" class="loading" lang="en">
<!-- BEGIN : Head-->

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <meta name="description" content="Apex admin is super flexible, powerful, clean &amp; modern responsive bootstrap 4 admin template with unlimited possibilities.">
    <meta name="keywords" content="admin template, Apex admin template, dashboard template, flat admin template, responsive admin template, web app">
    <meta name="author" content="PIXINVENT">
    <title>{{ $app_nama }} - {{ $app_versi }}</title>
    <link rel="shortcut icon" type="image/x-icon" href="template/Apex6/app-assets/img/ico/favicon.ico">
    <link rel="shortcut icon" type="image/png" href="template/Apex6/app-assets/img/ico/favicon-32.png">
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
    <link rel="stylesheet" type="text/css" href="template/Apex6/app-assets/vendors/css/datatables/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" type="text/css" href="template/Apex6/app-assets/vendors/css/select2.min.css">
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
    <link href="plugins/jquery-file-upload/css/jquery.fileupload.css" rel="stylesheet" type="text/css" />
    <!-- END APEX CSS-->
    <!-- BEGIN Page Level CSS-->
    <!-- END Page Level CSS-->
    <!-- BEGIN: Custom CSS-->
    <link rel="stylesheet" type="text/css" href="template/Apex6/assets/css/style.css">
    <!-- END: Custom CSS-->
</head>
<!-- END : Head-->

<!-- BEGIN : Body-->

<body class="vertical-layout vertical-menu 2-columns  navbar-sticky" data-menu="vertical-menu" data-col="2-columns">

    <nav class="navbar navbar-expand-lg navbar-light header-navbar navbar-fixed">
        <div class="container-fluid navbar-wrapper">
            <div class="navbar-header d-flex">
                <div class="navbar-toggle menu-toggle d-xl-none d-block float-left align-items-center justify-content-center" data-toggle="collapse"><i class="ft-menu font-medium-3"></i></div>
                <ul class="navbar-nav">
                    <li class="nav-item mr-2 d-none d-lg-block"><a class="nav-link apptogglefullscreen" id="navbar-fullscreen" href="javascript:;"><i class="ft-maximize font-medium-3"></i></a></li>
                    <li class="nav-item">{{ $info_nmkantor }}, TA. {{ $info_tahun }}</li>
                </ul>
            </div>
            <div class="navbar-container">
                <div class="collapse navbar-collapse d-block" id="navbarSupportedContent">
                    <ul class="navbar-nav">
                        <li class="dropdown nav-item">
                          <a class="nav-link dropdown-toggle dropdown-notification p-0 mt-2" id="dropdownBasic1" href="javascript:;" data-toggle="dropdown"><i class="ft-bell font-medium-3"></i><span class="notification badge badge-pill badge-danger">0</span></a>
                          <ul class="notification-dropdown dropdown-menu dropdown-menu-media dropdown-menu-right m-0 overflow-hidden">
                                <li class="dropdown-menu-header">
                                    <div class="dropdown-header d-flex justify-content-between m-0 px-3 py-2 white bg-primary">
                                        <div class="d-flex"><i class="ft-bell font-medium-3 d-flex align-items-center mr-2"></i><span id="judul-notifikasi" class="noti-title">0 Notifikasi</span></div>
                                    </div>
                                </li>
                                <li class="scrollable-container" id="isi-notifikasi">
                                <a class="d-flex justify-content-between" href="javascript:void(0)">
                                  <div class="media d-flex align-items-center">
                                      <div class="media-body">
                                          <h6 class="m-0"><span>Data tidak ditemukan.</span></h6>
                                      </div>
                                  </div>
                                </a>
                                </li>
                            </ul>
                        </li>
                        <li class="dropdown nav-item mr-1">
                            <a class="nav-link dropdown-toggle user-dropdown d-flex align-items-end" id="dropdownBasic2" href="javascript:;" data-toggle="dropdown">
                              <div class="user d-md-flex d-none mr-2"><span class="text-right">{{ $info_username }}</span><span class="text-right text-muted font-small-3">{{ $info_nmlevel }}</span></div><img class="avatar" src="data/user/foto/no-image.png" alt="avatar" height="35" width="35">
                            </a>
                            <div class="dropdown-menu text-left dropdown-menu-right m-0 pb-0" aria-labelledby="dropdownBasic2">
                              <a class="dropdown-item" ui-sref="profile">
                                <div class="d-flex align-items-center"><i class="ft-edit mr-2"></i><span>Pengaturan</span></div>
                              </a>
                              <a class="dropdown-item" href="data/manual/manual.pdf" target="_blank">
                                <div class="d-flex align-items-center"><i class="ft-mail mr-2"></i><span>Dokumentasi</span></div>
                              </a>
                              <div class="dropdown-divider"></div>
                              <a class="dropdown-item" href="auth/logout">
                                <div class="d-flex align-items-center"><i class="ft-power mr-2"></i><span>Keluar</span></div>
                              </a>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>
    <!-- Navbar (Header) Ends-->

    <!-- ////////////////////////////////////////////////////////////////////////////-->
    <div class="wrapper">


        <!-- main menu-->
        <!--.main-menu(class="#{menuColor} #{menuOpenType}", class=(menuShadow == true ? 'menu-shadow' : ''))-->
        <div class="app-sidebar menu-fixed" data-background-color="man-of-steel" data-image="template/Apex6/app-assets/img/sidebar-bg/01.jpg" data-scroll-to-active="true">
            <!-- main menu header-->
            <!-- Sidebar Header starts-->
            <div class="sidebar-header">
                <div class="logo clearfix">
                  <a class="logo-text float-left" ui-sref="/">
                    <!--<div class="logo-img"><img src="template/Apex6/app-assets/img/logo.png" alt="Apex Logo" /></div>-->
                    <span class="text">SIAP V2</span>
                  </a>
                  <a class="nav-toggle d-none d-lg-none d-xl-block" id="sidebarToggle" href="javascript:;">
                    <i class="toggle-icon ft-toggle-right" data-toggle="expanded"></i>
                  </a>
                  <a class="nav-close d-block d-lg-block d-xl-none" id="sidebarClose" href="javascript:;">
                    <i class="ft-x"></i>
                  </a>
                </div>
            </div>
            <!-- Sidebar Header Ends-->
            <!-- / main menu header-->
            <!-- main menu content-->
            <div class="sidebar-content main-menu-content">
                <div class="nav-container">
                    <ul class="navigation navigation-main" id="main-menu-navigation" data-menu="menu-navigation">
                        {!! $menu !!}
                    </ul>
                </div>
            </div>
            <!-- main menu content-->
            <div class="sidebar-background"></div>
            <!-- main menu footer-->
            <!-- include includes/menu-footer-->
            <!-- main menu footer-->
            <!-- / main menu-->
        </div>

        <div class="main-panel">
            <!-- BEGIN : Main Content-->
            <div class="main-content">
                <div class="content-overlay"></div>
                <div class="content-wrapper" ui-view>
                    
                </div>
            </div>
            <!-- END : End Main Content-->

            <!-- BEGIN : Footer-->
            <footer class="footer undefined undefined">
                <p class="clearfix text-muted m-0"><span>Copyright &copy; 2024 &nbsp;</span><a href="javascript:;" id="pixinventLink" target="_blank">The X Team</a><span class="d-none d-sm-inline-block">, All rights reserved.</span></p>
            </footer>
            <!-- End : Footer-->
            <!-- Scroll to top button -->
            <button class="btn btn-primary scroll-top" type="button"><i class="ft-arrow-up"></i></button>

        </div>
    </div>
    <!-- ////////////////////////////////////////////////////////////////////////////-->

    <!-- Theme customizer Starts-->
    <div class="customizer d-none d-lg-none d-xl-block">
      <a class="customizer-close"><i class="ft-x font-medium-3"></i></a>
      <a class="customizer-toggle bg-primary" id="customizer-toggle-icon"><i class="ft-settings font-medium-1 spinner white align-middle"></i></a>
      <div class="customizer-content p-3 ps-container ps-theme-dark" data-ps-id="df6a5ce4-a175-9172-4402-dabd98fc9c0a">
        <h4 class="text-uppercase">Theme Customizer</h4>
        <p>Customize & Preview in Real Time</p>
        <!-- Layout Options Starts-->
        <div class="ct-layout">
          <hr>
          <h6 class="mb-3 d-flex align-items-center"><i class="ft-layout font-medium-2 mr-2"></i><span>Layout Options</span></h6>
          <div class="layout-switch">
            <div class="radio radio-sm d-inline-block light-layout mr-3">
              <input id="ll-switch" type="radio" name="layout-switch" checked>
              <label for="ll-switch">Light</label>
            </div>
            <div class="radio radio-sm d-inline-block dark-layout mr-3">
              <input id="dl-switch" type="radio" name="layout-switch">
              <label for="dl-switch">Dark</label>
            </div>
            <div class="radio radio-sm d-inline-block transparent-layout">
              <input id="tl-switch" type="radio" name="layout-switch">
              <label for="tl-switch">Transparent</label>
            </div>
          </div>
          <!-- Layout Options Ends-->
        </div>
        <!-- Navbar Types-->
        <div class="ct-navbar-type">
          <hr>
          <h6 class="mb-3 d-flex align-items-center"><i class="ft-more-horizontal- font-medium-2 mr-2"></i><span>Navbar Type</span></h6>
          <div class="navbar-switch">
            <div class="radio radio-sm d-inline-block nav-static mr-3">
              <input id="nav-static" type="radio" name="navbar-switch" checked="checked">
              <label for="nav-static">Static</label>
            </div>
            <div class="radio radio-sm d-inline-block nav-fixed">
              <input id="nav-fixed" type="radio" name="navbar-switch">
              <label for="nav-fixed">Fixed</label>
            </div>
          </div>
        </div>
        <!-- Sidebar Options Starts-->
        <div class="ct-bg-color">
          <hr>
          <h6 class="sb-options d-flex align-items-center mb-3"><i class="ft-droplet font-medium-2 mr-2"></i><span>Sidebar Color Options</span></h6>
          <div class="cz-bg-color sb-color-options">
            <div class="row mb-3">
              <div class="col px-2"><span class="gradient-mint d-block rounded" style="width:30px; height:30px;" data-bg-color="mint"></span></div>
              <div class="col px-2"><span class="gradient-king-yna d-block rounded" style="width:30px; height:30px;" data-bg-color="king-yna"></span></div>
              <div class="col px-2"><span class="gradient-ibiza-sunset d-block rounded" style="width:30px; height:30px;" data-bg-color="ibiza-sunset"></span></div>
              <div class="col px-2"><span class="gradient-flickr d-block rounded" style="width:30px; height:30px;" data-bg-color="flickr"></span></div>
              <div class="col px-2"><span class="gradient-purple-bliss d-block rounded" style="width:30px; height:30px;" data-bg-color="purple-bliss"></span></div>
              <div class="col px-2"><span class="gradient-man-of-steel d-block rounded" style="width:30px; height:30px;" data-bg-color="man-of-steel"></span></div>
              <div class="col px-2"><span class="gradient-purple-love d-block rounded" style="width:30px; height:30px;" data-bg-color="purple-love"></span></div>
            </div>
            <div class="row">
              <div class="col px-2"><span class="bg-black d-block rounded" style="width:30px; height:30px;" data-bg-color="black"></span></div>
              <div class="col px-2"><span class="bg-grey bg-lighten-3 d-block rounded" style="width:30px; height:30px;" data-bg-color="white"></span></div>
              <div class="col px-2"><span class="bg-primary bg-darken-1 d-block rounded" style="width:30px; height:30px;" data-bg-color="primary"></span></div>
              <div class="col px-2"><span class="bg-success bg-darken-1 d-block rounded" style="width:30px; height:30px;" data-bg-color="success"></span></div>
              <div class="col px-2"><span class="bg-warning bg-darken-1 d-block rounded" style="width:30px; height:30px;" data-bg-color="warning"></span></div>
              <div class="col px-2"><span class="bg-info bg-darken-1 d-block rounded" style="width:30px; height:30px;" data-bg-color="info"></span></div>
              <div class="col px-2"><span class="bg-danger bg-darken-1 d-block rounded" style="width:30px; height:30px;" data-bg-color="danger"></span></div>
            </div>
          </div>
          <!-- Sidebar Options Ends-->
          <!-- Transparent BG Image Ends-->
          <div class="tl-bg-img">
            <h6 class="d-flex align-items-center mb-3"><i class="ft-star font-medium-2 mr-2"></i><span>Background Colors with Shades</span></h6>
            <div class="cz-tl-bg-image row">
              <div class="col-sm-3">
                <div class="rounded bg-glass-1 ct-glass-bg" data-bg-image="bg-glass-1"></div>
              </div>
              <div class="col-sm-3">
                <div class="rounded bg-glass-2 ct-glass-bg" data-bg-image="bg-glass-2"></div>
              </div>
              <div class="col-sm-3">
                <div class="rounded bg-glass-3 ct-glass-bg" data-bg-image="bg-glass-3"></div>
              </div>
              <div class="col-sm-3">
                <div class="rounded bg-glass-4 ct-glass-bg" data-bg-image="bg-glass-4"></div>
              </div>
            </div>
          </div>
          <!-- Transparent BG Image Ends-->
        </div>
        <!-- Sidebar BG Image Starts-->
        <div class="ct-bg-image">
          <hr>
          <h6 class="sb-bg-img d-flex align-items-center mb-3"><i class="ft-sidebar font-medium-2 mr-2"></i><span>Sidebar Bg Image</span></h6>
          <div class="cz-bg-image row sb-bg-img">
            <div class="col-2 px-2"><img class="rounded sb-bg-01" src="template/Apex6/app-assets/img/sidebar-bg/01.jpg" alt="sidebar bg image" width="90"></div>
            <div class="col-2 px-2"><img class="rounded sb-bg-02" src="template/Apex6/app-assets/img/sidebar-bg/02.jpg" alt="sidebar bg image" width="90"></div>
            <div class="col-2 px-2"><img class="rounded sb-bg-03" src="template/Apex6/app-assets/img/sidebar-bg/03.jpg" alt="sidebar bg image" width="90"></div>
            <div class="col-2 px-2"><img class="rounded sb-bg-04" src="template/Apex6/app-assets/img/sidebar-bg/04.jpg" alt="sidebar bg image" width="90"></div>
            <div class="col-2 px-2"><img class="rounded sb-bg-05" src="template/Apex6/app-assets/img/sidebar-bg/05.jpg" alt="sidebar bg image" width="90"></div>
            <div class="col-2 px-2"><img class="rounded sb-bg-06" src="template/Apex6/app-assets/img/sidebar-bg/06.jpg" alt="sidebar bg image" width="90"></div>
          </div>
          <!-- Transparent Layout Bg color Options-->
          <div class="tl-color-option">
            <h6 class="tl-color-options d-flex align-items-center mb-3"><i class="ft-droplet font-medium-2 mr-2"></i><span>Background Colors</span></h6>
            <div class="cz-tl-bg-color">
              <div class="row">
                <div class="col"><span class="bg-glass-hibiscus d-block rounded ct-color-bg" style="width:30px; height:30px;" data-bg-color="bg-glass-hibiscus"></span></div>
                <div class="col"><span class="bg-glass-purple-pizzazz d-block rounded ct-color-bg" style="width:30px; height:30px;" data-bg-color="bg-glass-purple-pizzazz"></span></div>
                <div class="col"><span class="bg-glass-blue-lagoon d-block rounded ct-color-bg" style="width:30px; height:30px;" data-bg-color="bg-glass-blue-lagoon"></span></div>
                <div class="col"><span class="bg-glass-electric-violet d-block rounded ct-color-bg" style="width:30px; height:30px;" data-bg-color="bg-glass-electric-violet"></span></div>
                <div class="col"><span class="bg-glass-portage d-block rounded ct-color-bg" style="width:30px; height:30px;" data-bg-color="bg-glass-portage"></span></div>
                <div class="col"><span class="bg-glass-tundora d-block rounded ct-color-bg" style="width:30px; height:30px;" data-bg-color="bg-glass-tundora"></span></div>
              </div>
            </div>
          </div>
          <!-- Transparent Layout Bg color Ends-->
        </div>
        <!-- Sidebar BG Image Toggle Starts-->
        <div class="ct-bg-image-toggler">
          <div class="togglebutton toggle-sb-bg-img">
            <hr>
            <div class="switch"><span>Sidebar Bg Image</span>
              <div class="float-right">
                <div class="checkbox">
                  <input class="cz-bg-image-display" id="sidebar-bg-img" type="checkbox" checked>
                  <label for="sidebar-bg-img"></label>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- Sidebar BG Image Toggle Ends-->
        <!-- Compact Menu Starts-->
        <div class="ct-compact-toggler">
          <hr>
          <div class="togglebutton">
            <div class="switch"><span>Compact Menu</span>
              <div class="float-right">
                <div class="checkbox">
                  <input class="cz-compact-menu" id="cz-compact-menu" type="checkbox">
                  <label for="cz-compact-menu"></label>
                </div>
              </div>
            </div>
          </div>
          <!-- Compact Menu Ends-->
        </div>
        <!-- Sidebar Width Starts-->
        <div class="ct-sidebar-size">
          <hr>
          <p>Sidebar Width</p>
          <div class="cz-sidebar-width btn-group btn-group-toggle" id="cz-sidebar-width" data-toggle="buttons">
            <label class="btn btn-outline-primary">
              <input id="cz-btn-radio-1" type="radio" name="cz-btn-radio" value="small"><span>Small</span>
            </label>
            <label class="btn btn-outline-primary active">
              <input id="cz-btn-radio-2" type="radio" name="cz-btn-radio" value="medium" checked><span>Medium</span>
            </label>
            <label class="btn btn-outline-primary">
              <input id="cz-btn-radio-3" type="radio" name="cz-btn-radio" value="large"><span>Large</span>
            </label>
          </div>
        </div>
        <!-- Sidebar Width Ends-->
      </div>
    </div>
    <!-- Theme customizer Ends-->

    <!-- Modal -->
    <div class="modal fade text-left" id="modal-notifikasi" tabindex="-1" role="dialog" aria-labelledby="myModalLabel2" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel2"><i class="ft-alert-triangle mr-2"></i>Perhatian</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"><i class="ft-x font-medium-2 text-bold-700"></i></span>
                    </button>
                </div>
                <div class="modal-body">
                    <h5><i class="ft-arrow-right mr-1"></i>Notifikasi Proses Dokumen</h5>
                    <p>Terdapat transaksi UMK/ BUK yang sudah mendekati batas akhir jatuh tempo pengajuan. Silahkan cek di tombol kanan atas untuk melihat detilnya.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn bg-light-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- END Notification Sidebar-->
    <div class="sidenav-overlay"></div>
    <div class="drag-target"></div>
    <!-- BEGIN VENDOR JS-->
    <script src="template/Apex6/app-assets/vendors/js/vendors.min.js"></script>
    <script src="template/Apex6/app-assets/vendors/js/switchery.min.js"></script>
    <!-- BEGIN VENDOR JS-->
    <!-- BEGIN PAGE VENDOR JS-->
    <script src="template/Apex6/app-assets/vendors/js/datatable/jquery.dataTables.min.js"></script>
    <script src="template/Apex6/app-assets/vendors/js/datatable/dataTables.bootstrap4.min.js"></script>
    <script src="template/Apex6/app-assets/vendors/js/datatable/dataTables.buttons.min.js"></script>
    <script src="template/Apex6/app-assets/vendors/js/datatable/buttons.html5.min.js"></script>
    <script src="template/Apex6/app-assets/vendors/js/datatable/buttons.print.min.js"></script>
    <script src="template/Apex6/app-assets/vendors/js/datatable/jszip.min.js"></script>
    <script src="template/Apex6/app-assets/vendors/js/datatable/pdfmake.min.js"></script>
    <script src="template/Apex6/app-assets/vendors/js/datatable/vfs_fonts.js"></script>
    <!-- END PAGE VENDOR JS-->
    <!-- BEGIN APEX JS-->
    <script src="template/Apex6/app-assets/js/core/app-menu.js"></script>
    <script src="template/Apex6/app-assets/js/core/app.js"></script>
    <script src="template/Apex6/app-assets/js/notification-sidebar.js"></script>
    <script src="template/Apex6/app-assets/js/customizer.js"></script>
    <script src="template/Apex6/app-assets/js/scroll-top.js"></script>
    <script src="template/Apex6/src/js/jquery-ui.min.js"></script>
    <!-- END APEX JS-->
    <!-- BEGIN PAGE LEVEL JS-->
    <script src="template/Apex6/app-assets/js/data-tables/dt-advanced-initialization.js"></script>
    <script src="template/Apex6/app-assets/vendors/js/select2.full.min.js"></script>
    <script src="plugins/jQueryMaskPlugin/src/jquery.mask.js"></script>
    <script src="plugins/jquery-inputmask/jquery.inputmask.min.js"></script>
    <script src="plugins/alertify/lib/alertify.min.js"></script>
    <script src="plugins/jquery-file-upload/js/jquery.fileupload.js"></script>
    <script src="plugins/highcharts/highcharts.js"></script>
    <script src="plugins/highcharts/highcharts-more.js"></script>
    <script src="plugins/highcharts/highcharts-3d.js"></script>
    
    <!-- END PAGE LEVEL JS-->
    <!-- BEGIN: Custom CSS-->
    <script src="template/Apex6/assets/js/scripts.js"></script>
    <!-- END: Custom CSS-->

    <!-- load angular -->
	  <script src="angular/angular.min.js"></script>
	  <script src="angular/angular-ui-router.min.js"></script>
	  <script src="angular/ngStorage.js"></script>
	  <script src="angular/loading-bar.js"></script>

    <!-- App Router JS -->
	  <script>{!! $angular !!}</script>

    <script>
      $(document).ready(function() {

        $.ajaxSetup({
          statusCode: {
            401: function() {
              window.location.href='auth/logout'
            }
          }
        });

        $.getJSON('notifikasi', function(result){
          if(result.success){
            var jml = result.data.length;
            $('#dropdownBasic1').html('<i class="ft-bell font-medium-3"></i><span class="notification badge badge-pill badge-danger">'+jml+'</span>');
            $('#judul-notifikasi').html(jml+' notifikasi');

            var html = ``;
            $.each(result.data, function(i, data){

              html += `<a class="d-flex justify-content-between" href="javascript:void(0)">
                        <div class="media d-flex align-items-center">
                            <div class="media-body">
                                <h6 class="m-0"><span>`+data.jenis+`. `+data.nourut+`</span><small class="grey lighten-1 font-italic float-right">`+data.jml_hari+` hari lagi</small></h6>
                                <small class="noti-text">Nilai Rp. `+data.nilai+`,-</small>
                                <small class="noti-text">/ Jatuh Tempo `+data.tgl_jatuh_tempo+`</small>
                                <h6 class="noti-text font-small-3 m-0">`+data.uraian+`</h6>
                            </div>
                        </div>
                      </a>`;

            });
            
            $('#isi-notifikasi').html(html);

            $('#modal-notifikasi').modal('show');
            
          }
        });

      });
    </script>

</body>
<!-- END : Body-->

</html>