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
require 'model/AttachementModel.php';
require 'model/ClientModel.php';

class AttachementController {

    //put your code here
    private $view;
    private $defaultModel;
    private $clientModel;
    private $attachementModel;

    //cointructor
    public function __construct() {
        $this->view = new View();
        $this->defaultModel = new DefaultModel();
        $this->clientModel = new ClientModel();
        $this->attachementModel = new AttachementModel();
    }
    public function all(){
        $result = array();
        $data = array();
        $data["idCard"] = '3102755692';
        $data["realmId"] = '123146053367214';
       
        $data["client"] = json_decode($this->clientModel->search2($data),true);
        $data["dataService"] = $this->defaultModel->getDataService($data["client"][0]);
        $result["results"] = $this->attachementModel->all($data["dataService"]);
    }
     public function allP(){
        $result = array();
        $data = array();
        $data["idCard"] = '3102755692';
        $data["realmId"] = '123146053367214';
       
        $data["client"] = json_decode($this->clientModel->search2($data),true);
        $data["dataService"] = $this->defaultModel->getDataService($data["client"][0]);
        $result["results"] = $this->attachementModel->allP($data["dataService"]);
    }
    

}
