<?php
/**
 * Description of DefaultController
 *
 * @author Kevin Campos
 */
require 'model/DefaultModel.php';
require 'model/ClientModel.php';
require 'model/ExpensesModel.php';

use Hacienda\Firmador;

class ExpensesController {

    private $view;
    private $defaultModel;
    private $clientModel;
    private $expensesModel;

    //put your code here
    public function __construct() {
        $this->view = new View();
        $this->defaultModel = new DefaultModel();
        $this->clientModel = new ClientModel();
        $this->expensesModel = new ExpensesModel();
    }

    public function all2() {
        $data = array();
        $data["idCard"] = $_SESSION['username'];
        $data["realmId"] = $_SESSION['realmId'];
        if(isset($_GET["FINI"]) and isset($_GET["FFIN"])){
            $data["FINI"] = $_GET["FINI"];
            $data["FFIN"] = $_GET["FFIN"];
        }
        $data = $this->expensesModel->allP2($data);
        $this->view->show("expensesPView.php", $data);
    }
     public function all() {
        $data = array();
        $data["idCard"] = $_SESSION['username'];
        $data["realmId"] = $_SESSION['realmId'];
        
        if(isset($_GET["start"]) and isset($_GET["end"])){
            $data["FINI"] = $_GET["start"];
            $data["FFIN"] = $_GET["end"];
        }
        $data = $this->expensesModel->allP2($data);
        $this->view->show("expensesPView.php", $data);
    }
   

    public function deleteP() {
        $alm = new ClientModel();
        $alm->idCard = $_SESSION['idCard'];
        $alm->realmId = $_SESSION['realmId'];
        $clave = $_GET['key'];

        //firma del doc 
        //Datos necesarios para la firma del xml
        $path = "files/" . $_SESSION['idCard'] . '/Recibidos/Procesados/' . $clave;
        foreach (glob($path . "/*") as $archivos_carpeta) {
            if (is_dir($archivos_carpeta)) {
                rmDir_rf($archivos_carpeta);
            } else {
                unlink($archivos_carpeta);
            }
        }
        if (is_dir($path)) {
            rmdir($path);
        }
        $this->all();
    }

    public function delete() {
        $alm = new ClientModel();
        $alm->idCard = $_SESSION['idCard'];
        $alm->realmId = $_SESSION['realmId'];
        $clave = $_GET['key'];

        //firma del doc 
        //Datos necesarios para la firma del xml
        $path = "files/" . $_SESSION['idCard'] . '/Recibidos/Sinprocesar/' . $clave;
        foreach (glob($path . "/*") as $archivos_carpeta) {
            if (is_dir($archivos_carpeta)) {
                rmDir_rf($archivos_carpeta);
            } else {
                unlink($archivos_carpeta);
            }
        }
        if (is_dir($path)) {
            rmdir($path);
        }
        $this->index();
    }

    public function index() {
        $data = array();
        $data = $this->expensesModel->all();
        $this->view->show("expensesView.php", $data);
    }

    public function upload() {
        
        $ds          = DIRECTORY_SEPARATOR;  //1
        $storeFolder = 'files/'.$_SESSION['username'].'/Recibidos/Sinprocesar/'.$_POST["claveUp"];   //2
        
        if (!file_exists($storeFolder)) {
            mkdir($storeFolder, 0755, true);
        }
           
        $tempFile = $_FILES['xmlFact']['tmp_name'];          //3             
          
        $targetPath = dirname( __FILE__ ) . $ds. $storeFolder . $ds;  //4
         
        $targetFile =  $storeFolder.'/'.$_POST["claveUp"].".xml" ;  //5
     
        if (move_uploaded_file($tempFile, $targetFile)) {
            $tempFile = $_FILES['xmlResp']['tmp_name'];          //3             
          
            $targetPath = dirname( __FILE__ ) . $ds. $storeFolder . $ds;  //4
             
            $targetFile =  $storeFolder.'/'.$_POST["claveUp"]."-R.xml" ;  //5
         
            if (move_uploaded_file($tempFile, $targetFile)) {
                $tempFile = $_FILES['pdfFact']['tmp_name'];          //3             
          
                $targetPath = dirname( __FILE__ ) . $ds. $storeFolder . $ds;  //4
                 
                $targetFile =  $storeFolder.'/'.$_POST["claveUp"].".pdf" ;  //5
             
                if (move_uploaded_file($tempFile, $targetFile)) {
                    //echo "Archivos subidos con éxito.\n";
                    $this->index();
                } else {
                   // echo "¡Imposible subir PDF de Factura!\n";
                   $this->index();
                }
            } else {
                //echo "¡Imposible subir XML de Respuesta!\n";
                $this->index();
            }
        } else {
            //echo "¡Imposible subir XML de Factura!\n";
            $this->index();
        }
    }

    public function process() {
        $data["idCard"] = $_SESSION['username'];
        $data["realmId"] = $_SESSION['realmId'];
        $data["key"] = $_GET['key'];
        $data["c"] = $_GET['c'];
        $data["consecutivo"] = $this->expensesModel->consecutive($data);
        
        $result = $this->expensesModel->crearXMLMensaje($data["consecutivo"], $data["key"], $data["c"]);
        
        $c = json_decode($this->clientModel->search2($data),true);

        $xml = (string) $result['factura']->NumeroConsecutivo;
        $tipo = substr($xml, 8, 2);
        
            // cliente
            $cliente = $c[0];
            //firma del doc 
            //Datos necesarios para la firma del xml
            $p12Url = "P12s/" . $cliente["idcard"] . "/" . $cliente["urlP12"]; 
            $pinP12 = $cliente["passP12"];
            //firmar Documento
            $firmador = new Firmador();
            $data["factura"]= $result['factura'];
            $xmlFirmado = $firmador->firmarXml($p12Url, $pinP12, $result['mensaje'], $firmador::TO_XML_STRING);
           
            //genre token
            if ($cliente != '') {
                $token = $this->defaultModel->tokenMH($cliente);
                $token = json_decode($token["message"]);
                $data["token"] = $token->access_token;
                $dataMH = $this->expensesModel->process($data, $xmlFirmado);
                $consecutivo = $this->expensesModel->aumentaConsecutivo($data);
                
                if($data["c"]==1){
                    if ($tipo == '01' || $tipo == '04' || $tipo == '02') {
                        $respuesta = $this->expensesModel->createBill($cliente, $data);
                    
                    }
                    if ($tipo == '03') {
                        $respuesta = $this->expensesModel->createVendorCredit($cliente, $data);
                    }
                }else{
                    $this->expensesModel->resultPDFR($data);
                }
            }
        $this->index();
    }
     public function saveQB() {
        $data["idCard"] = $_SESSION['username'];
        $data["realmId"] = $_SESSION['realmId'];
        $data["key"] = $_GET['key'];
        $c = json_decode($this->clientModel->search2($data),true);
        $tipo = substr($data["key"], 29, 2);
       
            // cliente
            $data["cliente"] = $c[0];
            //genre token
            if ( $data["cliente"] != '') {
                $pathEnvio = "files/" . $data["idCard"] . '/Recibidos/Sinprocesar/' . $data["key"];
                if(file_exists ($pathEnvio. '/' . $data["key"].'.xml') && filesize($pathEnvio. '/' . $data["key"].'.xml') != 0){ 
                
                    $data['factura'] = simplexml_load_file($pathEnvio . '/' . $data["key"].'.xml');
                    
                    $dataMH = $this->expensesModel->saveQB($data);
                        if ($tipo == '01' || $tipo == '04') {
                            $respuesta = $this->expensesModel->createBill($data["cliente"], $data);
                        }
                        if ($tipo == '03') {
                            $respuesta = $this->expensesModel->createVendorCredit($data["cliente"], $data);
                        }
                }
            }
        $this->index();
    }
    public function saveQBP() {
        $data["idCard"] = $_SESSION['username'];
        $data["realmId"] = $_SESSION['realmId'];
        $data["key"] = $_GET['key'];
        $c = json_decode($this->clientModel->search2($data),true);
        $tipo = substr($data["key"], 29, 2);
        // cliente
        $data["cliente"] = $c[0];
        
        $data['consecutivo'] = '';
        
        $pathEnvio = "files/" . $data["idCard"]  . '/Recibidos/Procesados/' . $data["key"];
        if(file_exists ( $pathEnvio. '/' . $data["key"].'.xml') && filesize( $pathEnvio. '/' . $data["key"].'.xml') != 0){ 
            $data['factura'] = simplexml_load_file( $pathEnvio. '/' . $data["key"].'.xml');
            //genre token
            if ($data["cliente"] != '') {
                if ($tipo == '01' || $tipo == '04') {
                    $respuesta = $this->expensesModel->createBill($data["cliente"], $data);
                }
                if ($tipo == '03') {
                    $respuesta = $this->expensesModel->createVendorCredit($data["cliente"], $data);
                }
            }
        }
        $this->all();
    }
    public function process2() {
        $data["idCard"] = $_SESSION['username'];
        $data["realmId"] = $_SESSION['realmId'];
       
        $c = json_decode($this->clientModel->search2($data),true);
        $cliente = $c[0];
        $this->expensesModel->vendor2($cliente);
    }
     public function process3() {
        $data["idCard"] = $_SESSION['username'];
        $data["realmId"] = $_SESSION['realmId'];
       
        $c = json_decode($this->clientModel->search2($data),true);
        $cliente = $c[0];
        $this->expensesModel->vendor3($cliente);
    }
    // listar
     public function importPayments() {
        $data = array();
        $data["idCard"] = $_SESSION['username'];
        $data["realmId"] = $_SESSION['realmId'];
        $result=array();
         if(isset($_POST["importar"])){
            //--- SECCION 2 ---
            $archivo = $_FILES["archivo"]["name"];
            $archivo_ruta = $_FILES["archivo"]["tmp_name"];
            $archivo_guardado = "files/importar.xlsx";
            if(copy($archivo_ruta, $archivo_guardado)){
                include ("libs/SimpleXLSX.php");
                $xlsx = @(new SimpleXLSX($archivo_guardado));
                $data["client"] = json_decode($this->clientModel->search2($data),true);
                $result["acounts"] =  $this->expensesModel->acounts($data);
                $result["lines"] =  json_encode($xlsx->rows());
            }
        }
        if(isset($_POST["importBills"])){
           
            if(isset($_POST['list'])) {
                $data["list"] = unserialize($_POST["list"]);
                $data["services"] = unserialize($_POST["listServices"]);
                $data["column0"] = $_POST["column0"];
                $data["column1"] = $_POST["column1"];
                $data["column2"] = $_POST["column2"];
                $data["column3"] = $_POST["column3"];
                $data["column4"] = $_POST["column4"];
                $data["column5"] = $_POST["column5"];
                $data["column6"] = $_POST["column6"];
                $data["column7"] = $_POST["column7"];
                $data["column8"] = $_POST["column8"];
                $data["column9"] = $_POST["column9"];
                $data["line1"] = $_POST["line1"];
                $data["adjustment"] = $_POST["adjustment"];
                $data["lastDept"] = $_POST["lastDept"];
                $data["bond"] = $_POST["bond"];
                $data["otherPay"] = $_POST["otherPay"];
                $data["administrativeAdjustment"] = $_POST["administrativeAdjustment"];
                $data["otherAdjustment"] = $_POST["otherAdjustment"];
                $data["list"] = json_decode($data["list"]);
                $data["services"] = json_decode($data["services"]);
                $tamano = $data["list"][sizeof($data["list"])-1][10] - $data["list"][1][10]+1;
                //if($tamano>=0 && $tamano<4300){
                if(true){
                    $data["client"] = json_decode($this->clientModel->search2($data),true);
                    $data["dataService"] = $this->defaultModel->getDataService($data["client"][0]);
                    $result["results"] = $this->expensesModel->updateBills($data);
                }else{
                   header('Location: /sincronizador/?controller=Invoice&action=import&error=1');
                }
            } 
        }
      $this->view->show("importBillView.php", $result); 
    }
    // listar
     public function importChecks() {
        $data = array();
        $data["idCard"] = $_SESSION['username'];
        $data["realmId"] = $_SESSION['realmId'];
        
        $data["client"] = json_decode($this->clientModel->search2($data),true);
        $result["results"] = $this->expensesModel->importChecks($data);
        echo json_encode($result["results"]);
        return ;
        $result=array();
         if(isset($_POST["importar"])){
            //--- SECCION 2 ---
            $archivo = $_FILES["archivo"]["name"];
            $archivo_ruta = $_FILES["archivo"]["tmp_name"];
            $archivo_guardado = "files/importar.xlsx";
            if(copy($archivo_ruta, $archivo_guardado)){
                include ("libs/SimpleXLSX.php");
                $xlsx = @(new SimpleXLSX($archivo_guardado));
                $data["client"] = json_decode($this->clientModel->search2($data),true);
                $result["acounts"] =  $this->expensesModel->acounts($data);
                $result["lines"] =  json_encode($xlsx->rows());
            }
        }
        if(isset($_POST["importChecks"])){
           
            if(isset($_POST['list'])) {
                $data["list"] = unserialize($_POST["list"]);
                $data["services"] = unserialize($_POST["listServices"]);
                $data["column0"] = $_POST["column0"];
                $data["column1"] = $_POST["column1"];
                $data["column2"] = $_POST["column2"];
                $data["column3"] = $_POST["column3"];
                $data["column4"] = $_POST["column4"];
                $data["column5"] = $_POST["column5"];
                $data["column6"] = $_POST["column6"];
                $data["column7"] = $_POST["column7"];
                $data["column8"] = $_POST["column8"];
                $data["line1"] = $_POST["line1"];
                $data["adjustment"] = $_POST["adjustment"];
                $data["lastDept"] = $_POST["lastDept"];
                $data["bond"] = $_POST["bond"];
                $data["otherPay"] = $_POST["otherPay"];
                $data["administrativeAdjustment"] = $_POST["administrativeAdjustment"];
                $data["otherAdjustment"] = $_POST["otherAdjustment"];
                $data["list"] = json_decode($data["list"]);
                $data["services"] = json_decode($data["services"]);
                $tamano = $data["list"][sizeof($data["list"])-1][10] - $data["list"][1][10]+1;
                if(true){
                    $data["client"] = json_decode($this->clientModel->search2($data),true);
                    $result["results"] = $this->expensesModel->updateBills($data);
                }else{
                   header('Location: /sincronizador/?controller=Invoice&action=import&error=1');
                }
            } 
        }
      $this->view->show("importBillView.php", $result); 
    }
    
}
