
<?php
/**
 * Description of companyView
 *
 * @author Kevin Campos Rodríguez
 */
include ("view/header.php");
?>
<!-- begin breadcrumb -->
<ol class="breadcrumb float-xl-right">
    <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
    <li class="breadcrumb-item active"><a>Comprobantes</a></li>
</ol>
<!-- end breadcrumb -->
<!-- begin page-header -->
<h1 class="page-header">Listado de comprobantes<small></small></h1>
<!-- end page-header -->
<!-- begin row -->
<div class="row ">
    <!-- begin col-10 -->
    <div class="col-xl-12">
        <div class="panel panel-inverse">
            <!-- begin panel-heading -->
            <div class="panel-heading">
                <h4 class="panel-title">Tabla de comprobantes</h4>
               <a href="#upload-Modal" title="Carga manual de archivos" data-toggle="modal" class="btn btn-default m-r-5"><i class="fa fa-cloud-upload-alt"></i> Cargar</a>
                

            </div>
            <!-- end panel-heading -->
            <!-- begin panel-body -->
            <div class="panel-body ">
                <table id="data-table-select" class="table table-striped table-bordered table-td-valign-middle">
                    <thead>
                        <tr>
                            <th width="1%">N°</th>
                            <th data-orderable="false">Acciones</th>
                            <th class="text-nowrap" >Fecha</th>
                            <th class="text-nowrap">Clave</th>
                            <th class="text-nowrap">Cedula E</th>
                            <th class="text-nowrap">Nombre Emisor</th>
                            <th class="text-nowrap">Detalle</th>
                            <th class="text-nowrap">Moneda</th>   
                            <th class="text-nowrap">Impuesto</th>
                            <th class="text-nowrap">Total</th>
                            <th class="text-nowrap">Cedula R</th>
                            <th class="text-nowrap">Nombre Receptor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $data = json_decode($vars, true);
                        $cont = 1;
                        foreach ($data as $value) {
                            echo '<tr class="gradeA">';
                            echo '<td class="f-s-600 text-white" width="1%">' . $cont . '</td>';
                            echo '<td><div class="btn-group">';
                            if (file_exists('files/' . $_SESSION['idCard'] . '/Recibidos/Sinprocesar/' . $value['clave'] . '/' . $value['PDF'])) {
                                echo '<a class="btn btn-default fa fa-search" href="files/' . $_SESSION['idCard'] . '/Recibidos/Sinprocesar/' . $value['clave'] . '/' . $value['PDF'] . '" target="_blank"> </a>';
                            } else {
                                echo "<button type='button' data-dismiss='modal' data-backdrop='false' class='btn btn-default fa fa-search' onclick='viewInvoice(" . json_encode($value) . ")'></button>";
                            }
                            echo '  <a class="btn btn-default fa fa-thumbs-up" href="?controller=Expenses&action=process&c=1&key=' . $value['clave'] . '"></a>
                                    <a class="btn btn-default fa fa-thumbs-down" href="?controller=Expenses&action=process&c=3&key=' . $value['clave'] . '"></a>
                                    <a class="btn btn-default fa fa-trash" href="?controller=Expenses&action=delete&key=' . $value['clave'] . '"></a>
                                    <a class="btn btn-default fa fa-save" title = "Solo guardar en QB" href="?controller=Expenses&action=saveQB&key=' . $value['clave'] . '"></a>
                                 </td>';
                            echo '<td>' . $value['fecha'][0] . '</td>';
                            echo '<td>' . "D" . $value['clave'] . '</td>';
                            echo '<td>' . $value['idEmisor'] . '</td>';
                            echo '<td>' . $value['Emisor'][0] . '</td>';
                            echo '<td>' . $value['detalle']["LineaDetalle"]["Detalle"] . '</td>';
                            echo '<td>' . $value['moneda'] . '</td>';
                            if (isset($value['impuesto'])) {
                                echo '<td>' . round($value['impuesto'],2) . '</td>';
                            } else {
                                echo '<td>0</td>';
                            }
                            echo '<td>' . round($value['monto'],2) . '</td>';
                            echo '<td>' . $value['idReceptor'][0] . '</td>';
                            echo '<td>' . $value['Receptor'][0] . '</td>';
                            echo '</tr>';

                            $cont++;
                        }
                        ?>

                    </tbody>
                </table>
            </div>
            <!-- end panel-body -->
        </div>
    </div>
    <!-- end col-10 -->
</div>
<!-- end row -->
<?php include ("view/footer.php"); ?>


<script type="text/javascript">

      function viewInvoice(datos) {
        var con = "" + datos["consecutivo"];
        var res = con.substring(8, 10);
        if (res == "01") {
            document.getElementById("tipoDoc").innerHTML = "Factura Electronica";
        }
        if (res == "03") {
            document.getElementById("tipoDoc").innerHTML = "Nota de Credito";
        }
        if (res == "04") {
            document.getElementById("tipoDoc").innerHTML = "Tiquete Electronico";
        }
        var detalle = datos["detalle"];
        var detalles = '';
        var cont = 0;

        for (var k in detalle) {
            if (detalle[k]["NumeroLinea"] != null) {
                
                detalles += '<tr>' +
                        '<td>' + detalle[k]["NumeroLinea"] + '</td>' +
                        '<td>' + detalle[k]["Detalle"] + 'dd</td>' +
                        '<td class="right">' + detalle[k]["Cantidad"] + '</td>' +
                        '<td class="right">' + detalle[k]["PrecioUnitario"] + '</td>';
                        
                 if(detalle[k]["Impuesto"] != null){
                    detalles +=        
                        '<td class="right">' + detalle[k]["Impuesto"]["Monto"] + '</td>';
                    if(detalle[k]["Impuesto"]["Exoneracion"] != null){
                        detalles +=        
                            '<td class="right">' + detalle[k]["Impuesto"]["Exoneracion"]["MontoExoneracion"] + '</td>';
                     }else{
                       detalles +=        
                            '<td class="right">0</td>';  
                     }
                 }else{
                   detalles +=        
                        '<td class="right">0</td>'+
                        '<td class="right">0</td>';
                 }
                 if(detalle[k]["Descuento"] != null){
                    detalles +=        
                        '<td class="right">' + detalle[k]["Descuento"] + '</td>';
                 }else{
                   detalles +=        
                        '<td class="right">0</td>';  
                 }
                 detalles +=
                        '<td class="right"><strong>' + detalle[k]["MontoTotalLinea"] + '</strong></td>' +
                        '</tr>';
            } else {
                for (var j in detalle[k]) {
                    detalles += '<tr>' +
                            '<td>' + detalle[k][j]["NumeroLinea"] + '</td>' +
                            '<td>' + detalle[k][j]["Detalle"] + '</td>' +
                            '<td class="right">' + detalle[k][j]["Cantidad"] + '</td>' +
                            '<td class="right">' + (detalle[k][j]["PrecioUnitario"] + 0) + '</td>';
                        
                             if(detalle[k][j]["Impuesto"] != null){
                                detalles +=        
                                    '<td class="right">' + detalle[k][j]["Impuesto"]["Monto"] + '</td>';
                                if(detalle[k][j]["Impuesto"]["Exoneracion"] != null){
                                    detalles +=        
                                        '<td class="right">' + detalle[k][j]["Impuesto"]["Exoneracion"]["MontoExoneracion"] + '</td>';
                                 }else{
                                   detalles +=        
                                        '<td class="right">0</td>';  
                                 }
                             }else{
                               detalles +=      
                                    '<td class="right">0</td>'+
                                    '<td class="right">0</td>';  
                             }
                              if(detalle[k][j]["Descuento"] != null){
                                    detalles +=        
                                        '<td class="right">' + detalle[k][j]["Descuento"] + '</td>';
                                 }else{
                                   detalles +=        
                                        '<td class="right">0</td>';  
                                 }
                             detalles +=
                            '<td class="right"><strong>' + detalle[k][j]["MontoTotalLinea"] + '</strong></td>' +
                            '</tr>';
                }
            }
        }
        document.getElementById("detalleDoc").innerHTML = detalles;
        document.getElementById("fechaDoc").innerHTML = "Fecha: " + datos["fecha"];
        document.getElementById("claveDoc").innerHTML = "Clave: " + datos["clave"];
        document.getElementById("consecutivo").innerHTML = "Consecutivo: " + datos["consecutivo"];
        document.getElementById("idEmisorDoc").innerHTML = datos["idEmisor"];
        document.getElementById("nombreEmisorDoc").innerHTML = datos["Emisor"];
        document.getElementById("correoEmisorDoc").innerHTML = datos["correoEmisor"];
        document.getElementById("idReceptorDoc").innerHTML = datos["idReceptor"];
        document.getElementById("nombreReceptorDoc").innerHTML = datos["Receptor"];
        document.getElementById("correoReceptorDoc").innerHTML = datos["correoReceptor"];
        document.getElementById("totalDoc").innerHTML = datos["monto"];
        document.getElementById("totalImpuesto").innerHTML = datos["impuesto"];
        document.getElementById("subtotal").innerHTML = datos["monto"] - datos["impuesto"];
        if(datos["descuento"] != null){
           document.getElementById("totalDescuento").innerHTML = datos["descuento"]; 
        }else{
           document.getElementById("totalDescuento").innerHTML = 0; 
        }
        
        $("#invoice-Modal").modal("toggle");
        var m = document.getElementById("invoice-Modal");
    }

</script>
<script language="Javascript">
    function printDiv() {
        var objeto = document.getElementById('invoiceModal');

        //obtenemos el objeto a imprimir
        var ventana = window.open('', '_blank');  //abrimos una ventana vac¨ªa nueva
        ventana.document.write(objeto.innerHTML);  //imprimimos el HTML del objeto en la nueva ventana
        ventana.document.close();  //cerramos el documento
        ventana.print();  //imprimimos la ventana
        ventana.close();  //cerramos la ventana
    }
</script>
<!-- #modal-dialog -->
<div class="modal modal-message" id="invoice-Modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                </ol>
                <!-- end breadcrumb -->
                <!-- end page-header -->
                <!-- begin invoice -->
                <div class="invoice">
                    <!-- begin invoice-company -->
                    <div class="invoice-company">
                        <span class="pull-right hidden-print">
                            <a href="javascript:;" onclick="window.print()" class="btn btn-sm btn-white m-b-10"><i class="fa fa-print t-plus-1 fa-fw fa-lg"></i> Print</a>
                            <a href="javascript:;" class="btn btn-sm btn-dark m-b-10" data-dismiss="modal">Close</a>
                        </span>  
                        <img src="public/img/logo.png" width="80" height="80" alt="Sincronizador QBO-MH"/>
                        <strong class="text-white" id="tipoDoc"></strong>
                    </div>
                    <!-- end invoice-company -->
                    <!-- begin invoice-header -->
                    <div class="invoice-header">
                        <div class="invoice-from">
                            <small>Emisor</small>
                            <address class="m-t-5 m-b-5">
                                <div class="text-white" id="nombreEmisorDoc"></div>
                                <div id="idEmisorDoc"></div>
                                <div id="correoEmisorDoc"></div>
                            </address>
                        </div>
                        <div class="invoice-to">
                            <small>Receptor</small>
                            <address class="m-t-5 m-b-5">
                                <div class="text-white" id="nombreReceptorDoc"></div>
                                <div id="idReceptorDoc"></div>
                                <div id="correoReceptorDoc"></div>
                            </address>
                        </div>
                         <div class="invoice-to">
                             <small id="fechaDoc"></small>
                            <address class="m-t-5 m-b-5">
                                <div class="text-white" id="consecutivo"></div>
                                <small id="claveDoc"></small>                                
                            </address>
                        </div>
                    </div>
                    <!-- end invoice-header -->
                    <!-- begin invoice-content -->
                    <div class="invoice-content">
                        <!-- begin table-responsive -->
                        <div class="table-responsive">
                            <table class="table table-invoice">
                                <thead>
                                    <tr>
                                        <th class="number" width="1%">N</th>
                                        <th>Descripcion</th>                                        
                                        <th class="text" width="10%">Cantidad</th>
                                        <th class="text" width="10%">Precio</th>
                                        <th class="text" width="10%">Impuesto</th>
                                        <th class="text" width="10%">Exoneracion</th>
                                        <th class="text" width="10%">Total</th>
                                    </tr>
                                </thead>
                                <tbody id="detalleDoc">

                                </tbody>
                            </table>
                        </div>
                        <!-- end table-responsive -->
                       
                        <!-- begin invoice-price -->
                        <div class="invoice-price">
                            <div class="invoice-price-left">
                                <div class="invoice-price-row">
                                    <div class="sub-price">
                                        <small>SUBTOTAL</small>
                                        <span class="text-white" id="subtotal"></span>
                                    </div>
                                    <div class="sub-price">
                                        <i class="fa fa-plus text-muted"></i>
                                    </div>
                                    <div class="sub-price">
                                        <small>IMPUESTOS</small>
                                        <span class="text-white" id="totalImpuesto"></span>
                                    </div>
                                    <div class="sub-price">
                                        <i class="fa fa-minus text-muted"></i>
                                    </div>
                                    <div class="sub-price">
                                        <small>DESCUENTOS</small>
                                        <span class="text-white" id="totalDescuento"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="invoice-price-right">
                                <div class="invoice-price-row">
                                    <div class="sub-price">
                                        <small>TOTAL</small> 
                                        <span class="text-white text-muted" id="totalDoc"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- end invoice-price -->
                    </div>
                    <!-- end invoice-content -->
                    <!-- begin invoice-note -->
                    <div class="invoice-note">
                        * Vista genereado por el sistema de sincronizacion, el archivo PDF no se a podido relacionar con el xml de la factura
                    </div>
                    <!-- end invoice-note -->
                    <!-- begin invoice-footer -->
                    <div class="invoice-footer">
                        <p class="text-center m-b-5 f-w-600">
                            GRACIAS POR USAR NUESTROS SISTEMAS
                        </p>
                        <p class="text-center">
                            <span class="m-r-10"><i class="fa fa-fw fa-lg fa-globe"></i> contafast.net</span>
                            <span class="m-r-10"><i class="fa fa-fw fa-lg fa-phone-volume"></i> T:8399-6444</span>
                            <span class="m-r-10"><i class="fa fa-fw fa-lg fa-envelope"></i> info@contafast.net</span>
                        </p>
                    </div>
                    <!-- end invoice-footer -->
                </div>
                <!-- end invoice -->
            </div>
            <div class="modal-footer hidden-print">
                
            </div>
        </div>
    </div>
</div>

	<!-- #modal-dialog -->
	<div class="modal fade" id="upload-Modal">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title">Cargar Archivos de Factura Electrónica</h4>
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				</div>
				<div class="modal-body">
				    <form class="form-horizontal" action="?controller=Expenses&action=upload" data-parsley-validate="true" name="demo-form" enctype="multipart/form-data" method="POST">
								<div class="form-group row m-b-15">
									<label class="col-md-2 col-sm-2 col-form-label" for="claveUp">Clave * :</label>
									<div class="col-md-10 col-sm-10">
										<input class="form-control" type="text" id="claveUp" name="claveUp" placeholder="Clave numerica 50 digitos" pattern="[0-9]{50}" data-parsley-required="true" />
									</div>
								</div>
								<div class="form-group row m-b-15">
									<label class="col-md-2 col-sm-2 col-form-label" for="xmlFact">XML Factura * :</label>
									<div class="col-md-10 col-sm-10">
										<input class="form-control" type="file" id="xmlFact" name="xmlFact" placeholder="XML Factura" data-parsley-required="true" accept=".xml"/>
									</div>
								</div>
								<div class="form-group row m-b-15">
									<label class="col-md-2 col-sm-2 col-form-label" for="xmlResp">XML Respuesta  :</label>
									<div class="col-md-10 col-sm-10">
										<input class="form-control" type="file" id="xmlResp" name="xmlResp" placeholder="XML Respuesta" data-parsley-required="true" accept=".xml"/>
									</div>
								</div>
								<div class="form-group row m-b-15">
									<label class="col-md-2 col-sm-2 col-form-label" for="pdfFact">PDF Factura  :</label>
									<div class="col-md-10 col-sm-10">
										<input class="form-control" type="file" id="pdfFact" name="pdfFact" placeholder="PDF Factura" accept=".pdf"/>
									</div>
								</div>
								<div class="form-group row m-b-0">
									<div class="col-md-12 col-sm-12">
										<button type="submit" class="btn btn-primary col-md-12">Cargar</button>
									</div>
								</div>
							</form>
                      
				</div>
				<div class="modal-footer">
				     
					<a href="javascript:;" class="btn btn-white" data-dismiss="modal">Cerrar</a>
				</div>
			</div>
		</div>
	</div>