<?php

$session_id = session_id();
if (empty($session_id)) {
    session_start();
}

require 'model/ClientModel.php';

class ClientController {

    private $clientModel;

    //private $view;    
    public function __construct() {
        $this->view = new View();
        $this->clientModel = new ClientModel();
    }

// constructor

    public function save() {
        $data = "";
        $alm = new ClientModel();
        $alm->idCard = $_SESSION['idCard'];
        $alm->realm_id = $_SESSION['realmId'];
        $alm->userMH = $_REQUEST['userMH'];
        $alm->passMH = $_REQUEST['passMH'];
        $alm->urlP12 = $_FILES["criptKey"]["name"];
        $alm->passP12 = $_REQUEST['passCriptkey'];
        $alm->userEmail = $_REQUEST['userEmail'];
        $alm->passEmail = $_REQUEST['passEmail'];
        $alm = $this->clientModel->Save($alm);

        if (!empty($_FILES['criptKey'])) {
            $carpeta = "P12s/" . $_SESSION['idCard'];
            if (!file_exists($carpeta)) {
                mkdir($carpeta, 0755, true);
            }

            $p = $_FILES['criptKey']['name'];
            $path = "P12s/" . $_SESSION['idCard'] . "/";
            $path = $path . basename($_FILES['criptKey']['name']);
            if (move_uploaded_file($_FILES['criptKey']['tmp_name'], $path)) {
                
            }
            header('Location: /sincronizador/?status=OK');
        }
    }

}

//end class
?>