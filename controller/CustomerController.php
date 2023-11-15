<?php
/**
 * Description of DefaultController
 *
 * @author Kevin Campos
 */
require 'model/DefaultModel.php';
require 'model/ClientModel.php';
require 'model/CustomerModel.php';

use Hacienda\Firmador;

class CustomerController {

    private $view;
    private $defaultModel;
    private $clienttModel;
    private $customerModel;

    //put your code here
    public function __construct() {
        $this->view = new View();
        $this->defaultModel = new DefaultModel();
        $this->clientModel = new ClientModel();
        $this->customerModel = new CustomerModel();
    }

    

    public function searchById() {
        $data = array();
        $data["id"] = $_GET['idCard'];
        $data["idCard"] = $_SESSION['username'];
        $data["realmId"] = $_SESSION['realmId'];
        $data["client"] = json_decode($this->clientModel->search2($data),true);
        $dataService = $this->defaultModel->getDataService($data["client"][0]);
        $respuesta = $this->customerModel->searchByIdCard($dataService,$data["id"]);
        echo  $respuesta;
    }
}
