<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title>Sincronizador | Login</title>
        <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" name="viewport" />
        <meta content="" name="description" />
        <meta content="" name="author" />

        <!-- ================== BEGIN BASE CSS STYLE ================== -->
        <link href="public/css/transparent/app.min.css" rel="stylesheet" />
        <!-- ================== END BASE CSS STYLE ================== -->
        <!-- ================== BEGIN PAGE LEVEL CSS STYLE ================== -->
        <link href="public/plugins/jvectormap-next/jquery-jvectormap.css" rel="stylesheet" />
        <link href="public/plugins/bootstrap-calendar/css/bootstrap_calendar.css" rel="stylesheet" />
        <link href="public/plugins/gritter/css/jquery.gritter.css" rel="stylesheet" />
        <link href="public/plugins/nvd3/build/nv.d3.css" rel="stylesheet" />
        <!-- ================== END PAGE LEVEL CSS STYLE ================== -->

        <!-- FAVICONS -->
        <link rel="shortcut icon" href="public/img/logito.png" type="image/x-icon">
        <link rel="icon" href="public/img/logito.png" type="image/x-icon">
    </head>
    <body class="pace-top">

        <!-- begin #page-loader -->
        <div id="page-loader" class="fade show"><span class="spinner"></span></div>
        <!-- end #page-loader -->

        <div class="login-cover">
            <div class="login-cover-image" style="background-image: url(public/img/fondo2.png)" data-id="login-cover-image"></div>
            <div class="login-cover-bg"></div>
        </div>

        <!-- begin #page-container -->
        <div id="page-container" class="fade page-container">
            <!-- begin login -->
            <div class="login login-v2" data-pageload-addclass="animated fadeIn">
                <!-- begin brand -->
                <div class="login-header ">
                    <div class="brand ">
                        <span class=""></span> <b>Sincronizador</b> 
                        <small>Por favor inicie sesion</small>
                    </div>
                    <div class="icon">
                        <img src="public/img/logo.png" width="150" height="150" alt="Sincronizador QBO-MH"/>
                    </div>
                </div>
                <!-- end brand -->

                <!-- begin panel-body -->
                <div class="panel-body panel-form">
                    <form class="form-horizontal form-bordered" action="?controller=Default&action=login" method="POST" >
                        <div class="form-group row">
                            <label class="col-lg-4 col-form-label">Usuario / Username</label>
                            <div class="col-lg-8">
                                <input data-toggle="" data-placement="after" class="form-control" type="text" name="user" id="user" placeholder="Usuario / Username" required minlength="9" maxlength="12" />
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-lg-4 col-form-label">Contraseña / Password</label>
                            <div class="col-lg-8">
                                <input data-toggle="password" data-placement="after" class="form-control" type="password" name="pass" id="pass" placeholder="contraseña / Password" required />
                            </div>
                        </div>
                        <div class="checkbox checkbox-css m-b-20">
                            <input type="checkbox" id="remember_checkbox" /> 
                            <label for="remember_checkbox">
                                Recordarme
                            </label>
                        </div>
                        <div class="login-buttons">
                            <button type="submit" class="btn btn-success btn-block btn-lg">Ingresar</button>
                        </div>
                        <div class="m-t-20">
                        </div>
                    </form>
                </div>
                <!-- end panel-body -->
                <!-- end login-content -->
            </div>
            <!-- end login -->
        </div>
        <!-- end page container -->


        <!-- ================== BEGIN BASE JS ================== -->
        <script src="public/js/app.min.js"></script>
        <script src="public/js/theme/transparent.min.js"></script>
        <!-- ================== END BASE JS ================== -->

        <!-- ================== BEGIN PAGE LEVEL JS ================== -->
        <script src="public/js/demo/login-v2.demo.js"></script>
        <!-- ================== END PAGE LEVEL JS ================== -->

        <!-- ================== BEGIN PAGE LEVEL JS ================== -->
        <script src="public/plugins/jquery-migrate/dist/jquery-migrate.min.js"></script>
        <script src="public/plugins/moment/min/moment.min.js"></script>
        <script src="public/plugins/bootstrap-datepicker/dist/js/bootstrap-datepicker.js"></script>
        <script src="public/plugins/ion-rangeslider/js/ion.rangeSlider.min.js"></script>
        <script src="public/plugins/bootstrap-colorpicker/dist/js/bootstrap-colorpicker.min.js"></script>
        <script src="public/plugins/jquery.maskedinput/src/jquery.maskedinput.js"></script>
        <script src="public/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
        <script src="public/plugins/pwstrength-bootstrap/dist/pwstrength-bootstrap.min.js"></script>
        <script src="public/plugins/@danielfarrell/bootstrap-combobox/js/bootstrap-combobox.js"></script>
        <script src="public/plugins/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
        <script src="public/plugins/tag-it/js/tag-it.min.js"></script>
        <script src="public/plugins/bootstrap-daterangepicker/daterangepicker.js"></script>
        <script src="public/plugins/select2/dist/js/select2.min.js"></script>
        <script src="public/plugins/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js"></script>
        <script src="public/plugins/bootstrap-show-password/dist/bootstrap-show-password.js"></script>
        <script src="public/plugins/bootstrap-colorpalette/js/bootstrap-colorpalette.js"></script>
        <script src="public/plugins/jquery-simplecolorpicker/jquery.simplecolorpicker.js"></script>
        <script src="public/plugins/clipboard/dist/clipboard.min.js"></script>
        <script src="public/js/demo/form-plugins.demo.js"></script>
        <!-- ================== END PAGE LEVEL JS ================== -->
        
        <!-- ================== BEGIN PAGE LEVEL JS ================== -->
        <script src="public/plugins/gritter/js/jquery.gritter.js"></script>
        <script src="public/plugins/sweetalert/dist/sweetalert.min.js"></script>
        <script src="public/js/demo/ui-modal-notification.demo.js"></script>
        <!-- ================== END PAGE LEVEL JS ================== -->
        <?php
        if (isset($_GET["error"])) {
            if($_GET["error"] == "1"){
               echo "<script type='text/javascript'>
                    $(document).ready(function () {
                        $.gritter.add({
                            title: 'Error de inicio de session',
                            text: 'Usuario u contraseña incorrectos'
                        });
                    });
                </script>"; 
            }
            if($_GET["error"] == "2"){
               echo "<script type='text/javascript'>
                    $(document).ready(function () {
                        $.gritter.add({
                            title: 'Error de inicio de session',
                            text: 'El usuario utilizado para acceso al sincronizador no tiene relacion con la base de datos accedida de QB'
                        });
                    });
                </script>"; 
            }
            
        }
        ?>
    </body>
</html>
