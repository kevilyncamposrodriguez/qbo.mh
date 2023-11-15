<?php
$session_id = session_id();
if (empty($session_id)) {
    session_start();
}

/**
 * Description of DefaultController
 *
 * @author Kevin Campos
 */


require 'model/DefaultModel.php';
require 'model/ExchangeRateModel.php';
require 'model/ExpensesModel.php';
require 'model/ClientModel.php';

class DefaultController implements IController {

    private $view;
    private $defaultModel;
    private $exchangeRate;
    private $expensesModel;
    private $clientModel;

    //cointructor
    public function __construct() {
        $this->view = new View();
        $this->defaultModel = new DefaultModel();
        $this->exchangeRate = new ExchangeRateModel();
        $this->expensesModel = new ExpensesModel();
        $this->clientModel = new ClientModel();
    }

    //carga pagina principal
    public function index() {
     
        $data = array();
        if(isset($_GET['realmId'])){//guarda la identificacion de la compania en session
           $_SESSION['realmId'] = $_GET['realmId'];
        }
        
        if ($this->defaultModel->isSession()) {
            if ($this->defaultModel->isSessionQB()) {
                   $r = $this->defaultModel->index();
                   if($r["status"] == "ok"){
                       $data["data"] = $r['message'];                       
                   }else{
                       //add code...
                   }
                   
                   $this->defaultModel->dolar();
                   $data["results"] = $this->expensesModel->countLocal();
                   $this->view->show("indexView.php", $data);
                    
            } else {
               $result = $this->defaultModel->loginQB($data);
                if($result["status"]=="ok"){
                    $_SESSION['authUrl']= $result["message"];
                    header("Location: " . $result["message"]);
                }else if($result["status"]=="error" && $result["message"]=="2"){
                    session_destroy();
                    header('Location: /sincronizador/?error=2');
                }else{
                    $_SESSION['realmId'] = $result["message"]["realmId"];
                    header('Location: /sincronizador/');
                }
            }
        } else {
               $this->view->show("loginView.php", $data); 
        }
    }

    //inicio de session a la app
    public function login() {
        $data = array();
        $data['user'] = $_POST['user'];
        $data['pass'] = $_POST['pass'];
        $result = $this->defaultModel->login($data);
       
    }

    //inicio de session a la app
    public function logout() {
        $this->defaultModel->logout();
    }
    //Muestra el tipo de cambio del dolar (compra/venta)
    public function dolar(){
        
    }

    public function all() {
        
    }

    public function create() {
        
    }

    public function delete() {
        
    }

    public function search() {
        
    }

    public function update() {
        
    }
    public function deleteClient($id){
        $data = array();
        $data = $this->clientModel->deleteClient($id);
        $this->client();
    }
    public function client(){
        $data = array();
        $data = $this->clientModel->all2();
        $this->view->show("clientsView.php", $data);
    }
    public function syncGMAIL() {
        $clients = $this->clientModel->all2();
        $clients = json_decode($clients, true);
        foreach ($clients as $client) {
            echo "Cargando facturas de: " . $client["emailUser"] . " - " . $client["idcard"] . "<br>";
            $clients = $this->defaultModel->downloadMails($client["emailUser"], $client["emailPass"], $client["idcard"]);
            echo "<br>";
        }
    }

}

// fin clase
?>