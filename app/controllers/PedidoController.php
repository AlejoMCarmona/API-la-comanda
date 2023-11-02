<?php

require_once './middlewares/Validadores.php';
require_once './models/Pedido.php';
require_once './interfaces/IApiUsable.php';

class PedidoController implements IApiUsable {

    public function CargarUno($request, $response, $args) {
        $parametros = $request -> getParsedBody();

        if (Validadores::ValidarParametros($parametros, [ "idMesa", "idProducto", "idEmpleado" ])) {
            $mesa = Mesa::ObtenerMesa($parametros["idMesa"]);
            if (get_class($mesa) == "Mesa" && get_class(Producto::ObtenerProducto($parametros["idProducto"])) == "Producto" && get_class(Empleado::ObtenerEmpleado($parametros["idEmpleado"])) == "Empleado") {
                $pedido = new Pedido($parametros['idMesa'], $parametros['idProducto'], $parametros['idEmpleado'], "F00000"); // Hacer identificador de 6 cifras
                $resultado = $pedido -> CrearPedido();
                $mesa -> CambiarEstado("Con cliente esperando pedido");

                if (is_numeric($resultado)) {
                    $payload = json_encode(array("Resultado" => "Se ha creado un pedido con el ID {$resultado}"));
                } else {
                    $payload = json_encode(array("ERROR" => "Hubo un error durante la creación del pedido"));
                }
            }
        } else {
            $payload = json_encode(array("ERROR" => "Los parámetros obligatorios para cargar un nuevo empleado son: nombre, idMesa, idProducto y idEmpleado"));
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