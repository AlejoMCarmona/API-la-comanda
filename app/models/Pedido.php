<?php

class Pedido {
    public $id;
    public $idMesa;
    public $idProducto;
    public $idEmpleado;
    public $numeroIdentificacion;
    public $fecha;

    public function __construct() {
        $parametros = func_get_args();
        $numero_parametros = func_num_args();
        $funcion_constructor = '__construct' . $numero_parametros;
        if (method_exists($this, $funcion_constructor)) {
            call_user_func_array(array($this, $funcion_constructor), $parametros);
        }
    }

    public function __construct6($id, $idMesa, $idProducto, $idEmpleado, $numeroIdentificacion, $fecha) {
        $this -> id = $id;
        $this -> idMesa = $idMesa;
        $this -> idProducto = $idProducto;
        $this -> idEmpleado = $idEmpleado;
        $this -> numeroIdentificacion = $numeroIdentificacion;
        $this -> fecha = $fecha;
    }

    public function __construct4($idMesa, $idProducto, $idEmpleado, $numeroIdentificacion) {
        $this -> __construct6(0, $idMesa, $idProducto, $idEmpleado, $numeroIdentificacion, "");
    }

    public function CrearPedido() {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::ObtenerInstancia();
        $consulta = $objetoAccesoDatos -> PrepararConsulta("INSERT INTO Pedidos (idMesa, idProducto, idEmpleado, numeroIdentificacion) VALUES (:idMesa, :idProducto, :idEmpleado, :numeroIdentificacion)");
        $consulta -> bindParam(':idMesa', $this -> idMesa);
        $consulta -> bindParam(':idProducto', $this -> idProducto);
        $consulta -> bindParam(':idEmpleado', $this -> idEmpleado);
        $consulta -> bindParam(':numeroIdentificacion', $this -> numeroIdentificacion);

        $resultado = $consulta -> execute();
        if ($resultado) {
            $retorno = $objetoAccesoDatos -> ObtenerUltimoId();
        }
        return $retorno;
    }

    public static function ObtenerTodosLosPedidos() {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::ObtenerInstancia();
        $consulta = $objetoAccesoDatos -> PrepararConsulta("SELECT * FROM Pedidos");
        $resultado = $consulta -> execute();
        if ($resultado) {
            $retorno = $consulta -> fetchAll(PDO::FETCH_CLASS, 'Pedido');
        }
        return $retorno;
    }
}

?>