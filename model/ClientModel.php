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

class ClientModel{

    public $pdo;

    public function __CONSTRUCT() {
        try {
            $this->pdo = SPDO::singleton();
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    public function saveToken($id, $refreshToken) {
        $sql = "UPDATE cliente SET refreshToken = '" . $refreshToken . "' where realmId = '" . $id . "'";
        $user = $this->pdo->prepare($sql);

        $user->execute();
    }

//fin save token

    public function all() {
        try {
            $sql = "SELECT * FROM cliente WHERE state= '1' ORDER BY `cliente`.`id` ASC";
            $clients = $this->pdo->prepare($sql);
            $clients->execute();
            $clients = $clients->fetchAll(PDO::FETCH_ASSOC);
            return json_encode($clients);
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }
     public function all2() {
        try {
            $sql = "SELECT * FROM cliente where state='1'";
            $clients = $this->pdo->prepare($sql);
            $clients->execute();
            $clients = $clients->fetchAll(PDO::FETCH_ASSOC);
            return json_encode($clients);
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    public function create($data) {
        
    }

    public function deleted($data) {
        
    }
    public function search2($data) {
        try {
            $sql = "SELECT * FROM cliente where idcard='" . $data["idCard"] . "' and realmId='" . $data["realmId"] . "'";

            $result = $this->pdo->prepare($sql);
            $result->execute();
            $result = $result->fetchAll(PDO::FETCH_ASSOC);
            if ($result) {
                return json_encode($result);
            } else {
                return false;
            }
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    public function search($data) {
        try {
            $sql = "SELECT * FROM cliente where idcard='" . $data . "'";

            $result = $this->pdo->prepare($sql);
            $result->execute();
            $result = $result->fetchAll(PDO::FETCH_ASSOC);
            if ($result) {
                return json_encode($result);
            } else {
                return false;
            }
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    public function update($data) {
        try {
            $sql = "UPDATE `Cliente`
            SET
            `accesstoken` ='" . $data['accesstoken'] . "',
            `refreshtoken` = '" . $data['refreshtoken'] . "'
            WHERE (`idcard` = '" . $data["idcard"] . "');";
            $result = $this->pdo->prepare($sql);
            $result->execute();
            $cret = $result->rowCount();
            if ($cret > 0) {
                return True;
            } else {
                return false;
            }
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }
    public function deleteClient($id) {
        try {
            $sql = "DELETE `Cliente`
            WHERE (`id` = '" . $id . "');";
            $result = $this->pdo->prepare($sql);
            $result->execute();
            if ($result) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    public function Save($data) {
        try {
            $idCard = $data->idCard;
            $realm_id = $data->realm_id;
            $userMH = $data->userMH;
            $passMH = $data->passMH;
            $urlP12 = $data->urlP12;
            $passP12 = $data->passP12;
            $userEmail = $data->userEmail;
            $passEmail = $data->passEmail;


            $sql = "UPDATE cliente SET userMH = '" . $userMH . "',passMH = '" . $passMH . "',urlP12 = '" . $urlP12 . "',passP12 = '" . $passP12 . "',emailUser = '" . $userEmail . "',emailPass = '" . $passEmail . "' WHERE idcard = '" . $idCard . "' and realmId ='" . $realm_id . "'";
            $user = $this->pdo->prepare($sql);
            $user->execute();

            return json_encode($user);
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

}
