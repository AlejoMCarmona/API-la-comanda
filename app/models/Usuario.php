<?php

require_once './db/AccesoDatos.php';

class Usuario {
    public $id;
    public $nombre;
    public $apellido;
    public $dni;
    public $email;
    public $clave;
    public $activo;
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

    public function __construct10($id, $nombre, $apellido, $dni, $email, $clave, $puesto, $sector, $activo, $fechaAlta) {
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
        $this -> __construct10(0, $nombre, $apellido, $dni, $email, $clave, $puesto, $sector, true, "");
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

    public static function ObtenerTodosLosUsuarios($soloActivos = false) {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::ObtenerInstancia();
        if ($soloActivos) {
            $query = "SELECT * FROM usuarios WHERE activo = TRUE";
        } else {
            $query = "SELECT * FROM usuarios";
        }
        $consulta = $objetoAccesoDatos -> PrepararConsulta($query);
        $resultado = $consulta -> execute();
        if ($resultado) {
            $retorno = $consulta -> fetchAll(PDO::FETCH_CLASS, 'Usuario');
        }
        return $retorno;
    }

    public static function ObtenerPorDNI($dni, $soloActivo = false) {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::ObtenerInstancia();
        if ($soloActivo) {
            $query = "SELECT * FROM usuarios WHERE dni = :dni AND activo = TRUE";
        } else {
            $query = "SELECT * FROM usuarios WHERE dni = :dni";
        }
        $consulta = $objetoAccesoDatos -> PrepararConsulta($query);
        $consulta -> bindParam(':dni', $dni);
        $resultado = $consulta -> execute();
        if ($resultado && $consulta -> rowCount() > 0) {
            $retorno = $consulta -> fetchObject('Usuario');
        }
        return $retorno;
    }

    public static function ObtenerPorID($id, $soloActivo = false) {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::ObtenerInstancia();
        if ($soloActivo) {
            $query = "SELECT * FROM usuarios WHERE id = :id AND activo = TRUE";
        } else {
            $query = "SELECT * FROM usuarios WHERE id = :id";
        }
        $consulta = $objetoAccesoDatos -> PrepararConsulta($query);
        $consulta -> bindParam(':id', $id);
        $resultado = $consulta -> execute();
        if ($resultado && $consulta -> rowCount() > 0) {
            $retorno = $consulta -> fetchObject('Usuario');
        }
        return $retorno;
    }

    public static function ObtenerUsuariosPorPuesto($puesto, $soloActivos = false) {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::ObtenerInstancia();
        if ($soloActivos) {
            $query = "SELECT * FROM usuarios WHERE activo = TRUE AND puesto = :puesto";
        } else {
            $query = "SELECT * FROM usuarios WHERE puesto = :puesto";
        }
        $consulta = $objetoAccesoDatos -> PrepararConsulta($query);
        $consulta -> bindParam(':puesto', $puesto);
        $resultado = $consulta -> execute();
        if ($resultado && $consulta -> rowCount() > 0) {
            $retorno = $consulta -> fetchAll(PDO::FETCH_CLASS, 'Usuario');
        }
        return $retorno;
    }

    public static function Borrar($dni) {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::ObtenerInstancia();
        $consulta = $objetoAccesoDatos -> PrepararConsulta("UPDATE usuarios SET activo = FALSE WHERE dni = :dni");
        $consulta -> bindParam(':dni', $dni);
        $resultado = $consulta -> execute();

        if ($resultado) {
            $retorno = true;
        }

        return $retorno;
    }

    public function Modificar() {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::ObtenerInstancia();
        if ($this -> sector == NULL) {
            $query = "UPDATE usuarios SET nombre = :nombre, apellido = :apellido, dni = :dni, email = :email, puesto = :puesto WHERE id = :id";
        } else {
            $query = "UPDATE usuarios SET nombre = :nombre, apellido = :apellido, dni = :dni, email = :email, puesto = :puesto, sector = :sector WHERE id = :id";
        }
        $consulta = $objetoAccesoDatos -> PrepararConsulta($query);
        $consulta -> bindParam(':id', $this -> id);
        $consulta -> bindParam(':nombre', $this -> nombre);
        $consulta -> bindParam(':apellido', $this -> apellido);
        $consulta -> bindParam(':dni', $this -> dni);
        $consulta -> bindParam(':email', $this -> email);
        $consulta -> bindParam(':puesto', $this -> puesto);
        if ($this -> sector != NULL) $consulta -> bindParam(':sector', $this -> sector);
        $resultado = $consulta -> execute();

        if ($resultado) {
            $retorno = true;
        }

        return $retorno;
    }

    public static function IniciarSesion($email, $clave) {
        $retorno = [ "resultado" => false, "mensaje" => "" ];
        $objetoAccesoDatos = AccesoDatos::ObtenerInstancia();
        $consulta = $objetoAccesoDatos -> PrepararConsulta("SELECT * FROM usuarios WHERE email = :email AND activo = TRUE");
        $consulta -> bindParam(':email', $email);
        $resultado = $consulta -> execute();

        if ($resultado && $consulta -> rowCount() > 0) {
            $usuario = $consulta -> fetchObject('Usuario');
            if (password_verify($clave, $usuario -> clave)) {
                $retorno["resultado"] = true;
                $retorno["mensaje"] = $usuario;
            } else {
                $retorno["mensaje"] = "La contraseña es incorrecta";
            }
        } else {
            $retorno["mensaje"] = "El email no se encuentra registrado o el usuario fue dado de baja";
        }
        return $retorno;
    }

    public static function ExisteElUsuario($dni, $email) {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::ObtenerInstancia();
        $consulta = $objetoAccesoDatos -> PrepararConsulta("SELECT id FROM usuarios WHERE dni = :dni OR email = :email");
        $consulta -> bindParam(':dni', $dni);
        $consulta -> bindParam(':email', $email);
        $resultado = $consulta -> execute();
        if ($resultado && $consulta -> rowCount() > 0) {
            $retorno = true;
        }
        return $retorno;
    }
}

?>