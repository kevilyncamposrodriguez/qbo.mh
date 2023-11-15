
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
<h1 class="page-header">Importar Facturas<small></small></h1>
<!-- end page-header -->
<!-- begin row -->
			<div class="row">
				<!-- begin col-12 -->
				<div class="col-12">
					<!-- begin panel -->
					<div class="panel panel-inverse" data-sortable-id="form-stuff-12">
						<!-- begin panel-heading -->
						<div class="panel-heading">
							<h4 class="panel-title">Cargar archivo</h4>
						
						</div>
						<!-- end panel-heading -->
						<!-- begin panel-body -->
						<div class="panel-body">
						    <div class="row">
                				<!-- begin col-12 -->
                				<div class="col-3">
        						
        						</div>
                				<div class="col-6 align-center">
                				<?php if(isset($vars)){
                                        if($vars["lines"]!=null && $vars["lines"] !=''){ 
                                        ?>
                                        
                                        <form method="POST"  action="?controller=Invoice&action=import" enctype="multipart/form-data" name="LayoutGrid1">
                                            <div class="form-group row m-b-15 align-items-center">
        									<label class="col-sm-3 col-form-label"><h4>Tipo de facturacion</h4></label>
        									<div class="col-sm-9">
        										<select class="form-group form-control "  name="service">
        										    <?php if(isset($vars["services"])){
                                                        if($vars["services"]!=null && $vars["services"] !=''){
                                                            $data = json_decode($vars["services"], true);
                                                            $cont = 0;
                                                            foreach ($data as $value) {
                    										    echo '<option value="'.$value["Id"].'" style="color: #000000;">'.$value["Name"].'</option>';
                                                             }}} ?>
        										</select>
        									</div>
        									<label class="col-sm-3 col-form-label"><h4>Impuesto</h4></label>
        									<div class="col-sm-9">
        										<select class="form-group form-control "  name="tax">
        										    <?php if(isset($vars["taxes"])){
                                                        if($vars["taxes"]!=null && $vars["taxes"] !=''){
                                                            $data = json_decode($vars["taxes"], true);
                                                            $cont = 0;
                                                            foreach ($data as $value) {
                                                                if($value["Active"] == "true" && $value["Active"] != ""){
                    										    echo '<option value="'.$value["Id"].'" style="color: #000000;">'.$value["Name"].'</option>';
                                                                }
                                                             }}} ?>
        										</select>
        									</div>
        									<label class="col-sm-3 col-form-label"><h4>Terminos de Pago</h4></label>
        									<div class="col-sm-9">
        										<select class="form-group form-control "  name="term">
        										    <?php if(isset($vars["terms"])){
                                                        if($vars["terms"]!=null && $vars["terms"] !=''){
                                                            $data = json_decode($vars["terms"], true);
                                                            $cont = 0;
                                                            foreach ($data as $value) {
                    										    echo '<option value="'.$value["Id"].'" style="color: #000000;">'.$value["Name"].'</option>';
                                                             }}} ?>
        										</select>
        									</div>
        									<label class="col-sm-3 col-form-label"><h4>Metodo de pago</h4></label>
        									<div class="col-sm-9">
        										<select class="form-group form-control "  name="pm">
        										    <?php if(isset($vars["pms"])){
                                                        if($vars["pms"]!=null && $vars["pms"] !=''){
                                                            $data = json_decode($vars["pms"], true);
                                                            $cont = 0;
                                                            foreach ($data as $value) {
                    										    echo '<option value="'.$value["Id"].'" style="color: #000000;">'.$value["Name"].'</option>';
                                                             }}} ?>
        										</select>
        									</div>
        									<label class="col-sm-3 col-form-label"><h4>Clasificar en:</h4></label>
        									<div class="col-sm-9">
        										<select class="form-group form-control "  name="class">
        										    <?php if(isset($vars["class"])){
                                                        if($vars["class"]!=null && $vars["class"] !=''){
                                                            $data = json_decode($vars["class"], true);
                                                            $cont = 0;
                                                            foreach ($data as $value) {
                    										    echo '<option value="'.$value["Id"].'" style="color: #000000;">'.$value["Name"].'</option>';
                                                             }}} ?>
        										</select>
        									</div>
        								</div>
        								
        								 <input type="submit" id="importInvoices" name="importInvoices" class="btn btn-lg btn-success m-r-10" style="width: 100%" value="Importar">
                                         <input type="hidden" name="list" value='<?php echo serialize($vars["lines"]) ?>'>
                                         <input type="hidden" name="listServices" value='<?php echo serialize($vars["services"]) ?>'>
                                        </form>
                                        
        						 	
                				<?php }else{ ?>
                				    <form class=" align-center" action="?controller=Invoice&action=import" method="post" enctype="multipart/form-data" name="LayoutGrid1" >
                				        <div class="row">
                            				<!-- begin col-12 -->
                            				<div class="col-6">
                								<div class="form-group m-r-10">
                									<input type="file" style = "width:100%;" class="form-control " name="archivo"  placeholder="Cargar archivo" accept=".xls,.xlsx" required />
                								</div>
                							</div>
                							<div class="col-6">
                    							<input type="submit" style = "width:100%;" id="importar" name="importar" value="Cargar"  class="btn btn-sm btn-primary m-r-5">
        						        	</div>
        						        </div>
        							</form>
        							<div class="row">
                        				<!-- begin col-12 -->
                        				<div class="col-12">
                							<a  href="files/Machotes/Machote F.xlsx" download >
                                              <button style = "width:100%;" class="btn btn-sm btn-default m-r-5"><i class="icon-download-alt"></i> Archivo de ejemplo</button> 
                                            </a>
                                        </div>
                                    </div>
                				       
                				     <?php }
                				}?>
        						</div>
        						<div class="col-3">
        						
        						</div>
        					</div>
						</div>
						<!-- end panel-body -->
					</div>
					<!-- end panel -->
				</div>
				<!-- end col-6 -->
			</div>
			<!-- end row -->
			 <?php
            if(isset($vars["lines"])){ ?>
                <!-- begin row -->
                <div class="row ">
                    <!-- begin col-10 -->
                    <div class="col-xl-12">
                        <div class="panel panel-inverse">
                            <!-- begin panel-heading -->
                            <div class="panel-heading">
                                <h4 class="panel-title">Facturas a cargar</h4>
                
                            </div>
                            <!-- end panel-heading -->
                            <!-- begin panel-body -->
                            <div class="panel-body ">
                               
                                <table id="data-table-select" class="table table-striped table-bordered table-td-valign-middle">
                                    <thead>
                                        <tr>
                                            <th width="1%">N°</th>
                                            <th class="text-nowrap" >Razon Social</th>
                                            <th class="text-nowrap">Cedula</th>
                                            <th class="text-nowrap">Correo</th>
                                            <th class="text-nowrap">Telefono</th>
                                            <th class="text-nowrap">Distrito</th>
                                            <th class="text-nowrap">Canton</th>   
                                            <th class="text-nowrap">Provincia</th>
                                            <th class="text-nowrap">Moneda</th>
                                            <th class="text-nowrap">Cantidad</th>
                                            <th class="text-nowrap">Precio U</th>
                                            <th class="text-nowrap">Total sin imp</th>
                                            <th class="text-nowrap">Total con imp</th>
                                            <th class="text-nowrap">Descripcion</th>
                                            <th class="text-nowrap">consecutivo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if(isset($vars["lines"])){
                                            if($vars["lines"]!=null && $vars["lines"] !=''){
                                                $data = json_decode($vars["lines"], true);
                                                $cont = 0;
                                                foreach ($data as $value) {
                                                    if($cont!=0){
                                                        echo '<tr class="gradeA">';
                                                        echo '<td class="f-s-600 text-white" width="1%">' . $cont . '</td>';
                                                        echo '<td>' . $value[0] . '</td>';
                                                        echo '<td>' . $value[1] . '</td>';
                                                        echo '<td>' . $value[2] . '</td>';
                                                        echo '<td>' . $value[3] . '</td>';
                                                        echo '<td>' . $value[4] . '</td>';
                                                        echo '<td>' . $value[5] . '</td>';
                                                        echo '<td>' . $value[6] . '</td>';
                                                        echo '<td>' . $value[7] . '</td>';
                                                        echo '<td>' . $value[8] . '</td>';
                                                        echo '<td>' . $value[9] . '</td>';
                                                        echo '<td>' . $value[10] . '</td>';
                                                        echo '<td>' . $value[11] . '</td>';
                                                        echo '<td>' . $value[12] . '</td>';
                                                        echo '<td>' . $value[13] . '</td>';
                                                        echo '</tr>';
                                                    }
                                                    $cont++;
                                                }
                                            }
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
                <?php } ?>
                	 <?php
            if(isset($vars["results"])){ ?>
                <!-- begin row -->
                <div class="row ">
                    <!-- begin col-10 -->
                    <div class="col-xl-12">
                        <div class="panel panel-inverse">
                            <!-- begin panel-heading -->
                            <div class="panel-heading">
                                <h4 class="panel-title">Estado carga de facturas</h4>
                
                            </div>
                            <!-- end panel-heading -->
                            <!-- begin panel-body -->
                            <div class="panel-body ">
                               
                                <table id="data-table-select" class="table table-striped table-bordered table-td-valign-middle">
                                    <thead>
                                        <tr>
                                            <th width="1%">N°</th>
                                            <th class="text-nowrap" >Razon Social</th>
                                            <th class="text-nowrap">Cedula</th>
                                            <th class="text-nowrap">Lineas</th>
                                            <th class="text-nowrap">Total</th>
                                            <th class="text-nowrap">Consecutivo</th>
                                            <th class="text-nowrap">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if(isset($vars["results"])){
                                            if($vars["results"]!=null && $vars["results"] !=''){
                                                $data = json_decode($vars["results"], true);
                                                foreach ($data as $value) {
                                                        echo '<tr class="gradeA">';
                                                        echo '<td class="f-s-600 text-white" width="1%">' . $cont . '</td>';
                                                        echo '<td>' . $value["razon"] . '</td>';
                                                        echo '<td>' . $value["cedula"] . '</td>';
                                                        echo '<td>' . $value["lineas"] . '</td>';
                                                        echo '<td>' . $value["total"] . '</td>';
                                                        echo '<td>' . $value["consecutivo"] . '</td>';
                                                        echo '<td>' . $value["estado"] . '</td>';
                                                        echo '</tr>';
                                                    $cont++;
                                                }
                                            }
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
                <?php } ?>
<?php include ("view/footer.php"); 
if (isset($_GET["error"])) {
    if($_GET["error"] == "1"){
       echo "<script type='text/javascript'>
            $(document).ready(function () {
                $.gritter.add({
                    title: 'Error en cantidad de lineas',
                    text: 'El archivo no debe de contener mas de 40 facturas'
                });
            });
        </script>"; 
    }
    
}
?>


