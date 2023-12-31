<?php

require_once './models/Encuesta.php';
require_once './models/Pedido.php';
require_once './models/Mesa.php';
require_once './utils/Validadores.php';

class EncuestaController {
    // Se pueden realizar muchas reseñas para un mismo pedido, puesto que los comensales pueden ser varios
    public function CargarUno($request, $response, $args) {
        $parametros = $request -> getParsedBody();
        if (Validadores::ValidarParametros($parametros, [ "codigoPedido", "puntuacionMesa", "puntuacionRestaurante", "puntuacionMozo", "puntuacionCocinero", "descripcionExperiencia" ]) && strlen($parametros["descripcionExperiencia"]) <= 66) {
            $pedidos = Pedido::ObtenerPorCodigoIdentificacion($parametros["codigoPedido"]);
            if (is_array($pedidos) && count($pedidos) > 0) {
                $codigoMesa = $pedidos[0] -> codigoMesa;
                $mesa = Mesa::ObtenerPorCodigoIdentificacion($codigoMesa);
                if ($mesa && ($mesa -> estado == 'con cliente pagando' || $mesa -> estado == 'cerrada')) {
                    $encuesta = new Encuesta($parametros["codigoPedido"], $parametros["puntuacionMesa"], $parametros["puntuacionRestaurante"], $parametros["puntuacionMozo"], $parametros["puntuacionCocinero"], $parametros["descripcionExperiencia"]); 
                    $resultado = $encuesta -> CrearEncuesta();
                    if (is_numeric($resultado)) {
                        $payload = json_encode(array("Resultado" => "Se ha creado con éxito una encuesta con el ID {$resultado}"));
                    } else {
                        $payload = json_encode(array("ERROR" => "Hubo un error durante la creación de la encuesta"));
                    }
                } else {
                    $payload = json_encode(array("ERROR" => "La encuesta aún no está habilitada"));
                }
            } else {
                $payload = json_encode(array("Resultado" => "No se encontraron los pedidos con el código {$parametros["codigoPedido"]}"));
            }
        } else {
            $payload = json_encode(array("ERROR" => "Los parámetros 'codigoPedido', 'puntuacionMesa', 'puntuacionRestaurante', 'puntuacionMozo', 'puntuacionCocinero', 'descripcionExperiencia' (66 caracteres máximo) son obligatorios para dar de crear una encuesta"));
        }

        $response -> getBody() -> write($payload);
        return $response -> withHeader('Content-Type', 'application/json');
    }
}

?>