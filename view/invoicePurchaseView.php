
<?php
/**
 * Description of companyView
 *
 * @author Kevin Campos Rodríguez
 */
include ("view/header.php");
$data = json_decode($vars, true);
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
                <button class="btn btn-danger btn-sm" type="button" data-dismiss='modal' data-backdrop='true' onclick='viewCreateInvoice(<?php echo json_encode($vars) ?>)'>Nueva Factura de Compra</button>
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
                            <th class="text-nowrap">Moneda</th>   
                            <th class="text-nowrap">Impuesto</th>
                            <th class="text-nowrap">Exoneracion</th>
                            <th class="text-nowrap">Descuento</th>
                            <th class="text-nowrap">Total</th>
                            <th class="text-nowrap">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $data = $vars["fcs"];
                        $cont = 1;
                        foreach ($data as $value) {
                            echo '<tr class="gradeA">';
                            echo '<td class="f-s-600 text-white" width="1%">' . $cont . '</td>';
                            echo '<td><div class="btn-group">';
                                echo '<a class="btn btn-default fa fa-search" href="files/' . $_SESSION['username'] . '/Recibidos/FacturaCompra/' . $value['clave'] . '/' .  $value['clave'] . '.pdf" target="_blank"> </a>';
                           
                            $filename = 'files/' . $_SESSION['username'] . '/Recibidos/FacturaCompra/' . $value['clave'] . '/' . $value['clave'] . '-F.xml';
                            if (file_exists($filename)) {
                                echo ' <a class="btn btn-default" download="' . $value['clave'] . '.xml" href="' . $filename . '" title = "Descargar XML Factura" <i class = " txt-color-blue">F</i></a>';
                            }
                            $filenamer = 'files/' . $_SESSION['username'] . '/Recibidos/FacturaCompra/' . $value['clave'] . '/' . $value['clave'] . '-R.xml';
                            if (file_exists($filenamer)) {
                                echo ' <a class="btn btn-default" download="' . $value['clave'] . '-R.xml" href="' . $filenamer  . '" title = "Descargar XML Respuesta" <i class = " txt-color-blue">R</i></a>';
                            }else{
                                echo '<a class="btn btn-default fa fa-sync-alt" href="?controller=InvoicePurchase&action=refreshState&key='.$value["clave"].'" title = "Actualizar Estado"> </a>';
                               
                            }
                            echo ' <a class="btn btn-default fa fa-save" title = "Cargar archivos a QB" href="?controller=InvoicePurchase&action=saveQBP&key=' . $value['clave'] . '"></a>';
                            echo '</td>';
                            echo '<td>' . $value['fechareferencia'] . '</td>';
                            echo '<td>' . "FC" . $value['clave'] . '</td>';
                            echo '<td>' . $value['cedulaE'] . '</td>';
                            echo '<td>' . $value['nombreE'] . '</td>';
                            echo '<td>' . $value['moneda'] . '</td>';
                            if (isset($value['impuestototal'])) {
                                echo '<td>' . round($value['impuestototal'],2) . '</td>';
                            } else {
                                echo '<td>0</td>';
                            }
                            echo '<td>' . round($value['exoneraciontotal']) . '</td>';
                            echo '<td>' . round($value['descuentototal']) . '</td>';
                            echo '<td>' . round($value['total'],2) . '</td>';
                            echo '<td>' . $value['estadoMH'] . '</td>';
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
<script src="public/js/functExpenses.js"></script>
<script type="text/javascript">
    function viewCreateInvoice(datos) {
        var f = new Date();
        //var con = "" + datos["consecutivo"];  
        document.getElementById("consecutivo").innerHTML = datos["consecutivo"];
        document.getElementById("fechaDoc").innerHTML =  f.toISOString();
        document.getElementById("claveDoc").innerHTML =  datos["clave"];
        document.getElementById("idEmisorDoc").innerHTML = datos["company"].EmployerId;
        document.getElementById("nombreEmisorDoc").innerHTML = datos["company"].CompanyName;
        document.getElementById("correoEmisorDoc").innerHTML = datos["company"].Email.Address;
        var phone = (datos["company"].PrimaryPhone.FreeFormNumber).replace(" ","");
        document.getElementById("telefonoEmisorDoc").innerHTML = phone.substr(-8,8);
        
      $("#invoice-Modal").modal("toggle");
      var m = document.getElementById("invoice-Modal");  
    }
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
                        '<td>' + detalle[k]["Detalle"] + '</td>' +
                        '<td class="right">' + detalle[k]["Cantidad"] + '</td>' +
                        '<td class="right">' + parseFloat(detalle[k]["PrecioUnitario"]) + '</td>' +
                        '<td class="right">' + parseFloat(detalle[k]["Impuesto"]["Monto"]) + '</td>' +
                        '<td class="right"><strong>' + parseFloat(detalle[k]["MontoTotalLinea"]) + '</strong></td>' +
                        '</tr>';
            } else {
                for (var j in detalle[k]) {
                    detalles += '<tr>' +
                            '<td>' + detalle[k][j]["NumeroLinea"] + '</td>' +
                            '<td>' + detalle[k][j]["Detalle"] + '</td>' +
                            '<td class="right">' + detalle[k][j]["Cantidad"] + '</td>' +
                            '<td class="right">' + (detalle[k][j]["PrecioUnitario"] + 0) + '</td>' +
                            '<td class="right"><strong>' + detalle[k][j]["MontoTotalLinea"] + '</strong></td>' +
                            '</tr>';
                }
            }
        }
        document.getElementById("detalleDoc").innerHTML = detalles;
        document.getElementById("fechaDoc").innerHTML = datos["fecha"];
        document.getElementById("claveDoc").innerHTML = datos["clave"];
        document.getElementById("consecutivo").innerHTML = datos["consecutivo"];
        document.getElementById("idEmisorDoc").innerHTML = datos["idEmisor"];
        document.getElementById("nombreEmisorDoc").innerHTML = datos["Emisor"];
        document.getElementById("correoEmisorDoc").innerHTML = datos["correoEmisor"];
        document.getElementById("idReceptorDoc").innerHTML = datos["idReceptor"];
        document.getElementById("nombreReceptorDoc").innerHTML = datos["Receptor"];
        document.getElementById("correoReceptorDoc").innerHTML = datos["correoReceptor"];
        document.getElementById("totalDoc").innerHTML = datos["monto"];
        document.getElementById("totalImpuesto").innerHTML = datos["impuesto"];
        document.getElementById("subtotal").innerHTML = datos["monto"] - datos["impuesto"];
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
<div class="modal modal-message" id="invoice-Modal" name="invoice-Modal">
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
                            
                            <a href="javascript:;" class="btn btn-sm btn-dark m-b-10" data-dismiss="modal">Close</a>
                        </span>  
                        <img src="public/img/logo.png" width="80" height="80" alt="Sincronizador QBO-MH"/>
                        <strong class="text-white" id="tipoDoc">Factura de Compra</strong>
                    </div>
                    <!-- end invoice-company -->
                    	<!-- begin panel-body -->
						<div class="panel panel-inverse" data-sortable-id="form-validation-1">
						<!-- begin panel-heading -->
						<!-- begin panel-body -->
						<div class="panel-body">
						    <div class="row">
							     <div class="invoice-from">
                             <strong class="text-white" >Receptor</strong>
                            <address class="m-t-5 m-b-5">
                                <div class="text-white" id="nombreEmisorDoc"></div>
                                <div class="text-white" id="idEmisorDoc"></div>
                                <div class="text-white" id="correoEmisorDoc"></div>
                                <div class="text-white" id="telefonoEmisorDoc"></div>
                            </address>
                             
                        </div>
                        <div class="invoice-to">
                            Fecha: 
                            <div class="text-white" id="fechaDoc"></div>
                            Consecutivo: 
                            <div class="text-white" id="consecutivo"></div>
                            Clave:
                            <div class="text-white" id="claveDoc"></div>
                             <small id="fechaDoc"></small>
                            <address class="m-t-5 m-b-5">
                                <div class="text-white" id="consecutivo"></div>                               
                            </address>
                        </div>
                        <div class="form-group row col-md-12">
							<label class="col-sm-3 col-form-label" for="message">Tipo Documento Ref:</label>
					        <div class="col-md-3">
								<select class="form-control " id="typeDocRef" name="typeDocRef" required>
								    <?php
								    
                                        if(isset($vars['tiposDocumento'])){
                                        $data = $vars['tiposDocumento'];
                                        if (is_array($data) ) {
                                            foreach ($data as $var) {
                                                echo '<option class="text-black" value="' . $var["codigo"] . '">' . $var["documento"] . '</option>';
                                            }
                                        }
                                        }
                                        ?>
									
								</select>
							</div>
							<label class="col-sm-3 col-form-label" for="searchIdcard">Numero Doc. Referencia:</label>
					        <div class="col-md-3 col-sm-3">
								<input class="form-control" type="number" id = "nRefDoc" name = "nRefDoc" data-parsley-type="number" placeholder="Numero de Referencia" required/>
							</div>
						</div>
                        <div class="form-group row col-md-12">
							<label class="col-lg-3 col-form-label">Fecha Doc. Referencia</label>
							<div class="col-lg-3">
								<input type="date" class="form-control" id="fReferencia" name="fReferencia" placeholder="Seleccionar fecha" data-date-format="YYYY MM DD" value="2020/01/01" required/>
							</div>
							<label class="col-sm-3 col-form-label" for="searchIdcard">Tipo cambio:</label>
					        <div class="col-md-2 col-sm-2">
								<input class="form-control" type="number" id = "tCambio" name = "tCambio" data-parsley-type="number" value="1" required />
							</div>
						</div>
						<div class="form-group row col-md-12">
					       <label class="col-sm-3 col-form-label" for="message">Codigo Referencia:</label>
					        <div class="col-md-3">
								<select class="form-control " id="codRef" name="codRef" required>
									<?php
								    
                                        if(isset($vars['referencias'])){
                                        $data = $vars['referencias'];
                                        if (is_array($data) ) {
                                            foreach ($data as $var) {
                                                echo '<option class="text-black" value="' . $var["codigo"] . '">' . $var["referencia"] . '</option>';
                                            }
                                        }
                                        }
                                        ?>
								</select>
							</div>
							<label class="col-sm-1 col-form-label" for="message">Razon:</label>
							<div class="col-md-5 col-sm-5">
								<input class="form-control" type="text" id="razon" name="razon" placeholder="Razon del documento" required/>
							</div>
							
						</div>
                        </div>
						</div>
						<!-- end panel-body -->
					</div>
                    	<!-- begin panel-body -->
						<div class="panel panel-inverse" data-sortable-id="form-validation-1">
						<!-- begin panel-heading -->
						<div class="panel-heading">
							<h4 class="panel-title">Datos de Emisor</h4>
							<div class="panel-heading-btn">
								<a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-warning" data-click="panel-collapse"><i class="fa fa-minus"></i></a>
							</div>
						</div>
						<!-- end panel-heading -->
						<!-- begin panel-body -->
						<div class="panel-body">
						    <div class="row">
						        <div class="form-group row col-md-12">
							        <label class="col-sm-3 col-form-label" for="searchIdcard">Buscar por cédula:</label>
							        <div class="col-md-4 col-sm-4">
										<input class="form-control" type="number" id = "searchIdCard" name = "searchIdCard" data-parsley-type="number" placeholder="9 a 13 Digitos" />
									</div>
									<div class="col-md-2 col-sm-2">
									    <a onclick="search1()" class="btn btn-danger" ><i class="fa fa-search"></i></a>
									</div>
								</div>
							    <div class="form-group row col-md-12">
							        <label class="col-sm-2 col-form-label" for="message">Tipo ced :</label>
							        <div class="col-md-3">
										<select class="form-control " id="typeIdCard" name="typeIdCard" required>
										<?php
								    
                                        if(isset($vars['tiposIdentificacion'])){
                                        $data = $vars['tiposIdentificacion'];
                                        if (is_array($data) ) {
                                            foreach ($data as $var) {
                                                echo '<option class="text-black" value="' . $var["codigo"] . '">' . $var["tipo"] . '</option>';
                                            }
                                        }
                                        }
                                        ?>
										</select>
									</div>
									<label class="col-md-2 col-sm-2 col-form-label" for="fullname">Nombre * :</label>
									<div class="col-md-5 col-sm-5">
										<input class="form-control" type="text" id="nameE" name="nameE" placeholder="Nombre Emisor" data-parsley-required="true" required />
									</div>
								</div>
								<div class="form-group row col-md-12">
								    <label class="col-sm-2 col-form-label" for="message">Cedula :</label>
									<div class="col-md-3 col-sm-3 ">
										<input class="form-control" type="text" id="idCardE" name="idCardE" data-parsley-type="number" placeholder="Cedula Emisor"  required/>
									</div>
									<label class="col-md-2 col-sm-2 col-form-label" for="email">Correo * :</label>
									<div class="col-md-5 col-sm-5">
										<input class="form-control" type="text" id="emailE" name="emailE" data-parsley-type="email" placeholder="ejemplo@dominio.com" data-parsley-required="true"  required/>
									</div>
								</div>
								<div class="form-group row col-md-12">
								    <label class="col-md-2 col-sm-2 col-form-label" for="message">Telefono :</label>
									<div class="col-md-3 col-sm-3">
										<input class="form-control" type="text" id="phoneE" name="phoneE" data-parsley-type="number" placeholder="N. Telefono"  required/>
									</div>
									<label class="col-md-2 col-sm-2 col-form-label" for="message">Forma pago :</label>
							        <div class="col-md-4">
										<select class="form-control" id="payE" name="payE" required>
											<?php
								    
                                        if(isset($vars['mediosPago'])){
                                        $data = $vars['mediosPago'];
                                        if (is_array($data) ) {
                                            foreach ($data as $var) {
                                                echo '<option class="text-black" value="' . $var["codigo"] . '">' . $var["mediopago"] . '</option>';
                                            }
                                        }
                                        }
                                        ?>
										</select>
									</div>
							    </div>
							    <div class="form-group row col-md-12">
									<label class="col-md-2 col-sm-2 col-form-label" for="message">Condicion venta:</label>
									<div class="col-md-3">
										<select class="form-control" id="typePay" name="typePay" required>
											<?php
								    
                                        if(isset($vars['condicionesVenta'])){
                                        $data = $vars['condicionesVenta'];
                                        if (is_array($data) ) {
                                            foreach ($data as $var) {
                                                echo '<option class="text-black" value="' . $var["codigo"] . '">' . $var["condicionventa"] . '</option>';
                                            }
                                        }
                                        }
                                        ?>
										</select>
									</div>
									<label class="col-md-2 col-sm-2 col-form-label" for="message">Plazo credito :</label>
									<div class="col-md-1 col-sm-1 ">
										<input class="form-control" type="text" id="credit" name="credit" data-parsley-type="number" value="1" />
									</div>
									
									<label class="col-sm-2 col-form-label" for="message">Moneda:</label>
					        <div class="col-md-2">
					            <select class="default-select2 form-control" id="currencyDoc" name="currencyDoc" required>
									<?php
								    
                                        if(isset($vars['monedas'])){
                                        $data = $vars['monedas'];
                                        if (is_array($data) ) {
                                            foreach ($data as $var) {
                                                if($var["codigo"]=="CRC"){
                                                    echo '<option selected="true" class="text-black" value="' . $var["codigo"] . '">' . $var["codigo"] . '</option>';
                                                }else{
                                                    echo '<option class="text-black" value="' . $var["codigo"] . '">' . $var["codigo"] . '</option>'; 
                                                }
                                            }
                                        }
                                        }
                                        ?>
								</select>
							</div>
							    </div>
							</div>
						</div>
						<!-- end panel-body -->
					</div>
					<!-- end panel -->
					<div class="panel panel-inverse" data-sortable-id="form-validation-1">
						<!-- begin panel-heading -->
						<div class="panel-heading">
							<h4 class="panel-title">Datos venta</h4>
							<div class="panel-heading-btn">
								<a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-warning" data-click="panel-collapse"><i class="fa fa-minus"></i></a>
							</div>
						</div>
						<!-- end panel-heading -->
						<!-- begin panel-body -->
						<div class="panel-body">
						    <div class="row">
							    <div class="form-group row col-md-12">
							        <label class="col-sm-2 col-form-label" for="message">CABYS :</label>
									<div class="col-md-2 col-sm-2">
										<input class="form-control" type="text" id="cabys" name="cabys" placeholder="CAByS" />
									</div>
									 <label class="col-sm-2 col-form-label" for="message">Descripcion :</label>
									<div class="col-md-6 col-sm-6">
										<input class="form-control" type="text" id="detail" name="detail" placeholder="Descripcion del producto" />
									</div>
								</div>
								<div class="form-group row col-md-12">
								    <label class="col-md-3 col-sm-3 col-form-label" for="">Unid medida:</label>
									<label class="col-md-2 col-sm-2 col-form-label" for="">Cantidad:</label>
									<label class="col-md-2 col-sm-2 col-form-label" for="">Precio Unid:</label>
									<label class="col-md-2 col-sm-2 col-form-label" for="">Impuesto:</label>
									<label class="col-md-2 col-sm-2 col-form-label" for="">Descuento:</label>
									<label class="col-md-1 col-sm-1 col-form-label" for=""></label>
								</div>
								<div class="form-group row col-md-12">
									<div class="col-md-3">
										<select class="default-select2 form-control" id="sku" name="sku">
										<?php
								    
                                        if(isset($vars['unidadesMedida'])){
                                        $data = $vars['unidadesMedida'];
                                        if (is_array($data) ) {
                                            foreach ($data as $var) {
                                                echo '<option class="text-black" value="' . $var["simbolo"] . '">' . $var["simbolo"]." - ".$var["descripcion"] . '</option>';
                                            }
                                        }
                                        }
                                        ?>
										</select>
									</div>
									<div class="col-md-2 col-sm-2">
										<input class="form-control" type="text" id="qty" name="qty" value="0" />
									</div>
									<div class="col-md-2 col-sm-2">
										<input class="form-control" type="text" id="unid" name="unid"  value="0" />
									</div>
									<div class="col-md-2 col-sm-2">
										<input class="form-control" type="text" id="tax" name="tax"value="0" />
									</div>
									<div class="col-md-2 col-sm-2">
										<input class="form-control" type="text" id="discount" name="discount"  value="0" />
									</div>
									<div class="col-md-1 col-sm-1">
									    <a onclick="addRow()" class="btn btn-danger" ><i class="fa fa-plus"></i></a>
									</div>
							    </div>
							</div>
						</div>
						<!-- end panel-body -->
					</div>
					<!-- end panel -->
                    <!-- begin invoice-content -->
                    <div class="invoice-content">
                        <!-- begin table-responsive -->
                        <div class="table-responsive">
                            <table class="table table-invoice" id="DetalleServicio" name="DetalleServicio" >
                                <thead>
                                    <tr>
                                        <th class="number" width="1%">N</th>
                                        <th>CABYS</th>
                                        <th>Descripcion</th>                                        
                                        <th class="text" width="10%">U. medida</th>
                                        <th class="text" width="10%">Cantidad</th>
                                        <th class="text" width="10%">Precio Unid</th>
                                        <th class="text" width="10%">Impuesto</th>
                                        <th class="text" width="10%">Descuento</th>
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
                                        <span class="text-white" id="subtotal" name="subtotal"></span>
                                    </div>
                                    <div class="sub-price">
                                        <i class="fa fa-plus text-muted"></i>
                                    </div>
                                    <div class="sub-price">
                                        <small>IMPUESTOS</small>
                                        <span class="text-white" id="tImpuesto" name="tImpuesto"></span>
                                    </div>
                                    <div class="sub-price">
                                        <i class="fa fa-min text-muted"></i>
                                    </div>
                                    <div class="sub-price">
                                        <small>DESCUENTO</small>
                                        <span class="text-white" id="tDescuento" name="tDescuento"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="invoice-price-right">
                                <div class="invoice-price-row">
                                    <div class="sub-price">
                                        <small>TOTAL</small> 
                                        <span class="text-white text-muted" id="totalDoc" name="totalDoc"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- end invoice-price -->
                    </div>
                    <!-- end invoice-content -->
                   
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
                <button type="submit" class="btn btn-success" onClick="proccess()">Guardar</button>
            </div>
        </div>
    </div>
</div>

