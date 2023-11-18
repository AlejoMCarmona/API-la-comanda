<?php

require_once './utils/Validadores.php';
require_once './models/Producto.php';
require_once './interfaces/IApiUsable.php';

class ProductoController implements IApiUsable {

    public function CargarUno($request, $response, $args) {
        $parametros = $request -> getParsedBody();

        if (Validadores::ValidarParametros($parametros, [ "nombre", "tipo", "sector", "precio" ]) && Validadores::ValidarEnum($parametros["tipo"], TiposComidaEnum::class)) { 
            $producto = new Producto($parametros['nombre'], $parametros['tipo'], $parametros['sector'], $parametros['precio']);
            $resultado = $producto -> CrearProducto();

            if (is_numeric($resultado)) {
                $payload = json_encode(array("Resultado" => "Se ha creado con éxito un producto con el ID {$resultado}"));
            } else {
                $payload = json_encode(array("ERROR" => "Hubo un error durante la incorporación del nuevo producto a la carta"));
            }
        } else {
            $payload = json_encode(array("ERROR" => "Los parámetros obligatorios para agregar un nuevo producto a la carta son: nombre, tipo (bebida/comida) y precio"));
        }

        $response -> getBody() -> write($payload);
        return $response -> withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args) {
        $lista = Producto::ObtenerTodosLosProductos(true);

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
            $producto = Producto::ObtenerPorID($args["id"], true);

            if ($producto) {
                $payload = json_encode(array("Producto" => $producto));
            } else {
                $payload = json_encode(array("ERROR" => "No se pudo encontrar un producto con el ID {$args["id"]}"));
            }
        } else {
            $payload = json_encode(array("ERROR" => "El parámetro 'id' es obligatorio para obtener un producto"));
        }

        $response -> getBody() -> write($payload);
        return $response -> withHeader('Content-Type', 'application/json');
    }

	public function BorrarUno($request, $response, $args) {
        if (Validadores::ValidarParametros($args, ["id"])) {
            $resultado = Producto::Borrar($args["id"]);

            if ($resultado) {
                $payload = json_encode(array("Resultado" => "Se ha dado de baja el producto con el ID {$args["id"]}"));
            } else {
                $payload = json_encode(array("ERROR" => "No se pudo encontrar un producto con el ID {$args["id"]}"));
            }
        } else {
            $payload = json_encode(array("ERROR" => "El parámetro 'id' es obligatorio para dar de baja un producto"));
        }

        $response -> getBody() -> write($payload);
        return $response -> withHeader('Content-Type', 'application/json');
    }

	public function ModificarUno($request, $response, $args) {
        $parametros = $request -> getParsedBody ();
        if (Validadores::ValidarParametros($parametros, [ "id", "tipo", "sector", "precio" ]) && Validadores::ValidarEnum($parametros["tipo"], TiposComidaEnum::class)) {
            $producto = Producto::ObtenerPorID($parametros["id"], true);
            if ($producto) {
                $producto -> tipo = $parametros["tipo"];
                $producto -> sector = $parametros["sector"];
                $producto -> precio = $parametros["precio"];
                if ($producto -> Modificar()) {
                    $payload = json_encode(array("producto modificado:" => $producto));
                } else {
                    $payload = json_encode(array("ERROR" => "No se pudo modificar el producto"));
                }
            } else {
                $payload = json_encode(array("ERROR" => "No se pudo encontrar el producto para realizar la modificación"));
            }
        } else {
            $payload = json_encode(array("ERROR" => "Los parámetros 'id', 'tipo', 'sector' y 'precio' son obligatorios para modificar un producto"));
        }
        
        $response -> getBody() -> write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function DescargarCSV($request, $response, $args) {
        $resultado = Producto::GuardarEnCSV();

        if ($resultado) {             
            $response = $response -> withHeader('Content-Type', 'octet-stream');
            $response = $response -> withHeader('Content-Disposition', 'attachment; filename="' . basename($resultado) . '"');
            $response = $response -> withHeader('Content-Length', filesize($resultado));
            readfile($resultado);

            $payload = json_encode(array("Resultado" => "El archivo de productos ha sido descargado con éxito"));
        } else {
            $payload = json_encode(array("ERROR" => "No se pudo descargar el archivo de productos"));
            $response = $response -> withHeader('Content-Type', 'application/json');
        }

        return $response;
    }

    public function CargarCSV($request, $response, $args) {
        $archivosCargados = $request -> getUploadedFiles();
        $archivo = $archivosCargados["listaProductos"];
        $payload = json_encode(array("ERROR" => "Hubo un error en la carga del archivo CSV de productos"));
        if ($archivo -> getError() === UPLOAD_ERR_OK) {
            if (Producto::CargarDesdeCSV($archivo -> getFilePath())) {
                $payload = json_encode(array("Resultado" => "El archivo de productos ha sido cargado con éxito en la base de datos"));
            };
        }
        $response -> getBody() -> write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}

?>