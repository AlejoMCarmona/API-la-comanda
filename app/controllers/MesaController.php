<?php

require_once './models/Mesa.php';
require_once './interfaces/IApiUsable.php';
require_once './middlewares/Validadores.php';

class MesaController implements IApiUsable {

    public function CargarUno($request, $response, $args) {
        $parametros = $request -> getParsedBody();
        if (Validadores::ValidarParametros($parametros, [ "asientos" ])) {
            $codigoIdentificacion = Validadores::GenerarNumeroAlfanumericoIdentificacion(5, "Mesa");
            $mesa = new Mesa($codigoIdentificacion, $parametros["asientos"]); 
            $resultado = $mesa -> CrearMesa();
            if (is_numeric($resultado)) {
                $payload = json_encode(array("Resultado" => "Se ha creado con éxito una mesa con el ID {$resultado}"));
            } else {
                $payload = json_encode(array("ERROR" => "Hubo un error durante la carga de la mesa"));
            }
        } else {
            $payload = json_encode(array("ERROR" => "El parámetro 'asientos' es obligatorio para dar de alta una mesa"));
        }


        $response -> getBody() -> write($payload);
        return $response -> withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args) {
        $lista = Mesa::ObtenerTodasLasMesas();

        if (is_array($lista)) {
            $payload = json_encode(array("Lista" => $lista));
        } else {
            $payload = json_encode(array("ERROR" => "Hubo un error al obtener todas las mesas"));
        }

        $response -> getBody() -> write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args) {
        if (Validadores::ValidarParametros($args, [ "codigoMesa" ])) {
            $mesa = Mesa::ObtenerPorCodigoIdentificacion($args["codigoMesa"]);

            if ($mesa) {
                $payload = json_encode(array("Mesa" => $mesa));
            } else {
                $payload = json_encode(array("ERROR" => "No se pudo encontrar una mesa con el código {$args["codigoMesa"]}"));
            }
        } else {
            $payload = json_encode(array("ERROR" => "El parámetro 'codigoMesa' es obligatorio para obtener una mesa"));
        }

        $response -> getBody() -> write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function CambiarEstado($request, $response, $args) {
        $parametros = $request -> getParsedBody();
        
        if (Validadores::ValidarParametros($parametros, [ "codigoMesa" ])) {
            $mesa = Mesa::ObtenerPorCodigoIdentificacion($parametros["codigoMesa"]);
            if ($mesa) {
                $nuevoEstado = false;
                switch($mesa -> estado) {
                    case 'cerrada':
                        $nuevoEstado = "con cliente esperando pedido";
                    break;
                    case 'con cliente esperando pedido':
                        $nuevoEstado = 'con cliente comiendo';
                    break;
                    case 'con cliente comiendo':
                        $nuevoEstado = 'con cliente pagando';
                    break;
                    case 'con cliente pagando':
                        $queryParams = $request -> getQueryParams();
                        if ($queryParams["puesto"] == "socio") {
                            $nuevoEstado = 'cerrada';
                        }
                    break;
                    default:
                        $nuevoEstado = false;
                    break;
                }

                if ($nuevoEstado) {
                    $mesa -> CambiarEstado($nuevoEstado);
                    $payload = json_encode(array("Resultado" => "La mesa con código {$parametros["codigoMesa"]} ahora tiene el estado '{$nuevoEstado}'"));
                } else {
                    $payload = json_encode(array("ERROR" => "Hubo un error en el cambio del estado"));
                }
            } else {
                $payload = json_encode(array("ERROR" => "No se pudo encontrar una mesa con el código {$parametros["codigoMesa"]}"));
            }
        } else {
            $payload = json_encode(array("ERROR" => "El parámetro 'codigoMesa' es obligatorio para modificar el estado de una mesa"));
        }

        $response -> getBody() -> write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args) {
        if (Validadores::ValidarParametros($args, ["codigoIdentificacion"])) {
            $resultado = Mesa::Borrar($args["codigoIdentificacion"]);

            if ($resultado) {
                $payload = json_encode(array("Resultado" => "Se ha dado de baja la mesa con el codigo de identificacion {$args["codigoIdentificacion"]}"));
            } else {
                $payload = json_encode(array("ERROR" => "No se pudo encontrar una mesa con el codigo de identificacion {$args["codigoIdentificacion"]}"));
            }
        } else {
            $payload = json_encode(array("ERROR" => "El parámetro 'id' es obligatorio para dar de baja una mesa"));
        }

        $response->getBody() -> write($payload);
        return $response -> withHeader('Content-Type', 'application/json');
    }

	public function ModificarUno($request, $response, $args) {
        $parametros = $request -> getParsedBody ();
        if (Validadores::ValidarParametros($parametros, [ "id", "asientos" ])) {
            $mesa = Mesa::ObtenerPorID($parametros["id"]);
            if ($mesa) {
                $mesa -> asientos = $parametros["asientos"];
                if ($mesa -> Modificar()) {
                    $payload = json_encode(array("mesa modificada:" => $mesa));
                } else {
                    $payload = json_encode(array("ERROR" => "No se pudo modificar la mesa"));
                }
            } else {
                $payload = json_encode(array("ERROR" => "No se pudo encontrar la mesa para realizar la modificación"));
            }
        } else {
            $payload = json_encode(array("ERROR" => "Los parámetros 'id' y 'asientos' son obligatorios para modificar una mesa"));
        }
        
        $response -> getBody() -> write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}

?>