<?php

require_once './db/AccesoDatos.php';

class Mesa {
    public $id;
    public $estado;
    public $fechaCreacion;

    public function __construct() {
        $parametros = func_get_args();
        $numero_parametros = func_num_args();
        $funcion_constructor = '__construct' . $numero_parametros;
        if (method_exists($this, $funcion_constructor)) {
            call_user_func_array(array($this, $funcion_constructor), $parametros);
        }
    }

    public function __construct3($id, $estado, $fecha) {
        $this -> id = $id;
        $this -> estado = $estado;
        $this -> fecha = $fecha;
    }

    public function CrearMesa() {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::ObtenerInstancia();
        $consulta = $objetoAccesoDatos -> PrepararConsulta("INSERT INTO MESAS () VALUES ()");
        $resultado = $consulta -> execute();
        if ($resultado) {
            $retorno = $objetoAccesoDatos -> ObtenerUltimoId();
        }
        return $retorno;
    }

    public static function ObtenerTodasLasMesas() {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::ObtenerInstancia();
        $consulta = $objetoAccesoDatos -> PrepararConsulta("SELECT * FROM MESAS");
        $resultado = $consulta -> execute();
        if ($resultado) {
            $retorno = $consulta -> fetchAll(PDO::FETCH_CLASS, 'Mesa');
        }
        return $retorno;
    }

    public static function ObtenerMesa($id) {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::ObtenerInstancia();
        $consulta = $objetoAccesoDatos -> PrepararConsulta("SELECT * FROM mesas WHERE id = :id");
        $consulta -> bindParam(':id', $id);
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
}

?>