
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
    <li class="breadcrumb-item active"><a>Cheques</a></li>
</ol>
<!-- end breadcrumb -->
<!-- begin page-header -->
<h1 class="page-header">Importar Cheques<small></small></h1>
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
                                        <form method="POST"  action="?controller=Check&action=importChecks" enctype="multipart/form-data" name="LayoutGrid1">
                                            <div class="form-group row">
            									<label class="col-lg-4 col-form-label">Cuenta bancaria</label>
            									<div class="col-lg-8">
            										<select class="form-control selectpicker" data-size="14" data-live-search="true" name="acount" id="acount" data-style="btn-white">
            											<?php if(isset($vars["acounts"])){
                                                        if($vars["acounts"]!=null && $vars["acounts"] !=''){
                                                            $data = json_decode($vars["acounts"], true);
                                                            $cont = 0;
                                                            foreach ($data as $value) {
                                                                if($value["Id"]=='194'){
                    										    echo '<option selected=true value="'.$value["Id"].'" style="color: #000000;">'.$value["Name"].'</option>';
                                                                }else{
                                                                     echo '<option value="'.$value["Id"].'" style="color: #000000;">'.$value["Name"].'</option>';
                                                                }
                                                             }}} ?>
            										</select>
            									</div>
            								</div>
            								<div class="form-group row">
            									<label class="col-lg-4 col-form-label">Cuenta contable</label>
            									<div class="col-lg-8">
            										<select class="form-control selectpicker" data-size="14" data-live-search="true" name="category" id="category" data-style="btn-white">
            											<?php if(isset($vars["acounts"])){
                                                        if($vars["acounts"]!=null && $vars["acounts"] !=''){
                                                            $data = json_decode($vars["acounts"], true);
                                                            $cont = 0;
                                                            foreach ($data as $value) {
                                                                if($value["Id"]=='209'){
                    										    echo '<option selected=true value="'.$value["Id"].'" style="color: #000000;">'.$value["Name"].'</option>';
                                                                }else{
                                                                     echo '<option value="'.$value["Id"].'" style="color: #000000;">'.$value["Name"].'</option>';
                                                                }
                                                             }}} ?>
            										</select>
            									</div>
            								</div>
            									<div class="form-group row">
            									<label class="col-lg-4 col-form-label">Impuestos</label>
            									<div class="col-lg-8">
            											<select class="form-control selectpicker" data-size="14" data-live-search="true"  name="tax">
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
            								</div>
            								<div class="form-group row">
            									<label class="col-lg-4 col-form-label">Clasificación (General)</label>
            									<div class="col-lg-8">
            											<select class="form-control selectpicker" data-size="14" data-live-search="true"  name="clase">
            											    <option value="0" style="color: #000000;">Ninguno</option>
            										    <?php if(isset($vars["class"])){
                                                            if($vars["class"]!=null && $vars["class"] !=''){
                                                                $data = json_decode($vars["class"], true);
                                                                $cont = 0;
                                                                foreach ($data as $value) {
                                                                    if($value["Active"] == "true" && $value["Active"] != ""){
                        										    echo '<option value="'.$value["Id"].'" style="color: #000000;">'.$value["Name"].'</option>';
                                                                    }
                                                                 }}} ?>
            										</select>
            									</div>
            								</div>
            								
        								 <input type="submit" id="importChecks" name="importChecks" class="btn btn-lg btn-success m-r-10" style="width: 100%" value="Importar">
                                         <input type="hidden" name="list" value='<?php echo serialize($vars["lines"]) ?>'>
                                         <input type="hidden" name="listServices" value='<?php echo serialize($vars["services"]) ?>'>
                                        </form>
                                        
        						 	
                				<?php }else{ ?>
                				    <form class=" align-center" action="?controller=Check&action=importChecks" method="post" enctype="multipart/form-data" name="LayoutGrid1" >
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
                							<a  href="files/Machotes/Machote cheques.xlsx" download >
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
                                <h4 class="panel-title">Cheques a cargar</h4>
                
                            </div>
                            <!-- end panel-heading -->
                            <!-- begin panel-body -->
                            <div class="panel-body ">
                               
                                <table id="data-table-select" class="table table-striped table-bordered table-td-valign-middle">
                                    <thead>
                                        <tr>
                                            <th width="1%">N°</th>
                                            <th class="text-nowrap" width="5%">Referencia</th>
                                            <th class="text-nowrap" width="5%">Cuenta</th>
                                            <th class="text-nowrap" width="15%">Nombre Proveedor</th>
                                            <th class="text-nowrap" width="5%">Monto</th>
                                            <th class="text-nowrap" width="10%">Fecha</th>
                                            <th class="text-nowrap" width="40%">Descripcion</th>
                                            <th class="text-nowrap" width="15%">Clasificación</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if(isset($vars["lines"])){
                                            if($vars["lines"]!=null && $vars["lines"] !=''){
                                                $data = json_decode($vars["lines"], true);
                                                $cont = 0;
                                                foreach ($data as $index =>$value) {
                                                    if($cont!=0){
                                                        echo '<tr class="gradeA">';
                                                        echo '<td class="f-s-600 text-white" width="1%">' . $index . '</td>';
                                                        echo '<td width="5%">' . $value[0] . '</td>';
                                                        echo '<td width="5%">' . $value[1] . '</td>';
                                                        echo '<td width="15%">' . $value[2] . '</td>';
                                                        echo '<td width="5%">' . $value[3] . '</td>';
                                                        echo '<td width="10%">' . $value[4] . '</td>';
                                                        echo '<td width="40%">' . $value[5] . '</td>';
                                                        echo '<td class="f-s-600 text-white" width="15%"><select class="form-group form-control"  name="clases[]" id="clases">';
                                                        echo '<option value="0" style="color: #000000;">Ninguno</option>';
        										        if(isset($vars["class"])){
                                                        if($vars["class"]!=null && $vars["class"] !=''){
                                                            $data = json_decode($vars["class"], true);
                                                            $cont = 0;
                                                            foreach ($data as $value) {
                    										    echo '<option value="'.$value["Id"].'" style="color: #000000;">'.$value["Name"].'</option>';
                                                             }}} 
        										        echo '</select> </td>';
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
                                <h4 class="panel-title">Estado carga de cheques</h4>
                
                            </div>
                            <!-- end panel-heading -->
                            <!-- begin panel-body -->
                            <div class="panel-body ">
                               
                                <table id="data-table-select" class="table table-striped table-bordered table-td-valign-middle">
                                    <thead>
                                        <tr>
                                            <th width="1%">N°</th>
                                            <th class="text-nowrap" >Referencia</th>
                                            <th class="text-nowrap" >Descripcion</th>
                                            <th class="text-nowrap">Monto</th>
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
                                                        echo '<td>' . $value["referencia"] . '</td>';
                                                        echo '<td>' . $value["descripcion"] . '</td>';
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


