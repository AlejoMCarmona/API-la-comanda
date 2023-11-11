<?php

class Pedido {
    public $id;
    public $codigoMesa;
    public $idProducto;
    public $nombreCliente;
    public $codigoIdentificacion;
    public $estado;
    public $tiempoPreparacion;
    public $fecha;

    public function __construct() {
        $parametros = func_get_args();
        $numero_parametros = func_num_args();
        $funcion_constructor = '__construct' . $numero_parametros;
        if (method_exists($this, $funcion_constructor)) {
            call_user_func_array(array($this, $funcion_constructor), $parametros);
        }
    }

    public function __construct7($id, $codigoMesa, $idProducto, $nombreCliente, $codigoIdentificacion, $estado, $fecha) {
        $this -> id = $id;
        $this -> codigoMesa = $codigoMesa;
        $this -> idProducto = $idProducto;
        $this -> nombreCliente = $nombreCliente;
        $this -> codigoIdentificacion = $codigoIdentificacion;
        $this -> estado = $estado;
        $this -> fecha = $fecha;
    }

    public function __construct4($codigoMesa, $idProducto, $nombreCliente, $codigoIdentificacion) {
        if ($codigoIdentificacion == "") $codigoIdentificacion = self::GenerarNumeroAlfanumericoIdentificacion(5);
        $this -> __construct7(0, $codigoMesa, $idProducto, $nombreCliente, $codigoIdentificacion, "", "");
    }

    public function CrearPedido() {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::ObtenerInstancia();
        $consulta = $objetoAccesoDatos -> PrepararConsulta("INSERT INTO Pedidos (codigoMesa, idProducto, nombreCliente, codigoIdentificacion) VALUES (:codigoMesa, :idProducto, :nombreCliente, :codigoIdentificacion)");
        $consulta -> bindParam(':codigoMesa', $this -> codigoMesa);
        $consulta -> bindParam(':idProducto', $this -> idProducto);
        $consulta -> bindParam(':nombreCliente', $this -> nombreCliente);
        $consulta -> bindParam(':codigoIdentificacion', $this -> codigoIdentificacion);

        $resultado = $consulta -> execute();
        if ($resultado) {
            $retorno = $this -> codigoIdentificacion;
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

    public static function ObtenerPorID($id) {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::ObtenerInstancia();
        $consulta = $objetoAccesoDatos -> PrepararConsulta("SELECT * FROM pedidos WHERE id = :id");
        $consulta -> bindParam(':id', $id);
        $resultado = $consulta -> execute();
        if ($resultado && $consulta -> rowCount() > 0) {
            $retorno = $consulta -> fetchObject('Pedido');
        }
        return $retorno;
    }

    public static function ObtenerUltimoPedidoPorMesa($codigoMesa) {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::ObtenerInstancia();
        $consulta = $objetoAccesoDatos -> PrepararConsulta("SELECT * FROM pedidos WHERE codigoMesa = :codigoMesa ORDER BY fecha DESC LIMIT 1");
        $consulta -> bindParam(':codigoMesa', $codigoMesa);
        $resultado = $consulta -> execute();
        if ($resultado) {
            $retorno = $consulta -> fetchObject('Pedido');
        }
        return $retorno;
    }

    public static function ObtenerPorCodigoIdentificacion($codigoIdentificacion) {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::ObtenerInstancia();
        $consulta = $objetoAccesoDatos -> PrepararConsulta("SELECT * FROM pedidos WHERE codigoIdentificacion = :codigoIdentificacion");
        $consulta -> bindParam(':codigoIdentificacion', $codigoIdentificacion);
        $resultado = $consulta -> execute();
        if ($resultado) {
            $retorno = $consulta -> fetchAll(PDO::FETCH_CLASS, 'Pedido');
        }
        return $retorno;
    }

    public static function ObtenerTiempoRestantePorCodigoIdentificacion($codigoMesa, $idPedido) {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::ObtenerInstancia();
        $consultaTiempo = $objetoAccesoDatos -> PrepararConsulta("SELECT estado, fecha, tiempoPreparacion/60 as minutosPreparacion FROM pedidos WHERE id = :idPedido AND codigoMesa = :codigoMesa");
        $consultaTiempo -> bindParam(':idPedido', $idPedido);
        $consultaTiempo -> bindParam(':codigoMesa', $codigoMesa);
        $resultadoTiempo = $consultaTiempo -> execute();

        if ($resultadoTiempo && $consultaTiempo -> rowCount() > 0) {
            $retornoConsulta = $consultaTiempo -> fetchObject();
            if ($retornoConsulta -> estado == 'en preparacion') { // Si el pedido no está en preparación, se le notificará al usuario que aún no se puede obtener el tiempo restante
                $fechaInicial = $retornoConsulta -> fecha;
                $minutosPreparacion = (int)($retornoConsulta -> minutosPreparacion);
                // Manejo de fechas
                $fechaInicio = new DateTime($fechaInicial);
                $fechaDeFinalizacion = $fechaInicio -> modify("+{$minutosPreparacion} minutes");
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
        }
        return $retorno;
    }

    public static function ObtenerPedidosPorSector($sector, $traerPendientes = true) {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::ObtenerInstancia();
        $query = "";
        if ($traerPendientes) {
            $query = "SELECT pe.codigoMesa AS mesa, pr.nombre AS nombreProducto, pe.fecha AS fechaPedido FROM pedidos AS pe INNER JOIN productos AS pr ON pe.idProducto = pr.id WHERE pr.sector = :sector AND pe.estado = 'pendiente'";
        } else {
            $query = "SELECT pe.codigoMesa AS mesa, pr.nombre AS nombreProducto, pe.fecha AS fechaPedido FROM pedidos AS pe INNER JOIN productos AS pr ON pe.idProducto = pr.id WHERE pr.sector = :sector";
        }
        $consulta = $objetoAccesoDatos -> PrepararConsulta($query);
        $consulta -> bindParam(':sector', $sector);
        $resultado = $consulta -> execute();
        if ($resultado) {
            $retorno = $consulta -> fetchAll(PDO::FETCH_OBJ);
        }
        return $retorno;
    }

    public static function CambiarEstado($id, $nuevoEstado, $tiempoPreparacion) {
        $objetoAccesoDatos = AccesoDatos::ObtenerInstancia();
        $consulta = "";
        if ($tiempoPreparacion != "") {
            $consulta = "UPDATE pedidos SET estado = :nuevoEstado, fecha = NOW(), tiempoPreparacion = :tiempoPreparacion WHERE id = :id";
        } else {
            $consulta = "UPDATE pedidos SET estado = :nuevoEstado, fecha = NOW() WHERE id = :id";
        }
        $consulta = $objetoAccesoDatos -> PrepararConsulta($consulta);
        $consulta -> bindParam(':nuevoEstado', $nuevoEstado);
        if ($tiempoPreparacion != "") $consulta -> bindParam(':tiempoPreparacion', $tiempoPreparacion);
        $consulta -> bindParam(':id', $id);
        $retorno = $consulta -> execute();
        return $retorno;
    }
}

?>