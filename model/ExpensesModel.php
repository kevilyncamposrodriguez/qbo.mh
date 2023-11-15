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

class ExpensesModel {

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

    public function all() {
        $datos = array();
        $path = "files/" . $_SESSION['idCard'] . '/Recibidos/Sinprocesar/';
        if (is_dir($path)) {
            if ($dir = opendir($path)) {
                $count = 0;
                while (($file = readdir($dir)) !== false) {
                    if (is_dir($path . $file) && $file != '.' && $file != '..') {
                       if(file_exists ($path . $file . '/' . basename($file . '.xml')) && filesize($path . $file . '/' . basename($file . '.xml')) != 0){ 
                        $xml = simplexml_load_file($path . $file . '/' . basename($file . '.xml'));
                        $consecutivo = $xml->NumeroConsecutivo;
                        $idEmisor = $xml->Emisor->Identificacion->Numero;
                        $idEmisor = json_decode($idEmisor);
                        $nombre = $xml->Emisor->Nombre;
                        $correoEmisor = $xml->Emisor->CorreoElectronico;
                        $idReceptor = $xml->Receptor->Identificacion->Numero;
                        $idIdReceptor = json_decode($idReceptor);
                        $nombreReceptor = $xml->Receptor->Nombre;
                        $correoReceptor = $xml->Receptor->CorreoElectronico;
                        $detalle = $xml->DetalleServicio;
                        $others = "";
                        if(isset($xml->OtrosCargos)){
                          $others = $xml->OtrosCargos;
                        }
                        $moneda [0] = "CRC";
                        //para v4.2
                        if (isset($xml->ResumenFactura->CodigoMoneda)) {
                            $moneda = ''.$xml->ResumenFactura->CodigoMoneda;
                        }
                        //para v4.3
                        if (isset($xml->ResumenFactura->CodigoTipoMoneda)) {
                            $moneda = ''.$xml->ResumenFactura->CodigoTipoMoneda->CodigoMoneda;
                        }
                        $impuesto = 0;
                        if (isset($xml->ResumenFactura->TotalImpuesto)) {
                            $impuesto = ''.$xml->ResumenFactura->TotalImpuesto;
                        } else {
                            $impuesto = 0;
                        }
                         $descuento = 0;
                        if (isset($xml->ResumenFactura->TotalDescuento)) {
                            $descuento = ''.$xml->ResumenFactura->TotalDescuento;
                        } else {
                            $descuento = 0;
                        }
                        $monto = $xml->ResumenFactura->TotalComprobante;
                        $fecha = $xml->FechaEmision;
                        $datos[$count] = array(
                            "clave" => $file,
                            "consecutivo" => $consecutivo,
                            "fecha" => $fecha,
                            "idEmisor" => $idEmisor,
                            "Emisor" => $nombre,
                            "correoEmisor" => $correoEmisor,
                            "idReceptor" => $idReceptor,
                            "Receptor" => $nombreReceptor,
                            "correoReceptor" => $correoReceptor,
                            "detalle" => $detalle,
                            "others" => $others,
                            "moneda" => $moneda,
                            "impuesto" => $impuesto,
                            "descuento" => $descuento,
                            "monto" => round($monto, 2),
                            "subtotal" => round($monto - $impuesto, 2),
                            "PDF" => basename($file . '.pdf')
                        );
                        $count = $count + 1;
                    }
                }
                }
                closedir($dir);
            }
        }
        return json_encode($datos);
    }
    public function allP() {
        $datos = array();
        $path = "files/" . $_SESSION['idCard'] . '/Recibidos/Procesados/';
        if (is_dir($path)) {
            if ($dir = opendir($path)) {
                $count = 0;
                while (($file = readdir($dir)) !== false) {
                    if (is_dir($path . $file) && $file != '.' && $file != '..') {
                        if ($xml = simplexml_load_file($path . $file . '/' . basename($file . '.xml'))) {
                            $consecutivo = $xml->NumeroConsecutivo;
                            $idEmisor = $xml->Emisor->Identificacion->Numero;
                            $idEmisor = json_decode($idEmisor);
                            $nombre = $xml->Emisor->Nombre;
                            $correoEmisor = $xml->Emisor->CorreoElectronico;
                            $idReceptor = $xml->Receptor->Identificacion->Numero;
                            $idIdReceptor = json_decode($idReceptor);
                            $nombreReceptor = $xml->Receptor->Nombre;
                            $correoReceptor = $xml->Receptor->CorreoElectronico;
                            $detalle = $xml->DetalleServicio;
                            $others = "";
                            if(isset($xml->OtrosCargos)){
                               $others = $xml->OtrosCargos;
                            }
                             $others = $xml->OtrosCargos;
                            $moneda [0] = "CRC";
                            //para v4.2
                            if (isset($xml->ResumenFactura->CodigoMoneda)) {
                                $moneda = $xml->ResumenFactura->CodigoMoneda;
                            }
                            //para v4.3
                            if (isset($xml->ResumenFactura->CodigoTipoMoneda)) {
                                $moneda = $xml->ResumenFactura->CodigoTipoMoneda->CodigoMoneda;
                            }
                            $impuesto = 0;
                            if (isset($xml->ResumenFactura->TotalImpuesto)) {
                                $impuesto = $xml->ResumenFactura->TotalImpuesto;
                            } else {
                                $impuesto = 0;
                            }
                            $monto = $xml->ResumenFactura->TotalComprobante;
                            $fecha = $xml->FechaEmision;
                            $datos[$count] = array(
                                "clave" => $file,
                                "consecutivo" => $consecutivo,
                                "fecha" => $fecha,
                                "idEmisor" => $idEmisor,
                                "Emisor" => $nombre,
                                "correoEmisor" => $correoEmisor,
                                "idReceptor" => $idReceptor,
                                "Receptor" => $nombreReceptor,
                                "correoReceptor" => $correoReceptor,
                                "detalle" => $detalle,
                                "others" => $others,
                                "moneda" => $moneda,
                                "impuesto" => $impuesto,
                                "monto" => round($monto, 2),
                                "subtotal" => round($monto - $impuesto, 2),
                                "PDF" => basename($file . '.pdf')
                            );
                            $count = $count + 1;
                        }
                    }
                }
                closedir($dir);
            }
        }
        return json_encode($datos);
    }
    public function allP2($data) {
        try
        {     
          if(isset($data["FINI"]) and isset($data["FFIN"])) {
              $sql = "SELECT * FROM FacturasRecibidas WHERE idCard= '".$data["idCard"]."' and (fecha BETWEEN '".$data["FINI"]."' AND '".$data["FFIN"]."')  ORDER BY `FacturasRecibidas`.`fecha` DESC"; 
          }else{
          $sql = "SELECT * FROM FacturasRecibidas WHERE idCard= '".$data["idCard"]."' and (fecha BETWEEN date_sub(now(), interval 1 month)  AND NOW()) ORDER BY `FacturasRecibidas`.`fecha` DESC"; 
          }
          $docs = $this->pdo->prepare($sql);
          $docs->execute();
          $docs=$docs->fetchAll(PDO::FETCH_ASSOC);
          return $this->allData($docs);
         // return json_encode($docs); 
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    }
    public function allData($docs){
        $count = 0;
        foreach($docs as $doc){
         $path = "files/" . $_SESSION['idCard'] . '/Recibidos/Procesados/'.$doc["clave"].'/'.$doc["clave"].'.xml';
                if ($xml = simplexml_load_file($path)) {
                    $consecutivo = $xml->NumeroConsecutivo;
                    $idEmisor = $xml->Emisor->Identificacion->Numero;
                    $idEmisor = json_decode($idEmisor);
                    $nombre = $xml->Emisor->Nombre;
                    $correoEmisor = $xml->Emisor->CorreoElectronico;
                    $idReceptor = $xml->Receptor->Identificacion->Numero;
                    $idIdReceptor = json_decode($idReceptor);
                    $nombreReceptor = $xml->Receptor->Nombre;
                    $correoReceptor = $xml->Receptor->CorreoElectronico;
                    $detalle = $xml->DetalleServicio;
                    $others = array();
                    if(isset( $xml->OtrosCargos)){
                        foreach ( $xml->OtrosCargos as $o) {
                            array_push($others, $o);
                        }
                    }
                    $moneda [0] = "CRC";
                    //para v4.2
                    if (isset($xml->ResumenFactura->CodigoMoneda)) {
                        $moneda = $xml->ResumenFactura->CodigoMoneda;
                    }
                    //para v4.3
                    if (isset($xml->ResumenFactura->CodigoTipoMoneda)) {
                        $moneda = $xml->ResumenFactura->CodigoTipoMoneda->CodigoMoneda;
                    }
                    $impuesto = 0;
                    if (isset($xml->ResumenFactura->TotalImpuesto)) {
                        $impuesto = $xml->ResumenFactura->TotalImpuesto;
                    } else {
                        $impuesto = 0;
                    }
                    $descuento = 0;
                    if (isset($xml->ResumenFactura->TotalDescuento)) {
                        $descuento = ''.$xml->ResumenFactura->TotalDescuento;
                    }else {
                        $descuento = 0;
                    }
                    $monto = $xml->ResumenFactura->TotalComprobante;
                    $fecha = $xml->FechaEmision;
                    $datos[$count] = array(
                        "clave" => $doc["clave"],
                        "consecutivo" => $consecutivo,
                        "estado" => $doc["estado"],
                        "RespuestaMH" => $doc["RespuestaMH"],
                        "RespuestaQBO" => $doc["RespuestaQBO"],
                        "fecha" => $fecha,
                        "idEmisor" => $idEmisor,
                        "Emisor" => $nombre,
                        "correoEmisor" => $correoEmisor,
                        "idReceptor" => $idReceptor,
                        "Receptor" => $nombreReceptor,
                        "correoReceptor" => $correoReceptor,
                        "detalle" => $detalle,
                        "others" => $others,
                        "moneda" => $moneda,
                        "impuesto" => $impuesto,
                        "descuento" => $descuento,
                        "monto" => round($monto, 2),
                        "subtotal" => round($monto - $impuesto, 2),
                        "fechaprocesado" => $doc["fechaprocesado"]
                    );
                    $count = $count + 1;
                }
            }
            return json_encode($datos);
    }

    public function createBill($client, $data) {
        $config = include('public/config.php');
        $accessToken = unserialize($_SESSION['sessionAccessToken']);
        try {
            $dataService = DataService::Configure(array(
                        'auth_mode' => 'oauth2',
                        'ClientID' => $config['client_id'],
                        'ClientSecret' => $config['client_secret'],
                        'RedirectURI' => $config['oauth_redirect_uri'],
                        'refreshTokenKey' => $client["refreshToken"],
                        'QBORealmID' => $client["realmId"],
                        'scope' => $config['oauth_scope'],
                        'baseUrl' => "production"
            ));
            $dataService->disableLog();
            $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
            $accessToken = $OAuth2LoginHelper->refreshToken();
            $dataService->updateOAuth2Token($accessToken);
            $_SESSION['sessionAccessToken'] = serialize($accessToken);
            $this->saveToken($client["realmId"], $accessToken->getRefreshToken());

            $error = $dataService->getLastError();
            if ($error != null) {
                return $result = array("status" => "400", "message" => 'Error en datos de acceso a QB');
            }
            $dataService->throwExceptionOnError(true);
            $idvendor = $this->vendor($dataService, $data);
            // Run a query
            $accountId = $this->getAccount($dataService,"Gasto sin clasificar");
            $accountId = json_decode($accountId, true);
            $accountId = $accountId[0]['Id'];
            $AccountRef = array(
                "Value" => $accountId
            );
            $AccountBasedExpenseLineDetail = array(
                "AccountRef" => $AccountRef
            );
            $line = array();
            $countL=0;
            foreach ($data['factura']->DetalleServicio->LineaDetalle as $l) {
                $lineData = array(
                    "Id" => (string) $countL++,
                    "Description" => (string) $l->Detalle,
                    "Amount" => (string) $l->MontoTotalLinea,
                    "DetailType" => "AccountBasedExpenseLineDetail",
                    "AccountBasedExpenseLineDetail" => $AccountBasedExpenseLineDetail
                );
                array_push($line, $lineData);
            }
            
            if(isset($data['factura']->OtrosCargos)){
                foreach ($data['factura']->OtrosCargos as $o) {
                    $lineData = array(
                        "Id" => $countL++,
                        "Description" =>(string) $o->Detalle,
                        "Amount" => (string) $o->MontoCargo,
                        "DetailType" => "AccountBasedExpenseLineDetail",
                        "AccountBasedExpenseLineDetail" => $AccountBasedExpenseLineDetail
                    );
                    array_push($line, $lineData);
                }
            }
            $vendor = array(
                "Value" => $idvendor
            );
            $ExchangeRate = 1;
            if(isset($data['factura']->ResumenFactura->CodigoTipoMoneda->CodigoMoneda)){
	            if(''.$data['factura']->ResumenFactura->CodigoTipoMoneda->CodigoMoneda == "USD"){
    	            $CurrencyRef = array(
    	                 "value"=> "USD",
                         "name"=> "Dólar estadounidense"
    	            );
    	            $ExchangeRate = $data['factura']->ResumenFactura->CodigoTipoMoneda->TipoCambio;
	            }
	            else{
                    $ExchangeRate = 1;
    	            $CurrencyRef = array(
            	                 "value"=> "CRC",
                                 "name"=> "Colón costarricense"
            	            );  
                }
            }else{
                $ExchangeRate = 1;
	            $CurrencyRef = array(
        	                 "value"=> "CRC",
                             "name"=> "Colón costarricense"
        	            );  
            }
            $exR = 1;
            if(isset($data['factura']->ResumenFactura->CodigoTipoMoneda->TipoCambio)){
                $exR = (string)$data['factura']->ResumenFactura->CodigoTipoMoneda->TipoCambio;
            }
            $bill = array(
                "TxnDate" => substr($data['factura']->FechaEmision, 0, 10),
                "CurrencyRef" => $CurrencyRef,
                "ExchangeRate" => $exR,
                "PrivateNote" => 'Consecutivo confirmacion = ' . $data["consecutivo"],
                "Line" => $line,
                "VendorRef" => $vendor,
                "DocNumber" => ''.$data['factura']->NumeroConsecutivo
            );
            
            $theResourceObj = Bill::create($bill);
            $resultingObj = $dataService->Add($theResourceObj);
            $error = $dataService->getLastError();
            if ($error) {
                $this->updateRespuestaQBO('error al guardar', $data['factura']->Clave);
            } else {
                $xmlBody = XmlObjectSerializer::getPostXmlFromArbitraryEntity($resultingObj, $urlResource);
                $this->updateRespuestaQBO('Guardado', $data['factura']->Clave);
                $this->resultPDF($dataService,$data, $resultingObj->Id,1); //funciona bien
                $this->uploadXML($dataService,$data, $resultingObj->Id,1); //funciona bien
                $this->uploadXMLR($dataService,$data, $resultingObj->Id,1);
                $this->uploadPDF($dataService,$data, $resultingObj->Id,1);
            }
            return true;
        } catch (Exception $e) {
            $this->updateRespuestaQBO('Guardado', $data['factura']->Clave);
          $nota=$e->getMessage();
          echo"<script type=\"text/javascript\">alert('".$nota."'); </script>"; 
        }
    }

    public function deleted($data) {
        
    }

    public function search($data) {
        
    }

    public function update($data) {
        
    }

    function countLocal() {
        $datos = array();
        $path = "files/" . $_SESSION['idCard'] . '/Recibidos/Sinprocesar/';
        $count = 0;
        if (is_dir($path)) {
            if ($dir = opendir($path)) {
                $count = 0;
                while (($file = readdir($dir)) !== false) {
                    if (is_dir($path . $file) && $file != '.' && $file != '..') {
                        $count = $count + 1;
                    }
                }
                closedir($dir);
            }
        }
        $sql = "Select count(*) as qty from FacturasRecibidas where idcard='" . $_SESSION['idCard'] . "'";
        $result = $this->pdo->prepare($sql);
        $result->execute();
        $total = $result->fetchAll(PDO::FETCH_ASSOC);
        $datos["p"] = $total[0]["qty"];
        $datos["np"] = $count;

        $sql = "Select count(*) as qty from FacturasRecibidas where idcard='" . $_SESSION['idCard'] . "' and estado='Aceptado'";
        $result = $this->pdo->prepare($sql);
        $result->execute();
        $acept = $result->fetchAll(PDO::FETCH_ASSOC);
        $datos["acepted"] = $acept[0]["qty"];

        $sql = "Select count(*) as qty from FacturasRecibidas where idcard='" . $_SESSION['idCard'] . "' and estado='Rechazado'";
        $result = $this->pdo->prepare($sql);
        $result->execute();
        $rejected = $result->fetchAll(PDO::FETCH_ASSOC);
        $datos["rejected"] = $rejected[0]["qty"];

        return json_encode($datos);
    }

    public function accept($data) {
        $consecutivo = $this->consecutive($data);
        $result = $this->crearXMLMensaje($consecutivo, $data["key"], "Aceptado");
        $xml = (string) $result['factura']->NumeroConsecutivo;
        $tipo = substr($xml, 8, 2);

        if ($tipo == '04') {
            $dataMH = $this->procesarT($data, $clave, 'Aceptado');
            $respuesta = $this->createBillT($c, $data, $consecutivo);
            $consecutivo = $this->aunmentaConsecutivo($alm);
        } else {
            
        }
    }

    public function process($data, $xmlFirmado) {
         date_default_timezone_set('America/Costa_Rica');
        $fecha = date(DATE_RFC3339);
        if($data["c"]==1){
            $MiR = "Aceptado";
        }else{
          $MiR = "Rechazado";
        }
        try {
            //Proceso con MH
            $result = $this->enviarMensaje($data, $xmlFirmado);
            if ($result['status'] == '200' || $result['status'] == '202' || $result['status'] == '100') {
                $respuesta = 'Aceptado';
                // guardado en bd
                 $sql = "INSERT IGNORE INTO FacturasRecibidas(consecutivo,clave,emisor,Estado,RespuestaMH,idCard,fecha)VALUES ('" . $data['consecutivo'] . "','" . $data["key"] . "','" . $data['factura']->Emisor->Nombre . "','" . $MiR . "','" . $respuesta . "','" . $_SESSION['idCard'] . "','" . $data['factura']->FechaEmision . "')";
                $fb = $this->pdo->prepare($sql);
                $fb = $fb->execute();
            } else {
               $respuesta = 'Rechazado';
                // guardado en bd
                $sql = "INSERT IGNORE INTO FacturasRecibidas(consecutivo,clave,emisor,Estado,RespuestaMH,idCard,fecha,MensajeError)VALUES ('" . $data['consecutivo'] . "','" . $data["key"] . "','" . $data['factura']->Emisor->Nombre . "','" . $MiR . "','" . $respuesta . "','" . $_SESSION['idCard'] . "','" . $data['factura']->FechaEmision . "','" . $result['text'] . "')";
                $fb = $this->pdo->prepare($sql);
                $fb = $fb->execute();
            }

            if ($fb == 1) {
                $carpeta = 'files/' . $_SESSION['idCard'] . '/Recibidos/Procesados';
                if (!file_exists($carpeta)) {
                    mkdir($carpeta, 0755, true);
                }

                $pathEnvio = "files/" . $_SESSION['idCard'] . '/Recibidos/Procesados/' . $data["key"];
                if (!file_exists($pathEnvio)) {
                    mkdir($pathEnvio, 0755, true);
                }
                $path = "files/" . $_SESSION['idCard'] . '/Recibidos/Sinprocesar/' . $data["key"];
                rename($path, $pathEnvio);
            }
            return $result;
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }
   
    public function saveQB($data) {
        try {
           $xml = $data["factura"];
                // guardado en bd
                $sql = "INSERT IGNORE INTO FacturasRecibidas(consecutivo,clave,emisor,Estado,RespuestaMH,idCard,fecha)VALUES ('Ninguno','" . $data["key"] . "','" . $xml->Emisor->Nombre . "','Solo guardar','Ninguno','" . $_SESSION['idCard'] . "','" . $xml->FechaEmision . "')";
               
                $fb = $this->pdo->prepare($sql);
                $fb = $fb->execute();
               
                if ($fb == 1) {
                    $carpeta = 'files/' . $_SESSION['idCard'] . '/Recibidos/Procesados';
                    if (!file_exists($carpeta)) {
                        mkdir($carpeta, 0755, true);
                    }
        
                    $pathEnvio = "files/" . $_SESSION['idCard'] . '/Recibidos/Procesados/' . $data["key"];
                    if (!file_exists($pathEnvio)) {
                        mkdir($pathEnvio, 0755, true);
                    }
                    $path = "files/" . $_SESSION['idCard'] . '/Recibidos/Sinprocesar/' . $data["key"];
                    rename($path, $pathEnvio);
                }
        return $result;
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    public function consecutive($data) {
        try {
           
            $sql = "SELECT * FROM consecutivos WHERE idCard= '" . $data["idCard"] . "'";
            $result = $this->pdo->prepare($sql);
            $result->execute();
            $result = $result->fetchAll(PDO::FETCH_ASSOC);
             if($data["c"]==1){
                $consecutivo = $result[0]["concutivo"];
                while (strlen($consecutivo) < 10) {
                $consecutivo = '0' . $consecutivo;
            }
            
            $consecutivo = "0010000105" . $consecutivo;
             }else{
                $consecutivo = $result[0]["rechazo"];
                while (strlen($consecutivo) < 10) {
                $consecutivo = '0' . $consecutivo;
            }
            
            $consecutivo = "0010000107" . $consecutivo;
             }
            
            return $consecutivo;
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    public function crearXMLMensaje($consecutivo, $clave, $condicion) {
        $path = "files/" . $_SESSION['idCard'] . '/Recibidos/Sinprocesar/' . $clave;
        if (simplexml_load_file($path . '/' . basename($clave . '.xml'))) {
            $factura = simplexml_load_file($path . '/' . basename($clave . '.xml'));

            $xmlString = '<?xml version="1.0" encoding="utf-8"?>
            <MensajeReceptor xmlns="https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.3/mensajeReceptor">
            <Clave>' . $factura->Clave . '</Clave>
            <NumeroCedulaEmisor>' . $factura->Emisor->Identificacion[0]->Numero . '</NumeroCedulaEmisor>
            <FechaEmisionDoc>' . $factura->FechaEmision . '</FechaEmisionDoc>
            <Mensaje>' . $condicion . '</Mensaje>
            ';
            if ($factura->ResumenFactura->TotalImpuesto == '') {
                $xmlString .= '<MontoTotalImpuesto>0.00</MontoTotalImpuesto>
	    ';
            } else {
                $xmlString .= '<MontoTotalImpuesto>' . $factura->ResumenFactura->TotalImpuesto . '</MontoTotalImpuesto>
	      ';
            }
            $xmlString .= '<TotalFactura>' . $factura->ResumenFactura->TotalComprobante . '</TotalFactura>
            <NumeroCedulaReceptor>' . $factura->Receptor->Identificacion[0]->Numero . '</NumeroCedulaReceptor>
            <NumeroConsecutivoReceptor>' . $consecutivo . '</NumeroConsecutivoReceptor>
            </MensajeReceptor>';
            $result = array(
                "factura" => $factura,
                "mensaje" => $xmlString,
                "consecutivo" => $consecutivo
            );
            return $result;
        } else {
            return false;
        }
    }

    //fin listar_archivos 
    function enviarMensaje($data, $xmlFirmado) {
             $fecha = new DateTime('now');
             $fecha->setTimezone(new DateTimeZone('America/Costa_Rica'));
             $fecha = $fecha->format(DATE_RFC3339);
    	    $url;
    	    $apiTo = 'api-prod';
    	    if ($apiTo == 'api-stag') {
    	        $url = "https://api.comprobanteselectronicos.go.cr/recepcion-sandbox/v1/recepcion/";
    	    } else if ($apiTo == 'api-prod') {
    	        $url = "https://api.comprobanteselectronicos.go.cr/recepcion/v1/recepcion/";
    	    }
    	    $idE= (string)$data['factura']->Emisor[0]->Identificacion[0]->Tipo;
    	    $datos = array(
    	        'clave' => $data["key"],
    	        'fecha' => $fecha,
    	        'emisor' => array(
    	            'tipoIdentificacion' => (string)$data['factura']->Emisor[0]->Identificacion[0]->Tipo,
    	            'numeroIdentificacion' => (string)$data['factura']->Emisor[0]->Identificacion[0]->Numero
    	        ),
    	        'receptor' => array(
    	            'tipoIdentificacion' => (string)$data['factura']->Receptor[0]->Identificacion[0]->Tipo,
    	            'numeroIdentificacion' => (string)$data['factura']->Receptor[0]->Identificacion[0]->Numero
    	        ),
    	        'consecutivoReceptor' => $data['consecutivo'],
    	        'comprobanteXml' => base64_encode($xmlFirmado)
    	    );
    	   // echo json_encode($datos);
    	//$datosJ= http_build_query($datos);
    	    $mensaje = json_encode($datos);
    	    $header = array(
    	        'Authorization: bearer ' . $data["token"],
    	        'Content-Type: application/json'
    	    );
    	    $curl = curl_init($url);
    	    curl_setopt($curl, CURLOPT_HEADER, true);
    	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    	    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    	    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
    	    curl_setopt($curl, CURLOPT_POSTFIELDS, $mensaje);
    	    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    	    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    	    $respuesta = curl_exec($curl);
    	    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    	    $err = curl_error($curl);
    	    curl_close($curl);
    	    if ($err) {
    	        $arrayResp = array(
    	            "status" => $status,
    	            "to" => $apiTo,
    	            "text" => $err,
    	            "datos" => $datos,
    	        );
    	        return $arrayResp;
    	    } else {
    	        $arrayResp = array(
    	            "status" => $status,
    	            "text" => explode("\n", $respuesta),
    	            "datos" => $datos
    	        );
    	        return $arrayResp;
    	        
    	    }
    	}
    public function aumentaConsecutivo($data)
    {
        try
        {   
          $sql = "SELECT * FROM consecutivos WHERE idCard= '".$data["idCard"]."'"; 
          $clients = $this->pdo->prepare($sql);
          $clients->execute();
          $clients=$clients->fetchAll(PDO::FETCH_ASSOC); 
          if($data["c"]==1){
            $sql = "UPDATE consecutivos SET concutivo= '".($clients[0]["concutivo"]+1) ."' where idCard = '".$data["idCard"]."'";
          }else{
            $sql = "UPDATE consecutivos SET rechazo= '".($clients[0]["rechazo"]+1) ."' where idCard = '".$data["idCard"]."'";
          }
              $user = $this->pdo->prepare($sql);
              $user->execute();
          return $clients; 
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    } 
    public function getAccount($dataService,$data)
	    {
	      try{
	            // Run a query
	            $entities = $dataService->Query("select * from Account where Name = '".$data."'");
	            $error = $dataService->getLastError();
	            if ($error) {
	                echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
	                echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
	                echo "The Response message is: " . $error->getResponseBody() . "\n";
	            }
	            // Echo some formatted output
	            return json_encode($entities);
	            
	        }
	        catch(Exception $e)
	        {
	            die($e->getMessage());
	        }
	    }
	    public function vendor2($client){   
	   
	    $config = include('public/config.php');
        $accessToken = unserialize($_SESSION['sessionAccessToken']);
        try {
            $dataService = DataService::Configure(array(
                        'auth_mode' => 'oauth2',
                        'ClientID' => $config['client_id'],
                        'ClientSecret' => $config['client_secret'],
                        'RedirectURI' => $config['oauth_redirect_uri'],
                        'refreshTokenKey' => $client["refreshToken"],
                        'QBORealmID' => $client["realmId"],
                        'scope' => $config['oauth_scope'],
                        'baseUrl' => "production"
            ));
            $dataService->disableLog();
            $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
            $accessToken = $OAuth2LoginHelper->refreshToken();
            $dataService->updateOAuth2Token($accessToken);
            $_SESSION['sessionAccessToken'] = serialize($accessToken);
            $this->saveToken($client["realmId"], $accessToken->getRefreshToken());

            $error = $dataService->getLastError();
            if ($error != null) {
                return $result = array("status" => "400", "message" => 'Error en datos de acceso a QB');
            }
            $dataService->throwExceptionOnError(true);
              
	            $entities = array();
	            $result= array();
	            $i=1;
	            $entities = $dataService->FindAll('Customer', 1, 1000);
	                echo "<br>".count($entities);
	                foreach($entities as $e){
                    if(isset($e->AlternatePhone->FreeFormNumber)){
                    if($e->AlternatePhone->FreeFormNumber != "" && $e->AlternatePhone->FreeFormNumber != $e->CompanyName){
                        $vendor = $e;
                        $theResourceObj = Customer::update($vendor , [
                            "CompanyName" => $e->AlternatePhone->FreeFormNumber
                		    ]);
                        $resultingObj = $dataService->Update($theResourceObj);
    	               echo "<br>Created Id={$resultingObj->Id}. Reconstructed response body:\n\n";
                    }
	              }	
	            }
	            return;
                
	            
	        }
	        catch(Exception $e)
	        {
	            die($e->getMessage());
	        }
	    }
	    public function vendor3($client){   
	   
	    $config = include('public/config.php');
        $accessToken = unserialize($_SESSION['sessionAccessToken']);
        try {
            $dataService = DataService::Configure(array(
                        'auth_mode' => 'oauth2',
                        'ClientID' => $config['client_id'],
                        'ClientSecret' => $config['client_secret'],
                        'RedirectURI' => $config['oauth_redirect_uri'],
                        'refreshTokenKey' => $client["refreshToken"],
                        'QBORealmID' => $client["realmId"],
                        'scope' => $config['oauth_scope'],
                        'baseUrl' => "production"
            ));
            $dataService->disableLog();
            $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
            $accessToken = $OAuth2LoginHelper->refreshToken();
            $dataService->updateOAuth2Token($accessToken);
            $_SESSION['sessionAccessToken'] = serialize($accessToken);
            $this->saveToken($client["realmId"], $accessToken->getRefreshToken());

            $error = $dataService->getLastError();
            if ($error != null) {
                return $result = array("status" => "400", "message" => 'Error en datos de acceso a QB');
            }
            $dataService->throwExceptionOnError(true);
              
	            $entities = array();
	            $result= array();
	            $i=1;
	            $entities = $dataService->FindAll('Vendor', 1, 1000);
	                echo "<br>".count($entities);
	                foreach($entities as $e){
                    if(isset($e->AlternatePhone->FreeFormNumber)){
                    if($e->AlternatePhone->FreeFormNumber != "" && $e->AlternatePhone->FreeFormNumber != $e->CompanyName){
                        $vendor = $e;
                        $theResourceObj = Vendor::update($vendor , [
                            "CompanyName" => $e->AlternatePhone->FreeFormNumber
                		    ]);
                        $resultingObj = $dataService->Update($theResourceObj);
    	               echo "<br>Created Id={$resultingObj->Id}. Reconstructed response body:\n\n";
                    }
	              }	
	            }
	            return;
                
	            
	        }
	        catch(Exception $e)
	        {
	            die($e->getMessage());
	        }
	    }
	    public function vendor($dataService, $xml){   
	    
	        try
	        {        
	            $result = $dataService->Query("select * from Vendor where CompanyName = '".''.$xml['factura']->Emisor[0]->Identificacion[0]->Numero."'");
	            
	            if(count($result)>1){
	            $moneda="CRC";
                if(isset($xml['factura']->ResumenFactura->CodigoTipoMoneda)){
                  $moneda=$xml['factura']->ResumenFactura->CodigoTipoMoneda->CodigoMoneda;
                }
                if(isset($xml['factura']->ResumenFactura->CodigoMoneda)){
                  $moneda=$xml['factura']->ResumenFactura->CodigoMoneda;
                }
                  
                foreach($result as $r){
                   if( $r->CurrencyRef== $moneda ){ 
                       return $r->Id;                 
                     }
	            	}
	            }
              if(count($result)>1){
                  
                return $result[0]->Id;
              }
	            if(count($result)==1){
	                $moneda="CRC";
                    if(isset($xml['factura']->ResumenFactura->CodigoTipoMoneda)){
                      $moneda=$xml['factura']->ResumenFactura->CodigoTipoMoneda->CodigoMoneda;
                    }
                    if(isset($xml['factura']->ResumenFactura->CodigoMoneda)){
                      $moneda=$xml['factura']->ResumenFactura->CodigoMoneda;
                    }
	                if( $result[0]->CurrencyRef == $moneda){
	                  return $result[0]->Id;  
	                }else{
	                  $idr=$this->createVendor($dataService, $xml);
	                  return $idr; 
	                }
	              
	            }
	            if(count($result)==0){
	             $idr=$this->createVendor($dataService, $xml);
	             return $idr;
	            }
	        }
	        catch(Exception $e)
	        {
	            die($e->getMessage());
	        }
	    }
	    public function createVendor($dataService, $xml){
            try
              {
                $moneda="CRC";
                if(isset($xml['factura']->ResumenFactura->CodigoTipoMoneda)){
                  $moneda=$xml['factura']->ResumenFactura->CodigoTipoMoneda->CodigoMoneda;
                }
                if(isset($xml['factura']->ResumenFactura->CodigoMoneda)){
                  $moneda=$xml['factura']->ResumenFactura->CodigoMoneda;
                }
                $telefono = "22222222";
                if(isset($xml['factura']->Emisor[0]->Telefono->NumTelefono)){
                    $telefono = $xml['factura']->Emisor[0]->Telefono->NumTelefono;
                }
                if(isset($xml['factura']->Emisor[0]->Telefono->NumeroTelefono)){
                    $telefono = $xml['factura']->Emisor[0]->Telefono->NumeroTelefono;
                }
              
	            //Add a new Vendor
		    $theResourceObj = Vendor::create([
		     "BillAddr" => [		       
		        "Country" => "COSTA RICA"
		    ],
		    "TaxIdentifier" => ''.$xml['factura']->Emisor[0]->Identificacion[0]->Numero,
		    "CurrencyRef" => ''.$moneda,
		    "CompanyName" =>  ''.$xml['factura']->Emisor[0]->Identificacion[0]->Numero,
		    "DisplayName" => ''.$xml['factura']->Emisor[0]->Nombre.' PR '.$moneda,
		    "PrintOnCheckName" => ''.$xml['factura']->Emisor[0]->Nombre.' '.$moneda,
		    "PrimaryPhone" => [
		        "FreeFormNumber" => ''.$telefono
		    ],		   
		    "AlternatePhone" => [
		        "FreeFormNumber" => ''.$xml['factura']->Emisor[0]->Identificacion[0]->Numero
		    ],
		    "PrimaryEmailAddr" => [
		        "Address" => ''.$xml['factura']->Emisor[0]->CorreoElectronico
		    ]
		    
		]);
		$resultingObj = $dataService->Add($theResourceObj);
		$error = $dataService->getLastError();
		if ($error) {
		    $this->updateRespuestaQBO('Error al crear el nuevo proveedor', $xml['factura']->Clave);
		}
		else {
		    return $resultingObj->Id;
		    
		}
	        }
	        catch(Exception $e)
	        {
	            die($e->getMessage());
	        }	 
	}//fin create vendor
	public function resultPDF($dataService,$data,$id,$type)
	    { 	    
	      $path = "files/".$_SESSION['idCard'].'/Recibidos/Procesados/'.$data['factura']->Clave.'/Confirmacion-'.$data['factura']->Clave.'.pdf';	    
	    
		require('libs/fpdf/fpdf.php'); 
		$pdf=new FPDF();
		$pdf->AddPage();
		$pdf->SetFont('Arial','B',10);
		$pdf->Cell(40,10,'SISTEMA DE SINCRONIZACION QBO-MH - QUICKBOOKS ONLINE');$pdf->Ln(10);
		$pdf->Cell(40,10,'MENSASAJE DE ACEPTACION DE COMPROBANTE ELECTRONICO');$pdf->Ln(10);$pdf->Ln(10);$pdf->Ln(10);
		$pdf->SetFont('Arial','',10);
		$pdf->Cell(20,10,'Clave:	'.$data['factura']->Clave);$pdf->Ln(10);
		$pdf->Cell(20,10,'Numero cedula emisor:	'.$data['factura']->Emisor->Identificacion->Numero);$pdf->Ln(10);
		$pdf->Cell(20,10,'Fecha emision:	'.substr ($data['factura']->FechaEmision,0,10));$pdf->Ln(10);
		$pdf->Cell(20,10,'Monto total impuesto:	'.$data['factura']->ResumenFactura->TotalImpuesto);$pdf->Ln(10);
		$pdf->Cell(20,10,'Total factura:	'.$data['factura']->ResumenFactura->TotalComprobante);$pdf->Ln(10);
		$pdf->Cell(20,10,'Numero cedula receptor:	'.$data['factura']->Receptor->Identificacion->Numero);$pdf->Ln(10);$pdf->Ln(10);
		$pdf->SetFont('Arial','B',10);
		$pdf->Cell(40,10,'DETALLE CONFIRMACION');$pdf->Ln(10);
		$pdf->SetFont('Arial','',10);
		$pdf->Cell(20,10,'Numero consecutivo receptor:	'.(string)$data['factura']->NumeroConsecutivo);$pdf->Ln(10);
		$pdf->Cell(20,10,'Mensaje :	Aceptado');$pdf->Ln(10);$pdf->Ln(10);
		$pdf->Cell(40,10,'Autorizada mediante resolución N° DGT-R-033-2019 del 20-06-2019 ');
		$pdf->Output($path,'F');
		if(file_exists($path)){
		try
	        {
	            	
		$sendMimeType = "application/pdf";		
		
		// Create a new IPPAttachable
		$randId = $data['factura']->Clave;
		if($type==1){
		    $entityRef = new IPPReferenceType(array('value'=>$id, 'type'=>'Bill'));
		}
		if($type==3){
		    $entityRef = new IPPReferenceType(array('value'=>$id, 'type'=>'VendorCredit'));
		}
		$attachableRef = new IPPAttachableRef(array('EntityRef'=>$entityRef));
		$objAttachable = new IPPAttachable();
		$objAttachable->FileName ="Confirmacion-".$randId.".pdf";
		$objAttachable->AttachableRef = $attachableRef;
		
		// Upload the attachment to the Bill
		$resultObj = $dataService->Upload(base64_encode (file_get_contents($path)),
		                                  $objAttachable->FileName,
		                                  $sendMimeType,
		                                  $objAttachable);
		$error = $dataService->getLastError();
			if ($error) {	
			   return $error;
			}
			else {
			   return true;
			}
	        }
	        catch(Exception $e)
	        {
	            die($e->getMessage());
	       } }
	    }
	    	public function resultPDFR($data)
	    { 	    
	      $path = "files/".$_SESSION['idCard'].'/Recibidos/Procesados/'.$data['factura']->Clave.'/Confirmacion-'.$data['factura']->Clave.'.pdf';	    
	    try
	        {
		require('libs/fpdf/fpdf.php'); 
		$pdf=new FPDF();
		$pdf->AddPage();
		$pdf->SetFont('Arial','B',10);
		$pdf->Cell(40,10,'SISTEMA DE SINCRONIZACION QBO-MH - QUICKBOOKS ONLINE');$pdf->Ln(10);
		$pdf->Cell(40,10,'MENSASAJE DE RECHAZO DE COMPROBANTE ELECTRONICO');$pdf->Ln(10);$pdf->Ln(10);$pdf->Ln(10);
		$pdf->SetFont('Arial','',10);
		$pdf->Cell(20,10,'Clave:	'.$data['factura']->Clave);$pdf->Ln(10);
		$pdf->Cell(20,10,'Numero cedula emisor:	'.$data['factura']->Emisor->Identificacion->Numero);$pdf->Ln(10);
		$pdf->Cell(20,10,'Fecha emision:	'.substr ($data['factura']->FechaEmision,0,10));$pdf->Ln(10);
		$pdf->Cell(20,10,'Monto total impuesto:	'.$data['factura']->ResumenFactura->TotalImpuesto);$pdf->Ln(10);
		$pdf->Cell(20,10,'Total factura:	'.$data['factura']->ResumenFactura->TotalComprobante);$pdf->Ln(10);
		$pdf->Cell(20,10,'Numero cedula receptor:	'.$data['factura']->Receptor->Identificacion->Numero);$pdf->Ln(10);$pdf->Ln(10);
		$pdf->SetFont('Arial','B',10);
		$pdf->Cell(40,10,'DETALLE CONFIRMACION');$pdf->Ln(10);
		$pdf->SetFont('Arial','',10);
		$pdf->Cell(20,10,'Numero consecutivo receptor:	'.(string)$data['factura']->NumeroConsecutivo);$pdf->Ln(10);
		$pdf->Cell(20,10,'Mensaje :	Rechazado');$pdf->Ln(10);$pdf->Ln(10);
		$pdf->Cell(40,10,'Autorizada mediante resolución N° DGT-R-033-2019 del 20-06-2019 ');
		$pdf->Output($path,'F');
		
		
	     
	        }
	        catch(Exception $e)
	        {
	            die($e->getMessage());
	       } 
	    }
	    public function uploadXML($dataService,$data,$id,$type)
	    {
            $xml= $data["factura"]->asXML();
	        try
	        {
	           
        		// Prepare entities for attachment upload
        		$xmlBase64 = array();
        		$xmlBase64['application/vnd.openxmlformats-officedocument.wordprocessingml.document'] = $xml;		
        		$sendMimeType = "application/vnd.openxmlformats-officedocument.wordprocessingml.document";		
        				
        		
        		// Create a new IPPAttachable
        		$randId = $data["key"];
        		if($type==1){
        		    $entityRef = new IPPReferenceType(array('value'=>$id, 'type'=>'Bill'));
        		}
        		if($type==3){
        		    $entityRef = new IPPReferenceType(array('value'=>$id, 'type'=>'VendorCredit'));
        		}
        		$attachableRef = new IPPAttachableRef(array('EntityRef'=>$entityRef));
        		$objAttachable = new IPPAttachable();
        		$objAttachable->FileName ="FA-".$randId.".xml";
        		$objAttachable->AttachableRef = $attachableRef;
        		$objAttachable->Tag = 'Tag_' . $randId;
        		
        		// Upload the attachment to the Bill
        		$resultObj = $dataService->Upload(base64_encode ($xmlBase64[$sendMimeType]),
        		                                  $objAttachable->FileName,
        		                                  $sendMimeType,
        		                                  $objAttachable);
        		$error = $dataService->getLastError();
        			if ($error) {			  
        			   return $error;
        			}
        			else {
        			   return 'insertado';
        			}
        	        }
        	        catch(Exception $e)
        	        {
        	            die($e->getMessage());
        	        }
	    }//fin uploadxml
	    public function uploadXMLR($dataService,$data,$id,$type)
	    { 	    
	      $path = "files/".$_SESSION['idCard'].'/Recibidos/Procesados/'.$data["key"].'/'.$data["key"].'-R.xml';	 
	      if(file_exists($path)){   
	        try
	        {
	            // Prepare entities for attachment upload		
        		$sendMimeType = "application/vnd.openxmlformats-officedocument.wordprocessingml.document";	
		
		// Create a new IPPAttachable
		$randId = $data["key"];
		if($type==1){
		    $entityRef = new IPPReferenceType(array('value'=>$id, 'type'=>'Bill'));
		}
		if($type==3){
		    $entityRef = new IPPReferenceType(array('value'=>$id, 'type'=>'VendorCredit'));
		}
		$attachableRef = new IPPAttachableRef(array('EntityRef'=>$entityRef));
		$objAttachable = new IPPAttachable();
		$objAttachable->FileName ="FA-Respuesta".$randId.".xml";
		$objAttachable->AttachableRef = $attachableRef;
		
		// Upload the attachment to the Bill
		$resultObj = $dataService->Upload(base64_encode (file_get_contents($path)),
		                                  $objAttachable->FileName,
		                                  $sendMimeType,
		                                  $objAttachable);
		$error = $dataService->getLastError();
			if ($error) {
			   return $error;
			}
			else {
			   return 'insertado';
			}
	        }
	        catch(Exception $e)
	        {
	            die($e->getMessage());
	        }
	    }}
	     public function uploadPDF($dataService,$data,$id,$type)
	    { 	  
	      $path = "files/".$_SESSION['idCard'].'/Recibidos/Procesados/'.$data["key"].'/'.$data["key"].'.pdf';	 
	      if(file_exists($path)){
	        try
	        {				
    		$sendMimeType = "application/pdf";		
    		
    		// Create a new IPPAttachable
    		$randId = $data["key"];
    		if($type==1){
		        $entityRef = new IPPReferenceType(array('value'=>$id, 'type'=>'Bill'));
    		}
    		if($type==3){
    		    $entityRef = new IPPReferenceType(array('value'=>$id, 'type'=>'VendorCredit'));
    		}
    		$attachableRef = new IPPAttachableRef(array('EntityRef'=>$entityRef));
    		$objAttachable = new IPPAttachable();
    		$objAttachable->FileName ="FA-".$randId.".pdf";
    		$objAttachable->AttachableRef = $attachableRef;
    		
    		// Upload the attachment to the Bill
    		$resultObj = $dataService->Upload(base64_encode (file_get_contents($path)),
    		                                  $objAttachable->FileName,
    		                                  $sendMimeType,
    		                                  $objAttachable);
    		$error = $dataService->getLastError();
			if ($error) {
			   return $error;
			}
			else {
			   return 'insertado';
			}
	        }
	        catch(Exception $e)
	        {
	            die($e->getMessage());
	        }
        }
	    }
	//Actualizar respuesta QBO
	public function updateRespuestaQBO($respuesta, $clave){
	   $sql = "UPDATE FacturasRecibidas SET RespuestaQBO = '".$respuesta."' where clave = '".$clave."'";
              $user = $this->pdo->prepare($sql);
              $user->execute();
	}
	public function createVendorCredit($client, $data){
	  $config = include('public/config.php');
      $accessToken = unserialize($_SESSION['sessionAccessToken']);	
            try
              {
              $dataService = DataService::Configure(array(
                        'auth_mode' => 'oauth2',
                        'ClientID' => $config['client_id'],
                        'ClientSecret' => $config['client_secret'],
                        'RedirectURI' => $config['oauth_redirect_uri'],
                        'refreshTokenKey' => $client["refreshToken"],
                        'QBORealmID' => $client["realmId"],
                        'scope' => $config['oauth_scope'],
                        'baseUrl' => "production"
            ));
            $dataService->disableLog();
            $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
            $accessToken = $OAuth2LoginHelper->refreshToken();
            $dataService->updateOAuth2Token($accessToken);
            $_SESSION['sessionAccessToken'] = serialize($accessToken);
            $this->saveToken($client["realmId"], $accessToken->getRefreshToken());
            $error = $dataService->getLastError();
            if ($error != null) {
                return $result = array("status" => "400", "message" => 'Error en datos de acceso a QB');
            }
            $dataService->throwExceptionOnError(true);
            $idvendor = $this->vendor($dataService, $data);
	            // Run a query
	             $accountId = $this->getAccount($dataService,"Gasto sin clasificar");
	             $accountId = json_decode($accountId, true);
	             $accountId = $accountId[0]['Id'];
	              
	            $AccountRef = array(
	                  "value"=>$accountId
	            );
	            $AccountBasedExpenseLineDetail = array(
	            	 "AccountRef" =>$AccountRef	            
	            );
	            $line = array();
	            foreach ($data['factura']->DetalleServicio->LineaDetalle as $l){			
		        $lineData = array(
                   "Id" =>(string) $l->NumeroLinea,
    			   "Description" => (string)$l->Detalle,
    			   "Amount" => (string)$l->MontoTotalLinea,
    			   "DetailType" => "AccountBasedExpenseLineDetail",
    			   "AccountBasedExpenseLineDetail" =>$AccountBasedExpenseLineDetail
	                );	           
	                array_push($line, $lineData);
	            } 
	            $vendor = array(
	                 "value" => $idvendor
	            );
	             $ExchangeRate = 1;
	          
	            if(isset($data['factura']->ResumenFactura->CodigoTipoMoneda->CodigoMoneda)){
    	            if($data['factura']->ResumenFactura->CodigoTipoMoneda->CodigoMoneda == "USD"){
        	            $CurrencyRef = array(
        	                 "value"=> "USD",
                             "name"=> "Dólar estadounidense"
        	            );
        	            $ExchangeRate = $data['factura']->ResumenFactura->CodigoTipoMoneda->TipoCambio;
    	            }
    	            else{
                    $ExchangeRate = 1;
    	            $CurrencyRef = array(
            	                 "value"=> "CRC",
                                 "name"=> "Colón costarricense"
            	            );  
                }
	            }else{
                    $ExchangeRate = 1;
    	            $CurrencyRef = array(
            	                 "value"=> "CRC",
                                 "name"=> "Colón costarricense"
            	            );  
                }
                $exR = 1;
                if(isset($data['factura']->ResumenFactura->CodigoTipoMoneda->TipoCambio)){
                    $exR = (string)$data['factura']->ResumenFactura->CodigoTipoMoneda->TipoCambio;
                }
	            $bill = array(
	                "TxnDate" => substr($data['factura']->FechaEmision, 0, 10),
                    "CurrencyRef" => $CurrencyRef,
                    "ExchangeRate" => $exR,
                    "PrivateNote" => 'Consecutivo confirmacion = ' . $data["consecutivo"],
                    "Line" => $line,
                    "VendorRef" => $vendor,
                    "DocNumber" => ''.$data['factura']->NumeroConsecutivo
	            );
                $theResourceObj =  VendorCredit::create($bill);
			    $resultingObj = $dataService->Add($theResourceObj);			
		    	$error = $dataService->getLastError();
		if ($error) {
                $this->updateRespuestaQBO('error al guardar', $data['factura']->Clave);
            } else {
                $xmlBody = XmlObjectSerializer::getPostXmlFromArbitraryEntity($resultingObj, $urlResource);
                $this->updateRespuestaQBO('Guardado', $data['factura']->Clave);
                $this->resultPDF($dataService,$data, $resultingObj->Id,3); //funciona bien
                $this->uploadXML($dataService,$data, $resultingObj->Id,3); //funciona bien
                 $this->uploadPDF($dataService,$data, $resultingObj->Id,3);
                $this->uploadXMLR($dataService,$data, $resultingObj->Id,3);
                 
            }
	          
	        }
	        catch(Exception $e)
	        {
	            die($e->getMessage());
	        }	 
	    }
    public function checks($data) {
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
            $cantidad = $dataService->Query("select count(*) from Check");
            $cantidad = ceil($cantidad / 1000) * 1000;
            $items = array();
            for ($i = 1; $i < $cantidad; $i = $i + 1000) {
                $items = array_merge($items, $dataService->findAll("Check", $i, 1000));
            }
            // Echo some formatted output
            return json_encode($items);
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }
	public function acounts($data) {
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
            $cantidad = $dataService->Query("select count(*) from Account");
            $cantidad = ceil($cantidad / 1000) * 1000;
            $items = array();
            for ($i = 1; $i < $cantidad; $i = $i + 1000) {
                $items = array_merge($items, $dataService->findAll("Account", $i, 1000));
            }
            // Echo some formatted output
            return json_encode($items);
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }
     public function searchBillByTotal($data) {
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
            $result =  $dataService->Query("select * from Bill ");
            return json_encode($result);
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }
    public function importBills($data){
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
                    $theResourceObj =Purchase::create([
                          "DocNumber" => $data["list"][$i][0],
                          "TxnDate"=> $data["list"][$i][4], 
                          "AccountRef" => [
                             "value"=> $data["category"]
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
                                   "value"=> $data["acount"]
                                 ],
                                 "TaxCodeRef"=> [
                                      "value"=> "7"
                                     ],
                                 "TaxInclusiveAmt"=> $data["list"][$i][3]
                               ]
                             ]
                            ]
                        ]);
                    $batch->AddEntity($theResourceObj, $idbatch, "Create");
                    $idbatch ++;
                    if($contador > 29){
                        $contador = 1;
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
                            }else{
                                $r["observacion"] = $batchItemResponse_queryCustomer->getError();
                            }
                            $getResult = $batchItemResponse_queryCustomer->getResult();
                            $r["referencia"] = $getResult->DocNumber;
                            $r["descripcion"] = $getResult->Line->Description;
                            $r["monto"] = $getResult->TotalAmt;
                            array_push($result, $r);
                        }
                        $batch = $dataService->CreateNewBatch();
                        $idbatch = 1;
                    }else{
                     $contador ++;
                    }
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
                        }else{
                           $r["observacion"] = $batchItemResponse_queryCustomer->getError(); 
                        }
                        $getResult = $batchItemResponse_queryCustomer->getResult();
                            $r["referencia"] = $getResult->DocNumber;
                            $r["descripcion"] = $getResult->Line->Description;
                            $r["monto"] = $getResult->TotalAmt;
                        array_push($result, $r);
                    }
                    
            }
            return json_encode($result);
        }catch (Exception $e) {
            die($e->getMessage());
        }
    }
    public function updateBills($data) {
        $dataService = $data["dataService"];
        foreach ($data["services"] as $service) {
            if ($service->Id == $data["service"]) {
                $serviceName = $service->Name;
            }
        }
        try{   
        $line = array();
        $result = array();
        $r = array();
        $suma = 0;
        $contador = 1;
        $idbatch = 1;
        $batch = $dataService->CreateNewBatch();
        for ($i = 1; $i < sizeof($data["list"]); $i++) {
            $vendor = $this->VendorByIdCard($dataService, $data["list"][$i][1]);
            if($vendor == null || $vendor == ""){
                $r["observacion"] = "Proveedor no encontrado";
            } else {
                $bill = $this->searchBill($dataService, $vendor[0]->Id, $data["list"][$i][0]);
                if ($bill == null) {
                    $r["observacion"] = "Factura no encontrada";
                } else {
                    if ($bill[0]->Line->Amount < 1) {
                        $r["observacion"] = "Monto de la factura  = 0";
                    } else {
                        $c = 0;
                        $montoFact = 0;
                        if (sizeof($bill[0]->Line) > 1) {
                            foreach ($bill[0]->Line as $line) {
                                $lineas[$c] = array(
                                    "Description" => $line->Description,
                                    "Amount" => bcdiv($line->Amount / 1.13, 1, 2),
                                    "DetailType" => "AccountBasedExpenseLineDetail",
                                    "AccountBasedExpenseLineDetail" => [
                                        "ClassRef" => [
                                            "value" => "1500000000000031744",
                                            "name" => "Servicios"
                                        ],
                                        "AccountRef" => [
                                            "value" => $line->AccountBasedExpenseLineDetail->AccountRef
                                        ],
                                        "TaxCodeRef" => [
                                            "value" => "3"
                                        ]
                                    ]
                                );
                                $montoFact = $montoFact + Float.parseFloat(bcdiv($line->Amount, 1, 2));
                                $c = $c + 1;
                            }
                        } else {
                            $lineas[$c] = array(
                                "Description" => $bill[0]->Line->Description,
                                "Amount" => bcdiv($bill[0]->Line->Amount / 1.13, 1, 2),
                                "DetailType" => "AccountBasedExpenseLineDetail",
                                "AccountBasedExpenseLineDetail" => [
                                    "ClassRef" => [
                                        "value" => "1500000000000031744",
                                        "name" => "Servicios"
                                    ],
                                    "AccountRef" => [
                                        "value" => $data["line1"]
                                    ],
                                    "TaxCodeRef" => [
                                        "value" => "3"
                                    ]
                                ]
                            );
                            $montoFact = $montoFact + Float.parseFloat(bcdiv($bill[0]->Line->Amount, 1, 2));
                            $c = $c + 1;
                        }
                        if ($montoFact > 1) {
                            if ($montoFact > Float.parseFloat($data["list"][$i][3]) * -1) {
                                $lineas[$c] = array(
                                    "Description" => $data["list"][0][3],
                                    "Amount" => bcdiv($data["list"][$i][3], 1, 2),
                                    "DetailType" => "AccountBasedExpenseLineDetail",
                                    "AccountBasedExpenseLineDetail" => [
                                        "AccountRef" => [
                                            "value" => $data["adjustment"],
                                            "name" => $serviceName
                                        ],
                                        "TaxCodeRef" => [
                                            "value" => "7"
                                        ]
                                    ]
                                );
                                $montoFact = $montoFact + Float.parseFloat($data["list"][$i][3]);
                            } else {
                                $lineas[$c] = array(
                                    "Description" => $data["list"][0][3],
                                    "Amount" => bcdiv(($montoFact * -1), 1, 2),
                                    "DetailType" => "AccountBasedExpenseLineDetail",
                                    "AccountBasedExpenseLineDetail" => [
                                        "AccountRef" => [
                                            "value" => $data["adjustment"],
                                            "name" => $serviceName
                                        ],
                                        "TaxCodeRef" => [
                                            "value" => "7"
                                        ]
                                    ]
                                );
                                $montoFact = 0;
                            }
                            $c = $c + 1;
                        }
                        if ($montoFact > 1) {
                            if ($montoFact > Float.parseFloat($data["list"][$i][4]) * -1) {
                                $lineas[$c] = array(
                                    "Description" => $data["list"][0][4],
                                    "Amount" => bcdiv($data["list"][$i][4], 1, 2),
                                    "DetailType" => "AccountBasedExpenseLineDetail",
                                    "AccountBasedExpenseLineDetail" => [
                                        "AccountRef" => [
                                            "value" => $data["lastDept"],
                                            "name" => $serviceName
                                        ],
                                        "TaxCodeRef" => [
                                            "value" => "7"
                                        ]
                                    ]
                                );
                                $montoFact = $montoFact + Float.parseFloat($data["list"][$i][4]);
                            } else {
                                echo "monto" . $montoFact;
                                echo "de" . bcdiv(($montoFact * -1), 1, 2);
                                $lineas[$c] = array(
                                    "Description" => $data["list"][0][4],
                                    "Amount" => bcdiv(($montoFact * -1), 1, 2),
                                    "DetailType" => "AccountBasedExpenseLineDetail",
                                    "AccountBasedExpenseLineDetail" => [
                                        "AccountRef" => [
                                            "value" => $data["lastDept"],
                                            "name" => $serviceName
                                        ],
                                        "TaxCodeRef" => [
                                            "value" => "7"
                                        ]
                                    ]
                                );
                                $montoFact = 0;
                            }
                            $c = $c + 1;
                        }
                        echo '<br>moto l2 ' . $montoFact;
                        if ($montoFact > 1) {
                            echo "l3 entre";
                            if ($montoFact > ($data["list"][$i][5] * -1)) {
                                echo "<br>l3 si";
                                $lineas[$c] = array(
                                    "Description" => $data["list"][0][5],
                                    "Amount" => bcdiv($data["list"][$i][5], 1, 2),
                                    "DetailType" => "AccountBasedExpenseLineDetail",
                                    "AccountBasedExpenseLineDetail" => [
                                        "AccountRef" => [
                                            "value" => $data["bond"],
                                            "name" => $serviceName
                                        ],
                                        "TaxCodeRef" => [
                                            "value" => "7"
                                        ]
                                    ]
                                );
                                $montoFact = $montoFact + Float.parseFloat($data["list"][$i][5]);
                            } else {
                                echo "<br>l3 no";
                                $lineas[$c] = array(
                                    "Description" => $data["list"][0][5],
                                    "Amount" => bcdiv(($montoFact * -1), 1, 2),
                                    "DetailType" => "AccountBasedExpenseLineDetail",
                                    "AccountBasedExpenseLineDetail" => [
                                        "AccountRef" => [
                                            "value" => $data["bond"],
                                            "name" => $serviceName
                                        ],
                                        "TaxCodeRef" => [
                                            "value" => "7"
                                        ]
                                    ]
                                );
                                $montoFact = 0;
                            }
                            $c = $c + 1;
                        }
                        echo '<br>moto l3 ' . $montoFact;
                        if ($montoFact > 1) {
                            if ($montoFact > $data["list"][$i][6] * -1) {
                                echo "<br>l4 si";
                                $lineas[$c] = array(
                                    "Description" => $data["list"][0][6],
                                    "Amount" => bcdiv($data["list"][$i][6], 1, 2),
                                    "DetailType" => "AccountBasedExpenseLineDetail",
                                    "AccountBasedExpenseLineDetail" => [
                                        "AccountRef" => [
                                            "value" => $data["otherPay"],
                                            "name" => $serviceName
                                        ],
                                        "TaxCodeRef" => [
                                            "value" => "7"
                                        ]
                                    ]
                                );
                                $montoFact = $montoFact + Float.parseFloat($data["list"][$i][6]);
                            } else {
                                echo "<br>l4 no";
                                $lineas[$c] = array(
                                    "Description" => $data["list"][0][6],
                                    "Amount" => bcdiv(($montoFact * -1), 1, 2),
                                    "DetailType" => "AccountBasedExpenseLineDetail",
                                    "AccountBasedExpenseLineDetail" => [
                                        "AccountRef" => [
                                            "value" => $data["otherPay"],
                                            "name" => $serviceName
                                        ],
                                        "TaxCodeRef" => [
                                            "value" => "7"
                                        ]
                                    ]
                                );
                                $montoFact = 0;
                            }
                            $c = $c + 1;
                        }
                        if ($montoFact > 1) {
                            if ($montoFact > $data["list"][$i][7] * -1) {
                                echo "<br>l5 si";
                                $lineas[$c] = array(
                                    "Description" => $data["list"][0][7],
                                    "Amount" => bcdiv($data["list"][$i][7], 1, 2),
                                    "DetailType" => "AccountBasedExpenseLineDetail",
                                    "AccountBasedExpenseLineDetail" => [
                                        "AccountRef" => [
                                            "value" => $data["administrativeAdjustment"],
                                            "name" => $serviceName
                                        ],
                                        "TaxCodeRef" => [
                                            "value" => "7"
                                        ]
                                    ]
                                );
                                $montoFact = $montoFact + Float.parseFloat($data["list"][$i][7]);
                            } else {
                                echo "<br>l5 no";
                                $lineas[$c] = array(
                                    "Description" => $data["list"][0][7],
                                    "Amount" => bcdiv(($montoFact * -1), 1, 2),
                                    "DetailType" => "AccountBasedExpenseLineDetail",
                                    "AccountBasedExpenseLineDetail" => [
                                        "AccountRef" => [
                                            "value" => $data["administrativeAdjustment"],
                                            "name" => $serviceName
                                        ],
                                        "TaxCodeRef" => [
                                            "value" => "7"
                                        ]
                                    ]
                                );
                                $montoFact = 0;
                            }
                            $c = $c + 1;
                        }
                        if ($montoFact > 1) {
                            if ($montoFact > $data["list"][$i][8] * -1) {
                                echo "<br>l6 si";
                                $lineas[$c] = array(
                                    "Description" => $data["list"][0][8],
                                    "Amount" => 0, // $data["list"][$i][8]
                                    "DetailType" => "AccountBasedExpenseLineDetail",
                                    "AccountBasedExpenseLineDetail" => [
                                        "AccountRef" => [
                                            "value" => $data["otherAdjustment"],
                                            "name" => $serviceName
                                        ],
                                        "TaxCodeRef" => [
                                            "value" => "7"
                                        ]
                                    ]
                                );
                                $montoFact = $montoFact + Float.parseFloat($data["list"][$i][8]);
                            } else {
                                echo "<br>l6 no";
                                $lineas[$c] = array(
                                    "Description" => $data["list"][0][8],
                                    "Amount" => 0, // $data["list"][$i][8]
                                    "DetailType" => "AccountBasedExpenseLineDetail",
                                    "AccountBasedExpenseLineDetail" => [
                                        "AccountRef" => [
                                            "value" => $data["otherAdjustment"],
                                            "name" => $serviceName
                                        ],
                                        "TaxCodeRef" => [
                                            "value" => "7"
                                        ]
                                    ]
                                );
                                $montoFact = 0;
                            }
                            $c = $c + 1;
                        }
                        $theResourceObj = Bill::Update($bill[0], [
                            "GlobalTaxCalculation" => "TaxInclusive",
                            "Line" => $lineas,
                        ]);
                        $resultingObj = $dataService->Update($theResourceObj);
                        $error = $dataService->getLastError();
                        if ($error) {
                            $r["observacion"] = $error;
                            $r["monto"] = bcdiv($montoFact,1,2);
                        } else {
                            $r["monto"] = bcdiv($montoFact,1,2);
                            $r["observacion"] = "Proceso realizado";
                        }
                        array_push($result, $r);
                    }
                }
            }
            $contador++;
            if ($contador > 45) {
                break;
            }
        }
    } catch (Exception $e) {
        $r["observacion"] = $e->getMessage();
        array_push($result, $r);
    }
    return json_encode($result);
        }

    public function VendorByIdCard($dataService, $idCard) {
        try {
            // Run a query
            $vendor = $dataService->Query("select * from Vendor where CompanyName = '".$idCard."'");
            $error = $dataService->getLastError();
            if ($error) {
                echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
                echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
                echo "The Response message is: " . $error->getResponseBody() . "\n";
            }
            if($vendor != null){
                return $vendor;
            }else{
                return 0;
            }
            // Echo some formatted output
            
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }
    public function searchBill($dataService, $idVendor, $docNumber) {
        try {
            // Run a query
            $bill = $dataService->Query("select * from bill where vendorRef = '".$idVendor."' and DocNumber = '".$docNumber."'");
            $error = $dataService->getLastError();
            if ($error) {
                echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
                echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
                echo "The Response message is: " . $error->getResponseBody() . "\n";
            }
            if($bill != null){
                return $bill;
            }else{
                return 0;
            }
            // Echo some formatted output
            
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }
}
