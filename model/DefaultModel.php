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
require_once('vendor/autoload.php');

use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;

class DefaultModel {

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

    public function isSession() {
        if (isset($_SESSION['username']) && isset($_SESSION['idCard']) && isset($_SESSION['roll'])) {
            return true;
        } else {
            return false;
        }
    }

    public function isSessionQB() {
        if (isset($_SESSION['authUrl']) && isset($_SESSION['sessionAccessToken'])) {
            return true;
        } else {
            return false;
        }
    }

    public function login($data) {
        try {
            $sql = "SELECT * FROM user WHERE username= '" . $data['user'] . "' and pass= '" . $data['pass'] . "'";
            $result = $this->pdo->prepare($sql);
            $result->execute();
            $result = $result->fetchAll(PDO::FETCH_ASSOC);
            if ($result) {
                $_SESSION['username'] = $result[0]['username'];
                $_SESSION['idCard'] = $result[0]['idCard'];
                $_SESSION['roll'] = $result[0]['roll'];
                header('Location: /sincronizador/');
            } else {
                header('Location: /sincronizador/?error=1');
            }
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    public function loginQB($data) {
        $config = include('public/config.php');
        $dataService = DataService::Configure(array(
                    'auth_mode' => 'oauth2',
                    'ClientID' => $config['client_id'],
                    'ClientSecret' => $config['client_secret'],
                    'RedirectURI' => $config['oauth_redirect_uri'],
                    'scope' => $config['oauth_scope'],
                    //'baseUrl' => "development"
                    'baseUrl' => "production"
        ));
        $dataService->disableLog();
        $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
        if (isset($_SESSION['authUrl']) && isset($_SERVER['QUERY_STRING'])) {
            if ($_SERVER['QUERY_STRING'] == "") {
                $this->logout();
            }
            $parseUrl = $this->parseAuthRedirectUrl($_SERVER['QUERY_STRING']);
            /*
             * Update the OAuth2Token
             */
            $accessToken = $OAuth2LoginHelper->exchangeAuthorizationCodeForToken($parseUrl['code'], $parseUrl['realmId']);
            $dataService->updateOAuth2Token($accessToken);

            $_SESSION['sessionAccessToken'] = serialize($accessToken);
            
            $sql = "SELECT * FROM cliente WHERE idcard= '" . $_SESSION['idCard'] . "' and realmId= '" . $parseUrl['realmId'] . "'";
            $result = $this->pdo->prepare($sql);
            $result->execute();
            $result = $result->fetchAll(PDO::FETCH_ASSOC);
            if ($result) {}else{
                //$sql = "INSERT INTO `cliente` (`idcard`, `realmId`, `state`) VALUES ('" . $_SESSION['idCard'] . "', '" . $parseUrl['realmId'] . "', '1');";
                   // $result = $this->pdo->prepare($sql);
                   // $result->execute();
                   //session_destroy();
                  return $result = array("status" => "error", "message" => '2');
            }
            $this->saveToken($parseUrl['realmId'], $accessToken->getRefreshToken());
            return $result = array("status" => "error", "message" => $parseUrl);
        } else {
            $authUrl = $OAuth2LoginHelper->getAuthorizationCodeURL();
            // Store the url in PHP Session Object;
            $_SESSION['authUrl'] = $authUrl;
            return $result = array("status" => "ok", "message" => $authUrl);
        }
    }

    public function dolar() {
        include 'libs/tipoCambio.php';
        $result = tipo_cambio($data = date('d/m/Y'));
        $_SESSION['purchase'] = bcdiv($result["compra"], '1', 2);
        $_SESSION['sale'] = bcdiv($result["venta"], '1', 2);
    }

    public function index() {
        try {
            $config = include('public/config.php');
            $accessToken = unserialize($_SESSION['sessionAccessToken']);
            $dataService = DataService::Configure(array(
                        'auth_mode' => 'oauth2',
                        'ClientID' => $config['client_id'],
                        'ClientSecret' => $config['client_secret'],
                        'RedirectURI' => $config['oauth_redirect_uri'],
                        'refreshTokenKey' => $accessToken->getRefreshToken(),
                        'QBORealmID' => $_SESSION['realmId'],
                        'scope' => $config['oauth_scope'],
                        //'baseUrl' => "development"
                        'baseUrl' => "production"
            ));
            $dataService->disableLog();
            $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
            $accessToken = $OAuth2LoginHelper->refreshToken();
            $dataService->updateOAuth2Token($accessToken);
            $_SESSION['sessionAccessToken'] = serialize($accessToken);
            $CompanyInfo = $dataService->getCompanyInfo();
            $error = $dataService->getLastError();
            if ($error) {
                return $result = array("status" => "error", "message" => $error->getResponseBody());
            } else {
                return $result = array("status" => "ok", "message" => json_encode($CompanyInfo));
            }
        } catch (Exception $e) {

            header('Location: /sincronizador/error408.php');
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

    public function create($data) {
        
    }

    public function deleted($data) {
        
    }

    public function search($data) {
        
    }

    public function update($data) {
        
    }

    function parseAuthRedirectUrl($url) {
        parse_str($url, $qsArray);
        return array(
            'code' => $qsArray['code'],
            'realmId' => $qsArray['realmId']
        );
    }

    //inicio de session a la app
    public function logout() {
        session_destroy();
        header('Location: /sincronizador/');
    }

    function downloadMails($username, $password, $idCard) {
       echo $username.'--'.$password;
        // Connect to gmail
        $hostname = '{imap.gmail.com:993/imap/ssl/novalidate-cert}INBOX';
        echo "conectando.... ";
        /* try to connect */
       if($username != null){
            $inbox = imap_open($hostname, $username, $password) or die('Cannot connect to Gmail: ' . imap_last_error());
        echo "conectado <br>";
        /* grab emails */
        $emails = imap_search($inbox, 'FROM ' . $username);
        $emails = imap_search($inbox, 'UNSEEN');

        /* if emails are returned, cycle through each... */
        if ($emails) {

            /* begin output var */
            $output = '';

            /* put the newest emails on top */
            rsort($emails);
            $cont = 1;
            foreach ($emails as $email_number) {//recorre emails
                echo "<br>Correo #" . $cont++ . "<br>";
                /* get information specific to this email */
                $structure = imap_fetchstructure($inbox, $email_number);
                $claveXML = "";
                $archXMLF = "";
                $archXMLM = "";
                $archPDFF = "";
                $bandera = "";

                $attachments = array();
                if (isset($structure->parts) && (count($structure->parts)-1)>0) {//if partes email
                    echo "Adjuntos: " . (count($structure->parts)-1) . "<br>";
                    for ($i = 1; $i < count($structure->parts); $i++) {// for recorre partes
                       
                        if (strcasecmp($structure->parts[$i]->subtype, "XML") === 0 || strcasecmp($structure->parts[$i]->subtype, "PDF") === 0 || strcasecmp($structure->parts[$i]->subtype, "OCTET-STREAM") === 0 || strcasecmp($structure->parts[$i]->subtype, "zip") === 0) {//if tipo de estructuras
                         echo "Adjunto: " . $i . $structure->parts[$i]->subtype."<br>";
                            $attachments[$i] = array(
                                'is_attachment' => false,
                                'filename' => '',
                                'name' => '',
                                'attachment' => '');
                            if ($structure->parts[$i]->ifdparameters) {
                                foreach ($structure->parts[$i]->dparameters as $object) {
                                    if (strtolower($object->attribute) == 'filename') {
                                        $attachments[$i]['is_attachment'] = true;
                                        $attachments[$i]['filename'] = $object->value;
                                    }
                                }
                            }

                            if ($structure->parts[$i]->ifparameters) {
                                foreach ($structure->parts[$i]->parameters as $object) {
                                    if (strtolower($object->attribute) == 'name') {
                                        $attachments[$i]['is_attachment'] = true;
                                        $attachments[$i]['filename'] = $object->value;
                                    }
                                }
                            }
                          
                            if ($attachments[$i]['is_attachment']) {// if adjunto
                                $attachments[$i]['attachment'] = imap_fetchbody($inbox, $email_number, $i+1);
                                if ($structure->parts[$i]->encoding == 3) { // 3 = BASE64
                                  $attachments [$i] ['attachment'] = base64_decode ( $attachments [$i] ['attachment'] );
                                }//fin encode 3 
                                else { // 4 = QUOTED-PRINTABLE
                                  $attachments [$i] ['attachment'] = quoted_printable_decode ( $attachments [$i] ['attachment'] );
                                }// fin encode 4
                            }//fin if adjunto
                    }
                     if (strcasecmp($structure->parts[$i]->subtype, "MIXED") === 0 ) {//if tipo de estructuras
                         echo "Adjunto: " . $i . $structure->parts[$i]->subtype."<br>";
                          for ($j = 1; $j < count($structure->parts[$i]->parts); $j++) {// for recorre partes
                            
                            $attachments2[$j] = array(
                                'is_attachment' => false,
                                'filename' => '',
                                'name' => '',
                                'attachment' => '');
                            if ($structure->parts[$i]->parts[$j]->ifdparameters) {
                                foreach ($structure->parts[$i]->parts[$j]->dparameters as $object) {
                                    if (strtolower($object->attribute) == 'filename') {
                                        $attachments2[$j]['is_attachment'] = true;
                                        $attachments2[$j]['filename'] = $object->value;
                                    }
                                }
                            }

                            if ($structure->parts[$i]->parts[$j]->ifparameters) {
                                foreach ($structure->parts[$i]->parts[$j]->parameters as $object) {
                                    if (strtolower($object->attribute) == 'name') {
                                        $attachments2[$j]['is_attachment'] = true;
                                        $attachments2[$j]['filename'] = $object->value;
                                    }
                                }
                            }
                            if ($attachments2[$j]['is_attachment']) {// if adjunto
                                $attachments2[$j]['attachment'] = imap_fetchbody($inbox, $email_number, $i+1);
                                if ($structure->parts[$i]->parts[$j]->encoding == 3) { // 3 = BASE64
                                  $attachments2 [$j] ['attachment'] = base64_decode ( $attachments2 [$j] ['attachment'] );
                                }//fin encode 3 
                                else { // 4 = QUOTED-PRINTABLE
                                  $attachments2 [$j] ['attachment'] = quoted_printable_decode ( $attachments2 [$j] ['attachment'] );
                                }// fin encode 4
                            }//fin if adjunto
                          }
                     }
                    }//fin for partes
                    
                    
                    for ($h = 1; $h <= count($attachments); $h++) {
                        if(count($attachments) > 1 && (strpos(strtolower($attachments[1]['filename']), ".pdf") )){
                            $temp = $attachments[1];
                            $attachments[1] = $attachments[2];
                            $attachments[2] = $temp;
                        }
                        if ($attachments[$h] ['is_attachment']) {
                             
                            if((strpos(strtolower($attachments[$h]['filename']), ".xml") )){
                                libxml_use_internal_errors(true);
                                $attachments[$i]['attachment'] = str_replace("o;?", "", $attachments[$i]['attachment']);
                                $attachments[$i]['attachment'] = str_replace("C", "O", $attachments[$i]['attachment']);
                                $attachments[$i]['attachment'] = str_replace("E", "O", $attachments[$i]['attachment']);
                                $attachments[$i]['attachment'] = str_replace("o?=", "O", $attachments[$i]['attachment']);
                                if ($xml = simplexml_load_string($attachments[$h]['attachment'])) {
                                    $claveXML = $xml->Clave;
                                    if (strrpos($attachments[$h]['attachment'], 'mensajeHacienda') || strrpos($attachments[$h]['attachment'], 'MensajeHacienda')) {
                                        $carpeta = 'files/' . $idCard . '/Recibidos/Sinprocesar/' . $xml->Clave;
                                        if (!file_exists($carpeta)) {
                                            mkdir($carpeta, 0777, true);
                                        }
                                        $nombre_fichero = $carpeta . '/' . $xml->Clave . '-R.xml';
                                        $xml->asXML($nombre_fichero);
                                        echo "Nombre archivo : ".$nombreFichero =$attachments[$h] ['filename']."<br>";
                                        echo "Guardado xml respuesta <br>";
                                    }
                                    if (strrpos($attachments[$h]['attachment'], 'facturaElectronica') || strrpos($attachments[$h]['attachment'], 'FacturaElectronica') || strrpos($attachments[$h]['attachment'], 'tiqueteElectronico') || strrpos($attachments[$h]['attachment'], 'TiqueteElectronico') ||
                                            strrpos($attachments[$h]['attachment'], 'NotaCreditoElectronica') || strrpos($attachments[$h]['attachment'], 'notaCreditoElectronica')) {
                                        $claveXML = $xml->Clave;
                                        $carpeta = 'files/' . $idCard . '/Recibidos/Sinprocesar/' . $xml->Clave;
                                        if (!file_exists($carpeta)) {
                                            mkdir($carpeta, 0777, true);
                                        }
                                        $nombre_fichero = $carpeta . '/' . $xml->Clave . '.xml';
                                        $xml->asXML($nombre_fichero);
                                        echo "Nombre archivo : ".$nombreFichero =$attachments[$h] ['filename']."<br>";
                                        echo "Guardado xml factura <br>";
                                    }
                                } else {
                                    echo "Nombre archivo : ".$nombreFichero =$attachments[$h] ['filename']."<br>";
                                    echo "error al abrir <br>";
                                }
                            }
                            if((strpos(strtolower($attachments[$h]['filename']), ".pdf")) && $claveXML != "" ){
                              
                                $carpeta = 'files/' . $idCard . '/Recibidos/Sinprocesar/' . $claveXML;
                                    if (!file_exists($carpeta)) {
                                        mkdir($carpeta, 0777, true);
                                    }
                                    $nombre_fichero = $carpeta . '/' . $claveXML . '.pdf';
                                    if (file_put_contents($nombre_fichero, $attachments[$h]['attachment'])) {
                                        echo "Nombre archivo : ".$nombreFichero =$attachments[$h] ['filename']."<br>";
                                        echo "Guardado  pdf de factura <br>";
                                    }
                                
                            }
                            
                            if ((strpos(strtolower($attachments[$h]['filename']), ".zip"))) {
                               
                                echo "ZIP ".str_replace(array("ATV_eFAC_", ".zip"), "", $attachments[$h]['filename'])." <br>";
                                $zip = new ZipArchive;
                                $carpeta = 'files/' . $idCard . '/Recibidos/Sinprocesar/' . str_replace(array("ATV_eFAC_", ".zip"), "", $attachments[$h]['filename']);
                                if (!file_exists($carpeta)) {
                                    mkdir($carpeta, 0777, true);
                                }
                                $nombre_fichero = $carpeta . '/' . str_replace(array("ATV_eFAC_", ".zip"), "", $attachments[$h]['filename']) . '.zip';
                                file_put_contents($nombre_fichero, $attachments[$h]['attachment']);
                                if ($zip->open($nombre_fichero) === TRUE) {
                                    $zip->extractTo($carpeta . '/');
                                    $zip->close();
                                    rename($carpeta . "/" . str_replace(".zip", "", $attachments[$h]['filename']) . ".xml", $carpeta . "/" . str_replace(array("ATV_eFAC_", ".zip"), "", $attachments[$h]['filename']) . ".xml");
                                    rename($carpeta . "/ATV_eFAC_Respuesta_" . str_replace(array("ATV_eFAC_", ".zip"), "", $attachments[$h]['filename']) . ".xml", $carpeta . "/" . str_replace(array("ATV_eFAC_", ".zip"), "", $attachments[$h]['filename']) . "-R.xml");
                                    rename($carpeta . "/" . str_replace(".zip", "", $attachments[$h]['filename']) . ".pdf", $carpeta . "/" . str_replace(array("ATV_eFAC_", ".zip"), "", $attachments[$h]['filename']) . ".pdf");
                                    echo 'OK';
                                } else {
                                    echo 'failed';
                                }
                            }//fin if pdf
                            
                           
                        } // Fin de es adjunto
                    }
                   
                   for ($m = 1; $m <= count($attachments2); $m++) {
                      
                        if(count($attachments2) > 1 && (strpos(strtolower($attachments2[1]['filename']), ".pdf") )){
                          
                            $temp = $attachments2[1];
                            $attachments2[1] = $attachments2[2];
                            $attachments2[2] = $temp;
                        }
                        if ($attachments2[$m] ['is_attachment']) {
                             
                            if((strpos(strtolower($attachments2[$m]['filename']), ".xml") )){
                                libxml_use_internal_errors(true);
                                echo $attachments2[$m]['attachment']= base64_decode ( $attachments2 [$m] ['attachment'] );
                                if ($xml = simplexml_load_string($attachments2[$m]['attachment'])) {
                                    
                                    $claveXML = $xml->Clave;
                                    if (strrpos($attachments2[$m]['attachment'], 'mensajeHacienda') || strrpos($attachments2[$m]['attachment'], 'MensajeHacienda')) {
                                        $carpeta = 'files/' . $idCard . '/Recibidos/Sinprocesar/' . $xml->Clave;
                                        if (!file_exists($carpeta)) {
                                            mkdir($carpeta, 0777, true);
                                        }
                                        $nombre_fichero = $carpeta . '/' . $xml->Clave . '-R.xml';
                                        $xml->asXML($nombre_fichero);
                                        echo "Nombre archivo : ".$nombreFichero =$attachments2[$m] ['filename']."<br>";
                                        echo "Guardado xml respuesta <br>";
                                    }
                                    if (strrpos($attachments2[$m]['attachment'], 'facturaElectronica') || strrpos($attachments2[$m]['attachment'], 'FacturaElectronica') || strrpos($attachments2[$m]['attachment'], 'tiqueteElectronico') || strrpos($attachments2[$m]['attachment'], 'TiqueteElectronico') ||
                                            strrpos($attachments2[$m]['attachment'], 'NotaCreditoElectronica') || strrpos($attachments2[$m]['attachment'], 'notaCreditoElectronica')) {
                                        $claveXML = $xml->Clave;
                                        $carpeta = 'files/' . $idCard . '/Recibidos/Sinprocesar/' . $xml->Clave;
                                        if (!file_exists($carpeta)) {
                                            mkdir($carpeta, 0777, true);
                                        }
                                        $nombre_fichero = $carpeta . '/' . $xml->Clave . '.xml';
                                        $xml->asXML($nombre_fichero);
                                        echo "Nombre archivo : ".$nombreFichero =$attachments2[$m] ['filename']."<br>";
                                        echo "Guardado xml factura <br>";
                                    }
                                } else {
                                    echo "Nombre archivo : ".$nombreFichero =$attachments2[$m] ['filename']."<br>";
                                    echo "error al abrir <br>";
                                }
                            }
                            if((strpos(strtolower($attachments2[$m]['filename']), ".pdf")) && $claveXML != "" ){
                              
                                $carpeta = 'files/' . $idCard . '/Recibidos/Sinprocesar/' . $claveXML;
                                    if (!file_exists($carpeta)) {
                                        mkdir($carpeta, 0777, true);
                                    }
                                    $nombre_fichero = $carpeta . '/' . $claveXML . '.pdf';
                                    if (file_put_contents($nombre_fichero, $attachments2[$m]['attachment'])) {
                                        echo "Nombre archivo : ".$nombreFichero =$attachments2[$m] ['filename']."<br>";
                                        echo "Guardado  pdf de factura <br>";
                                    }
                                
                            }
                        } // Fin de es adjunto
                    }
                    $claveXML = "";
                }//fin if partes email
            }//fin recorre emails
            // echo $output;
        }

        /* close the connection */
        imap_close($inbox);
       }
    }
    function downloadMails2($username, $password, $idCard) {
        // Connect to gmail
        $hostname = '{imap.gmail.com:993/imap/ssl}INBOX';
        echo "conectando.... ";
        /* try to connect */
      
            $inbox = imap_open($hostname, $username, $password) or die('Cannot connect to Gmail: ' . imap_last_error());
        echo "conectado <br>";
        /* grab emails */
        $emails = imap_search($inbox, 'FROM ' . $username);
        $emails = imap_search($inbox, 'UNSEEN');

        /* if emails are returned, cycle through each... */
        if ($emails) {

            /* begin output var */
            $output = '';

            /* put the newest emails on top */
            rsort($emails);
            $cont = 1;
            foreach ($emails as $email_number) {//recorre emails
                echo "<br>Correo #" . $cont++ . "<br>";
                /* get information specific to this email */
                $overview = imap_fetch_overview($inbox, $email_number, 0);
                $message = imap_fetchbody($inbox, $email_number, 2);
                $structure = imap_fetchstructure($inbox, $email_number);
                $claveXML = "";
                $archXMLF = "";
                $archXMLM = "";
                $archPDFF = "";
                $bandera = "";

                $attachments = array();
                if (isset($structure->parts) && count($structure->parts)) {//if partes email
                    echo "Adjuntos: " . count($structure->parts) . "<br>";
                  
                    for ($i = 0; $i < count($structure->parts); $i++) {// for recorre partes
                        echo "Adjunto: " . $i . $structure->parts[$i]->subtype."<br>";
                         
                        if (strcmp($structure->parts[$i]->subtype, "XML") === 0 || strcmp($structure->parts[$i]->subtype, "OCTET-STREAM") === 0 ) {//if tipo de estructuras
                       
                            $attachments[$i] = array(
                                'is_attachment' => false,
                                'filename' => '',
                                'name' => '',
                                'attachment' => '');

                            if ($structure->parts[$i]->ifdparameters) {
                                foreach ($structure->parts[$i]->dparameters as $object) {
                                    if (strtolower($object->attribute) == 'filename') {
                                        $attachments[$i]['is_attachment'] = true;
                                        $attachments[$i]['filename'] = $object->value;
                                    }
                                }
                            }

                            if ($structure->parts[$i]->ifparameters) {
                                foreach ($structure->parts[$i]->parameters as $object) {
                                    if (strtolower($object->attribute) == 'name') {
                                        $attachments[$i]['is_attachment'] = true;
                                        $attachments[$i]['name'] = $object->value;
                                    }
                                }
                            }
                          
                            if ($attachments[$i]['is_attachment']) {// if adjunto
                                $attachments[$i]['attachment'] = imap_fetchbody($inbox, $email_number, $i+1);
                                if ($structure->parts[$i]->encoding == 3) { // 3 = BASE64
                                    if (strpos($attachments[$i]['filename'], "zip") || strpos($attachments[$i]['filename'], "ZIP") || strpos($attachments[$i]['name'], "ZIP") || strpos($attachments[$i]['name'], "zip") || strpos($attachments[$i]['name'], "pdf") || strpos($attachments[$i]['name'], "PDF") || strpos($attachments[$i]['filename'], "pdf") || strpos($attachments[$i]['filename'], "PDF")) {
                                        if (base64_decode($attachments[$i]['attachment'], true)) {
                                            $attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
                                        } else {
                                            $attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
                                        }
                                    }
                                }//fin encode 3 
                                else { // 4 = QUOTED-PRINTABLE
                                    if (strpos($attachments[$i]['filename'], "zip") || strpos($attachments[$i]['filename'], "ZIP") || strpos($attachments[$i]['name'], "ZIP") || strpos($attachments[$i]['name'], "zip") || strpos($attachments[$i]['name'], "pdf") || strpos($attachments[$i]['name'], "PDF") || strpos($attachments[$i]['filename'], "pdf") || strpos($attachments[$i]['filename'], "PDF")) {
                                        if (base64_decode($attachments[$i]['attachment'], true)) {
                                            $attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
                                        } else {
                                            $attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
                                        }
                                    }
                                }// fin encode 4
                            }//fin if adjunto
                            if (strpos($attachments[$i]['name'], "xml") || strpos($attachments[$i]['name'], "XML") || strpos($attachments[$i]['filename'], "xml") || strpos($attachments[$i]['filename'], "XML")) {
                                if (base64_decode($attachments[$i]['attachment'], true)) {
                                    $attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
                                } else {
                                    $attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
                                }
                                libxml_use_internal_errors(true);
                                $attachments[$i]['attachment'] = str_replace("o;?", "", $attachments[$i]['attachment']);
                                $attachments[$i]['attachment'] = str_replace("C", "O", $attachments[$i]['attachment']);
                                $attachments[$i]['attachment'] = str_replace("E", "O", $attachments[$i]['attachment']);
                                $attachments[$i]['attachment'] = str_replace("o?=", "O", $attachments[$i]['attachment']);
                                if ($xml = simplexml_load_string($attachments[$i]['attachment'])) {
                                    $claveXML = $xml->Clave;
                                    if (strrpos($attachments[$i]['attachment'], 'mensajeHacienda') || strrpos($attachments[$i]['attachment'], 'MensajeHacienda')) {
                                        echo "mensaje " . $xml->Clave;
                                        $carpeta = 'files/' . $idCard . '/Recibidos/Sinprocesar/' . $xml->Clave;
                                        if (!file_exists($carpeta)) {
                                            mkdir($carpeta, 0777, true);
                                        }
                                        $nombre_fichero = $carpeta . '/' . $xml->Clave . '-R.xml';
                                        $xml->asXML($nombre_fichero);
                                        echo "Guardado <br>";
                                    }
                                    if (strrpos($attachments[$i]['attachment'], 'facturaElectronica') || strrpos($attachments[$i]['attachment'], 'FacturaElectronica') || strrpos($attachments[$i]['attachment'], 'tiqueteElectronico') || strrpos($attachments[$i]['attachment'], 'TiqueteElectronico') ||
                                            strrpos($attachments[$i]['attachment'], 'NotaCreditoElectronica') || strrpos($attachments[$i]['attachment'], 'notaCreditoElectronica')) {
                                        $claveXML = $xml->Clave;
                                        echo "factura " . $xml->Clave;
                                        $carpeta = 'files/' . $idCard . '/Recibidos/Sinprocesar/' . $xml->Clave;
                                        if (!file_exists($carpeta)) {
                                            mkdir($carpeta, 0777, true);
                                        }
                                        $nombre_fichero = $carpeta . '/' . $xml->Clave . '.xml';
                                        $xml->asXML($nombre_fichero);
                                        echo "Guardado <br>";
                                    }
                                } else {
                                  echo utf8_decode($attachments[$i]['attachment']);
                                    echo "error al abrir <br>";
                                }
                            }//fin if xml
                            
                     
                            if (strpos($attachments[$i]['name'], "zip") || strpos($attachments[$i]['name'], "ZIP") || strpos($attachments[$i]['filename'], "zip") || strpos($attachments[$i]['filename'], "ZIP")) {
                                echo "ZIP ".str_replace(array("ATV_eFAC_", ".zip"), "", $attachments[$i]['name'])." <br>";
                                $zip = new ZipArchive;
                                $carpeta = 'files/' . $idCard . '/Recibidos/Sinprocesar/' . str_replace(array("ATV_eFAC_", ".zip"), "", $attachments[$i]['name']);
                                if (!file_exists($carpeta)) {
                                    mkdir($carpeta, 0777, true);
                                }
                                $nombre_fichero = $carpeta . '/' . str_replace(array("ATV_eFAC_", ".zip"), "", $attachments[$i]['name']) . '.zip';
                                file_put_contents($nombre_fichero, $attachments[$i]['attachment']);
                                if ($zip->open($nombre_fichero) === TRUE) {
                                    $zip->extractTo($carpeta . '/');
                                    $zip->close();
                                    rename($carpeta . "/" . str_replace(".zip", "", $attachments[$i]['name']) . ".xml", $carpeta . "/" . str_replace(array("ATV_eFAC_", ".zip"), "", $attachments[$i]['name']) . ".xml");
                                    rename($carpeta . "/ATV_eFAC_Respuesta_" . str_replace(array("ATV_eFAC_", ".zip"), "", $attachments[$i]['name']) . ".xml", $carpeta . "/" . str_replace(array("ATV_eFAC_", ".zip"), "", $attachments[$i]['name']) . "-R.xml");
                                    rename($carpeta . "/" . str_replace(".zip", "", $attachments[$i]['name']) . ".pdf", $carpeta . "/" . str_replace(array("ATV_eFAC_", ".zip"), "", $attachments[$i]['name']) . ".pdf");
                                    echo 'OK';
                                } else {
                                    echo 'failed';
                                }
                            }//fin if pdf
                        }// fin if tipo de archivo
                        if (strcmp($structure->parts[$i]->subtype, "MIXED") === 0 ) {//if tipo de estructuras
                        
                            echo "es mixed";
                        }
                    }//fin for partes
                    
                    
                    for ($j = 0; $j < count($structure->parts); $j++) {// for recorre partes2
                        echo "Adjunto: " . $j . $structure->parts[$j]->subtype."<br>";
                         
                        if (strcmp($structure->parts[$j]->subtype, "PDF") === 0 || strcmp($structure->parts[$j]->subtype, "OCTET-STREAM") === 0 ) {//if tipo de estructuras
                       
                            $attachments[$j] = array(
                                'is_attachment' => false,
                                'filename' => '',
                                'name' => '',
                                'attachment' => '');

                            if ($structure->parts[$j]->ifdparameters) {
                                foreach ($structure->parts[$j]->dparameters as $object) {
                                    if (strtolower($object->attribute) == 'filename') {
                                        $attachments[$j]['is_attachment'] = true;
                                        $attachments[$j]['filename'] = $object->value;
                                    }
                                }
                            }

                            if ($structure->parts[$j]->ifparameters) {
                                foreach ($structure->parts[$j]->parameters as $object) {
                                    if (strtolower($object->attribute) == 'name') {
                                        $attachments[$j]['is_attachment'] = true;
                                        $attachments[$i]['name'] = $object->value;
                                    }
                                }
                            }
                          
                            if ($attachments[$j]['is_attachment']) {// if adjunto
                                $attachments[$j]['attachment'] = imap_fetchbody($inbox, $email_number, $j+1);
                                if ($structure->parts[$j]->encoding == 3) { // 3 = BASE64
                                    if (strpos($attachments[$j]['filename'], "zip") || strpos($attachments[$j]['filename'], "ZIP") || strpos($attachments[$j]['name'], "ZIP") || strpos($attachments[$j]['name'], "zip") || strpos($attachments[$j]['name'], "pdf") || strpos($attachments[$j]['name'], "PDF") || strpos($attachments[$j]['filename'], "pdf") || strpos($attachments[$j]['filename'], "PDF")) {
                                        if (base64_decode($attachments[$j]['attachment'], true)) {
                                            $attachments[$j]['attachment'] = base64_decode($attachments[$j]['attachment']);
                                        } else {
                                            $attachments[$j]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
                                        }
                                    }
                                }//fin encode 3 
                                else { // 4 = QUOTED-PRINTABLE
                                    if (strpos($attachments[$j]['filename'], "zip") || strpos($attachments[$j]['filename'], "ZIP") || strpos($attachments[$j]['name'], "ZIP") || strpos($attachments[$j]['name'], "zip") || strpos($attachments[$j]['name'], "pdf") || strpos($attachments[$j]['name'], "PDF") || strpos($attachments[$j]['filename'], "pdf") || strpos($attachments[$j]['filename'], "PDF")) {
                                        if (base64_decode($attachments[$j]['attachment'], true)) {
                                            $attachments[$j]['attachment'] = base64_decode($attachments[$j]['attachment']);
                                        } else {
                                            $attachments[$j]['attachment'] = quoted_printable_decode($attachments[$j]['attachment']);
                                        }
                                    }
                                }// fin encode 4
                            }//fin if adjunto
                           
                            echo json_encode ($attachments[$j]);
                            if (strpos($attachments[$j]['name'], "") ===0 ) {
                                if ($claveXML != '') {
                                    echo "PDF " . $claveXML;
                                    $carpeta = 'files/' . $idCard . '/Recibidos/Sinprocesar/' . $claveXML;
                                    if (!file_exists($carpeta)) {
                                        mkdir($carpeta, 0777, true);
                                    }
                                    $nombre_fichero = $carpeta . '/' . $claveXML . '.pdf';
                                    if (file_put_contents($nombre_fichero, $attachments[$j]['attachment'])) {
                                        echo "Guardado <br>";
                                    }
                                    //$xml->asXML($nombre_fichero);
                                }
                            }//fin if pdf
                            
                        }// fin if tipo de archivo
                    }//fin for partes
                    $claveXML = "";
                }//fin if partes email
            }//fin recorre emails
            // echo $output;
        }

        /* close the connection */
        imap_close($inbox);
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
       function getCondicionesVenta(){
           $sql = "SELECT * FROM condicionventa";
            $result = $this->pdo->prepare($sql);
            $result->execute();
            $result = $result->fetchAll(PDO::FETCH_ASSOC);
            if ($result) {
                return $result;
            }else{
                return false;
            }
       }
       function getMonedas(){
           $sql = "SELECT * FROM moneda";
            $result = $this->pdo->prepare($sql);
            $result->execute();
            $result = $result->fetchAll(PDO::FETCH_ASSOC);
            if ($result) {
                return $result;
            }else{
                return false;
            }
       }
       function getMediosPago(){
           $sql = "SELECT * FROM mediopago";
            $result = $this->pdo->prepare($sql);
            $result->execute();
            $result = $result->fetchAll(PDO::FETCH_ASSOC);
            if ($result) {
                return $result;
            }else{
                return false;
            }
       }
       function getReferencias(){
           $sql = "SELECT * FROM referencia";
            $result = $this->pdo->prepare($sql);
            $result->execute();
            $result = $result->fetchAll(PDO::FETCH_ASSOC);
            if ($result) {
                return $result;
            }else{
                return false;
            }
       }
       function getTiposDocumento(){
           $sql = "SELECT * FROM tipodocumento";
            $result = $this->pdo->prepare($sql);
            $result->execute();
            $result = $result->fetchAll(PDO::FETCH_ASSOC);
            if ($result) {
                return $result;
            }else{
                return false;
            }
       }
       function getTiposIdentificacion(){
           $sql = "SELECT * FROM tipoidentificacion";
            $result = $this->pdo->prepare($sql);
            $result->execute();
            $result = $result->fetchAll(PDO::FETCH_ASSOC);
            if ($result) {
                return $result;
            }else{
                return false;
            }
       }
       function getUnidadesMedida(){
           $sql = "SELECT * FROM unidad_medida";
            $result = $this->pdo->prepare($sql);
            $result->execute();
            $result = $result->fetchAll(PDO::FETCH_ASSOC);
            if ($result) {
                return $result;
            }else{
                return false;
            }
       }

}
