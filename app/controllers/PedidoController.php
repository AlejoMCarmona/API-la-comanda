<?php

require_once './utils/Validadores.php';
require_once './models/Pedido.php';
require_once './interfaces/IApiUsable.php';

class PedidoController implements IApiUsable {

    public function CargarUno($request, $response, $args) {
        $parametros = $request -> getParsedBody();

        if (Validadores::ValidarParametros($parametros, [ "codigoMesa", "idProducto", "nombreCliente" ])) {
            $mesa = Mesa::ObtenerPorCodigoIdentificacion($parametros["codigoMesa"], true);
            $producto = Producto::ObtenerPorID($parametros["idProducto"], true);
            if ($mesa && $producto) { // Si la mesa y el producto existen
                $codigoIdentificacion = "";
                if ($mesa -> estado != "cerrada") { // Si la mesa ya está siendo ocupada
                    $codigoIdentificacion = Pedido::ObtenerUltimoPedidoPorMesa($parametros['codigoMesa']) -> codigoIdentificacion;
                } else {
                    $codigoIdentificacion = Validadores::GenerarNumeroAlfanumericoIdentificacion(5, "Pedido");
                }

                $pedido = new Pedido($parametros['codigoMesa'], $parametros['idProducto'], $parametros["nombreCliente"], $codigoIdentificacion);
                $resultado = $pedido -> CrearPedido();
                $mesa -> CambiarEstado("con cliente esperando pedido"); // Cambio el estado de la mesa a 'con cliente esperando pedido'

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
        $lista = Pedido::ObtenerTodosLosPedidos(true);

        if (is_array($lista)) {
            $payload = json_encode(array("Lista" => $lista));
        } else {
            $payload = json_encode(array("ERROR" => "Hubo un error al obtener todos los pedidos"));
        }

        $response -> getBody() -> write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerPorCodigoIdentificacion($request, $response, $args) {
        if (Validadores::ValidarParametros($args, [ "codigoIdentificacion" ])) {
            $lista = Pedido::ObtenerPorCodigoIdentificacion($args["codigoIdentificacion"], true);

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

    public function TraerTiempoRestante($request, $response, $args) {
        if (Validadores::ValidarParametros($args, [ "codigoMesa", "codigoIdentificacion"],)) {
            $tiempo = Pedido::ObtenerTiempoRestante($args["codigoMesa"], $args["codigoIdentificacion"]);
            if ($tiempo["codigo"] == -1) {
                $payload = json_encode(array("ERROR" => $tiempo["mensaje"]));
            } else {
                $payload = json_encode(array("Resultado" => $tiempo["mensaje"]));
            }
        } else {
            $payload = json_encode(array("ERROR" => "Los parámetros 'codigoMesa' y 'codigoIdentificacion' son obligatorios para traer el tiempo de pedido"));
        }

        $response -> getBody() -> write($payload);
        return $response -> withHeader('Content-Type', 'application/json');
    }

    public function TraerPedidosPendientesPorSector($request, $response, $args) {
        if (Validadores::ValidarParametros($args, [ "sector" ])) {
            $lista = Pedido::ObtenerPedidosPorSector($args["sector"]);

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
            $pedido = Pedido::ObtenerPorID($parametros["id"], true);
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
                    case 'listo para servir':
                        $mesa = Mesa::ObtenerPorCodigoIdentificacion($pedido -> codigoMesa);
                        $mesa -> CambiarEstado('con cliente comiendo');
                        $nuevoEstado = 'entregado';
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
            } else {
                $payload = json_encode(array("ERROR" => "No se pudo encontrar el pedido buscado o fue cancelado"));
            }
        } else {
            $payload = json_encode(array("ERROR" => "El parámetro 'id' es obligatorio para cambiar el estado de un pedido. Si el pedido se pasa a 'en preparacion', también debe pasarse 'tiempoPreparacion'"));
        }
        
        $response -> getBody() -> write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args) {
        if (Validadores::ValidarParametros($args, ["id"])) {
            $pedido = Pedido::ObtenerPorID($args["id"], true);

            if ($pedido) {
                $payload = json_encode(array("Pedido" => $pedido));
            } else {
                $payload = json_encode(array("ERROR" => "El pedido con el id {$args["id"]} no existe o fue cancelado"));
            }
        } else {
            $payload = json_encode(array("ERROR" => "El parámetro 'id' es obligatorio para traer un pedido"));
        }
        
        $response -> getBody() -> write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

	public function BorrarUno($request, $response, $args) {
        $parametros = $request -> getParsedBody ();

        if (Validadores::ValidarParametros($args, ["id"])) {
            $payload = json_encode(array("ERROR" => "Hubo un error al intentar borrar un pedido"));
            $pedido = Pedido::ObtenerPorID($args["id"], true);
            if ($pedido) {
                $nuevoEstado = "cancelado";
                if (Pedido::CambiarEstado($args["id"], $nuevoEstado)) {
                    $payload = json_encode(array("Resultado" => "El pedido con el id {$args["id"]} fue cancelado"));
                }       
            } else {
            $payload = json_encode(array("ERROR" => "El pedido con el id {$args["id"]} no existe o ya fue cancelado"));
            }
        } else {
            $payload = json_encode(array("ERROR" => "El parámetro 'id' es obligatorio para borrar un pedido."));
        }
        
        $response -> getBody() -> write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

	public function ModificarUno($request, $response, $args) {
        $parametros = $request -> getParsedBody();

        if (Validadores::ValidarParametros($parametros, [ "id", "idProducto", "nombreCliente" ])) {
            $pedido = Pedido::ObtenerPorID($parametros["id"], true);
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

    public function SubirFotoMesa($request, $response, $args) {
        $parametros = $request -> getParsedBody();
        $fotoMesa = $request -> getUploadedFiles()['foto'];

        if (Validadores::ValidarParametros($parametros, [ "codigoIdentificacion" ]) && $fotoMesa -> getError() === UPLOAD_ERR_OK) {
            $codigoIdentificacion = $parametros["codigoIdentificacion"];
            $pedidos = Pedido::ObtenerPorCodigoIdentificacion($codigoIdentificacion);
            if ($pedidos && count($pedidos) > 0) {
                $resultado = Pedido::SubirFotoMesa($fotoMesa, $codigoIdentificacion);
                if ($resultado) {
                    $payload = json_encode(array("Resultado" => "La foto de la mesa para el pedido {$codigoIdentificacion} se ha subido correctamente"));
                } else {
                    $payload = json_encode(array("ERROR" => "Ya se ha subido una foto para el pedido de esta mesa"));
                }  
            } else {
                $payload = json_encode(array("ERROR" => "No existe un pedido con el código de identificación {$codigoIdentificacion}"));
            }
        } else {
            $payload = json_encode(array("ERROR" => "El parámetro 'codigoIdentificacion' y una foto son obligatorios para cargar la foto de la mesa"));
        }

        $response -> getBody() -> write($payload);
        return $response -> withHeader('Content-Type', 'application/json');
    }
}

?>