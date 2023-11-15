<?php

require_once './middlewares/Validadores.php';
require_once './models/Pedido.php';
require_once './interfaces/IApiUsable.php';

class PedidoController implements IApiUsable {

    public function CargarUno($request, $response, $args) {
        $parametros = $request -> getParsedBody();

        if (Validadores::ValidarParametros($parametros, [ "codigoMesa", "idProducto", "nombreCliente" ])) {
            $mesa = Mesa::ObtenerPorCodigoIdentificacion($parametros["codigoMesa"], true);
            $producto = Producto::ObtenerPorID($parametros["idProducto"], true);
            if ($mesa && $producto) {
                $codigoIdentificacion = "";
                if ($mesa -> estado != "cerrada") {
                    $codigoIdentificacion = Pedido::ObtenerUltimoPedidoPorMesa($parametros['codigoMesa']) -> codigoIdentificacion;
                } else {
                    //TODO: cambiar
                    $codigoIdentificacion = Validadores::GenerarNumeroAlfanumericoIdentificacion(5, "Pedido");
                    $fotoMesa = $request -> getUploadedFiles()['foto'];
                    self::SubirFotoMesa($codigoIdentificacion, $fotoMesa);
                }

                $pedido = new Pedido($parametros['codigoMesa'], $parametros['idProducto'], $parametros["nombreCliente"], $codigoIdentificacion);
                $resultado = $pedido -> CrearPedido();
                $mesa -> CambiarEstado("con cliente esperando pedido");

                if (is_string($resultado)) {
                    $payload = json_encode(array("Resultado" => "Se ha creado un pedido con el número de identificación {$resultado}"));
                } else {
                    $payload = json_encode(array("ERROR" => "Hubo un error durante la creación del pedido"));
                }
            } else {
                $payload = json_encode(array("ERROR" => "Tanto la mesa seleccionada como el producto, deben existir y estar activos para realizar un pedido"));
            }
        } else {
            $payload = json_encode(array("ERROR" => "Los parámetros obligatorios para cargar un nuevo pedido son: nombre, codigoMesa, idProducto y idEmpleado"));
        }

        $response -> getBody() -> write($payload);
        return $response -> withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args) {
        $lista = Pedido::ObtenerTodosLosPedidos(); // TODO: agregar el estado activo a los pedidos y modificar esta línea

        if (is_array($lista)) {
            $payload = json_encode(array("Lista" => $lista));
        } else {
            $payload = json_encode(array("ERROR" => "Hubo un error al obtener todos los pedidos"));
        }

        $response -> getBody() -> write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerPorCodigoIdentificacion($request, $response, $args) {
        $parametros = $request -> getParsedBody();

        if (Validadores::ValidarParametros($args, [ "codigoIdentificacion" ])) {
            $lista = Pedido::ObtenerPorCodigoIdentificacion($args["codigoIdentificacion"]); // TODO: agregar el estado activo a los pedidos y modificar esta línea

            if (is_array($lista)) {
                $payload = json_encode(array("Lista" => $lista));
            } else {
                $payload = json_encode(array("ERROR" => "Hubo un error al obtener los pedidos de esta mesa"));
            }
        } else {
            $payload = json_encode(array("ERROR" => "El parámetro 'codigoIdentificacion' es obligatorio para traer los pedidos"));
        }

        $response -> getBody() -> write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerTiempoEstimadoPedido($request, $response, $args) {
        if (Validadores::ValidarParametros($args, [ "codigoMesa", "idPedido" ])) {
            $tiempo = Pedido::ObtenerTiempoRestantePorCodigoIdentificacion($args["codigoMesa"], $args["idPedido"]); // TODO: agregar el estado activo a los pedidos y modificar esta línea

            if (is_numeric($tiempo)) {
                $mensaje = "Faltan {$tiempo} minutos para tener tu pedido";
                if ($tiempo < 0) {
                    $tiempo *= -1; 
                    $mensaje = "Tu pedido se encuentra retrasado {$tiempo} minutos";
                }
                $payload = json_encode(array("Resultado" => $mensaje ));
            } else {
                $payload = json_encode(array("ERROR" => "No se pudo obtener el tiempo restante para la entrega del pedido"));
            }
        } else {
            $payload = json_encode(array("ERROR" => "Los parámetros 'codigoMesa' y 'idPedido' son obligatorios para traer el tiempo de pedido"));
        }

        $response -> getBody() -> write($payload);
        return $response -> withHeader('Content-Type', 'application/json');
    }

    public function TraerPedidosPorSector($request, $response, $args) {
        if (Validadores::ValidarParametros($args, [ "sector" ])) {
            $lista = Pedido::ObtenerPedidosPorSector($args["sector"]); // TODO: agregar el estado activo a los pedidos y modificar esta línea

            if (is_array($lista)) {
                $payload = json_encode(array("Lista" => $lista));
            } else {
                $payload = json_encode(array("ERROR" => "Hubo un error al obtener todos los productos"));
            }
        } else {
            $payload = json_encode(array("ERROR" => "El parámetro 'sector' es obligatorio para traer los pedidos por sector"));
        }
        
        $response -> getBody() -> write($payload);
        return $response -> withHeader('Content-Type', 'application/json');
    }

    public function CambiarEstado($request, $response, $args) {
        $parametros = $request -> getParsedBody ();

        if (Validadores::ValidarParametros($parametros, ["id"])) {
            $payload = json_encode(array("ERROR" => "Hubo un error al cambiar el estado"));
            $pedido = Pedido::ObtenerPorID($parametros["id"]); // TODO: agregar el estado activo a los pedidos y modificar esta línea
            if ($pedido) {
                $nuevoEstado = false;
                $tiempoPreparacion = "";
                switch($pedido -> estado) {
                    case 'pendiente':
                        if (Validadores::ValidarParametros($parametros, ["tiempoPreparacion"])) {
                            $nuevoEstado = 'en preparacion';
                            $tiempoPreparacion = $parametros["tiempoPreparacion"];
                        }
                        break;
                    case 'en preparacion':
                        $nuevoEstado = 'listo para servir';
                    break;
                    default:
                        $nuevoEstado = false;
                    break;
                }

                if ($nuevoEstado) {
                    if (Pedido::CambiarEstado($parametros["id"], $nuevoEstado, $tiempoPreparacion)) {
                        $payload = json_encode(array("Resultado" => "El estado del pedido fue cambiado a '{$nuevoEstado}'"));
                    }
                }
            }
        } else {
            $payload = json_encode(array("ERROR" => "El parámetro 'sector' es obligatorio para traer los pedidos por sector. Si el pedido se pasa a 'en preparacion', también debe pasarse 'tiempoPreparacion'"));
        }
        
        $response -> getBody() -> write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args) {
        if (Validadores::ValidarParametros($args, ["id"])) {
            $pedido = Pedido::ObtenerPorID($args["id"]); // TODO: agregar el estado activo a los pedidos y modificar esta línea

            if ($pedido) {
                $payload = json_encode(array("Pedido" => $pedido));
            } else {
                $payload = json_encode(array("ERROR" => "No se pudo encontrar una mesa con el id {$args["id"]}"));
            }
        } else {
            $payload = json_encode(array("ERROR" => "El parámetro 'id' es obligatorio para traer un pedido"));
        }
        
        $response -> getBody() -> write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

	public function BorrarUno($request, $response, $args) {
        return; // No puedo borrar un pedido
    }

	public function ModificarUno($request, $response, $args) {
        $parametros = $request -> getParsedBody ();

        if (Validadores::ValidarParametros($parametros, [ "id", "idProducto", "nombreCliente" ])) {
            $pedido = Pedido::ObtenerPorID($parametros["id"]); // TODO: agregar el estado activo a los pedidos y modificar esta línea
            $producto = Producto::ObtenerPorID($parametros["idProducto"], true);
            if ($pedido && $producto) {
                $pedido -> idProducto = $parametros["idProducto"];
                $pedido -> nombreCliente = $parametros["nombreCliente"];
                if ($pedido -> Modificar()) {
                    $payload = json_encode(array("Pedido modificado:" => $pedido));
                } else {
                    $payload = json_encode(array("ERROR" => "No se pudo modificar el pedido"));
                }
            } else {
                $payload = json_encode(array("ERROR" => "Tanto el pedido como el producto deben existir y estar activos para realizar la modificación"));
            }
        } else {
            $payload = json_encode(array("ERROR" => "El parámetro 'id' es obligatorio para modificar un pedido"));
        }
        
        $response -> getBody() -> write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    private static function SubirFotoMesa($codigoIdentificacion, $fotoMesa) {
        $retorno = false;

        if ($fotoMesa -> getError() === UPLOAD_ERR_OK) {
            $path = './fotos/pedidosDeMesas';
    
            if (!file_exists($path)) {
                if (!file_exists('./fotos')) {
                    mkdir('./fotos', 0777);
                }
                mkdir($path, 0777);
            }

            $extension = pathinfo($fotoMesa -> getClientFilename(), PATHINFO_EXTENSION);
            $nombreFoto = $codigoIdentificacion . date("Ymd") . '.' . $extension;
            $fotoMesa -> moveTo($path . '/' . $nombreFoto);
    
            $retorno = true;
        } else {
            $retorno = false;
        }

        return $retorno;
    }
}

?>