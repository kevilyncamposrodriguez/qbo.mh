
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
    <li class="breadcrumb-item active"><a>Pagos</a></li>
</ol>
<!-- end breadcrumb -->
<!-- begin page-header -->
<h1 class="page-header">Importar Pagos<small></small></h1>
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
                                        
                                        <form method="POST"  action="?controller=Expenses&action=import" enctype="multipart/form-data" name="LayoutGrid1">
                                            <div class="form-group row">
            									<label class="col-lg-4 col-form-label">Lineas en factura</label>
            									<div class="col-lg-8">
            										<select class="form-control selectpicker" data-size="14" data-live-search="true" name="line1" id="line1" data-style="btn-white">
            											<?php if(isset($vars["acounts"])){
                                                        if($vars["acounts"]!=null && $vars["acounts"] !=''){
                                                            $data = json_decode($vars["acounts"], true);
                                                            $cont = 0;
                                                            foreach ($data as $value) {
                    										    echo '<option value="'.$value["Id"].'" style="color: #000000;">'.$value["Name"].'</option>';
                                                             }}} ?>
            										</select>
            									</div>
            								</div>
            								<div class="form-group row">
            									<label class="col-lg-4 col-form-label">Ajustes</label>
            									<div class="col-lg-8">
            										<select class="form-control selectpicker" data-size="14" data-live-search="true" name="adjustment" id="adjustment" data-style="btn-white">
            											<?php if(isset($vars["acounts"])){
                                                        if($vars["acounts"]!=null && $vars["acounts"] !=''){
                                                            $data = json_decode($vars["acounts"], true);
                                                            $cont = 0;
                                                            foreach ($data as $value) {
                    										    echo '<option value="'.$value["Id"].'" style="color: #000000;">'.$value["Name"].'</option>';
                                                             }}} ?>
            										</select>
            									</div>
            								</div>
            								<div class="form-group row">
            									<label class="col-lg-4 col-form-label">Ajustes de efectivo</label>
            									<div class="col-lg-8">
            										<select class="form-control selectpicker" data-size="14" data-live-search="true"  name="lastDept" id="lastDept" data-style="btn-white">
            											<?php if(isset($vars["acounts"])){
                                                        if($vars["acounts"]!=null && $vars["acounts"] !=''){
                                                            $data = json_decode($vars["acounts"], true);
                                                            $cont = 0;
                                                            foreach ($data as $value) {
                    										    echo '<option value="'.$value["Id"].'" style="color: #000000;">'.$value["Name"].'</option>';
                                                             }}} ?>
            										</select>
            									</div>
            								</div>
            								<div class="form-group row">
            									<label class="col-lg-4 col-form-label">Deuda anterior</label>
            									<div class="col-lg-8">
            										<select class="form-control selectpicker" data-size="14" data-live-search="true" name="bond" id="bond" data-style="btn-white">
            											<?php if(isset($vars["acounts"])){
                                                        if($vars["acounts"]!=null && $vars["acounts"] !=''){
                                                            $data = json_decode($vars["acounts"], true);
                                                            $cont = 0;
                                                            foreach ($data as $value) {
                    										    echo '<option value="'.$value["Id"].'" style="color: #000000;">'.$value["Name"].'</option>';
                                                             }}} ?>
            										</select>
            									</div>
            								</div>
            								<div class="form-group row">
            									<label class="col-lg-4 col-form-label">Fianza material</label>
            									<div class="col-lg-8">
            										<select class="form-control selectpicker" data-size="14" data-live-search="true" name="otherPay" id="otherPay" data-style="btn-white">
            											<?php if(isset($vars["acounts"])){
                                                        if($vars["acounts"]!=null && $vars["acounts"] !=''){
                                                            $data = json_decode($vars["acounts"], true);
                                                            $cont = 0;
                                                            foreach ($data as $value) {
                    										    echo '<option value="'.$value["Id"].'" style="color: #000000;">'.$value["Name"].'</option>';
                                                             }}} ?>
            										</select>
            									</div>
            								</div>
            								<div class="form-group row">
            									<label class="col-lg-4 col-form-label">Adjustes administrativos</label>
            									<div class="col-lg-8">
            										<select class="form-control selectpicker" data-size="14" data-live-search="true" name="administrativeAdjustment" id="administrativeAdjustment" data-style="btn-white">
            											<?php if(isset($vars["acounts"])){
                                                        if($vars["acounts"]!=null && $vars["acounts"] !=''){
                                                            $data = json_decode($vars["acounts"], true);
                                                            $cont = 0;
                                                            foreach ($data as $value) {
                    										    echo '<option value="'.$value["Id"].'" style="color: #000000;">'.$value["Name"].'</option>';
                                                             }}} ?>
            										</select>
            									</div>
            								</div>
            									<div class="form-group row">
            									<label class="col-lg-4 col-form-label">Otros ajustes</label>
            									<div class="col-lg-8">
            										<select class="form-control selectpicker" data-size="14" data-live-search="true" name="otherAdjustment" id="otherAdjustment" data-style="btn-white">
            											<?php if(isset($vars["acounts"])){
                                                        if($vars["acounts"]!=null && $vars["acounts"] !=''){
                                                            $data = json_decode($vars["acounts"], true);
                                                            $cont = 0;
                                                            foreach ($data as $value) {
                    										    echo '<option value="'.$value["Id"].'" style="color: #000000;">'.$value["Name"].'</option>';
                                                             }}} ?>
            										</select>
            									</div>
            								</div>
        								
        								 <input type="submit" id="importBills" name="importBills" class="btn btn-lg btn-success m-r-10" style="width: 100%" value="Importar">
                                         <input type="hidden" name="list" value='<?php echo serialize($vars["lines"]) ?>'>
                                         <input type="hidden" name="listServices" value='<?php echo serialize($vars["services"]) ?>'>
                                        </form>
                                        
        						 	
                				<?php }else{ ?>
                				    <form class=" align-center" action="?controller=Expenses&action=import" method="post" enctype="multipart/form-data" name="LayoutGrid1" >
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
                							<a  href="files/Machotes/Machote Pagos.xlsx" download >
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
                                            <th class="text-nowrap" >Consecutivo</th>
                                            <th class="text-nowrap" >Cedula</th>
                                            <th class="text-nowrap" >Nombre proveedor</th>
                                            <th class="text-nowrap">Ajuste</th>
                                            <th class="text-nowrap">Ajuste de efectivo</th>
                                            <th class="text-nowrap">Deuda anterior</th>
                                            <th class="text-nowrap">Fianza material</th>   
                                            <th class="text-nowrap">Adjustes administrativos</th>
                                            <th class="text-nowrap">Otros ajustes</th>
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
                                <h4 class="panel-title">Estado carga de pagos</h4>
                
                            </div>
                            <!-- end panel-heading -->
                            <!-- begin panel-body -->
                            <div class="panel-body ">
                               
                                <table id="data-table-select" class="table table-striped table-bordered table-td-valign-middle">
                                    <thead>
                                        <tr>
                                            <th width="1%">N°</th>
                                            <th class="text-nowrap">Consecutivo</th>
                                            <th class="text-nowrap">Cedula</th>
                                            <th class="text-nowrap">Nombre</th></th>
                                            <th class="text-nowrap">Proveedor</th>
                                            <th class="text-nowrap">Factura</th>
                                            <th class="text-nowrap">Cheque</th>
                                            <th class="text-nowrap">Monto Final</th>
                                            <th class="text-nowrap">Observacion</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if(isset($vars["results"])){
                                            if($vars["results"]!=null && $vars["results"] !=''){
                                                $data = json_decode($vars["results"], true);
                                                $cont = 1;
                                                foreach ($data as $value) {
                                                        echo '<tr class="gradeA">';
                                                        echo '<td class="f-s-600 text-white" width="1%">' . $cont . '</td>';
                                                        echo '<td>' . $value["consecutivo"] . '</td>';
                                                        echo '<td>' . $value["cedula"] . '</td>';
                                                        echo '<td>' . $value["nombre"] . '</td>';
                                                        echo '<td>' . $value["proveedor"] . '</td>';
                                                        echo '<td>' . $value["factura"] . '</td>';
                                                        echo '<td>' . $value["cheque"] . '</td>';
                                                        echo '<td>' . $value["monto"] . '</td>';
                                                        echo '<td>' . $value["observacion"] . '</td>';
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


