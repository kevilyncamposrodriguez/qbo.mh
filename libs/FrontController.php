<?php
/**
 * Description of View
 *
 * @author Kevin
 * @author Alberth Calderon Alvarado <albert.calderon@ucr.ac.cr>
 */
    class FrontController{
        
        static function main(){
            require 'libs/View.php';
            require 'libs/configuration.php';
            
            if(!empty($_GET['controller']))
                $controllerName=$_GET['controller'].'Controller';
            else 
                $controllerName='DefaultController';          
            
            if(!empty($_GET['action']))
                $actionName=$_GET['action'];
            else 
                 $actionName='index';
            
            $controllerRoute=$config->get('controllerFolder').$controllerName.'.php';
            
            if(is_file($controllerRoute))
                require $controllerRoute;
            else {
                die ('Controlador no encontrado - 404 not found');
                $this->view->show("error404.php", null);
            }
            if(is_callable(array($controllerName, $actionName))==FALSE){
                trigger_error($controllerName.'->'.$actionName.' no existe', E_USER_NOTICE);
                return FALSE;
            }
            $controller=new $controllerName();
            $controller->$actionName();
        } // main  
        static function syncGMAIL(){
            require 'libs/View.php';
            require 'libs/configuration.php';
            
            if(!empty($_GET['controller']))
                $controllerName=$_GET['controller'].'Controller';
            else 
                $controllerName='DefaultController';          
            
            if(!empty($_GET['action']))
                $actionName=$_GET['action'];
            else 
                 $actionName='syncGMAIL';
            
            $controllerRoute=$config->get('controllerFolder').$controllerName.'.php';
            
            if(is_file($controllerRoute))
                require $controllerRoute;
            else {
                die ('Controlador no encontrado - 404 not found');
                $this->view->show("error404.php", null);
            }
            if(is_callable(array($controllerName, $actionName))==FALSE){
                trigger_error($controllerName.'->'.$actionName.' no existe', E_USER_NOTICE);
                return FALSE;
            }
            $controller=new $controllerName();
            $controller->$actionName();
        } // main  
        static function syncFE(){
            require 'libs/View.php';
            require 'libs/configuration.php';
            
            if(!empty($_GET['controlador']))
                $controllerName=$_GET['controlador'].'Controller';
            else 
                $controllerName='InvoiceController';          
            
            if(!empty($_GET['accion']))
                $nombreAccion=$_GET['accion'];
            else 
                 $nombreAccion='syncFE';
            
            $rutaControlador=$config->get('controllerFolder').$controllerName.'.php';
            
            if(is_file($rutaControlador))
                require $rutaControlador;
            else 
                die ('Controlador no encontrado - 404 not found');
            
            if(is_callable(array($controllerName, $nombreAccion))==FALSE){
                trigger_error($controllerName.'->'.$nombreAccion.' no existe', E_USER_NOTICE);
                return FALSE;
            }
            $controller=new $controllerName();
            $controller->$nombreAccion();
        } // main

        static function syncTE(){
            require 'libs/View.php';
            require 'libs/configuration.php';
            
            if(!empty($_GET['controlador']))
                $controllerName=$_GET['controlador'].'Controller';
            else 
                $controllerName='InvoiceController';          
            
            if(!empty($_GET['accion']))
                $nombreAccion=$_GET['accion'];
            else 
                 $nombreAccion='syncTE';
            
            $rutaControlador=$config->get('controllerFolder').$controllerName.'.php';
            
            if(is_file($rutaControlador))
                require $rutaControlador;
            else 
                die ('Controlador no encontrado - 404 not found');
            
            if(is_callable(array($controllerName, $nombreAccion))==FALSE){
                trigger_error($controllerName.'->'.$nombreAccion.' no existe', E_USER_NOTICE);
                return FALSE;
            }
            $controller=new $controllerName();
            $controller->$nombreAccion();
        } // main
         static function syncNC(){
            require 'libs/View.php';
            require 'libs/configuration.php';
            
            if(!empty($_GET['controlador']))
                $controllerName=$_GET['controlador'].'Controller';
            else 
                $controllerName='CreditNoteController';          
            
            if(!empty($_GET['accion']))
                $nombreAccion=$_GET['accion'];
            else 
                 $nombreAccion='syncNC';
            
            $rutaControlador=$config->get('controllerFolder').$controllerName.'.php';
            
            if(is_file($rutaControlador))
                require $rutaControlador;
            else 
                die ('Controlador no encontrado - 404 not found');
            
            if(is_callable(array($controllerName, $nombreAccion))==FALSE){
                trigger_error($controllerName.'->'.$nombreAccion.' no existe', E_USER_NOTICE);
                return FALSE;
            }
            $controller=new $controllerName();
            $controller->$nombreAccion();
        } // main
        static function backup(){
            require 'libs/View.php';
            require 'libs/configuration.php';
            
            if(!empty($_GET['controlador']))
                $controllerName=$_GET['controlador'].'Controller';
            else 
                $controllerName='AttachementController';          
            
            if(!empty($_GET['accion']))
                $nombreAccion=$_GET['accion'];
            else 
                 $nombreAccion='all';
            
            $rutaControlador=$config->get('controllerFolder').$controllerName.'.php';
            
            if(is_file($rutaControlador))
                require $rutaControlador;
            else 
                die ('Controlador no encontrado - 404 not found');
            
            if(is_callable(array($controllerName, $nombreAccion))==FALSE){
                trigger_error($controllerName.'->'.$nombreAccion.' no existe', E_USER_NOTICE);
                return FALSE;
            }
            $controller=new $controllerName();
            $controller->$nombreAccion();
        } // main
        static function backupP(){
            require 'libs/View.php';
            require 'libs/configuration.php';
            
            if(!empty($_GET['controlador']))
                $controllerName=$_GET['controlador'].'Controller';
            else 
                $controllerName='AttachementController';          
            
            if(!empty($_GET['accion']))
                $nombreAccion=$_GET['accion'];
            else 
                 $nombreAccion='allP';
            
            $rutaControlador=$config->get('controllerFolder').$controllerName.'.php';
            
            if(is_file($rutaControlador))
                require $rutaControlador;
            else 
                die ('Controlador no encontrado - 404 not found');
            
            if(is_callable(array($controllerName, $nombreAccion))==FALSE){
                trigger_error($controllerName.'->'.$nombreAccion.' no existe', E_USER_NOTICE);
                return FALSE;
            }
            $controller=new $controllerName();
            $controller->$nombreAccion();
        } // main
    }   
