<?php
/**
 * Description of InvoiceController
 *
 * @author Kevin Campos
 */
    require 'libs/Config.php';
    $config= Config::singleton();
    $config->set('controllerFolder','controller/');
    $config->set('modelFolder', 'model/');
    $config->set('viewFolder', 'view/');
    
//    $config->set('dbhost', 'localhost');
//    $config->set('dbname', 'QBO-MH');
//    $config->set('dbuser', 'QBOMH');
//    $config->set('dbpass', 'Contafast.2019');
    
    $config->set('dbhost', '127.0.0.1');
    $config->set('dbname', 'u343224615_QBOMH');
    $config->set('dbuser', 'u343224615_admin');
    $config->set('dbpass', 'C@ntafast.2020');
 

?>

