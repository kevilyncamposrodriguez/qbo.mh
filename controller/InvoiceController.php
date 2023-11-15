<?php

/**
 * Description of DefaultController
 *
 * @author Kevin Campos
 */
$session_id = session_id();
if (empty($session_id)) {
    session_start();
}

require 'model/DefaultModel.php';
require 'model/InvoiceModel.php';
require 'model/ClientModel.php';
require 'model/ExchangeRateModel.php';

class InvoiceController {

    //put your code here
    private $view;
    private $defaultModel;
    private $clientModel;
    private $invoiceModel;
    private $exchangeRate;

    //cointructor
    public function __construct() {
        $this->view = new View();
        $this->defaultModel = new DefaultModel();
        $this->clientModel = new ClientModel();
        $this->invoiceModel = new InvoiceModel();
        $this->exchangeRate = new ExchangeRateModel();
    }
    
    public function import() {
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
                $result["services"] =  $this->invoiceModel->services($data);
                $result["taxes"] =  $this->invoiceModel->taxes($data);
                $result["pms"] =  $this->invoiceModel->pms($data);
                $result["terms"] =  $this->invoiceModel->terms($data);
                $result["class"] =  $this->invoiceModel->clases($data);
                $result["lines"] =  json_encode($xlsx->rows());
            }
            
        }
        if(isset($_POST["importInvoices"])){
            if(isset($_POST['list'])) {
                $data["list"] = unserialize($_POST["list"]);
                $data["services"] = unserialize($_POST["listServices"]);
                $data["service"] = $_POST["service"];
                $data["pm"] = $_POST["pm"];
                $data["tax"] = $_POST["tax"];
                $data["term"] = $_POST["term"];
                $data["class"] = $_POST["class"];
                $data["list"] = json_decode($data["list"]);
                $data["services"] = json_decode($data["services"]);
                $tamano = $data["list"][sizeof($data["list"])-1][13] - $data["list"][1][13]+1;
                //if($tamano>=0 && $tamano<4300){
                if(true){
                    $data["client"] = json_decode($this->clientModel->search2($data),true);
                    $result["results"] = $this->invoiceModel->importInvoice($data);
                }else{
                    header('Location: /sincronizador/?controller=Invoice&action=import&error=1');
                }
            } 
        }
       $this->view->show("importInvoiceView.php", $result); 
        
    }
    
    public function syncFE() {
        if(isset($_GET["idcard"]) and  isset($_GET["realmid"])){
            echo "<br> Facturas Electronicas<br>";
            $data["idCard"] = $_GET["idcard"];
            $data["realmId"] = $_GET["realmid"];
            
            $c = array();
            $c = $this->clientModel->search2($data);
            $c = json_decode($c,true);
            $this->invoiceModel->syncFE($c);
           
        }else{
            echo "<br> Facturas Electronicas<br>";
            $clients = json_decode($this->clientModel->all(),true);
            $this->invoiceModel->syncFE($clients); 
        }
        
    }
    public function syncTE() {
        
        if(isset($_GET["idcard"]) and  isset($_GET["realmid"])){
            echo "<br> Tiquetes Electronicos<br>";
            $data["idCard"] = $_GET["idcard"];
            $data["realmId"] = $_GET["realmid"];
            
            $c = array();
            $c = $this->clientModel->search2($data);
            $c = json_decode($c,true);
            $this->invoiceModel->syncTE($c);
           
        }else{
            echo "<br> Facturas Electronicas<br>";
            $clients = json_decode($this->clientModel->all(),true);
            $this->invoiceModel->syncTE($clients); 
        }
        
    }
    public function sendMails() {
      
        if(isset($_SESSION['username']) and  isset($_SESSION['realmId'])){
            $fecha = "2020-03-23";
            echo "<br>Envio de Facturas al correo del ".$fecha."<br>";
            $data["idCard"] = $_SESSION['username'];
            $data["realmId"] = $_SESSION['realmId'];
            
            $c = array();
            $c = $this->clientModel->search2($data);
            $c = json_decode($c,true);
            $this->invoiceModel->sendMails($c, $fecha);
           
           
        }
        
    }

}
