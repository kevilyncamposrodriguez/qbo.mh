<?php 
set_time_limit(0);
ini_set('max_execution_time', 300); //300 seconds = 5 minutes
$session_id = session_id();
if (empty($session_id)) {
    session_start();
}
/**
 * Description of InvoiceController
 *
 * @author Kevin Campos
 */
require 'libs/SPDO.php';
require 'vendor/autoload.php';
require 'model/IModel.php';
require 'controller/IController.php';
require 'libs/FrontController.php';
require 'libs/hacienda/Firmador.php';
FrontController::main();
