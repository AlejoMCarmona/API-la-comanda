<?php

class Pedido {
    public $id;
    public $idMesa;
    public $idProducto;
    public $nombreCliente;
    public $numeroIdentificacion;
    public $estado;
    public $fecha;

    public function __construct() {
        $parametros = func_get_args();
        $numero_parametros = func_num_args();
        $funcion_constructor = '__construct' . $numero_parametros;
        if (method_exists($this, $funcion_constructor)) {
            call_user_func_array(array($this, $funcion_constructor), $parametros);
        }
    }

    public function __construct7($id, $idMesa, $idProducto, $nombreCliente, $numeroIdentificacion, $estado, $fecha) {
        $this -> id = $id;
        $this -> idMesa = $idMesa;
        $this -> idProducto = $idProducto;
        $this -> nombreCliente = $nombreCliente;
        $this -> numeroIdentificacion = $numeroIdentificacion;
        $this -> estado = $estado;
        $this -> fecha = $fecha;
    }

    public function __construct4($idMesa, $idProducto, $nombreCliente, $numeroIdentificacion) {
        if ($numeroIdentificacion == "") $numeroIdentificacion = self::GenerarNumeroAlfanumericoIdentificacion(5);
        $this -> __construct7(0, $idMesa, $idProducto, $nombreCliente, $numeroIdentificacion, "", "");
    }

    public function CrearPedido() {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::ObtenerInstancia();
        $consulta = $objetoAccesoDatos -> PrepararConsulta("INSERT INTO Pedidos (idMesa, idProducto, nombreCliente, numeroIdentificacion) VALUES (:idMesa, :idProducto, :nombreCliente, :numeroIdentificacion)");
        $consulta -> bindParam(':idMesa', $this -> idMesa);
        $consulta -> bindParam(':idProducto', $this -> idProducto);
        $consulta -> bindParam(':nombreCliente', $this -> nombreCliente);
        $consulta -> bindParam(':numeroIdentificacion', $this -> numeroIdentificacion);

        $resultado = $consulta -> execute();
        if ($resultado) {
            $retorno = $this -> numeroIdentificacion;
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

    public static function ObtenerPedidoPorID($id) {
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

    public static function ObtenerPedidosPorSector($sector, $traerPendientes = true) {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::ObtenerInstancia();
        $query = "";
        if ($traerPendientes) {
            $query = "SELECT pe.idMesa AS mesa, pr.nombre AS nombreProducto, pe.fecha AS fechaPedido, pr.tiempoPreparacion / 60 AS minutosPreparacion FROM pedidos AS pe INNER JOIN productos AS pr ON pe.idProducto = pr.id WHERE pr.sector = :sector AND pe.estado = 'pendiente'";
        } else {
            $query = "SELECT pe.idMesa AS mesa, pr.nombre AS nombreProducto, pe.fecha AS fechaPedido, pr.tiempoPreparacion / 60 AS minutosPreparacion FROM pedidos AS pe INNER JOIN productos AS pr ON pe.idProducto = pr.id WHERE pr.sector = :sector";
        }
        $consulta = $objetoAccesoDatos -> PrepararConsulta($query);
        $consulta -> bindParam(':sector', $sector);
        $resultado = $consulta -> execute();
        if ($resultado) {
            $retorno = $consulta -> fetchAll(PDO::FETCH_OBJ);
        }
        return $retorno;
    }

    public static function CambiarEstado($id, $nuevoEstado) {
        $objetoAccesoDatos = AccesoDatos::ObtenerInstancia();
        $consulta = $objetoAccesoDatos -> PrepararConsulta("UPDATE pedidos SET estado = :nuevoEstado WHERE id = :id");
        $consulta -> bindParam(':nuevoEstado', $nuevoEstado);
        $consulta -> bindParam(':id', $id);
        $retorno = $consulta -> execute();
        return $retorno;
    }
}

?>