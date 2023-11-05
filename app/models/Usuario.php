<?php

require_once './db/AccesoDatos.php';

class Usuario {
    public $id;
    public $nombre;
    public $apellido;
    public $dni;
    public $email;
    public $clave;
    public $estado;
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

    public function __construct10($id, $nombre, $apellido, $dni, $email, $clave, $estado, $puesto, $sector, $fechaAlta) {
        $this -> id = $id;
        $this -> nombre = $nombre;
        $this -> apellido = $apellido;
        $this -> dni = $dni;
        $this -> email = $email;
        $this -> clave = $clave;
        $this -> estado = $estado;
        $this -> puesto = $puesto;
        $this -> sector = $sector;
        $this -> fechaAlta = $fechaAlta;
    }

    public function __construct7($nombre, $apellido, $dni, $email, $clave, $puesto, $sector) {
        $this -> __construct10(0, $nombre, $apellido, $dni, $email, $clave, "", $puesto, $sector, "");
    }

    public function __construct6($nombre, $apellido, $dni, $email, $clave, $puesto) {
        $this -> __construct7($nombre, $apellido, $dni, $email, $clave, $puesto, "");
    }

    public function CrearUsuario() {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::ObtenerInstancia();
        $query = "";
        if ($this -> sector == "") {
            $query = "INSERT INTO usuarios (nombre, apellido, dni, email, clave, puesto) VALUES (:nombre, :apellido, :dni, :email, :clave, :puesto)";
        } else {
            $query = "INSERT INTO usuarios (nombre, apellido, dni, email, clave, puesto, sector) VALUES (:nombre, :apellido, :dni, :email, :clave, :puesto, :sector)";
        }
        $consulta = $objetoAccesoDatos -> PrepararConsulta($query);
        $consulta -> bindParam(':nombre', $this -> nombre);
        $consulta -> bindParam(':apellido', $this -> apellido);
        $consulta -> bindParam(':dni', $this -> dni);
        $consulta -> bindParam(':email', $this -> email);
        $claveHash = password_hash($this -> clave, PASSWORD_DEFAULT);
        $consulta -> bindParam(':clave', $claveHash);
        $consulta -> bindParam(':puesto', $this -> puesto);
        if ($this -> sector != "") $consulta -> bindParam(':sector', $this -> sector);

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
        if ($resultado && $consulta -> rowCount() > 0) {
            $retorno = $consulta -> fetchObject('Usuario');
        }
        return $retorno;
    }

    public static function ObtenerUsuariosPorPuesto($puesto) {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::ObtenerInstancia();
        $consulta = $objetoAccesoDatos -> PrepararConsulta("SELECT * FROM usuarios WHERE puesto = :puesto");
        $consulta -> bindParam(':puesto', $puesto);
        $resultado = $consulta -> execute();
        if ($resultado && $consulta -> rowCount() > 0) {
            $retorno = $consulta -> fetchAll(PDO::FETCH_CLASS, 'Usuario');
        }
        return $retorno;
    }
    
    public static function IniciarSesion($email, $clave) {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::ObtenerInstancia();
        $consulta = $objetoAccesoDatos -> PrepararConsulta("SELECT * FROM usuarios WHERE email = :email");
        $consulta -> bindParam(':email', $email);
        $resultado = $consulta -> execute();

        if ($resultado && $consulta -> rowCount() > 0) {
            $usuario = $consulta -> fetchObject('Usuario');
            if (password_verify($clave, $usuario -> clave)) {
                $retorno = "Inicio sesión correcto";
            } else {
                $retorno = "La contraseña es incorrecta";
            }
        } else {
            $retorno = "El email no se encuentra registrado";
        }
        return $retorno;
    }
}

?>