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
require 'model/ExpensesModel.php';
require 'model/CheckModel.php';

class CheckController {

    //put your code here
    private $view;
    private $defaultModel;
    private $clientModel;
    private $invoiceModel;
    private $checkModel;
    private $exchangeRate;
    private $expensesModel;

    //cointructor
    public function __construct() {
        $this->view = new View();
        $this->defaultModel = new DefaultModel();
        $this->clientModel = new ClientModel();
        $this->invoiceModel = new InvoiceModel();
        $this->expensesModel = new ExpensesModel();
        $this->exchangeRate = new ExchangeRateModel();
        $this->checkModel = new CheckModel();
    }
    public function importChecks(){
        $data = array();
        $data["idCard"] = $_SESSION['username'];
        $data["realmId"] = $_SESSION['realmId'];
        $result = array();
        if(isset($_POST["importar"])){
            //--- SECCION 2 ---
            $archivo = $_FILES["archivo"]["name"];
            $archivo_ruta = $_FILES["archivo"]["tmp_name"];
            $archivo_guardado = "files/importarChecks.xlsx";
            if(copy($archivo_ruta, $archivo_guardado)){
                include ("libs/SimpleXLSX.php");
                $xlsx = @(new SimpleXLSX($archivo_guardado));
                $data["client"] = json_decode($this->clientModel->search2($data),true);
                $result["acounts"] =  $this->expensesModel->acounts($data);
                $result["taxes"] =  $this->invoiceModel->taxes($data);
                $result["class"] =  $this->invoiceModel->clases($data);
                $result["lines"] =  json_encode($xlsx->rows());
            }
        }
        if(isset($_POST["importChecks"])){
            if(isset($_POST['list'])) {
                $data["list"] = unserialize($_POST["list"]);
                $data["services"] = unserialize($_POST["listServices"]);
                $data["acount"] = $_POST["acount"];
                $data["category"] =  $_POST["category"];
                $data["tax"] =  $_POST["tax"];
                $data["class"]="";
                if(isset($_POST["clase"])){
                  $data["class"] =  $_POST["clase"];  
                }
                $data["list"] = json_decode($data["list"]);
                $data["services"] = json_decode($data["services"]);
                $data["services"] = json_decode($data["services"]);
                $tamano = $data["list"][sizeof($data["list"])-1][13] - $data["list"][1][13]+1;
                $data["client"] = json_decode($this->clientModel->search2($data),true);
                $data["dataService"] = $this->defaultModel->getDataService($data["client"][0]);
                $result["results"] = $this->checkModel->importChecks($data);
            } 
           
        }
       
       $this->view->show("importCheckView.php", $result); 
    }
    
    
   

}
