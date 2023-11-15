<?php

require_once './db/AccesoDatos.php';

class Mesa {
    public $id;
    public $estado;
    public $codigoIdentificacion;
    public $asientos;
    public $fechaCreacion;

    public function __construct() {
        $parametros = func_get_args();
        $numero_parametros = func_num_args();
        $funcion_constructor = '__construct' . $numero_parametros;
        if (method_exists($this, $funcion_constructor)) {
            call_user_func_array(array($this, $funcion_constructor), $parametros);
        }
    }

    public function __construct5($id, $estado, $codigoIdentificacion, $asientos, $fecha) {
        $this -> id = $id;
        $this -> estado = $estado;
        $this -> codigoIdentificacion = $codigoIdentificacion;
        $this -> asientos = $asientos;
        $this -> fecha = $fecha;
    }

    public function __construct2($codigoIdentificacion, $asientos) {
        $this -> __construct5(0, "", $codigoIdentificacion, $asientos, "");
    }

    public function CrearMesa() {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::ObtenerInstancia();
        $consulta = $objetoAccesoDatos -> PrepararConsulta("INSERT INTO MESAS (codigoIdentificacion, asientos) VALUES (:codigoIdentificacion, :asientos)");
        $consulta -> bindParam(":codigoIdentificacion", $this -> codigoIdentificacion);
        $consulta -> bindParam(":asientos", $this -> asientos);
        $resultado = $consulta -> execute();
        if ($resultado) {
            $retorno = $objetoAccesoDatos -> ObtenerUltimoId();
        }
        return $retorno;
    }

    public static function ObtenerTodasLasMesas($soloActivas = false) {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::ObtenerInstancia();
        if ($soloActivas) {
            $query = "SELECT * FROM MESAS WHERE activa = TRUE";
        } else {
            $query = "SELECT * FROM MESAS";
        }
        $consulta = $objetoAccesoDatos -> PrepararConsulta($query);
        $resultado = $consulta -> execute();
        if ($resultado) {
            $retorno = $consulta -> fetchAll(PDO::FETCH_CLASS, 'Mesa');
        }
        return $retorno;
    }

    public static function ObtenerPorID($id, $soloActiva = false) {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::ObtenerInstancia();
        if ($soloActiva) {
            $query = "SELECT * FROM mesas WHERE id = :id AND activa = TRUE";
        } else {
            $query = "SELECT * FROM mesas WHERE id = :id";
        }
        $consulta = $objetoAccesoDatos -> PrepararConsulta($query);
        $consulta -> bindParam(':id', $id);
        $resultado = $consulta -> execute();
        if ($resultado) {
            $retorno = $consulta -> fetchObject('Mesa');
        }
        return $retorno;
    }

    public static function ObtenerPorCodigoIdentificacion($codigoIdentificacion, $soloActivas = false) {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::ObtenerInstancia();
        if ($soloActivas) {
            $query = "SELECT * FROM mesas WHERE codigoIdentificacion = :codigoIdentificacion AND activa = TRUE";
        } else {
            $query = "SELECT * FROM mesas WHERE codigoIdentificacion = :codigoIdentificacion";
        }
        $consulta = $objetoAccesoDatos -> PrepararConsulta($query);
        $consulta -> bindParam(':codigoIdentificacion', $codigoIdentificacion);
        $resultado = $consulta -> execute();
        if ($resultado) {
            $retorno = $consulta -> fetchObject('Mesa');
        }
        return $retorno;
    }

    public function CambiarEstado($nuevoEstado) {
        $retorno = false;
        if ($this -> estado != $nuevoEstado) {
            $objetoAccesoDatos = AccesoDatos::ObtenerInstancia();
            $consulta = $objetoAccesoDatos -> PrepararConsulta("UPDATE mesas SET estado = :nuevoEstado WHERE id = :id");
            $consulta -> bindParam(':nuevoEstado', $nuevoEstado);
            $consulta -> bindParam(':id', $this -> id);
            $retorno = $consulta -> execute();
        }
        return $retorno;
    }

    public static function Borrar($codigoIdentificacion) {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::ObtenerInstancia();
        $consulta = $objetoAccesoDatos -> PrepararConsulta("UPDATE mesas SET activa = FALSE WHERE codigoIdentificacion = :codigoIdentificacion");
        $consulta -> bindParam(':codigoIdentificacion', $codigoIdentificacion);
        $resultado = $consulta -> execute();

        if ($resultado) {
            $retorno = true;
        }

        return $retorno;
    }

    public function Modificar() {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::ObtenerInstancia();
        $consulta = $objetoAccesoDatos -> PrepararConsulta("UPDATE mesas SET asientos = :asientos WHERE id = :id");
        $consulta -> bindParam(':id', $id);
        $consulta -> bindParam(':asientos', $this -> asientos);
        $resultado = $consulta -> execute();

        if ($resultado) {
            $retorno = true;
        }

        return $retorno;
    }
}

?>