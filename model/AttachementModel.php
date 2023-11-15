<?php
ini_set( 'max_execution_time' , 5000 );
/**
 * Description of InvoiceController
 *
 * @author Kevin Campos
 */

require 'libs/thread/thread.php';
require_once('vendor/autoload.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Core\ServiceContext;
use QuickBooksOnline\API\PlatformService\PlatformService;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;
use QuickBooksOnline\API\Data\IPPReferenceType;
use QuickBooksOnline\API\Data\IPPAttachableRef;
use QuickBooksOnline\API\Data\IPPAttachable;

class AttachementModel {

    //put your code here
    public $pdo, $pdo2;

    public function __CONSTRUCT() {
        try {
            $this->pdo = SPDO::singleton();
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }
    
    public function all($dataService) {
        $sql = "SELECT cont FROM cont WHERE id= '2' LIMIT 1";
        $i = $this->pdo->prepare($sql);
        $i->execute();
        $i = $i->fetchAll(PDO::FETCH_ASSOC);
        $i = $i[0]["cont"];
        $invoices = array();
        $c = 1;
        echo "antes";
        $invoicess =  $dataService->Query("select * from invoice", $i, 2);
        echo json_encode($invoicess);
            foreach($invoicess as $invoice){
                $curl = curl_init();
                $attachemments = $dataService->Query("select Id from attachable where AttachableRef.EntityRef.Type = 'CreditMemo' and AttachableRef.EntityRef.value = '".$invoice->Id."'");
                foreach($attachemments as $attachemment){
                    $att =  $dataService->Query("select * from Attachable where Id = '".$attachemment->Id."'");
                    if($invoice->CustomerMemo != ""){
                        $clave = str_replace('Clave: ','',$invoice->CustomerMemo);
                        $typeD = substr ($clave,29,2);
                        $typeDoc = "No identificado";
                        if($typeD == "01"){
                            $typeDoc = "Notas de credito";
                        }
                        $url = 'RespaldoGlovo/'.$typeDoc.'/'.$clave;
                        if (!file_exists($url)) {
                           mkdir($url, 0755, true);
                        }
                        $file = file_get_contents($att[0]->TempDownloadUri);
                        if(file_put_contents($url."/".$att[0]->FileName, $file)){
                            echo "Archivo ".$att[0]->FileName." guardado <br>";
                        }else{
                            echo "No se ha guardado el archivo ".$att[0]->FileName." <br>";
                        }
                    }
                }
                $numero = ($i+$c++);
                $sql = "UPDATE cont SET cont = '".$numero."' where id = '2'";
                $cont = $this->pdo->prepare($sql);
                $cont->execute();
                echo "<br>cantidad = ".$numero. "<br>";
	            echo "<br><br>";
            }
        
        echo "<br>Cantidad Facturas tramitadas: " . $i+$c . "<br><br>";
    }
     public function allP($dataService) {
        $sql = "SELECT cont FROM cont WHERE id= '2' LIMIT 1";
        $i = $this->pdo->prepare($sql);
        $i->execute();
        $i = $i->fetchAll(PDO::FETCH_ASSOC);
        $i = $i[0]["cont"];
        $invoices = array();
        $c = 0;
        while($invoicess =  $dataService->Query("select * from CreditMemo", $i, 20)){
            foreach($invoicess as $invoice){
                $curl = curl_init();
                $attachemments = $dataService->Query("select Id from attachable where AttachableRef.EntityRef.Type = 'CreditMemo' and AttachableRef.EntityRef.value = '".$invoice->Id."'");
                foreach($attachemments as $attachemment){
                    $att =  $dataService->Query("select * from Attachable where Id = '".$attachemment->Id."'");
                    if($invoice->CustomerMemo != ""){
                        $clave = str_replace('Clave: ','',$invoice->CustomerMemo);
                        $typeD = substr ($clave,29,2);
                        $typeDoc = "No identificado";
                        if($typeD == "01"){
                            $typeDoc = "Facturas";
                        }
                        $url = 'RespaldoGlovo/'.$typeDoc.'/'.$clave;
                        if (!file_exists($url)) {
                           mkdir($url, 0755, true);
                        }
                        $file = file_get_contents($att[0]->TempDownloadUri);
                        if(file_put_contents($url."/".$att[0]->FileName, $file)){
                            echo "Archivo ".$att[0]->FileName." guardado <br>";
                        }else{
                            echo "No se ha guardado el archivo ".$att[0]->FileName." <br>";
                        }
                    }
                }
	            echo "<br><br>";
            }
            break;
        }
        $conta = ($i+20);
        $sql = "UPDATE cont SET cont = '".$conta."' where id = '2'";
        $cont = $this->pdo->prepare($sql);
        $cont->execute();
        echo "<br>Cantidad Notas de credito tramitadas: " . $conta . "<br><br>";
    }
}
