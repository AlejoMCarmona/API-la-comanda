<?php

require_once './middlewares/Validadores.php';
require_once './models/Usuario.php';
require_once './interfaces/IApiUsable.php';

class UsuarioController implements IApiUsable {

    public function CargarUno($request, $response, $args) {
        $parametros = $request -> getParsedBody();

        if (Validadores::ValidarParametros($parametros, [ "nombre", "apellido", "dni", "email", "clave", "puesto" ])) {
            $resultado = false;
            $sector = "";
            if ($parametros["puesto"] != 'mozo') {
                if (Validadores::ValidarParametros($parametros, [ "sector" ])) {
                    $usuario = new Usuario($parametros['nombre'], $parametros['apellido'], $parametros['dni'], $parametros['email'], $parametros['clave'], $parametros['puesto'], $parametros['sector']);
                    $resultado = $usuario -> CrearUsuario();
                } else {
                    $payload = json_encode(array("ERROR" => "Se debe especificar un sector si el empleado no es un mozo"));
                }
            } else {
                $usuario = new Usuario($parametros['nombre'], $parametros['apellido'], $parametros['dni'], $parametros['email'], $parametros['clave'], $parametros['puesto']);
                $resultado = $usuario -> CrearUsuario();
            }

            if (is_numeric($resultado)) {
                $payload = json_encode(array("Resultado" => "Se ha creado con éxito un usuario con el ID {$resultado}"));
            } else {
                if (!isset($payload)) $payload = json_encode(array("ERROR" => "Hubo un error durante el alta del nuevo usuario"));              
            }
        } else {
            $payload = json_encode(array("ERROR" => "Los parámetros obligatorios para cargar un nuevo usuario son: nombre, apellido, dni, email, clave y puesto"));
        }

        $response -> getBody() -> write($payload);
        return $response -> withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args) {
        $lista = Usuario::ObtenerTodosLosUsuarios();

        if (is_array($lista)) {
            $payload = json_encode(array("Lista" => $lista));
        } else {
            $payload = json_encode(array("ERROR" => "Hubo un error al obtener todos los usuarios"));
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

    public function IniciarSesion($request, $response, $args) {
        $parametros = $request -> getParsedBody();

        if (Validadores::ValidarParametros($parametros, [ "email", "clave" ])) {
            $resultado = Usuario::IniciarSesion($parametros["email"], $parametros["clave"]);

            if (is_string($resultado)) {
                $payload = json_encode(array("Resultado" => $resultado));
            } else {
                $payload = json_encode(array("ERROR" => "Hubo un error al intentar iniciar sesion"));
            }
        }

        $response -> getBody() -> write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}

?>