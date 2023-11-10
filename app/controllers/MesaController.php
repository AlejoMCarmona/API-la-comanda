<?php

require_once './models/Mesa.php';
require_once './interfaces/IApiUsable.php';
require_once './middlewares/Validadores.php';

class MesaController implements IApiUsable {

    public function CargarUno($request, $response, $args) {
        $codigoIdentificacion = Validadores::GenerarNumeroAlfanumericoIdentificacion(5, "Mesa");
        $mesa = new Mesa($codigoIdentificacion);

        $resultado = $mesa -> CrearMesa();
        if (is_numeric($resultado)) {
            $payload = json_encode(array("Resultado" => "Se ha creado con éxito una mesa con el ID {$resultado}"));
        } else {
            $payload = json_encode(array("ERROR" => "Hubo un error durante la carga de la mesa"));
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