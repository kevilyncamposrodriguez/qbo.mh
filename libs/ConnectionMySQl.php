<?php

$session_id = session_id();
if (empty($session_id)) {
    session_start();
}
require 'libs/config.php';

class ConnectionMySQl {

    private $conexion;
    private $total_consultas;

    //put your code here
    public function __construct() {
        require 'libs/SPDO.php';
        $this->conexion = SPDO::singleton();
    }

    public function consulta($consulta) {
        $this->total_consultas++;
        $resultado = mysql_query($consulta, $this->conexion);
        if (!$resultado) {
            echo 'MySQL Error: ' . mysql_error();
            exit;
        }

        return $resultado;
    }

    public function fetch_array($consulta) {
        return mysql_fetch_array($consulta);
    }

    public function num_rows($consulta) {
        return mysql_num_rows($consulta);
    }

    public function getTotalConsultas() {
        return $this->total_consultas;
    }

}

?>  