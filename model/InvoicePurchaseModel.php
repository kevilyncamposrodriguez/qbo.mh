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
use Hacienda\Firmador;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;
use QuickBooksOnline\API\Facades\Bill;
use QuickBooksOnline\API\Data\IPPReferenceType;
use QuickBooksOnline\API\Data\IPPAttachableRef;
use QuickBooksOnline\API\Data\IPPAttachable;
use QuickBooksOnline\API\Facades\Vendor;
use QuickBooksOnline\API\Facades\Account;
use QuickBooksOnline\API\Facades\VendorCredit;

class InvoicePurchaseModel {

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
    function getFC(){
           $sql = "SELECT * FROM facturacompra where cedulaR = '".$_SESSION["username"]."' ORDER BY fechaemision DESC";
            $result = $this->pdo->prepare($sql);
            $result->execute();
            $result = $result->fetchAll(PDO::FETCH_ASSOC);
            if ($result) {
                return $result;
            }else{
                return false;
            }
    }
    public function aumentaConsecutivo($data)
    {
        try
        {   
            $sql = "UPDATE consecutivos SET fc= fc+1 where idCard = '".$data["idCard"]."'";
            $user = $this->pdo->prepare($sql);
            $user->execute();
          return 1; 
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    } 
    public function proccess($dataService, $data) {
        $xmlFirmado = $this->createXMLFC($data);
        $data["factura"] = simplexml_load_string(trim($xmlFirmado));
        
        //Acceso al token de hacienda
        $token = $this->tokenMH($data["client"][0]);
        $token = json_decode($token["message"]);
        $token = $token->access_token;
       
        $envio = $this->enviar($xmlFirmado, $token);
         
        if($envio["status"] == "200" || $envio["status"] == "202" || $envio["status"] == "400"){
            $data['estado']= "procesando";
            if($envio["status"] != "400"){
                $this->aumentaConsecutivo($data);
                $this->saveBD($xmlFirmado,$data);
                $carpeta=  'files/'. $data["idCard"].'/Recibidos/FacturaCompra/'. $data["clave"];
                if (!file_exists($carpeta)) {
                    mkdir($carpeta, 0777, true);
                }
                $path= $carpeta . '/' . $data["clave"] . '-F.xml';
                $data["factura"]->asXML($path);
                $this->createPDF($data);
                
            }
            
                $consulta = $this->consultar($data["clave"], $token);
                
                if($consulta != false){
                    
                    $data['estado']=$consulta["ind-estado"];
                    $this->updateFC($data);
                    
                    $xml = simplexml_load_string(trim($consulta["message"]));
                    $carpeta=  'files/'. $data["idCard"].'/Recibidos/FacturaCompra/' . $data["clave"];
                    if (!file_exists($carpeta)) {
                    mkdir($carpeta, 0777, true);
                    }
                    
                    $path= $carpeta . '/' . $data["clave"] . '-R.xml';
                    $xml->asXML($path);
                    
                    if($data['estado'] == "aceptado"){
                        
                        $this->createBill($dataService, $data);
                    }
                    echo "Documento enviado con exito, estado = ".$data['estado'];
                }else{
                    echo "Documento enviado con exito, error al consultar estado";
                }
        }else{echo "Error al enviar, intente de nuevo...";}
    }
     public function actualizarEstado($dataService, $data) {
        $carpeta=  'files/'. $data["idCard"].'/Recibidos/FacturaCompra/' . $data["clave"];
        $data["factura"] = simplexml_load_file($carpeta. '/' . $data["clave"] . '-F.xml');
        //Acceso al token de hacienda
        $token = $this->tokenMH($data["client"][0]);
        $token = json_decode($token["message"]);
        $token = $token->access_token;
      
        $consulta = $this->consultar($data["clave"], $token);
        if($consulta != false){
            $data['estado']=$consulta["ind-estado"];
            $this->updateFC($data);
            if($consulta["message"]!= ""){
                $xml = simplexml_load_string(trim($consulta["message"]));
                if (!file_exists($carpeta)) {
                    mkdir($carpeta, 0777, true);
                }
                $path= $carpeta . '/' . $data["clave"] . '-R.xml';
                $xml->asXML($path);
            }
            if($data['estado'] == "aceptado"){
                $this->createBill($dataService, $data);
            }
            return true;
        }else{
            return false;
        }
        
    }
    public function updateFC($data) {
        $sql = "UPDATE facturacompra SET estadoMH = '" . $data["estado"] . "' where cedulaR = '" . $_SESSION["username"] . "' and clave ='".$data["clave"]."'";
        $user = $this->pdo->prepare($sql);
        $user->execute();
    }
    public function createBill($dataService, $data) {
        try {
            
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
            foreach ($data['factura']->DetalleServicio->LineaDetalle as $l) {
                $lineData = array(
                    "Id" => (string) $l->NumeroLinea,
                    "Description" => (string) $l->Detalle,
                    "Amount" => (string) $l->MontoTotalLinea,
                    "DetailType" => "AccountBasedExpenseLineDetail",
                    "AccountBasedExpenseLineDetail" => $AccountBasedExpenseLineDetail
                );
                array_push($line, $lineData);
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
    	            }else{
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
            $bill = array(
                "TxnDate" => substr($data["fReferencia"], 0, 10),
                "CurrencyRef" => $CurrencyRef,
                "PrivateNote" => 'Consecutivo confirmacion = ' . $data["consecutivo"],
                "Line" => $line,
                "VendorRef" => $vendor,
                "DocNumber" => ''.$data['factura']->NumeroConsecutivo
            );
            $theResourceObj = Bill::create($bill);
            $resultingObj = $dataService->Add($theResourceObj);
            $error = $dataService->getLastError();
            $resultingObj->Id;
            if ($error) {
                $this->updateRespuestaQBO('error al guardar', $data['factura']->Clave);
            } else {
                $xmlBody = XmlObjectSerializer::getPostXmlFromArbitraryEntity($resultingObj, $urlResource);
                $this->uploadXML($dataService,$data, $resultingObj->Id,1); //funciona bien
                $this->uploadXMLR($dataService,$data, $resultingObj->Id,1);
                $this->uploadPDF($dataService,$data, $resultingObj->Id,1);
                 
            }
        } catch (Exception $e) {
            echo ($e->getMessage());
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
    function saveBD($xml,$data) {
        $xml = simplexml_load_string(trim($xml));
         try
        {
           $sql = "INSERT INTO `facturacompra`
            ( `clave`, `fechaemision`,`fechareferencia`, `cedulaE`, `cedulaR`, `nombreE`, `moneda`, `impuestototal`, `exoneraciontotal`, `descuentototal`, `total`, `numeroreferencia`, `estadoMH`)VALUES
            ('".$xml->Clave."','".$xml->FechaEmision."','".$data["fReferencia"]."','".$xml->Emisor->Identificacion->Numero."','".$data["idCard"]."','".$xml->Emisor->Nombre."','".$xml->ResumenFactura->CodigoTipoMoneda->CodigoMoneda."','".$xml->ResumenFactura->TotalImpuesto."','','".$xml->ResumenFactura->TotalDescuentos."','".$xml->ResumenFactura->TotalComprobante."','".$data["nRefDoc"]."','".$data["estado"]."')";  
            $result = $this->pdo->prepare($sql);
            $result->execute();
            $cret = $result->rowCount();
            if($cret>0){ 
               return true;
           }else{
               return false;
           }
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    }
    function consultar($clave, $token) {

	    $curl = curl_init();
	    //Validamos que venga el parametro de la clave
	
	    if ($clave == "" && strlen($clave) == 0) {
	        return "El valor codigoPais no debe ser vacio";
	    }
	    
	    $url = "https://api.comprobanteselectronicos.go.cr/recepcion/v1/recepcion/";
	
	
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
	        return false;
	    } else {
	        $xml = json_decode($response, true);
	        $indEstado = $xml["ind-estado"];
	        $xml = $xml["respuesta-xml"];
	        $xml = base64_decode($xml);
	        $result = array("status" => $status, "message" => $xml, "ind-estado" => $indEstado);
	      
	    }
	     return $result;
	}
    public function createXMLFC($data){
        //echo json_encode($data["company"]->Email->Address); return;
        $phoneR = str_replace("+506", "", $data["company"]->PrimaryPhone->FreeFormNumber);
        $phoneR = str_replace("+", "", $phoneR);
        $phoneR = str_replace(" ", "", $phoneR);
        $idEmisor = str_replace("-", "", $data["company"]->EmployerId);
        $idE = (string) $idEmisor;
        if (strpos($idE, 'R') !== false) {
            $tipoEmisor = '03';
            $idE = str_replace("R", "", $idE);
        } else if (strpos($idE, 'N') !== false) {
           $tipoEmisor = '04';
            $idR = str_replace("N", "", $idR);
        } else {
            if (strlen($idEmisor) < 10) {
                $tipoEmisor = '01';
                $idEmisor = "000" . $idEmisor;
            } else {
                $tipoEmisor = '02';
                $idEmisor = "00" . $idEmisor;
            }
        }
        $uEmisor = $this->ubicacion($data["company"]->Country, $data["company"]->LegalAddr->CountrySubDivisionCode, $data["company"]->LegalAddr->City, $data["company"]->LegalAddr->Line1);
        $uEmisor = json_decode($uEmisor, true);
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
         date_default_timezone_set('America/Costa_Rica');
         $date = date(DATE_RFC3339);
       
       try{
         
          $xmlString = '<?xml version="1.0" encoding="utf-8"?><FacturaElectronicaCompra xmlns="https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.3/facturaElectronicaCompra" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
          ';
        $xmlString .= '<Clave>'.$data["clave"].'</Clave>
        <CodigoActividad>'.str_pad($data["company"]->CompanyAddr->PostalCode, 6, "0", STR_PAD_LEFT).'</CodigoActividad>
        <NumeroConsecutivo>'.$data["consecutivo"].'</NumeroConsecutivo>
        <FechaEmision>'.$date.'</FechaEmision>
        <Emisor>
            <Nombre>'.$data["nombreEmisor"].'</Nombre>
            <Identificacion>
                <Tipo>'.$data["tipoIdentificacion"].'</Tipo>
                <Numero>'.$data["identificacion"].'</Numero>
            </Identificacion>
            <NombreComercial>'.$data["nombreEmisor"].'</NombreComercial>
            <Ubicacion>
            	<Provincia>' . $provinciaE . '</Provincia>
            	<Canton>' . $cantonE . '</Canton>
            	<Distrito>' . $distritoE . '</Distrito>
            	<OtrasSenas>' . $data["company"]->CompanyAddr->Line1 . '</OtrasSenas>
            </Ubicacion>
            <Telefono>
                <CodigoPais>506</CodigoPais>
                <NumTelefono>'.$data["telefonoEmisor"].'</NumTelefono>
            </Telefono>
            <CorreoElectronico>'.$data["emailE"].'</CorreoElectronico>
        </Emisor>
        ';
         
   
        
        $xmlString .= '<Receptor>
            <Nombre>'.$data["company"]->CompanyName.'</Nombre>
            <Identificacion>
                <Tipo>'.$tipoEmisor.'</Tipo>
                <Numero>'.$data["company"]->EmployerId.'</Numero>
            </Identificacion>
            <NombreComercial>'.$data["company"]->CompanyName.'</NombreComercial>
            <Ubicacion>
            	<Provincia>' . $provinciaE . '</Provincia>
            	<Canton>' . $cantonE . '</Canton>
            	<Distrito>' . $distritoE . '</Distrito>
            	<OtrasSenas>' . $data["company"]->CompanyAddr->Line1 . '</OtrasSenas>
            </Ubicacion>
            <Telefono>
                <CodigoPais>506</CodigoPais>
                <NumTelefono>'.$phoneR.substr(-8,8).'</NumTelefono>
            </Telefono>
            <CorreoElectronico>'.$data["company"]->Email->Address.'</CorreoElectronico>
        </Receptor>
        ';
      
        
        $plazo=1;
        if(isset($data["plazoCredito"])){
            $plazo = $data["plazoCredito"];
        }else{
            $plazo=1;
        }
        if($data["plazoCredito"]=="02"){
             $xmlString .= '<CondicionVenta>0'.$data["condicionVenta"].'</CondicionVenta>
        <PlazoCredito>'. $plazo.'</PlazoCredito>
        <MedioPago>'.$data["formaPago"].'</MedioPago>
        <DetalleServicio>
        ';
        }else{
             $xmlString .= '<CondicionVenta>'.$data["condicionVenta"].'</CondicionVenta>
             <PlazoCredito>'. $plazo.'</PlazoCredito>
        <MedioPago>'.$data["formaPago"].'</MedioPago>
        <DetalleServicio>
        ';
        }
       
        $mg=0;
        $sg=0;
        $me=0;
        $se=0;
        $td=0;
        $ti=0;
        foreach ($data['detalle'] as $valor){
            $td=$td+$valor[7];
            $ti=$ti+$valor[6];
            if($valor[3]=="Sp" || $valor[3]=="Spe" || $valor[3]=="Os"){
                if($valor[6]!=0){
                    $sg=$sg+($valor[4]*$valor[5]); 
                }else{
                    $se=$se+($valor[4]*$valor[5]);
                }
            }else{
                if($valor[6]!=0){
                    $mg=$mg+($valor[4]*$valor[5]);
                }else{
                    $me=$me+($valor[4]*$valor[5]);
                }
            }
            $xmlString .= 
            '   <LineaDetalle>
                <NumeroLinea>'.$valor[0].'</NumeroLinea>
                <Codigo>'.$valor[1].'</Codigo>
                <Cantidad>'.$valor[4].'</Cantidad>
                <UnidadMedida>'.$valor[3].'</UnidadMedida>
                <Detalle>'.$valor[2].'</Detalle>
                <PrecioUnitario>'.bcdiv($valor[5],1,5).'</PrecioUnitario>
                <MontoTotal>'.bcdiv($valor[4]*$valor[5],1,5).'</MontoTotal>'
                ;
                if($valor[7]!=0){
                   $xmlString .= '<Descuento>
                    <MontoDescuento>'.$valor[7].'</MontoDescuento>
                    <NaturalezaDescuento>Ninguna</NaturalezaDescuento>
                </Descuento>
                '; 
                }
                $xmlString .= '<SubTotal>'.bcdiv(($valor[4]*$valor[5])-$valor[7],1,5).'</SubTotal>';
                 
                if($valor[6]!=0){
                    $t = $valor[6]/$valor[5]*100;
                    $codet = '08';
                    switch ($t) {
                        case 1:
                            $codet = '02';
                            break;
                        case 2:
                            $codet = '03';
                            break;
                        case 4:
                            $codet = '04';
                            break;
                        case 13:
                            $codet = '08';
                            break;
                        default:
                            $codet = '08';
                            break;
                    }
                $xmlString .= '<Impuesto>
                    <Codigo>01</Codigo>
                    <CodigoTarifa>'.$codet.'</CodigoTarifa>
                    <Tarifa>'.$t.'</Tarifa>
                    <Monto>'.bcdiv(($valor[6]),1,5).'</Monto>
                </Impuesto>
                ';
                }
                $xmlString .= '<MontoTotalLinea>'.bcdiv($valor[8],1,5).'</MontoTotalLinea>
            </LineaDetalle>
        ';
        }
        $xmlString .=
        '</DetalleServicio>
        <ResumenFactura>
            <CodigoTipoMoneda>
                <CodigoMoneda>'.$data["moneda"].'</CodigoMoneda>
                <TipoCambio>'.$data["tCambio"].'</TipoCambio>
            </CodigoTipoMoneda>
            <TotalServGravados>'.bcdiv($sg,1,5).'</TotalServGravados>
            <TotalServExentos>'.bcdiv($se,1,5).'</TotalServExentos>
            <TotalMercanciasGravadas>'.bcdiv($mg,1,5).'</TotalMercanciasGravadas>
            <TotalMercanciasExentas>'.bcdiv($me,1,5).'</TotalMercanciasExentas>
            <TotalGravado>'.bcdiv($sg+$mg,1,5).'</TotalGravado>
            <TotalExento>'.bcdiv($se+$me,1,5).'</TotalExento>
            <TotalVenta>'.bcdiv($se+$sg+$me+$mg,1,5).'</TotalVenta>
            <TotalDescuentos>'.$td.'</TotalDescuentos>
            <TotalVentaNeta>'.bcdiv($se+$sg+$me+$mg-$td,1,5).'</TotalVentaNeta>
            <TotalImpuesto>'.bcdiv( $ti,1,5).'</TotalImpuesto>
            <TotalComprobante>'.bcdiv($se+$sg+$me+$mg-$td+$ti,1,5).'</TotalComprobante>
            </ResumenFactura>
            ';
            
            if($data["nRefDoc"]!=""){
            $xmlString .='<InformacionReferencia>
                <TipoDoc>'.$data["typeDocRef"].'</TipoDoc>
                <Numero>'.$data["nRefDoc"].'</Numero>
                <FechaEmision>'.$data["fReferencia"].'T00:00:00-06:00</FechaEmision>
                <Codigo>'.$data["codRef"].'</Codigo>
                <Razon>'.$data["razon"].'</Razon>
            </InformacionReferencia>
            ';
            }
            $xmlString .='</FacturaElectronicaCompra>
            ';
       
         //Datos necesarios para la firma del xml
        $p12Url =  "P12s/".$data["idCard"]."/".$data["client"][0]["urlP12"];
        $pinP12 = $data["client"][0]["passP12"]; 
       
        
        //firmar Documento
        $firmador = new Firmador();
        $xml_string = $firmador->firmarXml($p12Url, $pinP12, trim($xmlString), $firmador::TO_XML_STRING);
        if($xml_string!= false){
            return $xml_string;
        }else{
            return false;
        }
        }
        catch(Exception $e)
        {
        die($e->getMessage());
        }
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
    public function consecutive($data) {
        try {
           
            $sql = "SELECT * FROM consecutivos WHERE idCard= '" . $data["idCard"] . "'";
            $result = $this->pdo->prepare($sql);
            $result->execute();
            $result = $result->fetchAll(PDO::FETCH_ASSOC);
                $consecutivo = $result[0]["fc"];
                while (strlen($consecutivo) < 10) {
                $consecutivo = '0' . $consecutivo;
                }
                $consecutivo = "0010000108" . $consecutivo;
             
            return $consecutivo;
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }


      function enviar($xmlFirmado, $token) {
        $xml = simplexml_load_string(trim($xmlFirmado));
        $xml64= base64_encode($xmlFirmado);
        date_default_timezone_set('America/Costa_Rica');
        $date = date(DATE_RFC3339);
        $idE = $xml->Emisor->Identificacion->Numero;
        for($i=strlen ($xml->Emisor->Identificacion->Numero);$i<12;$i++){
            $idE = "0".$idE;
        }
        $idR = $xml->Receptor->Identificacion->Numero;
        for($j=strlen($idR);$j<12;$j++){
            $idR = "0".$idR;
        }
         
	    $datos = array(
	        'clave' => "".$xml->Clave,
	        'fecha' => $date,
	        'emisor' => array(
	            'tipoIdentificacion' => (string)$xml->Emisor->Identificacion->Tipo,
	            'numeroIdentificacion' => (string)$idE
	        ),
	        'receptor' => array(
	            'tipoIdentificacion' => (string)$xml->Receptor->Identificacion->Tipo,
	            'numeroIdentificacion' =>  (string)$idR
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
        		$randId = $data["clave"];
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
        		$resultObj = $dataService->Upload($xmlBase64[$sendMimeType],
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
	      $path = "files/".$_SESSION['idCard'].'/Recibidos/FacturaCompra/'.$data["clave"].'/'.$data["clave"].'-R.xml';	 
	      if(file_exists($path)){   
	        try
	        {
	            // Prepare entities for attachment upload		
        		$sendMimeType = "application/vnd.openxmlformats-officedocument.wordprocessingml.document";	
		
		// Create a new IPPAttachable
		$randId = $data["clave"];
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
		$resultObj = $dataService->Upload(file_get_contents($path),
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
	      $path = "files/".$_SESSION['idCard'].'/Recibidos/FacturaCompra/'.$data["key"].'/'.$data["key"].'.pdf';
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
    		$resultObj = $dataService->Upload(file_get_contents($path),
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
	    public function createPDF($data) {
       
        $xml = $data["factura"];
        
        $type = substr($xml->NumeroConsecutivo,8,2);
       
        $mpdf = new \Mpdf\Mpdf();
        $mpdf -> WriteHTML(file_get_contents("public/css/pdf/style.css"),1);
        $nombredoc='FACTURA COMPRA';
       
         $mpdf -> SetTitle($nombredoc);
        $html='
	    <!DOCTYPE html>
        <html lang="en">
          <head>
            <meta charset="utf-8">
            <title>'.$xml->Clave.'</title>
            <link rel="stylesheet" href="style.css" media="all" />
          </head>
          
          <body>
        
            <header class="clearfix">
              <div id="logo">
                <img src="public/img/logo.png" width="200" height="100" alt="invoice icon">
              </div>
              <div id="company">
                <h2 class="name">'.$xml->Emisor->Nombre.'</h2>
                <div>'.$xml->Emisor->Identificacion->Numero.'</div>
                <div>('.$xml->Emisor->Telefono->CodigoPais.')'.$xml->Emisor->Telefono->NumTelefono.'</div>
                <div><a >'.$xml->Emisor->CorreoElectronico.'</a></div>
                <div>Codigo Actividad '.$xml->CodigoActividad.'</div>
              </div>
              </div>
            </header>
            <br>
          <br>
            <main>
              <div id="details" class="clearfix">
              <div id="invoice">
                  <h1>'.$nombredoc.'</h1>
                  <div class="date">Clave: '.$xml->Clave.'</div>
                  <div class="date">Consecutivo: '.$xml->NumeroConsecutivo.'</div>
                  <div class="date">Fecha: '.$xml->FechaEmision.'</div>
                  <div class="date">Condicion Venta: '.$data["condicionVenta"].'</div>
                  <div class="date">Plazo Credito: '.$data["plazoCredito"].'</div>
                  <div class="date">Medio Pago: '.$data["formaPago"].' </div>
                  <div class="date">Codigo Moneda:'.$data["moneda"].' </div>
                  <div class="date">Tipo Cambio: '.$xml->ResumenFactura->CodigoTipoMoneda->TipoCambio.'</div>
                  <div class="date">Doc.Referencia: '.$xml->InformacionReferencia->Numero.'</div>
                </div>
                <div id="client">
                  <h2 class="name">DIRIGIDA A:</h2>
                  <div class="to">'.$xml->Receptor->NombreComercial.'</div>          
                  <div>'.$xml->Receptor->Identificacion->Numero.'</div>
                <div>('.$xml->Receptor->Telefono->CodigoPais.')'.$xml->Receptor->Telefono->NumTelefono.'</div>
                <div><a >'.$xml->Receptor->CorreoElectronico.'</a></div>
                </div>
                
              </div>
              <br>
              <br>
              <table border="0" cellspacing="0" cellpadding="0">
                <thead>
                  <tr>
                    <th class="no">#</th>
                    <th class="unit">DETALLE</th>
                    <th class="qty">CANTIDAD</th>
                    <th class="unit">UNIDAD</th>
                    <th class="qty">PRECIO U.</th>
                    <th class="unit">IMPUESTO</th>
                    <th class="qty">DECUENTO</th>
                    <th class="total">TOTAL</th>
                  </tr>
                </thead>
                <tbody>';
                if(isset($linea->Impuesto)){
                    $impuesto = $linea->Impuesto->Monto;
                }else{
                    $impuesto = 0;
                }
                if(isset($linea->Descuento)){
                    $descuento = $linea->Descuento->MontoDescuento;
                }else{
                    $descuento = 0;
                }
                $str ="";
                foreach ($xml->DetalleServicio->LineaDetalle as $linea){ 
                  $str .='<tr>
                    <td class="no">'.$linea->NumeroLinea.'</td>
                    <td class="unit">'.$linea->Detalle.'</td>
                    <td class="qty">'.$linea->Cantidad.'</td>
                    <td class="unit">'.$linea->UnidadMedida.'</td>
                    <td class="qty">'.$linea->PrecioUnitario.'</td>
                    <td class="unit">'.$impuesto.'</td>
                    <td class="qty">'.$descuento.'</td>
                    <td class="total">'.$linea->MontoTotalLinea.'</td>
                  </tr>';
                }
                $html .= $str;
                $html .='</tbody>
                <tfoot>
                  <tr>
                    <td colspan="4"></td>
                    <td colspan="3">SUBTOTAL</td>
                    <td>'.$data["moneda"].' '.$xml->ResumenFactura->TotalVenta.'</td>
                  </tr>
                  <tr>
                    <td colspan="4"></td>
                    <td colspan="3">TOTAL IMPUESTOS</td>
                    <td>'.$data["moneda"].' '.$xml->ResumenFactura->TotalImpuesto.'</td>
                  </tr>
                  <tr>
                    <td colspan="4"></td>
                    <td colspan="3">TOTAL DESCUENTOS</td>
                    <td>'.$data["moneda"].' '.$xml->ResumenFactura->TotalDescuentos.'</td>
                  </tr>
                  <tr>
                    <td colspan="4"></td>
                    <td colspan="3">TOTAL</td>
                    <td>'.$data["moneda"].' '.$xml->ResumenFactura->TotalComprobante.'</td>
                  </tr>
                </tfoot>
              </table>
            </main>
            <footer>
              "Autorizada mediante resolución N° DGT-R-033-2019 del 20-06-2019 "
            </footer>
          </body>
        </html>';
        $mpdf ->writeHTML($html);
        $mpdf -> Output('files/'.$xml->Receptor->Identificacion->Numero.'/Recibidos/FacturaCompra/'.$xml->Clave.'/'.$xml->Clave.'.pdf', 'F');
        
        //I=Muestra al cliente D= descarga F= guarda en disco
       
        
    }
	
}
