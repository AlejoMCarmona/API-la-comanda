<?php

require_once './db/AccesoDatos.php';

class Empleado {
    public $id;
    public $nombre;
    public $apellido;
    public $dni;
    public $puesto;
    public $sector;
    public $fechaAlta;

    public function __construct() {
        $parametros = func_get_args();
        $numero_parametros = func_num_args();
        $funcion_constructor = '__construct' . $numero_parametros;
        if (method_exists($this, $funcion_constructor)) {
            call_user_func_array(array($this, $funcion_constructor), $parametros);
        }
    }

    public function __construct7($id, $nombre, $apellido, $dni, $puesto, $sector, $fechaAlta) {
        $this -> id = $id;
        $this -> nombre = $nombre;
        $this -> apellido = $apellido;
        $this -> dni = $dni;
        $this -> puesto = $puesto;
        $this -> sector = $sector;
        $this -> fechaAlta = $fechaAlta;
    }

    public function __construct5($nombre, $apellido, $dni, $puesto, $sector) {
        $this -> __construct7(0, $nombre, $apellido, $dni, $puesto, $sector,"");
    }

    public function CrearEmpleado() {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::ObtenerInstancia();
        $consulta = $objetoAccesoDatos -> PrepararConsulta("INSERT INTO Empleados (nombre, apellido, dni, puesto, sector) VALUES (:nombre, :apellido, :dni, :puesto, :sector)");
        $consulta -> bindParam(':nombre', $this -> nombre);
        $consulta -> bindParam(':apellido', $this -> apellido);
        $consulta -> bindParam(':dni', $this -> dni);
        $consulta -> bindParam(':puesto', $this -> puesto);
        $consulta -> bindParam(':sector', $this -> sector);

        $resultado = $consulta -> execute();
        if ($resultado) {
            $retorno = $objetoAccesoDatos -> ObtenerUltimoId();
        }
        return $retorno;
    }

    public static function ObtenerTodosLosEmpleados() {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::ObtenerInstancia();
        $consulta = $objetoAccesoDatos -> PrepararConsulta("SELECT * FROM Empleados");
        $resultado = $consulta -> execute();
        if ($resultado) {
            $retorno = $consulta -> fetchAll(PDO::FETCH_CLASS, 'Empleado');
        }
        return $retorno;
    }
}

?>