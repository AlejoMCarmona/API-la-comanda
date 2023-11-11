<?php

require_once './middlewares/Validadores.php';
require_once './models/Producto.php';
require_once './interfaces/IApiUsable.php';

class ProductoController implements IApiUsable {

    public function CargarUno($request, $response, $args) {
        $parametros = $request -> getParsedBody();

        if (Validadores::ValidarParametros($parametros, [ "nombre", "tipo", "sector", "precio" ])) { 
            $producto = new Producto($parametros['nombre'], $parametros['tipo'], $parametros['sector'], $parametros['precio']);
            $resultado = $producto -> CrearProducto();

            if (is_numeric($resultado)) {
                $payload = json_encode(array("Resultado" => "Se ha creado con éxito un producto con el ID {$resultado}"));
            } else {
                $payload = json_encode(array("ERROR" => "Hubo un error durante la incorporación del nuevo producto a la carta"));
            }
        } else {
            $payload = json_encode(array("ERROR" => "Los parámetros obligatorios para agregar un nuevo producto a la carta son: nombre, tipo y precio"));
        }

        $response -> getBody() -> write($payload);
        return $response -> withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args) {
        $lista = Producto::ObtenerTodosLosProductos();

        if (is_array($lista)) {
            $payload = json_encode(array("Lista" => $lista));
        } else {
            $payload = json_encode(array("ERROR" => "Hubo un error al obtener todos los productos"));
        }

        $response -> getBody() -> write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args) {
        if (Validadores::ValidarParametros($args, ["id"])) {
            $producto = Producto::ObtenerPorId($args["id"]);

            if ($producto) {
                $payload = json_encode(array("Producto" => $producto));
            } else {
                $payload = json_encode(array("ERROR" => "No se pudo encontrar un producto con el ID {$args["id"]}"));
            }
        } else {
            $payload = json_encode(array("ERROR" => "El parámetro 'id' es obligatorio para obtener un producto"));
        }

        $response ->getBody()-> write($payload);
        return $response -> withHeader('Content-Type', 'application/json');
    }

	public function BorrarUno($request, $response, $args) {
        return;
    }

	public function ModificarUno($request, $response, $args) {
        return;
    }
}

?>