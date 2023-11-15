

<!-- begin scroll to top btn -->
<a href="javascript:;" class="btn btn-icon btn-circle btn-success btn-scroll-to-top fade" data-click="scroll-top"><i class="fa fa-angle-up"></i></a>
<!-- end scroll to top btn -->
</div>
<!-- end page container -->

<!-- #modal-dialog -->
<div class="modal modal-message" id="config-Modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Datos de configuración</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">

                <form action="?controller=Client&action=save" method="POST"  class="form-horizontal" enctype="multipart/form-data" name="form-wizard" class="form-control-with-bg">
                    <!-- begin wizard -->
                    <div id="wizard">
                        <!-- begin wizard-step -->
                        <ul>
                            <li>
                                <a href="#step-1">
                                    <span class="number">1</span> 
                                    <span class="info">
                                        Ministerio Hacienda
                                        <small>Datos para comunicación con el sistema</small>
                                    </span>
                                </a>
                            </li>
                            <li>
                                <a href="#step-2">
                                    <span class="number">2</span> 
                                    <span class="info">
                                        Correo Electrónico
                                        <small>Recepción de documentos</small>
                                    </span>
                                </a>
                            </li>
                            <li>
                                <a href="#step-3">
                                    <span class="number">3</span>
                                    <span class="info">
                                        Completar
                                        <small>Guardar datos</small>
                                    </span>
                                </a>
                            </li>

                        </ul>
                        <!-- end wizard-step -->
                        <!-- begin wizard-content -->
                        <div>
                            <!-- begin step-1 -->
                            <div id="step-1">
                                <!-- begin fieldset -->
                                <fieldset>
                                    <!-- begin row -->
                                    <div class="row">
                                        <!-- begin col-8 -->
                                        <div class="col-xl-8 offset-xl-2">

                                            <!-- begin form-group -->
                                            <div class="form-group row m-b-10">
                                                <label class="col-lg-3 col-form-label">Usuario MH: <span class="text-danger"></span></label>
                                                <div class="col-lg-9 col-xl-9">
                                                    <input type="text" name="userMH" <?php
                                                    if (!isset($userMH)) {
                                                        echo 'placeholder="Usuario brindado por Ministerio de Hacienda"';
                                                    } else {
                                                        echo 'value="' . $userMH . '"';
                                                    }
                                                    ?>  data-parsley-group="step-1" data-parsley-required="true" class="form-control" />
                                                </div>
                                            </div>
                                            <!-- end form-group -->
                                            <!-- begin form-group -->
                                            <div class="form-group row m-b-10">
                                                <label class="col-lg-3 col-form-label">Contraseña MH: </label>
                                                <div class="col-lg-9 col-xl-9">
                                                    <input data-toggle="password" data-placement="after" class="form-control" type="password"  name="passMH" <?php
                                                    if (!isset($passMH)) {
                                                        echo 'placeholder="Contraseña brindada por Ministerio de Hacienda"';
                                                    } else {
                                                        echo 'value="' . $passMH . '"';
                                                    }
                                                    ?> data-parsley-group="step-1" data-parsley-required="true"  />
                                                </div>
                                            </div>
                                            <!-- end form-group -->
                                            <!-- begin form-group -->
                                            <div class="form-group row m-b-10">
                                                <label class="col-lg-3 col-form-label">Lave criptografica: <span class="text-danger"></span></label>
                                                <div class="col-lg-9 col-xl-9">
                                                    <input data-toggle="file" data-placement="after" class="form-control" type="file"  id="criptKey" name="criptKey" <?php
                                                    if (!isset($criptKey)) {
                                                        
                                                    } else {
                                                        echo 'value="' . $criptKey . '"';
                                                    }
                                                    ?> placeholder="Llave Criptografica" data-parsley-group="step-1" data-parsley-required="true" />
                                                </div>
                                            </div>                                            
                                            <!-- end form-group -->  
                                            <!-- begin form-group -->
                                            <div class="form-group row m-b-10">
                                                <label class="col-lg-3 col-form-label">PIN: <span class="text-danger"></span></label>
                                                <div class="col-lg-9 col-xl-9">
                                                    <input data-toggle="password" data-placement="after" class="form-control" type="password"  name="passCriptkey" placeholder="PIN de llave criptografica" minlength="4" maxlength="4" data-parsley-group="step-1" data-parsley-required="true"  />
                                                </div>
                                            </div>                                            
                                            <!-- end form-group --> 
                                        </div>
                                        <!-- end col-8 -->
                                    </div>
                                    <!-- end row -->
                                </fieldset>
                                <!-- end fieldset -->
                            </div>
                            <!-- end step-1 -->
                            <!-- begin step-2 -->
                            <div id="step-2">
                                <!-- begin fieldset -->
                                <fieldset>
                                    <!-- begin row -->
                                    <div class="row">
                                        <!-- begin col-8 -->
                                        <div class="col-xl-8 offset-xl-2">
                                            <!-- begin form-group -->
                                            <div class="form-group row m-b-10">
                                                <label class="col-lg-4  col-form-label">Correo Electrónico<span class="text-danger">*</span></label>
                                                <div class="col-lg-9 col-xl-8">
                                                    <input type="email" name="userEmail"  <?php
                                                    if (!isset($emailUser)) {
                                                        echo 'placeholder="someone@example.com"';
                                                    } else {
                                                        echo 'value="' . $emailUser . '"';
                                                    }
                                                    ?> class="form-control" data-parsley-group="step-2" data-parsley-required="true" data-parsley-type="email" />
                                                </div>
                                            </div>
                                            <!-- end form-group -->
                                            <!-- begin form-group -->
                                            <div class="form-group row m-b-10">
                                                <label class="col-lg-4  col-form-label">Contraseña <span class="text-danger">*</span></label>
                                                <div class="col-lg-9 col-xl-8">
                                                    <input data-toggle="password" data-placement="after" class="form-control" type="password"  name="passEmail" placeholder="Contraseña brindada por Ministerio de Hacienda" data-parsley-group="step-2" data-parsley-required="true"  />
                                                </div>
                                            </div>
                                            <!-- end form-group -->

                                        </div>
                                        <!-- end col-8 -->
                                    </div>
                                    <!-- end row -->
                                </fieldset>
                                <!-- end fieldset -->
                            </div>
                            <!-- end step-2 -->

                            <!-- begin step-4 -->
                            <div id="step-3">
                                <div class="jumbotron m-b-0 text-center">
                                    <h2 class="display-4">¡ATENCIÓN!</h2>
                                    <p class="lead mb-4">Si se realiza alguna modificación de los datos suminitrados en la plataforma ATV o correo electrónico , también debe de realizar la actualizacion de dichos datos en nuestra plataforma. <br />La equidad de estos datos en ambos sistema es importante para el buen funcionamiento del sistema. </p>

                                    <button type="submit" class="btn btn-primary btn-lg">Guardar</button>
                                </div>
                            </div>
                            <!-- end step-4 -->
                        </div>
                        <!-- end wizard-content -->
                    </div>
                    <!-- end wizard -->
                </form>
                <!-- end wizard-form -->
            </div>
            <div class="modal-footer">
                <a href="javascript:;" class="btn btn-dark" data-dismiss="modal">Close</a>
            </div>
        </div>
    </div>
</div>

<!-- ================== BEGIN BASE JS ================== -->
<script src="public/js/app.min.js"></script>
<script src="public/js/theme/transparent.min.js"></script>
<!-- ================== END BASE JS ================== -->

<!-- ================== BEGIN PAGE LEVEL JS ================== -->
<script src="public/plugins/d3/d3.min.js"></script>
<script src="public/plugins/nvd3/build/nv.d3.min.js"></script>
<script src="public/plugins/jvectormap-next/jquery-jvectormap.min.js"></script>
<script src="public/plugins/jvectormap-next/jquery-jvectormap-world-mill.js"></script>
<script src="public/plugins/bootstrap-calendar/js/bootstrap_calendar.min.js"></script>
<script src="public/plugins/gritter/js/jquery.gritter.js"></script>
<script>
    COLOR_GREEN = '#00cbff';
</script>
<script src="public/js/demo/dashboard-v2.js"></script>
<!-- ================== END PAGE LEVEL JS ================== -->
<!-- ================== BEGIN PAGE LEVEL JS ================== -->
<script src="public/plugins/gritter/js/jquery.gritter.js"></script>
<script src="public/plugins/sweetalert/dist/sweetalert.min.js"></script>
<script src="public/js/demo/ui-modal-notification.demo.js"></script>
<!-- ================== END PAGE LEVEL JS ================== -->
<!-- ================== BEGIN PAGE LEVEL JS ================== -->
<script src="public/plugins/jquery-migrate/dist/jquery-migrate.min.js"></script>
<script src="public/plugins/moment/moment.js"></script>
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
<!-- ================== BEGIN PAGE LEVEL JS ================== -->
	<script src="public/plugins/dropzone/dist/dropzone.js"></script>
	<script src="public/plugins/highlight.js/highlight.min.js"></script>
	<script src="public/js/demo/render.highlight.js"></script>
	<!-- ================== END PAGE LEVEL JS ================== -->
<script src="public/js/demo/form-plugins.demo.js"></script>
<!-- ================== END PAGE LEVEL JS ================== -->
<!-- ================== BEGIN PAGE LEVEL JS ================== -->
<script src="public/plugins/parsleyjs/dist/parsley.js"></script>
<script src="public/plugins/smartwizard/dist/js/jquery.smartWizard.js"></script>
<script src="public/js/demo/form-wizards-validation.demo.js"></script>
<!-- ================== END PAGE LEVEL JS ================== -->
<!-- ================== BEGIN PAGE LEVEL JS ================== -->
	<script src="public/plugins/datatables.net/js/jquery.dataTables.min.js"></script>
	<script src="public/plugins/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
	<script src="public/plugins/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
	<script src="public/plugins/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>
	<script src="public/plugins/datatables.net-select/js/dataTables.select.min.js"></script>
	<script src="public/plugins/datatables.net-select-bs4/js/select.bootstrap4.min.js"></script>
	<script src="public/js/demo/table-manage-select.demo.js"></script>
	<!-- ================== END PAGE LEVEL JS ================== -->
        <!-- ================== BEGIN PAGE LEVEL JS ================== -->
	<script src="public/plugins/datatables.net-select/js/dataTables.select.min.js"></script>
	<script src="public/plugins/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
	<script src="public/plugins/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js"></script>
	<script src="public/plugins/datatables.net-buttons/js/buttons.colVis.min.js"></script>
	<script src="public/plugins/datatables.net-buttons/js/buttons.flash.min.js"></script>
	<script src="public/plugins/datatables.net-buttons/js/buttons.html5.js"></script>
	<script src="public/plugins/datatables.net-buttons/js/buttons.print.min.js"></script>
	<script src="public/plugins/pdfmake/build/pdfmake.min.js"></script>
	<script src="public/plugins/pdfmake/build/vfs_fonts.js"></script>
	<script src="public/plugins/jszip/dist/jszip.min.js"></script>
	<!-- ================== END PAGE LEVEL JS ================== -->
<!--Start of Tawk.to Script-->
<script type="text/javascript">
var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
(function(){
var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
s1.async=true;
s1.src='https://embed.tawk.to/5d7684f8eb1a6b0be60bc353/default';
s1.charset='UTF-8';
s1.setAttribute('crossorigin','*');
s0.parentNode.insertBefore(s1,s0);
})();
</script>
<!--End of Tawk.to Script-->

</body>
</html>



