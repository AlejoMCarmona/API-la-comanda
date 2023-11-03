<?php

require_once './db/AccesoDatos.php';

class Usuario {
    public $id;
    public $nombre;
    public $apellido;
    public $dni;
    public $email;
    public $clave;
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

    public function __construct9($id, $nombre, $apellido, $dni, $email, $clave, $puesto, $sector, $fechaAlta) {
        $this -> id = $id;
        $this -> nombre = $nombre;
        $this -> apellido = $apellido;
        $this -> dni = $dni;
        $this -> email = $email;
        $this -> clave = $clave;
        $this -> puesto = $puesto;
        $this -> sector = $sector;
        $this -> fechaAlta = $fechaAlta;
    }

    public function __construct7($nombre, $apellido, $dni, $email, $clave, $puesto, $sector) {
        $this -> __construct9(0, $nombre, $apellido, $dni, $email, $clave, $puesto, $sector, "");
    }

    public function CrearUsuario() {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::ObtenerInstancia();
        $consulta = $objetoAccesoDatos -> PrepararConsulta("INSERT INTO usuarios (nombre, apellido, dni, email, clave, puesto, sector) VALUES (:nombre, :apellido, :dni, :email, :clave, :puesto, :sector)");
        $consulta -> bindParam(':nombre', $this -> nombre);
        $consulta -> bindParam(':apellido', $this -> apellido);
        $consulta -> bindParam(':dni', $this -> dni);
        $consulta -> bindParam(':email', $this -> email);
        $claveHash = password_hash($this -> clave, PASSWORD_DEFAULT);
        $consulta -> bindParam(':clave', $claveHash);
        $consulta -> bindParam(':puesto', $this -> puesto);
        $consulta -> bindParam(':sector', $this -> sector);

        $resultado = $consulta -> execute();
        if ($resultado) {
            $retorno = $objetoAccesoDatos -> ObtenerUltimoId();
        }
        return $retorno;
    }

    public static function ObtenerTodosLosUsuarios() {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::ObtenerInstancia();
        $consulta = $objetoAccesoDatos -> PrepararConsulta("SELECT * FROM usuarios");
        $resultado = $consulta -> execute();
        if ($resultado) {
            $retorno = $consulta -> fetchAll(PDO::FETCH_CLASS, 'Usuario');
        }
        return $retorno;
    }

    public static function ObtenerUsuario($id) {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::ObtenerInstancia();
        $consulta = $objetoAccesoDatos -> PrepararConsulta("SELECT * FROM usuarios WHERE id = :id");
        $consulta -> bindParam(':id', $id);
        $resultado = $consulta -> execute();
        if ($resultado) {
            $retorno = $consulta -> fetchObject('Usuario');
        }
        return $retorno;
    }
}

?>