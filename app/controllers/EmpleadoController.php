<?php

require_once './middlewares/Validadores.php';
require_once './models/Empleado.php';
require_once './interfaces/IApiUsable.php';

class EmpleadoController implements IApiUsable {

    public function CargarUno($request, $response, $args) {
        $parametros = $request -> getParsedBody();

        if (Validadores::ValidarParametros($parametros, [ "nombre", "apellido", "dni", "puesto", "sector" ])) { 
            $empleado = new Empleado($parametros['nombre'], $parametros['apellido'], $parametros['dni'], $parametros['puesto'], $parametros['sector']);
            $resultado = $empleado -> CrearEmpleado();

            if (is_numeric($resultado)) {
                $payload = json_encode(array("Resultado" => "Se ha creado con éxito un empleado con el ID {$resultado}"));
            } else {
                $payload = json_encode(array("ERROR" => "Hubo un error durante el alta del nuevo empleado"));
            }
        } else {
            $payload = json_encode(array("ERROR" => "Los parámetros obligatorios para cargar un nuevo empleado son: nombre, apellido, dni, puesto y sector"));
        }

        $response -> getBody() -> write($payload);
        return $response -> withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args) {
        $lista = Empleado::ObtenerTodosLosEmpleados();

        if (is_array($lista)) {
            $payload = json_encode(array("Lista" => $lista));
        } else {
            $payload = json_encode(array("ERROR" => "Hubo un error al obtener todos los empleados"));
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
}

?>