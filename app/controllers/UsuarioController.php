<?php

require_once './utils/Validadores.php';
require_once './utils/Enums.php';
require_once './models/Usuario.php';
require_once './interfaces/IApiUsable.php';

class UsuarioController implements IApiUsable {

    public function CargarUno($request, $response, $args) {
        $parametros = $request -> getParsedBody();
    
        if (!Validadores::ValidarParametros($parametros, ["nombre", "apellido", "dni", "email", "clave", "puesto"]) || !Validadores::ValidarEnum($parametros["puesto"], PuestosEnum::class)) {
            $payload = json_encode(array("ERROR" => "Los parámetros obligatorios para cargar un nuevo usuario son: nombre, apellido, dni, email, clave y puesto (cocinero/mozo/bartender/cervecero/socio)"));
        } else {
            $resultado = false;
    
            if (!Usuario::ExisteElUsuario($parametros["dni"], $parametros["email"])) {
                // Si NO existe un usuario con mismo DNI o email, se intenta agregar:
                if ($parametros["puesto"] != 'mozo' && $parametros["puesto"] != 'socio') {
                    // Si el nuevo usuario no es ni socio ni mozo
                    if (!Validadores::ValidarParametros($parametros, ["sector"]) || !Validadores::ValidarEnum($parametros["sector"], SectoresEnum::class)) {
                        $payload = json_encode(array("ERROR" => "Se debe especificar un sector válido (cocina/candyBar/barraChoperas/barraTragos) si el empleado no es un mozo ni un socio"));
                    } else {
                        $usuario = new Usuario($parametros['nombre'], $parametros['apellido'], $parametros['dni'], $parametros['email'], $parametros['clave'], $parametros['puesto'], $parametros['sector']);
                        $resultado = $usuario -> CrearUsuario();
                    }
                } else {
                    // Si el nuevo usuario es un mozo o un socio
                    $usuario = new Usuario($parametros['nombre'], $parametros['apellido'], $parametros['dni'], $parametros['email'], $parametros['clave'], $parametros['puesto']);
                    $resultado = $usuario -> CrearUsuario();
                }
        
                if (is_numeric($resultado)) {
                    $payload = json_encode(array("Resultado" => "Se ha creado con éxito un usuario con el ID {$resultado}"));
                } elseif (!isset($payload)) {
                    $payload = json_encode(array("ERROR" => "Hubo un error durante el alta del nuevo usuario"));
                }
            } else {
                $payload = json_encode(array("ERROR" => "El email o número de documento proporcionado para el usuario ya existe"));
            }

        }
    
        $response -> getBody() -> write($payload);
        return $response -> withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args) {
        $lista = Usuario::ObtenerTodosLosUsuarios(true);

        if (is_array($lista)) {
            $payload = json_encode(array("Lista" => $lista));
        } else {
            $payload = json_encode(array("ERROR" => "Hubo un error al obtener todos los usuarios"));
        }

        $response -> getBody() -> write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerPorPuesto($request, $response, $args) {
        if (Validadores::ValidarParametros($args, [ "puesto" ]) && Validadores::ValidarEnum($args["puesto"], PuestosEnum::class)) {
            $lista = Usuario::ObtenerUsuariosPorPuesto($args["puesto"], true);

            if (is_array($lista)) {
                $payload = json_encode(array("Lista" => $lista));
            } else {
                $payload = json_encode(array("ERROR" => "No se encontraron usuarios con ese puesto"));
            }
        } else {
            $payload = json_encode(array("ERROR" => "El parámetro 'puesto' (cocinero/mozo/bartender/cervecero/socio) es obligatorio para traer a los empleados por puesto"));
        }

        $response -> getBody() -> write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args) {
        if (Validadores::ValidarParametros($args, ["dni"])) {
            $usuario = Usuario::ObtenerPorDNI($args["dni"], true);

            if ($usuario) {
                $payload = json_encode(array("Usuario" => $usuario));
            } else {
                $payload = json_encode(array("ERROR" => "No se pudo encontrar un usuario con el DNI {$args["dni"]}"));
            }
        } else {
            $payload = json_encode(array("ERROR" => "El parámetro 'dni' es obligatorio para obtener un usuario"));
        }

        $response ->getBody() -> write($payload);
        return $response -> withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args) {
        if (Validadores::ValidarParametros($args, ["dni"])) {
            $resultado = Usuario::Borrar($args["dni"]);

            if ($resultado) {
                $payload = json_encode(array("Resultado" => "Se ha dado de baja el usuario con el dni {$args["dni"]}"));
            } else {
                $payload = json_encode(array("ERROR" => "No se pudo encontrar un usuario con el dni {$args["dni"]}"));
            }
        } else {
            $payload = json_encode(array("ERROR" => "El parámetro 'dni' es obligatorio para dar de baja un usuario"));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }


	public function ModificarUno($request, $response, $args) {
        $parametros = $request -> getParsedBody ();

        if (Validadores::ValidarParametros($parametros, [ "id", "nombre", "apellido", "dni", "email", "puesto" ]) && Validadores::ValidarEnum($parametros["puesto"], PuestosEnum::class)) {
            $usuario = Usuario::ObtenerPorID($parametros["id"], true);
            if ($usuario) {
                $nuevoPuestoUsuario = $parametros["puesto"];
                if ($nuevoPuestoUsuario == 'mozo' || $nuevoPuestoUsuario == 'socio') {
                    $usuario -> sector = NULL;
                } else if ($nuevoPuestoUsuario != 'mozo' && $nuevoPuestoUsuario != 'socio') {
                    if (Validadores::ValidarParametros($parametros, ["sector"]) && Validadores::ValidarEnum($parametros["sector"], SectoresEnum::class)) {
                        $usuario -> sector = $parametros["sector"];
                    } else {
                        $payload = json_encode(array("ERROR" => "Se debe especificar un sector (cocina/candyBar/barraChoperas/barraTragos) si el empleado no es un mozo o un socio"));
                    }
                }

                if (!isset($payload)) {
                    $usuario -> nombre = $parametros["nombre"];                
                    $usuario -> apellido = $parametros["apellido"];                
                    $usuario -> dni = $parametros["dni"];                
                    $usuario -> email = $parametros["email"];                
                    $usuario -> puesto = $parametros["puesto"];
                    if ($usuario -> Modificar()) {
                        $payload = json_encode(array("Usuario modificado:" => $usuario));
                    } else {
                        $payload = json_encode(array("ERROR" => "No se pudo modificar el usuario"));
                    }
                }
            } else {
                $payload = json_encode(array("ERROR" => "No se pudo encontrar al usuario para realizar la modificación"));
            }
        } else {
            $payload = json_encode(array("ERROR" => "El parámetro 'id', 'nombre', 'apellido', 'dni', 'email' y 'puesto' (cocinero/mozo/bartender/cervecero/socio) son obligatorios para modificar un usuario"));
        }
        
        $response -> getBody() -> write($payload);
        return $response -> withHeader('Content-Type', 'application/json');
    }
}

?>