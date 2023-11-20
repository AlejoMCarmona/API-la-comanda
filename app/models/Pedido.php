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
        $consulta = $objetoAccesoDatos -> PrepararConsulta("INSERT INTO pedidos (codigoMesa, idProducto, nombreCliente, codigoIdentificacion) VALUES (:codigoMesa, :idProducto, :nombreCliente, :codigoIdentificacion)");
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

    public function Modificar() {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::ObtenerInstancia();
        $consulta = $objetoAccesoDatos -> PrepararConsulta("UPDATE pedidos SET idProducto = :idProducto, nombreCliente = :nombreCliente WHERE id = :id");
        $consulta -> bindParam(':id', $this -> id);
        $consulta -> bindParam(':idProducto', $this -> idProducto);
        $consulta -> bindParam(':nombreCliente', $this -> nombreCliente);

        $resultado = $consulta -> execute();
        if ($resultado) {
            $retorno = true;
        }
        return $retorno;
    }

    public static function ObtenerTodosLosPedidos($soloActivos = false) {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::ObtenerInstancia();
        if ($soloActivos) {
            $query = "SELECT * FROM pedidos WHERE estado <> 'cancelado'";
        } else {
            $query = "SELECT * FROM pedidos";
        }
        $consulta = $objetoAccesoDatos -> PrepararConsulta($query);
        $resultado = $consulta -> execute();
        if ($resultado) {
            $retorno = $consulta -> fetchAll(PDO::FETCH_CLASS, 'Pedido');
        }
        return $retorno;
    }

    public static function ObtenerPorID($id, $soloActivo = false) {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::ObtenerInstancia();
        if ($soloActivo) {
            $query = "SELECT * FROM pedidos WHERE id = :id AND estado <> 'cancelado'";
        } else {
            $query = "SELECT * FROM pedidos WHERE id = :id";
        }
        $consulta = $objetoAccesoDatos -> PrepararConsulta($query);
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

    public static function ObtenerPorCodigoIdentificacion($codigoIdentificacion, $soloActivos = false) {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::ObtenerInstancia();
        if ($soloActivos) {
            $query = "SELECT * FROM pedidos WHERE codigoIdentificacion = :codigoIdentificacion AND estado <> 'cancelado'";
        } else {
            $query = "SELECT * FROM pedidos WHERE codigoIdentificacion = :codigoIdentificacion";
        }
        $consulta = $objetoAccesoDatos -> PrepararConsulta($query);
        $consulta -> bindParam(':codigoIdentificacion', $codigoIdentificacion);
        $resultado = $consulta -> execute();
        if ($resultado) {
            $retorno = $consulta -> fetchAll(PDO::FETCH_CLASS, 'Pedido');
        }
        return $retorno;
    }

    /**
     * Obtiene el tiempo restante para la preparación/completado de un conjunto de pedidos en una mesa mediante un código de identificación.
     *
     * @param int $codigoMesa El código de la mesa donde se realiza el pedido.
     * @param string $codigoIdentificacion El código de identificación único del conjunto de pedidos.
     *
     * @return array Un array asociativo con dos claves:
     *   - 'codigo' (int): Código que indica el resultado de la operación. 0 si la operación fue exitosa, -1 si hubo un error.
     *   - 'mensaje' (string): Mensaje informativo sobre el tiempo restante o el motivo del error.
     */
    public static function ObtenerTiempoRestante($codigoMesa, $codigoIdentificacion) {
        $retorno = [ "codigo" => 0, "mensaje" => "" ];
        $objetoAccesoDatos = AccesoDatos::ObtenerInstancia();
    
        $consultaTiempo = $objetoAccesoDatos -> PrepararConsulta("SELECT estado, fecha, tiempoPreparacion/60 as minutosPreparacion FROM pedidos WHERE codigoIdentificacion = :codigoIdentificacion AND codigoMesa = :codigoMesa AND estado <> 'cancelado'");       
        $consultaTiempo -> bindParam(':codigoIdentificacion', $codigoIdentificacion);
        $consultaTiempo -> bindParam(':codigoMesa', $codigoMesa);
        
        $resultadoTiempo = $consultaTiempo -> execute();
        if ($resultadoTiempo && $consultaTiempo -> rowCount() > 0) {
            $pedidos = $consultaTiempo -> fetchAll();
            
            // Corroboro que ningún elemento tiene el estado 'pendiente'
            $hayPendientes = in_array("pendiente", array_column($pedidos, "estado"));  
            if (!$hayPendientes) {
                $pedidosEnPreparacion = array_filter($pedidos, function ($elemento) { return $elemento["estado"] === "en preparacion"; });
                if (count($pedidosEnPreparacion) > 0) {
                    // Me quedo solo con los elementos cuyo estado sea 'en preparacion'
                    $tiempoMaximo = 0;
                    $primerBucle = true;
                    foreach ($pedidosEnPreparacion as $pedido) {
                        $minutosPreparacion = (int)$pedido["minutosPreparacion"];
                        $minutosRestantes = self::CalcularDiferenciaTiempo($pedido["fecha"], $minutosPreparacion);
                        if ($primerBucle || $tiempoMaximo < $minutosRestantes) {
                            $tiempoMaximo = $minutosRestantes;
                            $primerBucle = false;
                        }    
                    }

                    $retorno["codigo"] = 0;
                    if ($tiempoMaximo < 0) {
                        $tiempoMaximo *= -1;
                        $retorno["mensaje"] = "Tu pedido completo está atrasado {$tiempoMaximo} minutos";
                    } else {
                        $retorno["mensaje"] = "Faltan {$tiempoMaximo} minutos para que tu pedido entero se complete";
                    }
                } else {
                    $retorno["codigo"] = 0;
                    $retorno["mensaje"] = "Todos los productos de este pedido ya han sido preparados";                    
                }

            } else {
                $retorno["codigo"] = -1;
                $retorno["mensaje"] = "Aún hay pedidos que no están siendo preparados y por ende, no puede calcularse el tiempo de finalización del pedido completo"; 
            }
        } else {
            $retorno["codigo"] = -1;
            $retorno["mensaje"] = "No se pudo encontrar el pedido buscado";            
        }
    
        return $retorno;
    }
    private static function CalcularDiferenciaTiempo($fechaInicial, $minutosPreparacion) {
        $fechaInicio = new DateTime($fechaInicial);
        $fechaDeFinalizacion = $fechaInicio -> modify("+{$minutosPreparacion} minutes");
        $fechaActual = new DateTime();

        if ($fechaActual > $fechaDeFinalizacion) {
            $retorno = -1;
        } else {
            $retorno = 1;
        }

        $diferencia = date_diff($fechaActual, $fechaDeFinalizacion);
        $minutos = ($diferencia->days * 24 * 60) + ($diferencia->h * 60) + $diferencia->i;
        $retorno *= $minutos;
        return $retorno;
    }

    public static function ObtenerPedidosPorSector($sector, $soloPendientes = true, $soloActivos = false) {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::ObtenerInstancia();
        $query = "";
        if ($soloPendientes) {
            $query = "SELECT pe.id AS idPedido, pe.codigoMesa AS mesa, pr.nombre AS nombreProducto, pe.fecha AS fechaPedido FROM pedidos AS pe INNER JOIN productos AS pr ON pe.idProducto = pr.id WHERE pr.sector = :sector AND pe.estado = 'pendiente'";
        } else if ($soloActivos) {
            $query = "SELECT pe.id AS idPedido, pe.codigoMesa AS mesa, pr.nombre AS nombreProducto, pe.fecha AS fechaPedido FROM pedidos AS pe INNER JOIN productos AS pr ON pe.idProducto = pr.id WHERE pr.sector = :sector AND pe.estado <> 'cancelado'";
        } else {
            $query = "SELECT pe.id AS idPedido, pe.codigoMesa AS mesa, pr.nombre AS nombreProducto, pe.fecha AS fechaPedido FROM pedidos AS pe INNER JOIN productos AS pr ON pe.idProducto = pr.id WHERE pr.sector = :sector";
        }
        $consulta = $objetoAccesoDatos -> PrepararConsulta($query);
        $consulta -> bindParam(':sector', $sector);
        $resultado = $consulta -> execute();
        if ($resultado) {
            $retorno = $consulta -> fetchAll(PDO::FETCH_OBJ);
        }
        return $retorno;
    }

    public static function CambiarEstado($id, $nuevoEstado, $tiempoPreparacion = "") {
        $objetoAccesoDatos = AccesoDatos::ObtenerInstancia();
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

    public static function ObtenerFacturaPedido($codigoIdentificacion) {
        $objetoAccesoDatos = AccesoDatos::ObtenerInstancia();
        $consulta = $objetoAccesoDatos -> PrepararConsulta("SELECT SUM(pr.precio) as precioFinal FROM pedidos AS pe INNER JOIN productos AS pr ON pe.idProducto = pr.id WHERE pe.estado <> 'cancelado' AND codigoIdentificacion = :codigoIdentificacion");
        $consulta -> bindParam(':codigoIdentificacion', $codigoIdentificacion);
        $retorno = $consulta -> execute();
        if ($retorno) {
            $retorno = $consulta -> fetchObject();
            $retorno = (float)$retorno -> precioFinal;
        }
        return $retorno;
    }

    public static function SubirFotoMesa($fotoMesa, $codigoIdentificacion) {
        $retorno = false;
        $ruta = './fotos/pedidosDeMesas';
        if (!file_exists($ruta)) {
            if (!file_exists('./fotos')) {
                mkdir('./fotos', 0777);
            }
            mkdir($ruta, 0777);
        }

        $extension = pathinfo($fotoMesa -> getClientFilename(), PATHINFO_EXTENSION);
        $nombreFoto = $codigoIdentificacion . date("Ymd") . '.' . $extension;
        $rutaCompleta = $ruta . '/' . $nombreFoto;

        if (!file_exists($rutaCompleta)) {
            $fotoMesa -> moveTo($rutaCompleta);
            $retorno = true;
        }

        return $retorno;
    }
}

?>