<?php

require_once './models/Usuario.php';
require_once './utils/Validadores.php';
require_once './utils/AutentificadorJWT.php';

class LoginController {
    public static function Login($request, $response, $args) {
        $parametros = $request -> getParsedBody();

        if (Validadores::ValidarParametros($parametros, [ "email", "clave" ])) {
            $resultado = Usuario::IniciarSesion($parametros["email"], $parametros["clave"]);
            if ($resultado["resultado"]) {
                $usuario = $resultado["mensaje"];
                $token = AutentificadorJWT::CrearToken([ "id" => $usuario -> id, "puesto" => $usuario -> puesto ]);
                $payload = json_encode(array("Token" => $token));
            } else {
                $payload = json_encode(array("ERROR" => $resultado["mensaje"]));
            }
        } else {
            $payload = json_encode(array("ERROR" => "Los paramétros 'email' y 'clave' son obligatorios para iniciar sesion"));
        }

        $response -> getBody() -> write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}

?>