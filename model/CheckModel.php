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
use QuickBooksOnline\API\Facades\Customer;
use QuickBooksOnline\API\Facades\Account;
use QuickBooksOnline\API\Facades\VendorCredit;
use QuickBooksOnline\API\Facades\Purchase;

class CheckModel {

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
    public function importChecks($data){
        $dataService = $data["dataService"];
         foreach ($data["services"] as $service) {
            if ($service->Id == $data["service"]) {
                $serviceName = $service->Name;
            }
        }
        try {
            $line = array();
            $result = array();
            $r = array();
            $suma = 0;
            $contador=1;
            $idbatch = 1;
            $batch = $dataService->CreateNewBatch();
            for ($i = 1; $i < sizeof($data["list"]); $i++) {
                $vendor = $this->searchVendorByname($dataService, $data["list"][$i][2]);
                if($vendor != null && $vendor != ""){
                    if($data["class"]==0){
                        $theResourceObj =Purchase::create([
                          "DocNumber" => $data["list"][$i][0],
                          "TxnDate"=> $data["list"][$i][4], 
                          "AccountRef" => [
                             "value"=> $data["acount"]
                            ],
                            "PaymentType"=> "Check",
                            "EntityRef"=> [
                               "value"=> $vendor[0]->Id,
                               "type"=> "Vendor"
                              ],
                              "TotalAmt"=> $data["list"][$i][3],
                              "GlobalTaxCalculation"=> "TaxInclusive",
                            "Line"=> [
                             [
                               "Description"=> $data["list"][$i][5],
                               "Amount"=> $data["list"][$i][3],
                               "DetailType"=> "AccountBasedExpenseLineDetail",
                               "AccountBasedExpenseLineDetail"=> [
                                "AccountRef"=> [
                                   "value"=> $data["category"]
                                 ],
                                 "TaxCodeRef"=> [
                                      "value"=> $data["tax"]
                                     ],
                                 "TaxInclusiveAmt"=> $data["list"][$i][3]
                               ]
                             ]
                            ]
                        ]); 
                    }else{
                       $theResourceObj =Purchase::create([
                          "DocNumber" => $data["list"][$i][0],
                          "TxnDate"=> $data["list"][$i][4], 
                          "AccountRef" => [
                             "value"=> $data["acount"]
                            ],
                            "PaymentType"=> "Check",
                            "EntityRef"=> [
                               "value"=> $vendor[0]->Id,
                               "type"=> "Vendor"
                              ],
                              "TotalAmt"=> $data["list"][$i][3],
                              "GlobalTaxCalculation"=> "TaxInclusive",
                            "Line"=> [
                             [
                               "Description"=> $data["list"][$i][5],
                               "Amount"=> $data["list"][$i][3],
                               "DetailType"=> "AccountBasedExpenseLineDetail",
                               "AccountBasedExpenseLineDetail"=> [
                                 "ClassRef"=> [
                                      "value"=>  $data["class"]
                                     ],
                                "AccountRef"=> [
                                   "value"=> $data["category"]
                                 ],
                                 "TaxCodeRef"=> [
                                      "value"=> $data["tax"]
                                     ],
                                 "TaxInclusiveAmt"=> $data["list"][$i][3]
                               ]
                             ]
                            ]
                        ]); 
                    }
                    
                    if($contador > 30){
                        $batch->Execute();
                        $error = $batch->getLastError();
                        if ($error) {
                            echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
                            echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
                            echo "The Response message is: " . $error->getResponseBody() . "\n";
                        }
                        for($j=1;$j<$idbatch;$j++){
                            $batchItemResponse_queryCustomer = $batch->intuitBatchItemResponses[$j];
                            if($batchItemResponse_queryCustomer->isSuccess()){
                                $r["observacion"] = "Inresado con exito";
                                $getResult = $batchItemResponse_queryCustomer->getResult();
                                $r["referencia"] = $getResult->DocNumber;
                                $r["descripcion"] = $getResult->Line->Description;
                                $r["monto"] = $getResult->TotalAmt;
                            }else{
                                $r["observacion"] = $batchItemResponse_queryCustomer->getError();
                                $r["referencia"] = "";
                                $r["descripcion"] = "";
                                $r["monto"] = "";
                            }
                            
                            array_push($result, $r);
                        }
                        $batch = "";
                        $batch = $dataService->CreateNewBatch();
                        $contador = 1;
                        $idbatch = 1;
                    }
                     $batch->AddEntity($theResourceObj, $idbatch,"Create");
                     $contador ++;
                     $idbatch ++;
                    
                }else{
                    $r["observacion"] = "Proveedor no encontrado";
                }
            }
            if($contador>1){
                 $batch->Execute();
                    $error = $batch->getLastError();
                    if ($error) {
                        echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
                        echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
                        echo "The Response message is: " . $error->getResponseBody() . "\n";
                    }
                    for($i=1;$i<$idbatch;$i++){
                        $batchItemResponse_queryCustomer = $batch->intuitBatchItemResponses[$i];
                        if($batchItemResponse_queryCustomer->isSuccess()){
                           $r["observacion"] = "Inresado con exito";
                           $getResult = $batchItemResponse_queryCustomer->getResult();
                            $r["referencia"] = $getResult->DocNumber;
                            $r["descripcion"] = $getResult->Line->Description;
                            $r["monto"] = $getResult->TotalAmt;
                        }else{
                            $r["observacion"] = json_encode($batchItemResponse_queryCustomer->getError());
                            $r["referencia"] = "";
                            $r["descripcion"] = "";
                            $r["monto"] = "";
                        }
                        
                        array_push($result, $r);
                    }
                    
            }
            return json_encode($result);
        }catch (Exception $e) {
            die($e->getMessage());
        }
    }
    public function searchVendorByname($dataService, $name){
        try {
            $result = $dataService->Query("select * from Vendor where DisplayName = '" . $name . "'");
            return $result;
        }catch (Exception $e) {
            die($e->getMessage());
        }
    }
    public function vendors($dataService){   
             try {
                // Run a query
                $cantidad = $dataService->Query("select count(*) from Vendor");
                $cantidad = ceil($cantidad / 1000) * 1000;
                $error = $dataService->getLastError();
                if ($error) {
                    echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
                    echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
                    echo "The Response message is: " . $error->getResponseBody() . "\n";
                }
                $vendors = array();
                for ($i = 1; $i < $cantidad; $i = $i + 1000) {
                    $vendors = array_merge($vendors, $dataService->findAll("Vendor", $i, 1000));
                }
                // Echo some formatted output
                return json_encode($vendors);
            } catch (Exception $e) {
                die($e->getMessage());
            }
	    }
   
}
