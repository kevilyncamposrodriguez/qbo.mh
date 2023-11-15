
<?php
/**
 * Description of indexView
 *
 * @author Kevin Campos Rodríguez
 */
include ("view/header.php");
if (isset($vars['contingencia'])) {
    $contingencia = $vars['contingencia'];
} else{
    $contingencia = 2;
}

if (isset($vars['data'])) {
    $data = json_decode($vars['data'], true);
    $ae = $data['CompanyAddr']['PostalCode'];
    $name = $data['CompanyName'];
    $idcard = $data['EmployerId'];
    $address = $data['CompanyAddr']['Line1'] . ', ' . $data['CompanyAddr']['City'] . ', ' . $data['CompanyAddr']['Country'];
    $phone = str_replace(array("+", " "),'',$data['PrimaryPhone']['FreeFormNumber']);
    $mail = $data['Email']['Address'];
} else {
    $ae = "N/R";
    $name = "N/R";
    $idcard = "N/R";
    $address = "N/R";
    $phone = "N/R";
    $mail = "N/R";
}
if (isset($vars['results'])) {
    $results = json_decode($vars['results'], true);
    $p = $results["p"];
    $np = $results["np"];
    $acepted = $results["acepted"];
    $rejected = $results["rejected"];
    $pP = ($p / ($p + $np)) * 100;
    $pNP = ($np / ($p + $np)) * 100;
    $pA = ($acepted / $p) * 100;
    $pR = ($rejected / $p) * 100;
} else {
    $p = 0;
    $np = 0;
    $acepted = 0;
    $rejected = 0;
}
?>
 <title>Sincronizador</title>  

<!-- begin row -->
<div class="row">
    <!-- begin col-6 -->
    <div class="col-xl-6">
        <!-- begin card -->
        <div class="card border-0 mb-4 overflow-hidden">
            <!-- begin card-body -->
            <div class="card-body">
                <!-- begin row -->
                <div class="row">
                    <!-- begin col-7 -->
                    <div class="col-xl-7 col-lg-8">
                        <!-- begin title -->
                        <div class="mb-3 text-grey">
                            <h1 class="page-header mb-3"><?php echo $name; ?></h1>

                        </div>
                        <!-- end title -->


                        <hr class="bg-white-transparent-9" />
                        <!-- begin row -->
                        <div class="row text-truncate">
                            <!-- begin col-6 -->
                            <div class="col-6">
                                <div class="f-s-12 text-grey">CEDULA:</div>
                            </div>
                            <!-- end col-6 -->
                            <!-- begin col-6 -->
                            <div class="col-6">
                                <div class="f-s-12 text-grey"><?php echo $idcard; ?></div>
                            </div>
                            <!-- end col-6 -->
                            <!-- begin col-6 -->
                            <div class="col-6">
                                <div class="f-s-12 text-grey">Actividad Económica:</div>
                            </div>
                            <!-- end col-6 -->
                            <!-- begin col-6 -->
                            <div class="col-6">
                                <div class="f-s-12 text-grey"><?php echo $ae; ?></div>
                            </div>
                            <!-- end col-6 -->
                            <!-- begin col-6 -->
                            <div class="col-6">
                                <div class="f-s-12 text-grey">UBICACIÓN:</div>
                            </div>
                            <!-- end col-6 -->
                            <!-- begin col-6 -->
                            <div class="col-6">
                                <div class="f-s-12 text-grey"><?php echo $address; ?></div>
                            </div>
                            <!-- end col-6 -->
                            <!-- begin col-6 -->
                            <div class="col-6">
                                <div class="f-s-12 text-grey">TELEFONO:</div>
                            </div>
                            <!-- end col-6 -->
                            <!-- begin col-6 -->
                            <div class="col-6">
                                <div class="f-s-12 text-grey"><?php echo $phone; ?></div>
                            </div>
                            <!-- end col-6 -->
                            <!-- begin col-6 -->
                            <div class="col-6">
                                <div class="f-s-12 text-grey">CORREO:</div>
                            </div>
                            <!-- end col-6 -->
                            <!-- begin col-6 -->
                            <div class="col-6">
                                <div class="f-s-12 text-grey"><?php echo $mail; ?></div>
                            </div>
                            <!-- end col-6 -->
                        </div>
                        <!-- end row -->
                    </div>
                    <!-- end col-7 -->
                    <!-- begin col-5 -->
                    <div class="col-xl-5 col-lg-4 align-items-center d-flex justify-content-center">
                        <img src="public/img/svg/img-1.svg" height="100px" class="d-none d-lg-block" />
                    </div>
                    <!-- end col-5 -->
                </div>
                <!-- end row -->
            </div>
            <!-- end card-body -->
        </div>
        <!-- end card -->
    </div>
    <!-- end col-6 -->
    <div class="col-xl-6 col-md-6">
    <div class="row">
        <div class="col-xl-12 col-md-12">
            <div class="card border-0 mb-4 overflow-hidden">
            <!-- begin card-body -->
            <div class="card-body">
                <!-- begin row -->
                <div class="row">
                   <!-- begin col-6 -->
                    <div class="col-6">
                        <div class="f-s-12 text-grey">Activar Contingencia:</div>
                    </div>
                    <!-- end col-6 -->
                    <!-- begin col-6 -->
                    <div class="col-6">
                        <div class="switcher switcher-success">
							<input type="checkbox" name="contingencia" id="switcher_checkbox_2"  onchange="doalert(this)" >
							<label for="switcher_checkbox_2"></label>
						</div>
                    </div>
                    <!-- end col-6 -->
                </div>
            </div>
        </div>
        </div>
    <!-- begin col-3 -->
    <div class="col-xl-6 col-md-6">
        <div class="widget widget-stats bg-gradient-green">
            <div class="stats-icon stats-icon-lg"><i class="fa fa-dollar-sign fa-fw"></i></div>
            <div class="stats-content">
                <div class="stats-title">COMPRA</div>
                <div class="stats-number">₡<?php if (isset($_SESSION['purchase'])) {
    echo $_SESSION['purchase'];
} else {
    echo "N/R";
} ?></div>
            </div>
        </div>
    </div>
    
    <!-- end col-3 -->
    
    <!-- begin col-3 -->
    <div class="col-xl-6 col-md-6">
        <div class="widget widget-stats bg-gradient-blue-indigo">
            <div class="stats-icon stats-icon-lg"><i class="fa fa-dollar-sign fa-fw"></i></div>
            <div class="stats-content">
                <div class="stats-title">VENTA</div>
                <div class="stats-number">₡<?php if (isset($_SESSION['sale'])) {
    echo $_SESSION['sale'];
} else {
    echo "N/R";
} ?></div>

            </div>
        </div>
    </div>
    </div>
    </div>
    <!-- end col-3 -->
</div>
<!-- end row -->
<!-- begin row -->
<div class="row">
    <!-- begin col-6 -->
    <div class="col-xl-12">
        <!-- begin row -->
        <div class="row">
            <!-- begin col-6 -->
            <div class="col-sm-12">
                <!-- begin card -->
                <div class="card border-0 text-truncate mb-1">
                    <!-- begin card-body -->
                    <div class="card-body">
                        <!-- begin title -->
                        <div class="mb-1 text-grey">
                            <b class="mb-1">DOCUMENTOS INGRESADOS</b> 
                        </div>
                        <!-- end title -->
                        <!-- begin conversion-rate -->
                        <div class="d-flex align-items-center mb-1">
                            <h2 class="text-white mb-sm-1"><span data-animation="number" data-value="<?PHP echo $p . " ARCHIVOS"; ?>">0.00</span></h2>
                            <div class="ml-auto">
                                <div id="conversion-rate-sparkline"></div>
                            </div>
                        </div>
                        <!-- end conversion-rate -->
                        <!-- begin row -->
                        <div class="row align-items-center p-b-1">
                            <!-- begin col-4 -->
                            <div class="col-4">
                                
                            </div>
                            <!-- end col-4 -->
                            <!-- begin col-8 -->
                            <div class="col-6">
                                <div class="m-b-1 text-truncate"><h4>Documentos aceptados <?PHP echo " ".round($pA, 2); ?>%</h4></div>
                                <div class="d-flex align-items-center m-b-1">
                                    <div class="flex-grow-1">
                                        <div class="progress progress-xs rounded-corner bg-white-transparent-5">
                                            <div class="progress-bar progress-bar-striped bg-green-darker" data-animation="width" data-value="<?PHP echo $pA; ?>%" style="width: 0%"></div>
                                        </div>
                                    </div>
                                    <div class="ml-1 f-s-1 width-30 text-center"><span data-animation="number" data-value="<?PHP echo $acepted . " ARCHIVOS"; ?>">0</span>%</div>
                                </div>                               
                            </div>
                            <!-- end col-8 -->
                        </div>
                        <!-- end row -->
                        <!-- begin row -->
                        <div class="row align-items-center p-b-1">
                            <!-- begin col-4 -->
                            <div class="col-4">
                                <div class="height-100 d-flex align-items-center justify-content-center">
                                    <img src="public/img/logoQB.png" width="100%"  />
                                </div>
                            </div>
                            <!-- end col-4 -->
                            <!-- begin col-8 -->
                            <div class="col-6">
                                <div class="m-b-1 text-truncate"><h4>Documentos rechazados <?PHP echo " ".round($pR, 2); ?>%</h4></div>
                                <div class="d-flex align-items-center m-b-1">
                                    <div class="flex-grow-1">
                                        <div class="progress progress-xs rounded-corner bg-white-transparent-5">
                                            <div class="progress-bar progress-bar-striped bg-red-darker" data-animation="width" data-value="<?PHP echo $pR; ?>%" style="width: 0%"></div>
                                        </div>
                                    </div>
                                    <div class="ml-2 f-s-1 width-30 text-center"><span data-animation="number" data-value="<?PHP echo $rejected . " ARCHIVOS"; ?>">0</span></div>
                                </div>
                            </div>
                            <!-- end col-8 -->
                        </div>
                        <!-- end row -->
                        <!-- begin row -->
                        <div class="row align-items-center p-b-1">
                            <!-- begin col-4 -->
                            <div class="col-4">
                               
                            </div>
                            <!-- end col-4 -->
                            <!-- begin col-8 -->
                            <div class="col-6">
                                <div class="m-b-1 text-truncate"><h5>Documentos sin procesar <?php echo " ".round($pNP, 2); ?>%</h5></div>
                                <div class="d-flex align-items-center m-b-1">
                                    <div class="flex-grow-1">
                                        <div class="progress progress-xs rounded-corner bg-white-transparent-5">
                                            <div class="progress-bar progress-bar-striped bg-indigo" data-animation="width" data-value="<?PHP echo $pNP; ?>%" style="width: 0%"></div>
                                        </div>
                                    </div>
                                    <div class="ml-1 f-s-1 width-30 text-center"><span data-animation="number" data-value="<?PHP echo $np . " ARCHIVOS"; ?>">0</span></div>
                                </div>
                                
                            </div>
                            <!-- end col-8 -->
                        </div>
                        <!-- end row -->
                    </div>
                    <!-- end card-body -->
                </div>
                <!-- end card -->
            </div>
            <!-- end col-6 -->
        </div>
        <!-- end row -->
    </div>
    <!-- end col-6 -->
</div>
<!-- end row -->
<!-- end row -->
<?php include ("view/footer.php"); ?>
<script language="Javascript">
  function doalert(checkboxElem) {
  if (checkboxElem.checked) {
    alert ("Metodo para envio de facturas fuera de tiempo activado");
  } else {
   alert ("Metodo para envio de facturas fuera de tiempo desactivado");
  }
}
</script>