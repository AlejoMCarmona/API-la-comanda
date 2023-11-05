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

    public function __construct7($id, $idMesa, $idProducto, $idEmpleado, $numeroIdentificacion, $estado, $fecha) {
        $this -> id = $id;
        $this -> idMesa = $idMesa;
        $this -> idProducto = $idProducto;
        $this -> idEmpleado = $idEmpleado;
        $this -> numeroIdentificacion = $numeroIdentificacion;
        $this -> estado = $estado;
        $this -> fecha = $fecha;
    }

    public function __construct4($idMesa, $idProducto, $idEmpleado, $numeroIdentificacion) {
        if ($numeroIdentificacion == "") $numeroIdentificacion = self::GenerarNumeroAlfanumericoIdentificacion(5);
        $this -> __construct7(0, $idMesa, $idProducto, $idEmpleado, $numeroIdentificacion, "", "");
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
        $consulta = $objetoAccesoDatos -> PrepararConsulta("SELECT * FROM pedidos");
        $resultado = $consulta -> execute();
        if ($resultado) {
            $retorno = $consulta -> fetchAll(PDO::FETCH_CLASS, 'Pedido');
        }
        return $retorno;
    }

    public static function ObtenerUltimoPedidoPorMesa($idMesa) {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::ObtenerInstancia();
        $consulta = $objetoAccesoDatos -> PrepararConsulta("SELECT * FROM pedidos WHERE idMesa = :idMesa ORDER BY fecha DESC LIMIT 1");
        $consulta -> bindParam(':idMesa', $idMesa);
        $resultado = $consulta -> execute();
        if ($resultado) {
            $retorno = $consulta -> fetchObject('Pedido');
        }
        return $retorno;
    }

    public static function ObtenerPedidosPorNumeroIdentificacion($numeroIdentificacion) {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::ObtenerInstancia();
        $consulta = $objetoAccesoDatos -> PrepararConsulta("SELECT * FROM pedidos WHERE numeroIdentificacion = :numeroIdentificacion");
        $consulta -> bindParam(':numeroIdentificacion', $numeroIdentificacion);
        $resultado = $consulta -> execute();
        if ($resultado) {
            $retorno = $consulta -> fetchAll(PDO::FETCH_CLASS, 'Pedido');
        }
        return $retorno;
    }

    public static function ObtenerTiempoRestantePorNumeroIdentificacion($numeroIdentificacion) {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::ObtenerInstancia();
        $consultaTiempo = $objetoAccesoDatos -> PrepararConsulta("SELECT MIN(pe.fecha) as fechaInicial, MAX(pr.tiempoPreparacion)/60 as tiempoPreparacion FROM pedidos AS pe INNER JOIN productos as pr ON pe.idProducto = pr.id WHERE numeroIdentificacion = :numeroIdentificacion");
        $consultaTiempo -> bindParam(':numeroIdentificacion', $numeroIdentificacion);
        $resultadoTiempo = $consultaTiempo -> execute();
        if ($resultadoTiempo) {
            $retornoConsulta = $consultaTiempo -> fetchObject();
            $fechaInicial = $retornoConsulta -> fechaInicial;
            $tiempoPreparacion = (int)($retornoConsulta -> tiempoPreparacion);
            // Manejo de fechas
            $fechaInicio = new DateTime($fechaInicial);
            $fechaDeFinalizacion = $fechaInicio -> modify("+{$tiempoPreparacion} minutes");
            $fechaActual = new DateTime();
            if ($fechaActual > $fechaDeFinalizacion) {
                $retorno = -1;
            } else {
                $retorno = 1;
            }
            $diferencia = date_diff($fechaActual, $fechaDeFinalizacion);
            $minutos = ($diferencia -> days * 24 * 60) + ($diferencia -> h * 60) + $diferencia -> i;
            $retorno *= $minutos;
        }
        return $retorno;
    }
}

?>