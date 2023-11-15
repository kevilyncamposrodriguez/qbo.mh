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
require 'model/CreditNoteModel.php';
require 'model/ClientModel.php';
require 'model/ExchangeRateModel.php';

class CreditNoteController {

    //put your code here
    private $view;
    private $defaultModel;
    private $clientModel;
    private $creditNoteModel;
    private $exchangeRate;

    //cointructor
    public function __construct() {
        $this->view = new View();
        $this->defaultModel = new DefaultModel();
        $this->clientModel = new ClientModel();
        $this->creditNoteModel = new CreditNoteModel();
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
                $result["services"] =  $this->creditNoteModel->services($data);
                $result["taxes"] =  $this->creditNoteModel->taxes($data);
                $result["pms"] =  $this->creditNoteModel->pms($data);
                $result["terms"] =  $this->creditNoteModel->terms($data);
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
                $data["list"] = json_decode($data["list"]);
                $data["services"] = json_decode($data["services"]);
                $tamano = $data["list"][sizeof($data["list"])-1][13] - $data["list"][1][13]+1;
                if($tamano>=0 && $tamano<4300){
                    $data["client"] = json_decode($this->clientModel->search2($data),true);
                    $result["results"] = $this->creditNoteModel->importInvoice($data);
                }else{
                    header('Location: /sincronizador/?controller=CreditNote&action=import&error=1');
                }
            } 
        }
       $this->view->show("importCreditNoteView.php", $result); 
        
    }
    public function syncNC() {
        
        if(isset($_GET["idcard"]) and  isset($_GET["realmid"])){
            echo "<br> Notas de Credito<br>";
            $data["idCard"] = $_GET["idcard"];
            $data["realmId"] = $_GET["realmid"];
            
            $c = array();
            $c = $this->clientModel->search2($data);
            $c = json_decode($c,true);
            $this->creditNoteModel->syncNC($c);
           
        }else{
            echo "<br> Notas de Credito<br>";
            $clients = json_decode($this->clientModel->all(),true);
            $this->creditNoteModel->syncNC($clients); 
        }
        
    }

}
