<?php

require_once './middlewares/Validadores.php';
require_once './models/Pedido.php';
require_once './interfaces/IApiUsable.php';

class PedidoController implements IApiUsable {

    //TODO: AGREGAR IMAGEN
    public function CargarUno($request, $response, $args) {
        $parametros = $request -> getParsedBody();

        if (Validadores::ValidarParametros($parametros, [ "idMesa", "idProducto", "idEmpleado" ])) {
            $mesa = Mesa::ObtenerMesa($parametros["idMesa"]);
            if ($mesa != false && Producto::ObtenerProducto($parametros["idProducto"]) != false && Usuario::ObtenerUsuario($parametros["idEmpleado"]) != false) {
                $numeroIdentificacion = "";
                if ($mesa -> estado != "Cerrada") {
                    $numeroIdentificacion = Pedido::ObtenerUltimoPedidoPorMesa($parametros['idMesa']) -> numeroIdentificacion;
                } else {
                    $numeroIdentificacion = self::GenerarNumeroAlfanumericoIdentificacion(5);
                    $fotoMesa = $request -> getUploadedFiles()['foto'];
                    self::SubirFotoMesa($numeroIdentificacion, $fotoMesa);
                }
                $pedido = new Pedido($parametros['idMesa'], $parametros['idProducto'], $parametros['idEmpleado'], $numeroIdentificacion);
                $resultado = $pedido -> CrearPedido();
                $mesa -> CambiarEstado("Con cliente esperando pedido");

                if (is_numeric($resultado)) {
                    $payload = json_encode(array("Resultado" => "Se ha creado un pedido con el ID {$resultado}"));
                } else {
                    $payload = json_encode(array("ERROR" => "Hubo un error durante la creación del pedido"));
                }
            } else {
                $payload = json_encode(array("ERROR" => "Tanto la mesa seleccionada, como el producto y el empleado, deben existir para realizar un pedido"));
            }
        } else {
            $payload = json_encode(array("ERROR" => "Los parámetros obligatorios para cargar un nuevo pedido son: nombre, idMesa, idProducto y idEmpleado"));
        }

        $response -> getBody() -> write($payload);
        return $response -> withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args) {
        $lista = Pedido::ObtenerTodosLosPedidos();

        if (is_array($lista)) {
            $payload = json_encode(array("Lista" => $lista));
        } else {
            $payload = json_encode(array("ERROR" => "Hubo un error al obtener todos los pedidos"));
        }

        $response -> getBody() -> write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerPorNumeroIdentificacion($request, $response, $args) {
        $parametros = $request -> getParsedBody();

        if (Validadores::ValidarParametros($args, [ "numeroIdentificacion" ])) {
            $lista = Pedido::ObtenerPedidosPorNumeroIdentificacion($args["numeroIdentificacion"]);

            if (is_array($lista)) {
                $payload = json_encode(array("Lista" => $lista));
            } else {
                $payload = json_encode(array("ERROR" => "Hubo un error al obtener los pedidos de esta mesa"));
            }
        } else {
            $payload = json_encode(array("ERROR" => "El parámetro 'numeroIdentificacion' es obligatorio para traer los pedidos"));
        }

        $response -> getBody() -> write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerTiempoEstimadoPedido($request, $response, $args) {
        $parametros = $request -> getParsedBody();

        if (Validadores::ValidarParametros($args, [ "numeroIdentificacion" ])) {
            $tiempo = Pedido::ObtenerTiempoRestantePorNumeroIdentificacion($args["numeroIdentificacion"]);

            if (is_numeric($tiempo)) {
                $mensaje = "Faltan {$tiempo} minutos para tener tu pedido";
                if ($tiempo < 0) {
                    $tiempo *= -1; 
                    $mensaje = "Tu pedido se encuentra retrasado {$tiempo} minutos";
                }
                $payload = json_encode(array("Resultado" => $mensaje ));
            } else {
                $payload = json_encode(array("ERROR" => "Hubo un error al obtener el tiempo estimado del pedido"));
            }
        } else {
            $payload = json_encode(array("ERROR" => "El parámetro 'numeroIdentificacion' es obligatorio para traer el tiempo de pedido"));
        }

        $response -> getBody() -> write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args) {
        return;
    }

	public function BorrarUno($request, $response, $args) {
        return;
    }

	public function ModificarUno($request, $response, $args) {
        return;
    }

    private static function GenerarNumeroAlfanumericoIdentificacion($longitud) {
        $caracteres = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numeroIdentificacion = '';
        
        do {
            for ($i = 0; $i < $longitud; $i++) {
                $indiceRandom = mt_rand(0, strlen($caracteres) - 1);
                $numeroIdentificacion .= $caracteres[$indiceRandom];
            }
            $posiblePedidoExistente = Pedido::ObtenerPedidosPorNumeroIdentificacion($numeroIdentificacion);
        } while ($posiblePedidoExistente != false);
        
        return $numeroIdentificacion;
    }

    private static function SubirFotoMesa($numeroIdentificacion, $fotoMesa) {
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
            $nombreFoto = $numeroIdentificacion . date("Ymd") . '.' . $extension;
            $fotoMesa -> moveTo($path . '/' . $nombreFoto);
    
            $retorno = true;
        } else {
            $retorno = false;
        }

        return $retorno;
    }
}

?>