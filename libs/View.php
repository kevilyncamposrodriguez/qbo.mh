<?php

/**
 * Description of View
 *
 * @author Kevin
 * @author Alberth Calderon Alvarado <albert.calderon@ucr.ac.cr>
 */
class View {
    public function __construct() {
        ;
    } // constructor
    
    public function show($nombreVista, $vars=array()){
       $config= Config::singleton();
       $path=$config->get('viewFolder').$nombreVista;
       if(is_file($path)==FALSE){
           trigger_error('Pagina '.$path. ' No existe', E_USER_NOTICE);
           return FALSE;
       } // if
       if(is_array($vars)){
           foreach($vars as $key=>$value){
               $key=$value;
           } // for
       } // if(is_array($vars))
       
       include $path;
    } // show
} // fin clase

?>
