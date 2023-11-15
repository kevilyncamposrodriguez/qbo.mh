<?php

/**
 * Description of InvoiceController
 *
 * @author Kevin Campos
 */

require 'libs/thread/thread.php';
require_once('vendor/autoload.php');
use Hacienda\Firmador;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Core\ServiceContext;
use QuickBooksOnline\API\PlatformService\PlatformService;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;
use QuickBooksOnline\API\Data\IPPReferenceType;
use QuickBooksOnline\API\Data\IPPAttachableRef;
use QuickBooksOnline\API\Data\IPPAttachable;
use QuickBooksOnline\API\Facades\Invoice;
use QuickBooksOnline\API\Facades\Customer;

class InvoiceModel {

    //put your code here
    public $pdo;

    public function __CONSTRUCT() {
        try {
            $this->pdo = SPDO::singleton();
        } catch (Exception $e) {
            die($e->getMessage());
        }
        require 'libs/PHPMailer/Exception.php';
        require 'libs/PHPMailer/PHPMailer.php';
        require 'libs/PHPMailer/SMTP.php';
    }
    public function sendMails($client, $fecha) {
        try {
            echo "<br>".$client[0]['idcard']."<br>";
            $dataService = $this->getDataService($client[0]);
            $i = 1;
            $invoices = array();
            $c = 1;
            $a = 1;
            while($invoicess =  $dataService->Query("select * from Invoice where TxnDate = '" . $fecha . "'", $i, 100)){
                foreach($invoicess as $invoice){
                   $mail = new PHPMailer; 
                   $clave = str_replace ( "Clave: " , "" , $invoice->CustomerMemo);
                  
                   if(file_exists("files/".$client[0]['idcard']."/Creados/SinEnviar/".$clave."/".$clave.".xml") && $invoice->CustomField[0]->StringValue == 'aceptado'){
                       echo "Factura".$c++.": ".$clave."<br>";
                       $correo = $invoice->BillEmail->Address;
                       echo "Enviando a: ".$correo ."<br>";
                       $this->sendFiles($dataService,$mail, $correo, $clave, $client[0]['idcard'],$client[0]['name'],$invoice->Id);
                       
                   }
                }
                if(count($invoicess)<1){
                  break;
                }else{
                    $i = $i+100;  
                }
            }
           $mail->SmtpClose();
             return;
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }
    public function sendFiles($dataService,$mail, $to, $clave, $idcard, $cliente,$invoiceId){
         $mail->isSMTP(); 
            $mail->SMTPDebug = 0; // 0 = off (for production use) - 1 = client messages - 2 = client and server messages
            $mail->Host = "smtp.gmail.com";//"smtp.gmail.com"; // use $mail->Host = gethostbyname('smtp.gmail.com'); // if your network does not support SMTP over IPv6
            $mail->Port = "587"; //587; // TLS only
            $mail->SMTPSecure = 'tls'; // ssl is depracated
            $mail->SMTPAuth = true;
            $mail->Username = "sincronizador_qbo-mh@contafast.net";
            $mail->Password = "Contafast.2020";
            $mail->setFrom("sincronizador_qbo-mh@contafast.net", $cliente);  
        $mail->ClearAddresses(); // clear all
        echo "<br> Enviando a ".$to."<br>";
        $mail->addAddress($to);
        $mail->addBCC('sincronizador_qbo-mh@contafast.net');
        $mail->Subject = 'Factura #'.$clave." de ".$cliente;
        $mail->msgHTML('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    
    <body>
        <table class="wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation">
            <tr>
                <td align="center">
                    <table class="content" width="100%" cellpadding="0" cellspacing="0" role="presentation">
                        <!-- Email Body -->
                        <tr>
                            <td class="body" width="100%" cellpadding="0" cellspacing="0">
                                <table class="inner-body" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
                                    <!-- Body content -->
                                    <tr>
                                        <td class="content-cell" align="center">

                                            Se adjunta XMLs de factura electrónica con clave: '.$clave.' 

                                            <br>
                                             Este correo fue creado de forma automatica favor no contestar a este.  

                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <table class="footer" align="center" width="100%" cellpadding="0" cellspacing="0" role="presentation">
                                    <tr>
                                        <td class="content-cell" align="center">
                                            Sincronzador QBO-MH
                                            <br>Todos los derechos reservados.
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
</html>
'); 
        $path = "files/".$idcard."/Creados/SinEnviar/".$clave.'/'.$clave.'.pdf';
        $dataService->throwExceptionOnError(true);
        $invoice = Invoice::create([
            "Id" => $invoiceId
        ]);
		$pdfTmp = $dataService->DownloadPDF($invoice);
        rename($pdfTmp, $path);
        $mail->AltBody = 'HTML messaging not supported';
        $mail->addAttachment("files/".$idcard."/Creados/SinEnviar/".$clave."/".$clave.".xml"); //Attach an image file
        $mail->addAttachment("files/".$idcard."/Creados/SinEnviar/".$clave."/".$clave.".pdf"); //Attach an image
        $mail->addAttachment("files/".$idcard."/Creados/SinEnviar/".$clave."/".$clave."-R.xml"); //Attach an image
      echo "<br>listo para enviar archivos al correo<br>";
        if(!$mail->send()){
            echo "<br>Mailer Error: " . $mail->ErrorInfo."<br>";
        }else{
            $path = "files/".$idcard."/Creados/SinEnviar/".$clave;
            $pathEnvio = "files/".$idcard."/Creados/Enviados/".$clave;
            if (!file_exists($pathEnvio)) {
                mkdir($pathEnvio, 0755, true);
            }
            rename($path, $pathEnvio);
            echo "<br>XMLs Enviados <br>"; 
        }
         
        
    }
   
    public function all($dataService) {
        $dt_Ayer = date('Y-m-d', strtotime('-30 day')); // resta 3 dÃ­a 
        
        $i = 1;
        $invoices = array();
        $c = 0;
        while($invoicess =  $dataService->Query("select * from Invoice where TxnDate > '" . $dt_Ayer . "'", $i, 100)){
            foreach($invoicess as $invoice){
                if($invoice->CustomField[0]->StringValue == ''){
                    array_push($invoices, $invoice); 
                }
            }
            if(count($invoices)>0){
              break;
            }else{
                $i = $i+100;  
            }
        }
        
        echo "<br>Cantidad Facturas sin tramitar: " . count($invoices) . "<br>";
        // Echo some formatted output
        return json_encode($invoices);
    }

    public function saveToken($id, $refreshToken) {
        $sql = "UPDATE cliente SET refreshToken = '" . $refreshToken . "' where realmId = '" . $id . "'";
        $user = $this->pdo->prepare($sql);
        $user->execute();
    }

    public function importInvoice($data) {
        foreach ($data["services"] as $service) {
            if ($service->Id == $data["service"]) {
                $serviceName = $service->Name;
            }
        }

        $config = include('public/config.php');
        $accessToken = unserialize($_SESSION['sessionAccessToken']);
        try {
            $dataService = DataService::Configure(array(
                        'auth_mode' => 'oauth2',
                        'ClientID' => $config['client_id'],
                        'ClientSecret' => $config['client_secret'],
                        'RedirectURI' => $config['oauth_redirect_uri'],
                        'refreshTokenKey' => $data["client"][0]["refreshToken"],
                        'QBORealmID' => $data["realmId"],
                        'scope' => $config['oauth_scope'],
                        'baseUrl' => "production"
            ));
            $dataService->disableLog();
            $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
            $accessToken = $OAuth2LoginHelper->refreshToken();
            $dataService->updateOAuth2Token($accessToken);
            $_SESSION['sessionAccessToken'] = serialize($accessToken);
            $this->saveToken($data["realmId"], $accessToken->getRefreshToken());

            $error = $dataService->getLastError();
            if ($error != null) {
                return $result = array("status" => "400", "message" => 'Error en datos de acceso a QB');
            }
            $dataService->throwExceptionOnError(true);
            $customers = $this->listAllCustomer($dataService);
            $line = array();
            $result = array();
            $suma = 0;
            $contador=1;
            for ($i = 1; $i < sizeof($data["list"]); $i++) {
                $lineData = array(
                    "Amount" => $data["list"][$i][10],
                    "Description" => $data["list"][$i][12],
                    "DetailType" => "SalesItemLineDetail",
                    "SalesItemLineDetail" => [
                        "ItemRef" => [
                            "value" => $data["service"],
                            "name" => $serviceName
                        ],
                        "ClassRef"=> [
                          "value"=> $data["class"]
                         ],
                        "UnitPrice" => $data["list"][$i][9],
                        "Qty" => $data["list"][$i][8],
                        "TaxCodeRef" => [
                            "value" => $data["tax"]
                        ],
                    ]
                );
                if ($data["list"][$i][13] == $data["list"][$i + 1][13]) {
                    $suma = $suma + $data["list"][$i][11];
                    array_push($line, $lineData);
                } else {
                    $suma = $suma + $data["list"][$i][11];
                    array_push($line, $lineData);
                    $date = date("t") . "-" . date("m") . "-" . date("Y");
                    $idCustomer = $this->getCustomerId($dataService, $customers, $data["list"][$i], $data["pm"]);
                    $theResourceObj = Invoice::create([
                                "DocNumber" => "0010000" . $data["list"][$i][13],
                                "TxnDate" => $date,
                                "CurrencyRef" => [
                                    "value" => $data["list"][$i][7]
                                ],
                                "ExchangeRate" => 1,
                                "Line" => $line,
                                "SalesTermRef" => [
                                    "value" => $data["term"]
                                ],
                                "BillEmail" => [
                                    "Address" => $data["list"][$i][2]
                                ],
                                "CustomerRef" => [
                                    "value" => $idCustomer,
                                ]
                    ]);
                    $resultingObj = $dataService->Add($theResourceObj);
                    $error = $dataService->getLastError();

                    if ($error) {
                        array_push($result, array(
                            "razon" => $data["list"][$i][0],
                            "cedula" => $data["list"][$i][1],
                            "lineas" => sizeof($line),
                            "total" => $suma,
                            "consecutivo" => "0010000" . $data["list"][$i][13],
                            "estado" => "No creado"));
                    } else {
                        array_push($result, array(
                            "razon" => $data["list"][$i][0],
                            "cedula" => $data["list"][$i][1],
                            "lineas" => sizeof($line),
                            "total" => $suma,
                            "consecutivo" => "0010000" . $data["list"][$i][13],
                            "estado" => "Creado"));
                    }
                    $line = array();
                    $suma = 0;
                    $contador++;
               
                }
                
            }

            return json_encode($result);
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    public function getCustomerId($dataService, $customers, $data, $pm) {
        $customers = json_decode($customers, true);
        foreach ($customers as $customer) {
            if ($customer["AlternatePhone"]["FreeFormNumber"] == $data[1]) {
                $id = $customer["Id"];
                $data [1] = $customer["Id"];
                $data [2] = "Encontrado";
                break;
            }
        }
        if ($id == "") {
            $idCust = $this->createCustomer($dataService, $data, $pm);
            $data [1] = $idCust;
            $data [2] = "Creado";
            $id = $idCust;
        }
        return $id;
    }

    public function createCustomer($dataService, $data, $pm) {
        try {
            $theResourceObj = Customer::create([
                        "BillAddr" => [
                            "Line1" => $data[4],
                            "City" => $data[5],
                            "Country" => "Costa Rica",
                            "CountrySubDivisionCode" => $data[6]
                        ],
                        "ShipAddr" => [
                            "Line1" => $data[6]
                        ],
                        "SalesTermRef" => [
                            "value" => "5"
                        ],
                        "PaymentMethodRef" => [
                            "value" => $pm
                        ],
                        "CurrencyRef" => [
                            "value" => "CRC",
                            "name" => "Costa Rica Colon"
                        ],
                        "FullyQualifiedName" => $data[0] . " " . $data[7],
                        "CompanyName" => $data[1],
                        "DisplayName" => $data[0] . " CL " . $data[7],
                        "PrimaryPhone" => [
                            "FreeFormNumber" => $data[3]
                        ],
                        "AlternatePhone" => [
                            "FreeFormNumber" => $data[1]
                        ],
                        "PrimaryEmailAddr" => [
                            "Address" => $data[2]
                        ]
            ]);
            $resultingObj = $dataService->Add($theResourceObj);
            $error = $dataService->getLastError();
            if ($error) {
                echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
                echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
                echo "The Response message is: " . $error->getResponseBody() . "\n";
            } else {
                return $resultingObj->Id;
            }
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    public function listAllCustomer($dataService) {
        try {
            // Run a query
            $cantidad = $dataService->Query("select count(*) from Customer");
            $cantidad = ceil($cantidad / 1000) * 1000;
            $error = $dataService->getLastError();
            if ($error) {
                echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
                echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
                echo "The Response message is: " . $error->getResponseBody() . "\n";
            }
            $customers = array();
            for ($i = 1; $i < $cantidad; $i = $i + 1000) {
                $customers = array_merge($customers, $dataService->findAll("Customer", $i, 1000));
            }
            // Echo some formatted output
            return json_encode($customers);
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    public function services($data) {
        $config = include('public/config.php');
        $accessToken = unserialize($_SESSION['sessionAccessToken']);
        try {
            $dataService = DataService::Configure(array(
                        'auth_mode' => 'oauth2',
                        'ClientID' => $config['client_id'],
                        'ClientSecret' => $config['client_secret'],
                        'RedirectURI' => $config['oauth_redirect_uri'],
                        'refreshTokenKey' => $data["client"][0]["refreshToken"],
                        'QBORealmID' => $data["realmId"],
                        'scope' => $config['oauth_scope'],
                        'baseUrl' => "production"
            ));
            $dataService->disableLog();
            $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
            $accessToken = $OAuth2LoginHelper->refreshToken();
            $dataService->updateOAuth2Token($accessToken);
            $_SESSION['sessionAccessToken'] = serialize($accessToken);
            $this->saveToken($data["realmId"], $accessToken->getRefreshToken());

            $error = $dataService->getLastError();
            if ($error != null) {
                return $result = array("status" => "400", "message" => 'Error en datos de acceso a QB');
            }
            $dataService->throwExceptionOnError(true);

            // Run a query
            $cantidad = $dataService->Query("select count(*) from Item");
            $cantidad = ceil($cantidad / 1000) * 1000;
            $items = array();
            for ($i = 1; $i < $cantidad; $i = $i + 1000) {
                $items = array_merge($items, $dataService->findAll("Item", $i, 1000));
            }
            // Echo some formatted output
            return json_encode($items);
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    public function taxes($data) {
        $config = include('public/config.php');
        $accessToken = unserialize($_SESSION['sessionAccessToken']);
        try {
            $dataService = DataService::Configure(array(
                        'auth_mode' => 'oauth2',
                        'ClientID' => $config['client_id'],
                        'ClientSecret' => $config['client_secret'],
                        'RedirectURI' => $config['oauth_redirect_uri'],
                        'refreshTokenKey' => $data["client"][0]["refreshToken"],
                        'QBORealmID' => $data["realmId"],
                        'scope' => $config['oauth_scope'],
                        'baseUrl' => "production"
            ));
            $dataService->disableLog();
            $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
            $accessToken = $OAuth2LoginHelper->refreshToken();
            $dataService->updateOAuth2Token($accessToken);
            $_SESSION['sessionAccessToken'] = serialize($accessToken);
            $this->saveToken($data["realmId"], $accessToken->getRefreshToken());

            $error = $dataService->getLastError();
            if ($error != null) {
                return $result = array("status" => "400", "message" => 'Error en datos de acceso a QB');
            }
            $dataService->throwExceptionOnError(true);

            // Run a query
            $cantidad = $dataService->Query("select count(*) from TaxCode");
            $cantidad = ceil($cantidad / 1000) * 1000;
            $items = array();
            for ($i = 1; $i < $cantidad; $i = $i + 1000) {
                $items = array_merge($items, $dataService->findAll("TaxCode", $i, 1000));
            }
            // Echo some formatted output
            return json_encode($items);
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    public function pms($data) {
        $config = include('public/config.php');
        $accessToken = unserialize($_SESSION['sessionAccessToken']);
        try {
            $dataService = DataService::Configure(array(
                        'auth_mode' => 'oauth2',
                        'ClientID' => $config['client_id'],
                        'ClientSecret' => $config['client_secret'],
                        'RedirectURI' => $config['oauth_redirect_uri'],
                        'refreshTokenKey' => $data["client"][0]["refreshToken"],
                        'QBORealmID' => $data["realmId"],
                        'scope' => $config['oauth_scope'],
                        'baseUrl' => "production"
            ));
            $dataService->disableLog();
            $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
            $accessToken = $OAuth2LoginHelper->refreshToken();
            $dataService->updateOAuth2Token($accessToken);
            $_SESSION['sessionAccessToken'] = serialize($accessToken);
            $this->saveToken($data["realmId"], $accessToken->getRefreshToken());

            $error = $dataService->getLastError();
            if ($error != null) {
                return $result = array("status" => "400", "message" => 'Error en datos de acceso a QB');
            }
            $dataService->throwExceptionOnError(true);

            // Run a query
            $cantidad = $dataService->Query("select count(*) from PaymentMethod");
            $cantidad = ceil($cantidad / 1000) * 1000;
            $items = array();
            for ($i = 1; $i < $cantidad; $i = $i + 1000) {
                $items = array_merge($items, $dataService->findAll("PaymentMethod", $i, 1000));
            }
            // Echo some formatted output
            return json_encode($items);
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    public function terms($data) {
        $config = include('public/config.php');
        $accessToken = unserialize($_SESSION['sessionAccessToken']);
        try {
            $dataService = DataService::Configure(array(
                        'auth_mode' => 'oauth2',
                        'ClientID' => $config['client_id'],
                        'ClientSecret' => $config['client_secret'],
                        'RedirectURI' => $config['oauth_redirect_uri'],
                        'refreshTokenKey' => $data["client"][0]["refreshToken"],
                        'QBORealmID' => $data["realmId"],
                        'scope' => $config['oauth_scope'],
                        'baseUrl' => "production"
            ));
            $dataService->disableLog();
            $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
            $accessToken = $OAuth2LoginHelper->refreshToken();
            $dataService->updateOAuth2Token($accessToken);
            $_SESSION['sessionAccessToken'] = serialize($accessToken);
            $this->saveToken($data["realmId"], $accessToken->getRefreshToken());

            $error = $dataService->getLastError();
            if ($error != null) {
                return $result = array("status" => "400", "message" => 'Error en datos de acceso a QB');
            }
            $dataService->throwExceptionOnError(true);

            // Run a query
            $cantidad = $dataService->Query("select count(*) from Term");
            $cantidad = ceil($cantidad / 1000) * 1000;
            $items = array();
            for ($i = 1; $i < $cantidad; $i = $i + 1000) {
                $items = array_merge($items, $dataService->findAll("Term", $i, 1000));
            }
            // Echo some formatted output
            return json_encode($items);
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }
    public function clases($data) {
        $config = include('public/config.php');
        $accessToken = unserialize($_SESSION['sessionAccessToken']);
        try {
            $dataService = DataService::Configure(array(
                        'auth_mode' => 'oauth2',
                        'ClientID' => $config['client_id'],
                        'ClientSecret' => $config['client_secret'],
                        'RedirectURI' => $config['oauth_redirect_uri'],
                        'refreshTokenKey' => $data["client"][0]["refreshToken"],
                        'QBORealmID' => $data["realmId"],
                        'scope' => $config['oauth_scope'],
                        'baseUrl' => "production"
            ));
            $dataService->disableLog();
            $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
            $accessToken = $OAuth2LoginHelper->refreshToken();
            $dataService->updateOAuth2Token($accessToken);
            $_SESSION['sessionAccessToken'] = serialize($accessToken);
            $this->saveToken($data["realmId"], $accessToken->getRefreshToken());

            $error = $dataService->getLastError();
            if ($error != null) {
                return $result = array("status" => "400", "message" => 'Error en datos de acceso a QB');
            }
            $dataService->throwExceptionOnError(true);

            // Run a query
            $cantidad = $dataService->Query("select count(*) from Class");
            $cantidad = ceil($cantidad / 1000) * 1000;
            $items = array();
            for ($i = 1; $i < $cantidad; $i = $i + 1000) {
                $items = array_merge($items, $dataService->findAll("Class", $i, 1000));
            }
            // Echo some formatted output
            return json_encode($items);
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }
    public function getDataService($data) {
        $config = include('public/config.php');;
        try {
            $dataService = DataService::Configure(array(
                    'auth_mode' => 'oauth2',
                    'ClientID' => $config['client_id'],
                    'ClientSecret' => $config['client_secret'],
                    'RedirectURI' => $config['oauth_redirect_uri'],
                    'refreshTokenKey' => $data["refreshToken"],
                    'QBORealmID' => $data["realmId"],
                    'scope' => $config['oauth_scope'],
                    'baseUrl' => "production"
            ));
            $dataService->disableLog();
            $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
            $accessToken = $OAuth2LoginHelper->refreshToken();
            $dataService->updateOAuth2Token($accessToken);
            $_SESSION['sessionAccessToken'] = serialize($accessToken);
            $this->saveToken($data["realmId"], $accessToken->getRefreshToken());

            $error = $dataService->getLastError();
            if ($error != null) {
                return $result = array("status" => "400", "message" => 'Error en datos de acceso a QB');
            }
            $dataService->throwExceptionOnError(true);
            return $dataService;
        }catch(Exception $e)
        {
            die($e->getMessage());
        }
    }
    function proceso($tiempo, $resultado) {
    usleep($tiempo);
    exit($resultado);
}
    public function syncFE($clients) {
        try {
            
            foreach($clients as $client){ 
            echo "<br>".$client['idcard'];
            $dataService = $this->getDataService($client);
            $invoices = json_decode($this->all($dataService), true);
            //obtiene los datos del emisor
            $emisor = json_encode($dataService->getCompanyInfo());
            $customers = $this->listAllCustomer($dataService);
            $customers = json_decode($customers,true);
            $items = $dataService->FindAll('Item',0,500);
            $taxCodes = $dataService->FindAll('TaxCode',0,500);
            $taxRates = $dataService->FindAll('TaxRate',0,500);
            $cont = 0;
            foreach ($invoices as $invoice) {
                
                if ($invoice['CustomField'][0]['StringValue'] == '') {
                    if (substr($invoice["DocNumber"], 8, 2) == '01') {
                        if (substr($invoice["DocNumber"], 8, 2) == '01') {
                            echo "<br>" . $invoice["DocNumber"];
                            //optiene los datos del receptor 
                            foreach($customers as $cus){
                                if($cus["Id"] == $invoice ["CustomerRef"]){
                                   $receptor = json_encode($cus);
                                }
                            }
                            
                            //crea los xml por factura 
                            if($invoice["CustomerMemo"]==""){
                                $result = $this->crearXMLFE($dataService, $invoice, $emisor, $receptor, $client,$items,$taxRates,$taxCodes);
                                $result = $this->syncFEMH($dataService,$client,$result,$invoice,$clave);
                            }else{
                                $clave = str_replace("Clave: ", "", $invoice["CustomerMemo"]);
                                $result = $this->syncFEMHConsulta($dataService,$client,$clave,$invoice["Id"],$invoice);
                                echo json_encode($result);
                            }
                            $cont++;
                        }
                    }
                  
                }
               if($cont == 25){
                   break;
               }
            }
        }
             return;
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }
    public function syncTE($clients) {
        try {
            foreach($clients as $client){ 
            echo "<br>".$client['idcard'];
            $dataService = $this->getDataService($client);
            $invoices = json_decode($this->all($dataService), true);
            //obtiene los datos del emisor
            $emisor = json_encode($dataService->getCompanyInfo());
            $customers = $this->listAllCustomer($dataService);
            $customers = json_decode($customers,true);
            $items = $dataService->FindAll('Item',0,500);
            $taxCodes = $dataService->FindAll('TaxCode',0,500);
            $taxRates = $dataService->FindAll('TaxRate',0,500);
            $cont = 0;
            foreach ($invoices as $invoice) {
                if ($invoice['CustomField'][0]['StringValue'] == '') {
                    if (substr($invoice["DocNumber"], 8, 2) == '04') {
                            echo "<br>" . $invoice["DocNumber"];
                            //optiene los datos del receptor 
                            foreach($customers as $cus){
                                if($cus["Id"] == $invoice ["CustomerRef"]){
                                   $receptor = json_encode($cus);
                                }
                            }
                            //crea los xml por factura 
                            if($invoice["CustomerMemo"]==""){
                                $result = $this->crearXMLTE($dataService, $invoice, $emisor, $receptor, $client,$items,$taxRates,$taxCodes);
                                $result = $this->syncFEMH($dataService,$client,$result,$invoice,$clave);
                            }else{
                                $clave = str_replace("Clave: ", "", $invoice["CustomerMemo"]);
                                $result = $this->syncFEMHConsulta($dataService,$client,$clave,$invoice["Id"],$invoice);
                            }
                            $cont++;
                    }
                }
               if($cont == 25){
                   break;
               }
            }
        }
             return;
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }
    public function syncFEMHConsulta($dataService,$client ,$clave, $invoiceId,$invoice){
        
        //Datos necesarios para la firma del xml
        $p12Url = "P12s/".$client["idcard"]."/".$client["urlP12"];
        $pinP12 = $client["passP12"]; 
        //Acceso al token de hacienda
        $token = $this->tokenMH($client);
        $token = json_decode($token["message"]);
        $token = $token->access_token;
        
        //consultar estado
        $consulta = $this->consultar('api-prod', $clave, $token);
        echo "<br> Estado de consulta:".$consulta["status"];

        if($consulta["status"]=="200" && $consulta["ind-estado"]!= "" && $consulta["ind-estado"]!= "procesando"){
		    $path = "files/".$client["idcard"]."/Creados/SinEnviar/".$clave.'/'.$clave.'.pdf';
		    $invoice = Invoice::create([
                "Id" => $invoiceId
            ]);
		    $pdfTmp = $dataService->DownloadPDF($invoice);
            rename($pdfTmp, $path);
		    
            $this->upload($dataService,"Respuesta",$consulta["message"],$invoiceId,$clave);
            echo "<br> upload respuesta en qb";
            $this->updateState($dataService,$invoiceId, $consulta["ind-estado"],$client,$clave);
            echo "<br> upload estado en qb";
            echo "<br>Respuesta: ".$consulta["ind-estado"];
        }if($consulta["status"]=="404"){
            echo "<br>Respuesta: No encontrado";
        }if($consulta["status"]=="401"){
            echo "<br> Respuesta: Sin autorizacion";
        }
        return;
    }
    public function syncFEMH($dataService, $client, $result, $invoice,$clave){
        
        //Datos necesarios para la firma del xml
        $p12Url = "P12s/".$client["idcard"]."/".$client["urlP12"];
        $pinP12 = $client["passP12"]; 
        //Acceso al token de hacienda
        $token = $this->tokenMH($client);
        $token = json_decode($token["message"]);
        $token = $token->access_token;
        
        $firmador = new Firmador();
        $xmlFirmado = $firmador->firmarXml($p12Url, $pinP12, $result['xml'], $firmador::TO_XML_STRING);
        
        $envio = $this->enviar($result, $xmlFirmado, $token);
        
        echo "<br> Estado de envio:".$envio["status"];
        if($envio["status"]=="202" || $envio["status"]=="200"){
            if($envio["ind-estado"]!= "" && $envio["ind-estado"]!= "procesando"){
                
                $this->upload($dataService,"Respuesta",$envio["message"],$invoice["Id"],$result["clave"]);
                echo "<br> upload respuesta en qb";
                $this->updateState($dataService,$invoice["Id"], $envio["ind-estado"],$client,$clave);
                echo "<br> upload estado en qb";
                echo "<br>Respuesta: ".$envio["ind-estado"];
                //envia xmls al correo
                if($envio["ind-estado"] == 'aceptado'){
                   
                }
                
            }
            echo "<br> Respuesta de envio: Enviado";
            $this->upload($dataService,"",$result['xml'],$invoice["Id"],$result["clave"]);
            echo "<br> upload xml a qb";
            $this->upload($dataService,"Firmado",$xmlFirmado,$invoice["Id"],$result["clave"]); 
            echo "<br> upload xml firmado a qb";
            $this->updateInvoice($dataService,$invoice["Id"], $result["clave"]);
            echo "<br> agrega la clave a qb";
        }
        if($envio["status"]=="400"){
            //consultar estado
            $consulta = $this->consultar('api-prod', $result["clave"], $token);
            echo "<br> Estado de consulta:".$consulta["status"];

            if($invoice["CustomerMemo"]=="" && $consulta["ind-estado"]!= ""){
                $this->updateInvoice($dataService,$invoice["Id"], $result["clave"]);
                echo "<br> agrega la clave a qb";
                $this->upload($dataService,"",$result["xml"],$invoice["Id"],$result["clave"]);
                echo "<br> upload xml a qb";
                $this->upload($dataService,"Firmado",$xmlFirmado,$invoice["Id"],$result["clave"]); 
                echo "<br> upload xml firmado a qb";
            }

            if($consulta["ind-estado"]!= "" && $consulta["ind-estado"]!= "procesando"){
                
                $this->upload($dataService,"Respuesta",$consulta["message"],$invoice["Id"],$result["clave"]);
                 $this->updateState($dataService,$invoice["Id"], $consulta["ind-estado"],$client,$clave);
                echo "<br> upload estado en qb";
                echo "<br>Respuesta: ".$consulta["ind-estado"];
                
                echo "<br> upload respuesta en qb";
            }if($consulta["status"]=="404"){
                echo "<br>Respuesta: No encontrado";
            }if($consulta["status"]=="401"){
                echo "<br> Respuesta: Sin autorizacion";
            }
        }if($consulta["status"]=="401"){
            echo "<br> Respuesta: Sin autorizacion";
        }
        return;
    }
    public function updateState($dataService,$invoiceId, $state,$client,$clave)
    {
        try
        {
		//Add a new Invoice
		$invoice = $dataService->FindbyId('Invoice', $invoiceId);
		$theResourceObj = Invoice::update($invoice, [
		      "CustomField" =>  [
		       [
		       "DefinitionId" => "1",
		       "Type"=> "StringType",
		      	"StringValue" => $state
		       ] 		      
		      ]		      
		]);
		$resultingObj = $dataService->Update($theResourceObj);
		if($state == "aceptado" && $client["ea"]== "1" && substr($invoice ->DocNumber, 8, 2) == '01'){
		   
		   // echo "enviar mensaje";
		   //$dataService->SendEmail($invoice);
		   $mail = new PHPMailer; 
		   $clave = str_replace ( "Clave: " , "" , $invoice->CustomerMemo);
		   echo "enviar al correo ->";
		   $this->sendFiles($dataService,$mail, $invoice->BillEmail->Address, $clave,$client["idcard"], $client["name"],$invoiceId);
		   echo "Enviado al correo";
		}
		$error = $dataService->getLastError();
		if ($error) {
		    return false;
		}
		else {
		    return "1";
		}
            
        }
        catch(Exception $e)
        {
             return false;
        }
    }
    function consultar($clientId, $clave, $token) {

	    $curl = curl_init();
	    //Validamos que venga el parametro de la clave
	
	    if ($clave == "" && strlen($clave) == 0) {
	        return "El valor codigoPais no debe ser vacio";
	    }
	    
	    $url;
	    if ($clientId == 'api-stag') {
	        $url = "https://api.comprobanteselectronicos.go.cr/recepcion-sandbox/v1/recepcion/";
	    } else if ($clientId == 'api-prod') {
	        $url = "https://api.comprobanteselectronicos.go.cr/recepcion/v1/recepcion/";
	    }
	
	
	    curl_setopt_array($curl, array(
	        CURLOPT_URL => $url . $clave,
	        CURLOPT_RETURNTRANSFER => true,
	        CURLOPT_ENCODING => "",
	        CURLOPT_MAXREDIRS => 15,
	        CURLOPT_TIMEOUT => 40,
	        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	        CURLOPT_CUSTOMREQUEST => "GET",
	        CURLOPT_HTTPHEADER => array(
	            "Authorization: Bearer " . $token,
	            "Cache-Control: no-cache",
	            "Content-Type: application/x-www-form-urlencoded",
	            "Postman-Token: bf8dc171-5bb7-fa54-7416-56c5cda9bf5c"
	        ),
	    ));
        
	
	    $response = curl_exec($curl);
	    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	    $err = curl_error($curl);
	    curl_close($curl);
	
	    if ($err) {
	        $result = array("status" => $status, "message" => "Error:" . $err);
	    } else {
	        $xml = json_decode($response, true);
	        $indEstado = $xml["ind-estado"];
	        $xml = $xml["respuesta-xml"];
	        $xml = base64_decode($xml);
	        $result = array("status" => $status, "message" => $xml, "ind-estado" => $indEstado);
	      
	    }
	     return $result;
	}
    public function updateInvoice($dataService,$invoiceId, $clave)
    {
        try
        {
           
		$invoice = $dataService->FindbyId('Invoice', $invoiceId);
		$theResourceObj = Invoice::update($invoice, [
		      "CustomerMemo" => 'Clave: '.$clave
		      
		]);
		$resultingObj = $dataService->Update($theResourceObj);
		$error = $dataService->getLastError();
		if ($error) {
		    echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
		    echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
		    echo "The Response message is: " . $error->getResponseBody() . "\n";
		}
		else {
		    return "true";
		}
            
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    }
     public function upload($dataService,$t,$xml,$id,$clave)
    {
        try
        {	
            
         if (strrpos($xml, 'mensajeHacienda') || strrpos($xml, 'MensajeHacienda')) {
             echo "respuesta guarda";
             $saveXML = simplexml_load_string ($xml);
             $carpeta = 'files/' . $saveXML->NumeroCedulaEmisor . '/Creados/SinEnviar/' . $saveXML->Clave;
            if (!file_exists($carpeta)) {
                mkdir($carpeta, 0777, true);
            }
            //agregar envio aqui
            $nombre_fichero = $carpeta . '/' . $saveXML->Clave . '-R.xml';
            
            $saveXML->asXML($nombre_fichero);
         }else{
             echo "factura guardada";
             $saveXML = simplexml_load_string ($xml);
             $carpeta = 'files/' . $saveXML->Emisor->Identificacion->Numero . '/Creados/SinEnviar/' . $saveXML->Clave;
            if (!file_exists($carpeta)) {
                mkdir($carpeta, 0777, true);
            }
             $nombre_fichero = $carpeta . '/' . $saveXML->Clave . '.xml'; 
             $saveXML->asXML($nombre_fichero);
         }
       
	// Prepare entities for attachment upload
	$xmlBase64 = array();
	$xmlBase64['application/vnd.openxmlformats-officedocument.wordprocessingml.document'] = $xml;
	
	$sendMimeType = "application/vnd.openxmlformats-officedocument.wordprocessingml.document";
	
	
	
	// Create a new IPPAttachable
	$randId = $clave;
	$entityRef = new IPPReferenceType(array('value'=>$id, 'type'=>'Invoice'));
	$attachableRef = new IPPAttachableRef(array('EntityRef'=>$entityRef));
	$objAttachable = new IPPAttachable();
	$objAttachable->FileName ="FA-".$t." ".$randId.".xml";
	$objAttachable->AttachableRef = $attachableRef;
	
	// Upload the attachment to the Bill
	$resultObj = $dataService->Upload(base64_encode($xmlBase64[$sendMimeType]),
	                                  $objAttachable->FileName,
	                                  $sendMimeType,
	                                  $objAttachable);
	
	return true;
        }
        catch(Exception $e)
        {
            return false;
        }
    }
    ///envio de xml firmado a hacienda
       function enviar($request,$xmlFirmado, $token) {
        $xml64= base64_encode($xmlFirmado);
	    $datos = array(
	        'clave' => $request["clave"],
	        'fecha' => $request["fecha"],
	        'emisor' => array(
	            'tipoIdentificacion' => $request["tipoEmisor"],
	            'numeroIdentificacion' => $request["idEmisor"]
	        ),
	        'receptor' => array(
	            'tipoIdentificacion' => $request["tipoReceptor"],
	            'numeroIdentificacion' => $request["idReceptor"]
	        ),
	        'comprobanteXml' => $xml64
	    );
	    $mensaje = json_encode($datos);
	    $header = array(
	        'Authorization: bearer ' . $token,
	        'Content-Type: application/json'
	    );
	
	    //$curl = curl_init("https://api.comprobanteselectronicos.go.cr/recepcion-sandbox/v1/recepcion");
	    $curl = curl_init("https://api.comprobanteselectronicos.go.cr/recepcion/v1/recepcion");
	    curl_setopt($curl, CURLOPT_HEADER, true);
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
	    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
	    curl_setopt($curl, CURLOPT_POSTFIELDS, $mensaje);
	
	    $respuesta = curl_exec($curl);
	    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);   
	    curl_close($curl);
	    $result = array("status" => $status, "message" => $respuesta);
	    return $result;
	}
    function tokenMH($request){
    $url;
        $client_id= "api-prod";
        $client_secret= "";
        $grant_type= "password";   
        //selecccion e acceso a DB
        if ($client_id == 'api-stag') {
            $url = "https://idp.comprobanteselectronicos.go.cr/auth/realms/rut-stag/protocol/openid-connect/token";
        } else if ($client_id == 'api-prod') {
            $url = "https://idp.comprobanteselectronicos.go.cr/auth/realms/rut/protocol/openid-connect/token";
        }
        $data;
        //Solicitud de un nuevo token
        if ($grant_type == "password") {            
           $username= $request["userMH"];
           $password= $request["passMH"];
            
            //Validation de los datos necesarios
            if ($client_id == '') {
                $result = array("status" => "400", "message" => "El parametro Client ID es requerido");
                return $result;
            } else if ($grant_type == '') {
                $result = array("status" => "400", "message" => "El parametro Grant Type es requerido");
                return $result;
            } else if ($username == '') {
                $result = array("status" => "400", "message" => "El parametro Username es requerido");
                return $result;
            } else if ($password == '') {
                $result = array("status" => "400", "message" => "El parametro Password es requerido");
                return $result;
            }
            
            //creadcion del array de acceso 
            $data = array(
                'client_id' => $client_id,
                'client_secret' => $client_secret,
                'grant_type' => $grant_type,
                'username' => $username,
                'password' => $password
            );
        //refrescand el token
        } 

        //creacion del header para la consulta
        $options = array(
            'http' => array(
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data)
            )
        );
        //consulta y resultado
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);   
        $result = array("status" => "200", "message" => $result);
        return $result;
       }

    public function crearXMLFE($dataService, $dataInvoice, $dataEmisor, $dataReceptor, $client, $items, $taxRates, $taxCodes) {
      
        $dataReceptor = json_decode($dataReceptor, true);
        $dataEmisor = json_decode($dataEmisor, true);

        //Ubicaciones
        $uEmisor = $this->ubicacion($dataEmisor["Country"], $dataEmisor["LegalAddr"]["CountrySubDivisionCode"], $dataEmisor["LegalAddr"]["City"], $dataEmisor["LegalAddr"]["Line1"]);
        $uEmisor = json_decode($uEmisor, true);

        $uReceptor = $this->ubicacion($dataReceptor["BillAddr"]["Country"], $dataReceptor["BillAddr"]["CountrySubDivisionCode"], $dataReceptor["BillAddr"]["City"], $dataReceptor["BillAddr"]["Line1"]);
        $uReceptor = json_decode($uReceptor, true);

        //identificaciones
        $idEmisor = str_replace("-", "", $dataEmisor["EmployerId"]);
        $idE = $idEmisor;
        if (strlen($idEmisor) < 10) {
            $tipoEmisor = '01';
            $idEmisor = "000" . $idEmisor;
        } else if (strlen($idEmisor) == 12) {
            $tipoEmisor = '02';
        } else {
            $tipoEmisor = '02';
            $idEmisor = "00" . $idEmisor;
        }

        $idReceptor = str_replace("-", "", $dataReceptor["AlternatePhone"]["FreeFormNumber"]);
        $idR = (string) $idReceptor;
        if (strpos($idR, 'R') !== false) {
            $tipoReceptor = '03';
            $idR = str_replace("R", "", $idR);
        } else if (strpos($idR, 'N') !== false) {
            $tipoReceptor = '04';
            $idR = str_replace("N", "", $idR);
        } else {
            if (strlen($idReceptor) < 10) {
                $tipoReceptor = '01';
                $idReceptor = "000" . $idReceptor;
            } else {
                $tipoReceptor = '02';
                $idReceptor = "00" . $idReceptor;
            }
        }
        if ($dataInvoice["SalesTermRef"] == null || $dataInvoice["SalesTermRef"] == "") {
            $cond = "01";
            $plazo = 1;
        } else {
            $term = $dataService->FindbyId('Term', $dataInvoice["SalesTermRef"]);
            $cond = $this->termVenta($term->Name);
            $cond = json_decode($cond, true);
            $cond = ($cond[0]["codigo"] == "") ? "99" : $cond[0]["codigo"];
            $plazo = ($term->DueDays == "") ? "1" : $term->DueDays;
        }

        if ($dataInvoice["PaymentMethodRef"] == null || $dataInvoice["PaymentMethodRef"] == "") {
            $m = "04";
        } else {
            $medio = $dataService->FindbyId('PaymentMethod', $dataInvoice["PaymentMethodRef"]);
            $medioP = $this->medioPago($medio->Name);
            echo "dada";
            $medioP = json_decode($medioP, true);
            $m = ($medioP[0]["codigo"] == "") ? "99" : $medioP[0]["codigo"];
        }
        $impuesto = '';
        if ($dataInvoice["ShipMethodRef"] != '') {
            $impuesto = $dataService->FindbyId('TaxRate', $dataInvoice["ShipMethodRef"]);
        }
        $fecha = $dataInvoice["MetaData"]["CreateTime"];
        $dia = substr($dataInvoice["MetaData"]["CreateTime"], 8, 2);
        $mes = substr($dataInvoice["MetaData"]["CreateTime"], 5, 2);
        $ano = substr($dataInvoice["MetaData"]["CreateTime"], 2, 2);
        $clave = "506" . $dia . $mes . $ano . $idEmisor . $dataInvoice["DocNumber"] . $client["tipoenvio"] . "87654321";

        //Datos emisor
        $telefono = str_replace(array(" ", "+","-"), array("", "",""), $dataEmisor["PrimaryPhone"]["FreeFormNumber"]);
        if ($telefono == null || $telefono == "") {
            $telefono = '22222222';
        }

        $provinciaE = $uEmisor["provincia"][0]["codigo"];
        $cantonE = $uEmisor["canton"][0]["codigo"];
        $distritoE = $uEmisor["distrito"][0]["codigo"];
        $codigoPE = $uEmisor["pais"][0]["codigoTelefono"];

        if ($provinciaE == null || $provinciaE == "") {
            $provinciaE = '1';
            $cantonE = '01';
            $distritoE = '01';
        }
        if ($cantonE == null || $cantonE == "") {
            $cantonE = '01';
            $distritoE = '01';
        }
        if ($distritoE == null || $distritoE == "") {
            $distritoE = '01';
        }
        if ($codigoPE == null || $codigoPE == "") {
            $codigoPE = '506';
        }

        $xmlString = '<?xml version="1.0" encoding="utf-8"?><FacturaElectronica xmlns="https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.3/facturaElectronica" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
          <Clave>' . $clave . '</Clave>
          <CodigoActividad>' . $dataEmisor["LegalAddr"]["PostalCode"] . '</CodigoActividad>
          <NumeroConsecutivo>' . $dataInvoice["DocNumber"] . '</NumeroConsecutivo>
          <FechaEmision>' . $dataInvoice['MetaData']['CreateTime'] . '</FechaEmision>
          <Emisor>
            <Nombre>' . $dataEmisor["LegalName"] . '</Nombre>
            <Identificacion>
            	<Tipo>' . $tipoEmisor . '</Tipo>
            	<Numero>' . $idE . '</Numero>
            </Identificacion>
            <NombreComercial>' . $dataEmisor["LegalName"] . '</NombreComercial>
            <Ubicacion>
            	<Provincia>' . $provinciaE . '</Provincia>
            	<Canton>' . $cantonE . '</Canton>
            	<Distrito>' . $distritoE . '</Distrito>
            	<OtrasSenas>' . $dataEmisor["CompanyAddr"]["Line1"] . '</OtrasSenas>
            </Ubicacion>
            <Telefono>
            	<CodigoPais>' . $codigoPE . '</CodigoPais>
            	<NumTelefono>' . $telefono . '</NumTelefono>
            </Telefono>
            <CorreoElectronico>' . $dataEmisor["Email"]["Address"] . '</CorreoElectronico>
          </Emisor>
            ';
        $provincia = $uReceptor["provincia"][0]["codigo"];
        $canton = $uReceptor["canton"][0]["codigo"];
        $distrito = $uReceptor["distrito"][0]["codigo"];

        if ($provincia == null) {
            $provincia = "1";
            $canton = "01";
            $distrito = "01";
        }
        if ($canton == null) {
            $canton = "01";
            $distrito = "01";
        }
        if ($distrito == null) {
            $distrito = "01";
        }
        $telefonoR = $dataReceptor["PrimaryPhone"]["FreeFormNumber"];
        if ($telefonoR == null || $telefonoR == "") {
            $telefonoR = '22222222';
        }
        $xmlString .= '<Receptor>
            <Nombre>' . $dataReceptor["DisplayName"] . '</Nombre>
            <Identificacion>
            	<Tipo>' . $tipoReceptor . '</Tipo>
            	<Numero>' . $idR . '</Numero>
            </Identificacion>
            <NombreComercial>' . $dataReceptor["DisplayName"] . '</NombreComercial>
            <Ubicacion>
            	<Provincia>' . $provincia . '</Provincia>
            	<Canton>' . $canton . '</Canton>
            	<Distrito>' . $distrito . '</Distrito>
            	<OtrasSenas>' . $dataReceptor["ShipAddr"]["Line1"] . '</OtrasSenas>
            </Ubicacion>
            <Telefono>
            	<CodigoPais>506</CodigoPais>
            	<NumTelefono>' . $telefonoR . '</NumTelefono>
            </Telefono>
            <CorreoElectronico>' . $dataReceptor["PrimaryEmailAddr"]["Address"] . '</CorreoElectronico>
          </Receptor>
            ';
        $xmlString .= '<CondicionVenta>' . $cond . '</CondicionVenta>
            ';
        if ($cond == "02") {
            $xmlString .= '<PlazoCredito>30</PlazoCredito>
      		  ';
        }
        $xmlString .= '<MedioPago>' . $m . '</MedioPago>
                <DetalleServicio>
                ';
        $sumaPGravados = 0;
        $sumaPExentos = 0;
        $sumaSGravados = 0;
        $sumaSExentos = 0;
        $p = 100;
        $sumaDescuentos = 0;
        $sumaImpuestos = 0;
        $exo = 0;
        $sumaSExo = 0;
        $sumaMExo = 0;
        
        foreach ($dataInvoice["Line"] as $line) {
            if ($line["LineNum"] != "") {
                foreach($items as $item){
                    if($item->Id == $line["SalesItemLineDetail"]["ItemRef"]){
                       $itemm = $item->Sku; 
                    }
                }
                $unidadMedida = $this->unidadMedia($itemm);
                $unidadMedida = json_decode($unidadMedida, true);
                if ($unidadMedida[0]['simbolo'] == '') {
                    $unidadMedida = $itemm;
                } else {
                    $unidadMedida = $unidadMedida[0]['simbolo'];
                }
                $cabys = '0000000000000';
                $descrip = $line["Description"];
                if(is_numeric(substr($line["Description"],0,13))){ 
                    $cabys = substr($line["Description"],0,13);
                    $descrip = str_replace($cabys."-", "", $line["Description"]);
                }
                $xmlString .= '<LineaDetalle>
		      <NumeroLinea>' . $line["LineNum"] . '</NumeroLinea>
		      <Codigo>'.$cabys.'</Codigo>
		      <Cantidad>' . bcdiv($line["SalesItemLineDetail"]["Qty"], 1, 3) . '</Cantidad>
		      <UnidadMedida>' . $unidadMedida . '</UnidadMedida>
		      <Detalle>' . $descrip . '</Detalle>
		      <PrecioUnitario>' . bcdiv($line["SalesItemLineDetail"]["UnitPrice"], 1, 5) . '</PrecioUnitario>
		      <MontoTotal>' . bcdiv($line["Amount"], 1, 5) . '</MontoTotal>
		      <SubTotal>' . bcdiv($line["Amount"], 1, 5) . '</SubTotal>
		      ';
                if (($line["SalesItemLineDetail"]["TaxCodeRef"] != "")) {
                    foreach($taxCodes as $taxCode){
                        if($taxCode->Id == $line["SalesItemLineDetail"]["TaxCodeRef"]){
                           $impuesto = $taxCode; 
                        }
                    }

                    if (($impuesto->Description != '98')) {
                        foreach($taxRates as $taxRate){
                            if($taxRate->Id == $impuesto->SalesTaxRateList->TaxRateDetail->TaxRateRef){
                               $imp = $taxRate; 
                            }
                        }
                        $exo=0;
                        $codig = substr($impuesto->Description, 0, 2);
                        $codigTarifa = substr($impuesto->Description, 2, 2);
                        $tarifa = 0;
                        $impMonto = 0;
                         if($impuesto->Description =='010803'){
                            $tarifa = 13;
                            $impMonto =  bcdiv($line["Amount"] *13 / $p, 1, 5);
                         }else{
                            $tarifa = bcdiv($imp->RateValue, 1, 0);
                            $impMonto =  bcdiv($line["Amount"] * $imp->RateValue / $p, 1, 5);
                         }
                        
                        if ($codig == "01") {
                            
                            $xmlString .= '<Impuesto>
                    <Codigo>' . $codig . '</Codigo>
                    <CodigoTarifa>' . $codigTarifa . '</CodigoTarifa>
          			    <Tarifa>' . $tarifa . '</Tarifa>
          			    <Monto>' . $impMonto . '</Monto>';
          			    if($impuesto->Description =='010803'){
          			        $tipoDocImp = substr($impuesto->Description, 4, 2);
          			         $xmlString .= '
          			         <Exoneracion>
                                <TipoDocumento>'.$tipoDocImp.'</TipoDocumento>
                                <NumeroDocumento>9830</NumeroDocumento>
                                <NombreInstitucion>Ley de alivio Fiscal ante el COVID-19</NombreInstitucion>
                                <FechaEmision>2020-03-19T00:00:00</FechaEmision>
                                <PorcentajeExoneracion>100</PorcentajeExoneracion>
                                <MontoExoneracion>' . $impMonto . '</MontoExoneracion>
                            </Exoneracion>
          			         ';
          			        $exo = $impMonto;
          			        if ($itemm == "Sp" || $itemm == "Os" || $itemm == "Spe") {
                                $sumaSExo = $sumaSExo  + $line["Amount"];
                            } else {
                                $sumaMExo = $sumaMExo + $line["Amount"];
                            }
          			    }
          			    if($impuesto->Description =='010803'){
        			     $xmlString .= '</Impuesto>
                  <ImpuestoNeto>0</ImpuestoNeto>
                  ';
                  $d = 0;
                        $sumaImpuestos = $sumaImpuestos + $d;
          			    }else{
          			        $xmlString .= '</Impuesto>
                  <ImpuestoNeto>' . $impMonto. '</ImpuestoNeto>
                  ';
                  $d = $impMonto;
                        $sumaImpuestos = $sumaImpuestos + $d;
          			    }
                        }

                        
                        $xmlString .= '<MontoTotalLinea>' . bcdiv($line["Amount"] + ($line["Amount"] * $imp->RateValue / $p), 1, 5) . '</MontoTotalLinea>
                </LineaDetalle>
                ';     if($impuesto->Description !='010803'){
                        if ($itemm == "Sp" || $itemm == "Os" || $itemm == "Spe") {
                            $sumaSGravados = $sumaSGravados + $line["Amount"];
                        } else {
                            $sumaPGravados = $sumaPGravados + $line["Amount"];
                        }
                }
                    } else {
                        $xmlString .= '<MontoTotalLinea>' . bcdiv($line["Amount"], 1, 5) . '</MontoTotalLinea>
                </LineaDetalle>
                ';
                        if ($itemm == "Sp" || $itemm == "Os" || $itemm == "Spe") {
                            $sumaSExentos = $sumaSExentos + $line["Amount"];
                        } else {
                            $sumaPExentos = $sumaPExentos + $line["Amount"];
                        }
                    }
                } else {

                    $xmlString .= '<MontoTotalLinea>' . bcdiv($line["Amount"], 1, 5) . '</MontoTotalLinea>
            </LineaDetalle>
            ';
                    if ($itemm == "Sp" || $itemm == "Os" || $itemm == "Spe") {
                        $sumaSExentos = $sumaSExentos + $line["Amount"];
                    } else {
                        $sumaPExentos = $sumaPExentos + $line["Amount"];
                    }
                }
            }
        }
        $xmlString .= '</DetalleServicio>
		  <ResumenFactura>
      ';
        
        if ($dataInvoice["CurrencyRef"] != "CRC" || $dataInvoice["CurrencyRef"] != "crc") {
            $xmlString .= '<CodigoTipoMoneda>
        <CodigoMoneda>' . $dataInvoice["CurrencyRef"] . '</CodigoMoneda>
        <TipoCambio>' . $dataInvoice["ExchangeRate"] . '</TipoCambio>
        </CodigoTipoMoneda>
        ';
        }
        $xmlString .= '<TotalServGravados>' . bcdiv($sumaSGravados, 1, 5) . '</TotalServGravados>
		    <TotalServExentos>' . bcdiv($sumaSExentos, 1, 5) . '</TotalServExentos>
		    <TotalServExonerado>' . bcdiv($sumaSExo, 1, 5) . '</TotalServExonerado>
		    <TotalMercanciasGravadas>' . bcdiv($sumaPGravados, 1, 5) . '</TotalMercanciasGravadas>
		    <TotalMercanciasExentas>' . bcdiv($sumaPExentos, 1, 5) . '</TotalMercanciasExentas>
		    <TotalMercExonerada>' . bcdiv($sumaMExo, 1, 5) . '</TotalMercExonerada>
		    <TotalGravado>' . bcdiv($sumaSGravados + $sumaPGravados, 1, 5) . '</TotalGravado>
		    <TotalExento>' . bcdiv($sumaSExentos + $sumaPExentos, 1, 5) . '</TotalExento>
		    <TotalExonerado>' . bcdiv($sumaSExo+$sumaMExo, 1, 5) . '</TotalExonerado>
		    <TotalVenta>' . bcdiv($sumaSGravados + $sumaPGravados + $sumaSExentos + $sumaPExentos+$sumaSExo+$sumaMExo, 1, 5) . '</TotalVenta>
		    <TotalDescuentos>0.00000</TotalDescuentos>
		    <TotalVentaNeta>' . bcdiv($sumaSGravados + $sumaPGravados + $sumaSExentos + $sumaPExentos+$sumaSExo+$sumaMExo, 1, 5) . '</TotalVentaNeta>
		    <TotalImpuesto>' . bcdiv($sumaImpuestos, 1, 5) . '</TotalImpuesto>
        <TotalIVADevuelto>0</TotalIVADevuelto>
		    <TotalComprobante>' . bcdiv($sumaSGravados + $sumaPGravados + $sumaSExentos + $sumaPExentos + $dataInvoice["TxnTaxDetail"]["TotalTax"]+$sumaSExo+$sumaMExo, 1, 5) . '</TotalComprobante>
		  </ResumenFactura>
		  ';
		  if($dataInvoice['CustomField'][1]['StringValue']!=""){
		      $xmlString .='<InformacionReferencia>
                <TipoDoc>99</TipoDoc>
                <Numero>'.$dataInvoice['CustomField'][1]['StringValue'].'</Numero>
                <FechaEmision>'.$dataInvoice['MetaData']['CreateTime'].'</FechaEmision>
                <Codigo>99</Codigo>
                <Razon>'.$dataInvoice['CustomField'][2]['StringValue'].'</Razon>
              </InformacionReferencia>';
		  }
		  
		 $xmlString .='</FacturaElectronica>
		  ';
		  echo $xmlString;
        $arrayResp = array(
            "fecha" => $fecha,
            "tipoEmisor" => $tipoEmisor,
            "idEmisor" => $idEmisor,
            "tipoReceptor" => $tipoReceptor,
            "idReceptor" => $idReceptor,
            "clave" => $clave,
            "xml" => trim($xmlString)
        );

        return $arrayResp;
    }
    public function crearXMLTE($dataService, $dataInvoice, $dataEmisor, $dataReceptor, $client, $items, $taxRates, $taxCodes) {
        $dataReceptor = json_decode($dataReceptor, true);
        $dataEmisor = json_decode($dataEmisor, true);

        //Ubicaciones
        $uEmisor = $this->ubicacion($dataEmisor["Country"], $dataEmisor["LegalAddr"]["CountrySubDivisionCode"], $dataEmisor["LegalAddr"]["City"], $dataEmisor["LegalAddr"]["Line1"]);
        $uEmisor = json_decode($uEmisor, true);

        $uReceptor = $this->ubicacion($dataReceptor["BillAddr"]["Country"], $dataReceptor["BillAddr"]["CountrySubDivisionCode"], $dataReceptor["BillAddr"]["City"], $dataReceptor["BillAddr"]["Line1"]);
        $uReceptor = json_decode($uReceptor, true);

        //identificaciones
        $idEmisor = str_replace("-", "", $dataEmisor["EmployerId"]);
        $idE = $idEmisor;
        if (strlen($idEmisor) < 10) {
            $tipoEmisor = '01';
            $idEmisor = "000" . $idEmisor;
        } else if (strlen($idEmisor) == 12) {
            $tipoEmisor = '02';
        } else {
            $tipoEmisor = '02';
            $idEmisor = "00" . $idEmisor;
        }

        $idReceptor = str_replace("-", "", $dataReceptor["AlternatePhone"]["FreeFormNumber"]);
        $idR = (string) $idReceptor;
        if (strpos($idR, 'R') !== false) {
            $tipoReceptor = '03';
            $idR = str_replace("R", "", $idR);
        } else if (strpos($idR, 'N') !== false) {
            $tipoReceptor = '04';
            $idR = str_replace("N", "", $idR);
        } else {
            if (strlen($idReceptor) < 10) {
                $tipoReceptor = '01';
                $idReceptor = "000" . $idReceptor;
            } else {
                $tipoReceptor = '02';
                $idReceptor = "00" . $idReceptor;
            }
        }
        if ($dataInvoice["SalesTermRef"] == null || $dataInvoice["SalesTermRef"] == "") {
            $cond = "01";
            $plazo = 1;
        } else {
            $term = $dataService->FindbyId('Term', $dataInvoice["SalesTermRef"]);
            $cond = $this->termVenta($term->Name);
            $cond = json_decode($cond, true);
            $cond = ($cond[0]["codigo"] == "") ? "99" : $cond[0]["codigo"];
            $plazo = ($term->DueDays == "") ? "1" : $term->DueDays;
        }

        if ($dataInvoice["PaymentMethodRef"] == null || $dataInvoice["PaymentMethodRef"] == "") {
            $m = "04";
        } else {
            $medio = $dataService->FindbyId('PaymentMethod', $dataInvoice["PaymentMethodRef"]);
            $medioP = $this->medioPago($medio->Name);
            echo "dada";
            $medioP = json_decode($medioP, true);
            $m = ($medioP[0]["codigo"] == "") ? "99" : $medioP[0]["codigo"];
        }
        $impuesto = '';
        if ($dataInvoice["ShipMethodRef"] != '') {
            $impuesto = $dataService->FindbyId('TaxRate', $dataInvoice["ShipMethodRef"]);
        }
        $fecha = $dataInvoice["MetaData"]["CreateTime"];
        $dia = substr($dataInvoice["MetaData"]["CreateTime"], 8, 2);
        $mes = substr($dataInvoice["MetaData"]["CreateTime"], 5, 2);
        $ano = substr($dataInvoice["MetaData"]["CreateTime"], 2, 2);
        $clave = "506" . $dia . $mes . $ano . $idEmisor . $dataInvoice["DocNumber"] . $client["tipoenvio"] . "87654321";

        //Datos emisor
        $telefono = str_replace(array(" ", "+","-"), array("", "",""), $dataEmisor["PrimaryPhone"]["FreeFormNumber"]);
        if ($telefono == null || $telefono == "") {
            $telefono = '22222222';
        }

        $provinciaE = $uEmisor["provincia"][0]["codigo"];
        $cantonE = $uEmisor["canton"][0]["codigo"];
        $distritoE = $uEmisor["distrito"][0]["codigo"];
        $codigoPE = $uEmisor["pais"][0]["codigoTelefono"];

        if ($provinciaE == null || $provinciaE == "") {
            $provinciaE = '1';
            $cantonE = '01';
            $distritoE = '01';
        }
        if ($cantonE == null || $cantonE == "") {
            $cantonE = '01';
            $distritoE = '01';
        }
        if ($distritoE == null || $distritoE == "") {
            $distritoE = '01';
        }
        if ($codigoPE == null || $codigoPE == "") {
            $codigoPE = '506';
        }

        $xmlString = '<?xml version="1.0" encoding="utf-8"?><TiqueteElectronico xmlns="https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.3/tiqueteElectronico" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
          <Clave>' . $clave . '</Clave>
          <CodigoActividad>' . $dataEmisor["LegalAddr"]["PostalCode"] . '</CodigoActividad>
          <NumeroConsecutivo>' . $dataInvoice["DocNumber"] . '</NumeroConsecutivo>
          <FechaEmision>' . $dataInvoice['MetaData']['CreateTime'] . '</FechaEmision>
          <Emisor>
            <Nombre>' . $dataEmisor["LegalName"] . '</Nombre>
            <Identificacion>
            	<Tipo>' . $tipoEmisor . '</Tipo>
            	<Numero>' . $idE . '</Numero>
            </Identificacion>
            <NombreComercial>' . $dataEmisor["LegalName"] . '</NombreComercial>
            <Ubicacion>
            	<Provincia>' . $provinciaE . '</Provincia>
            	<Canton>' . $cantonE . '</Canton>
            	<Distrito>' . $distritoE . '</Distrito>
            	<OtrasSenas>' . $dataEmisor["CompanyAddr"]["Line1"] . '</OtrasSenas>
            </Ubicacion>
            <Telefono>
            	<CodigoPais>' . $codigoPE . '</CodigoPais>
            	<NumTelefono>' . $telefono . '</NumTelefono>
            </Telefono>
            <CorreoElectronico>' . $dataEmisor["Email"]["Address"] . '</CorreoElectronico>
          </Emisor>
            ';
        $provincia = $uReceptor["provincia"][0]["codigo"];
        $canton = $uReceptor["canton"][0]["codigo"];
        $distrito = $uReceptor["distrito"][0]["codigo"];

        if ($provincia == null) {
            $provincia = "1";
            $canton = "01";
            $distrito = "01";
        }
        if ($canton == null) {
            $canton = "01";
            $distrito = "01";
        }
        if ($distrito == null) {
            $distrito = "01";
        }
        $telefonoR = $dataReceptor["PrimaryPhone"]["FreeFormNumber"];
        if ($telefonoR == null || $telefonoR == "") {
            $telefonoR = '22222222';
        }
         
        $xmlString .= '<CondicionVenta>' . $cond . '</CondicionVenta>
            ';
        if ($cond == "02") {
            $xmlString .= '<PlazoCredito>30</PlazoCredito>
      		  ';
        }
        $xmlString .= '<MedioPago>' . $m . '</MedioPago>
                <DetalleServicio>
                ';
        $sumaPGravados = 0;
        $sumaPExentos = 0;
        $sumaSGravados = 0;
        $sumaSExentos = 0;
        $p = 100;
        $sumaDescuentos = 0;
        $sumaImpuestos = 0;
        
        
        foreach ($dataInvoice["Line"] as $line) {
            if ($line["LineNum"] != "") {
                foreach($items as $item){
                    if($item->Id == $line["SalesItemLineDetail"]["ItemRef"]){
                       $itemm = $item->Sku; 
                    }
                }
                $unidadMedida = $this->unidadMedia($itemm);
                $unidadMedida = json_decode($unidadMedida, true);
                if ($unidadMedida[0]['simbolo'] == '') {
                    $unidadMedida = $itemm;
                } else {
                    $unidadMedida = $unidadMedida[0]['simbolo'];
                }

                $cabys = '0000000000000';
                $descrip = $line["Description"];
                if(is_numeric(substr($line["Description"],0,13))){ 
                    $cabys = substr($line["Description"],0,13);
                    $descrip = str_replace($cabys."-", "", $line["Description"]);
                }
                $xmlString .= '<LineaDetalle>
		      <NumeroLinea>' . $line["LineNum"] . '</NumeroLinea>
		      <Codigo>'.$cabys.'</Codigo>
		      <Cantidad>' . bcdiv($line["SalesItemLineDetail"]["Qty"], 1, 3) . '</Cantidad>
		      <UnidadMedida>' . $unidadMedida . '</UnidadMedida>
		      <Detalle>' . $descrip . '</Detalle>
		      <PrecioUnitario>' . bcdiv($line["SalesItemLineDetail"]["UnitPrice"], 1, 5) . '</PrecioUnitario>
		      <MontoTotal>' . bcdiv($line["Amount"], 1, 5) . '</MontoTotal>
		      <SubTotal>' . bcdiv($line["Amount"], 1, 5) . '</SubTotal>
		      ';
                if (($line["SalesItemLineDetail"]["TaxCodeRef"] != "")) {
                    foreach($taxCodes as $taxCode){
                        if($taxCode->Id == $line["SalesItemLineDetail"]["TaxCodeRef"]){
                           $impuesto = $taxCode; 
                        }
                    }

                    if (($impuesto->Description != '98')) {
                        foreach($taxRates as $taxRate){
                            if($taxRate->Id == $impuesto->SalesTaxRateList->TaxRateDetail->TaxRateRef){
                               $imp = $taxRate; 
                            }
                        }
                        
                        $codig = substr($impuesto->Description, 0, 2);
                        $codigTarifa = substr($impuesto->Description, 2, 2);
                        if ($codig == "01") {
                            $xmlString .= '<Impuesto>
                    <Codigo>' . $codig . '</Codigo>
                    <CodigoTarifa>' . $codigTarifa . '</CodigoTarifa>
          			    <Tarifa>' . bcdiv($imp->RateValue, 1, 0) . '</Tarifa>
          			    <Monto>' . bcdiv($line["Amount"] * $imp->RateValue / $p, 1, 5) . '</Monto>
        			    </Impuesto>
                  <ImpuestoNeto>' . bcdiv($line["Amount"] * $imp->RateValue / $p, 1, 5) . '</ImpuestoNeto>
                  ';
                        }

                        $d = $line["Amount"] * $imp->RateValue / $p;
                        $sumaImpuestos = $sumaImpuestos + $d;
                        $xmlString .= '<MontoTotalLinea>' . bcdiv($line["Amount"] + ($line["Amount"] * $imp->RateValue / $p), 1, 5) . '</MontoTotalLinea>
                </LineaDetalle>
                ';
                        if ($itemm == "Sp" || $itemm == "Os" || $itemm == "Spe") {
                            $sumaSGravados = $sumaSGravados + $line["Amount"];
                        } else {
                            $sumaPGravados = $sumaPGravados + $line["Amount"];
                        }
                    } else {
                        $xmlString .= '<MontoTotalLinea>' . bcdiv($line["Amount"], 1, 5) . '</MontoTotalLinea>
                </LineaDetalle>
                ';
                        if ($itemm == "Sp" || $itemm == "Os" || $itemm == "Spe") {
                            $sumaSExentos = $sumaSExentos + $line["Amount"];
                        } else {
                            $sumaPExentos = $sumaPExentos + $line["Amount"];
                        }
                    }
                } else {

                    $xmlString .= '<MontoTotalLinea>' . bcdiv($line["Amount"], 1, 5) . '</MontoTotalLinea>
            </LineaDetalle>
            ';
                    if ($itemm == "Sp" || $itemm == "Os" || $itemm == "Spe") {
                        $sumaSExentos = $sumaSExentos + $line["Amount"];
                    } else {
                        $sumaPExentos = $sumaPExentos + $line["Amount"];
                    }
                }
            }
        }
        $xmlString .= '</DetalleServicio>
		  <ResumenFactura>
      ';
        
        if ($dataInvoice["CurrencyRef"] != "CRC" || $dataInvoice["CurrencyRef"] != "crc") {
            $xmlString .= '<CodigoTipoMoneda>
        <CodigoMoneda>' . $dataInvoice["CurrencyRef"] . '</CodigoMoneda>
        <TipoCambio>' . $dataInvoice["ExchangeRate"] . '</TipoCambio>
        </CodigoTipoMoneda>
        ';
        }
        $xmlString .= '<TotalServGravados>' . bcdiv($sumaSGravados, 1, 5) . '</TotalServGravados>
		    <TotalServExentos>' . bcdiv($sumaSExentos, 1, 5) . '</TotalServExentos>
		    <TotalMercanciasGravadas>' . bcdiv($sumaPGravados, 1, 5) . '</TotalMercanciasGravadas>
		    <TotalMercanciasExentas>' . bcdiv($sumaPExentos, 1, 5) . '</TotalMercanciasExentas>
		    <TotalGravado>' . bcdiv($sumaSGravados + $sumaPGravados, 1, 5) . '</TotalGravado>
		    <TotalExento>' . bcdiv($sumaSExentos + $sumaPExentos, 1, 5) . '</TotalExento>
		    <TotalVenta>' . bcdiv($sumaSGravados + $sumaPGravados + $sumaSExentos + $sumaPExentos, 1, 5) . '</TotalVenta>
		    <TotalDescuentos>0.00000</TotalDescuentos>
		    <TotalVentaNeta>' . bcdiv($sumaSGravados + $sumaPGravados + $sumaSExentos + $sumaPExentos, 1, 5) . '</TotalVentaNeta>
		    <TotalImpuesto>' . bcdiv($sumaImpuestos, 1, 5) . '</TotalImpuesto>
        <TotalIVADevuelto>0</TotalIVADevuelto>
		    <TotalComprobante>' . bcdiv($sumaSGravados + $sumaPGravados + $sumaSExentos + $sumaPExentos + $dataInvoice["TxnTaxDetail"]["TotalTax"], 1, 5) . '</TotalComprobante>
		  </ResumenFactura>
		  </TiqueteElectronico>
		  ';
		
        $arrayResp = array(
            "fecha" => $fecha,
            "tipoEmisor" => $tipoEmisor,
            "idEmisor" => $idEmisor,
            "tipoReceptor" => $tipoReceptor,
            "idReceptor" => $idReceptor,
            "clave" => $clave,
            "xml" => trim($xmlString)
        );

        return $arrayResp;
    }

    public function ubicacion($pais, $provincia, $canton, $distrito) {
        try {
            $sql = "SELECT * FROM pais WHERE UPPER(nombre) like UPPER('" . $pais . "') || UPPER(iso2) like UPPER('" . $pais . "')";
            $pais = $this->pdo->prepare($sql);
            $pais->execute();
            $result['pais'] = $pais->fetchAll(PDO::FETCH_ASSOC);
            $sql = "SELECT * FROM provincia WHERE UPPER(provincia) like UPPER('" . $provincia . "')";
            $provincia = $this->pdo->prepare($sql);
            $provincia->execute();
            $result['provincia'] = $provincia->fetchAll(PDO::FETCH_ASSOC);
            $sql = "SELECT * FROM canton WHERE UPPER(canton) like UPPER('" . $canton . "') and UPPER(idProvincia) like UPPER('" . $result["provincia"][0]["id"] . "')";
            $canton = $this->pdo->prepare($sql);
            $canton->execute();
            $result['canton'] = $canton->fetchAll(PDO::FETCH_ASSOC);
            $sql = "SELECT * FROM distrito WHERE UPPER(distrito) like UPPER('" . $distrito . "') and idCanton = '" . $result["canton"][0]["id"] . "'";
            $distrito = $this->pdo->prepare($sql);
            $distrito->execute();
            $result['distrito'] = $distrito->fetchAll(PDO::FETCH_ASSOC);
            return json_encode($result);
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    public function termVenta($term) {
        try {
            $sql = "SELECT * FROM condicion_venta WHERE UPPER(condicionVenta) like UPPER('" . $term . "')";
            $term = $this->pdo->prepare($sql);
            $term->execute();
            $result = $term->fetchAll(PDO::FETCH_ASSOC);

            return json_encode($result);
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    public function unidadMedia($unidad) {
        try {
            $sql = "SELECT * FROM unidad_medida WHERE UPPER(simbolo) like UPPER('" . $unidad . "') || UPPER(descripcion) like UPPER('" . $unidad . "')";
            $unidad = $this->pdo->prepare($sql);
            $unidad->execute();
            $result = $unidad->fetchAll(PDO::FETCH_ASSOC);

            return json_encode($result);
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }
     public function medioPago($medio){
        $m = $medio;
	    try
	        {
	        $sql = "SELECT * FROM medio_pago WHERE medioPago like '".$m."'";
	          $term = $this->pdo->prepare($sql);
	          $term->execute();
	          $result=$term->fetchAll(PDO::FETCH_ASSOC);
	        
	          return json_encode($result);
	        }
	        catch(Exception $e)
	        {
	            die($e->getMessage());
	        }
	}

}
