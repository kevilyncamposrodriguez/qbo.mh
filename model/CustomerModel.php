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
require_once('vendor/autoload.php');

use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;
use QuickBooksOnline\API\Facades\Bill;
use QuickBooksOnline\API\Data\IPPReferenceType;
use QuickBooksOnline\API\Data\IPPAttachableRef;
use QuickBooksOnline\API\Data\IPPAttachable;
use QuickBooksOnline\API\Facades\Vendor;
use QuickBooksOnline\API\Facades\Account;
use QuickBooksOnline\API\Facades\VendorCredit;

class CustomerModel {

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
    function searchByIdCard($dataService,$idCard){
      
         $respuesta = $dataService->Query("select * from Vendor where CompanyName = '".$idCard."' ");
         $error = $dataService->getLastError();
         if ($error) {
		    return "The Response message is: " . $error->getResponseBody() . "\n";
		}
		else {
		    return json_encode($respuesta);
		}
        
    }

}
