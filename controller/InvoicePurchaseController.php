<?php
/**
 * Description of DefaultController
 *
 * @author Kevin Campos
 */

use Hacienda\Firmador;
require 'model/DefaultModel.php';
require 'model/ExpensesModel.php';
require 'model/InvoicePurchaseModel.php';
require 'model/ClientModel.php';

class InvoicePurchaseController {

    private $view;
    private $defaultModel;
    private $expensesModel;
    private $invoicePurchaseModel;
    private $clientModel;

    //put your code here
    public function __construct() {
        $this->view = new View();
        $this->defaultModel = new DefaultModel();
        $this->clientModel = new ClientModel();
        $this->expensesModel = new ExpensesModel();
        $this->invoicePurchaseModel = new InvoicePurchaseModel();
        
    }
    
    public function index() {
        $data = array();
        $data["idCard"] = $_SESSION['username'];
        $data["realmId"] = $_SESSION['realmId'];
        $data["client"] = json_decode($this->clientModel->search2($data),true);
        $dataService = $this->defaultModel->getDataService($data["client"][0]);
        $data["company"] = $dataService->getCompanyInfo();
        $data["idCardc"] = $data["company"]->EmployerId;
        $data["consecutivo"] = $this->invoicePurchaseModel->consecutive($data);
        for($i=strlen($data["idCardc"]);$i<12;$i++){
            $data['idCardc']="0".$data['idCardc'];
        }
        $data['clave'] = str_replace(" ","","506".date("d").date("m").date("y").$data['idCardc'].$data["consecutivo"] ."1"."19890717");
        $data["condicionesVenta"] = $this->defaultModel->getCondicionesVenta();
        $data["fcs"] = $this->invoicePurchaseModel->getFC();
        $data["monedas"] = $this->defaultModel->getMonedas();
        $data["mediosPago"] = $this->defaultModel->getMediosPago();
        $data["referencias"] = $this->defaultModel->getReferencias();
        $data["tiposDocumento"] = $this->defaultModel->getTiposDocumento();
        $data["tiposIdentificacion"] = $this->defaultModel->getTiposIdentificacion();
        $data["unidadesMedida"] = $this->defaultModel->getUnidadesMedida();
        $this->view->show("invoicePurchaseView.php", $data);
    }
    public function proccess() {
        $data = array();
        //datos documento
        $data["detalle"] = $_POST["details"];
        $data["tipoDocumento"] = $_POST["tipoDocumento"];
        $data["consecutivo"] = $_POST["consecutive"];
        $data["clave"] = $_POST["clave"];
        $data["subtotal"] = $_POST["subtotal"];
        $data["descuento"] = $_POST["descuentos"];
        $data["impuesto"] = $_POST["impuestos"];
        $data["total"] = $_POST["total"];
        //datos emisor
        $data["tipoIdentificacion"] = $_POST["typeIdCard"];
        $data["identificacion"] = $_POST["idCardE"];
        $data["nombreEmisor"] = $_POST["nameE"];
        $data["telefonoEmisor"] = $_POST["phoneE"];
        if($data["telefonoEmisor"]==""){
            $data["telefonoEmisor"]="22222222";
        }
        $data["formaPago"] = $_POST["payE"];
        $data["condicionVenta"] = $_POST["typePay"];
        $data["plazoCredito"] = $_POST["credit"];
        $data["moneda"] = $_POST["currency"];
        $data["nRefDoc"] = $_POST["nRefDoc"];
        $data["typeDocRef"] = $_POST["typeDocRef"];
        $data["fReferencia"] = $_POST["fReferencia"];
        $data["codRef"] = $_POST["codRef"];
        $data["razon"] = $_POST["razon"];
        $data["tCambio"] = $_POST["tCambio"];
        $data["emailE"] = $_POST["emailE"];
        
        $data["idCard"] = $_SESSION['username'];
        $data["realmId"] = $_SESSION['realmId'];
      
        $data["client"] = json_decode($this->clientModel->search2($data),true);
       
        $dataService = $this->defaultModel->getDataService($data["client"][0]);
        
        $data["company"] = $dataService->getCompanyInfo();
      
        $this->invoicePurchaseModel->proccess($dataService,$data);
        
        
    }
     public function refreshState() {
        $data = array();
        $data["clave"] = $_GET["key"];
        $data["idCard"] = $_SESSION['username'];
        $data["realmId"] = $_SESSION['realmId'];
        $data["client"] = json_decode($this->clientModel->search2($data),true);
        $dataService = $this->defaultModel->getDataService($data["client"][0]);
        $data["company"] = $dataService->getCompanyInfo();
        $this->invoicePurchaseModel->actualizarEstado($dataService,$data);
        
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
        $pathEnvio = "files/" . $data["idCard"]  . '/Recibidos/FacturaCompra/' . $data["key"];
        if(file_exists ( $pathEnvio. '/' . $data["key"].'-F.xml') && filesize( $pathEnvio. '/' . $data["key"].'-F.xml') != 0){ 
            $data['factura'] = simplexml_load_file( $pathEnvio. '/' . $data["key"].'-F.xml');
            //genre token
            if ($data["cliente"] != '') {
                if ($tipo == '01' || $tipo == '04' || $tipo == '08') {
                    $respuesta = $this->expensesModel->createBill($data["cliente"], $data);
                }
                if ($tipo == '03') {
                    $respuesta = $this->expensesModel->createVendorCredit($data["cliente"], $data);
                }
            }
        }
        $this->index();
    }
  
}
