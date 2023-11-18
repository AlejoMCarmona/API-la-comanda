<?php

class Producto {
    public $id;
    public $nombre;
    public $tipo;
    public $sector;
    public $precio;
    public $activo;
    public $fechaIncorporacion;

    public function __construct() {
        $parametros = func_get_args();
        $numero_parametros = func_num_args();
        $funcion_constructor = '__construct' . $numero_parametros;
        if (method_exists($this, $funcion_constructor)) {
            call_user_func_array(array($this, $funcion_constructor), $parametros);
        }
    }

    public function __construct7($id, $nombre, $tipo, $sector, $precio, $activo, $fechaIncorporacion) {
        $this -> id = $id;
        $this -> nombre = $nombre;
        $this -> tipo = $tipo;
        $this -> sector = $sector;
        $this -> precio = $precio;
        $this -> activo = $activo;
        $this -> fechaIncorporacion = $fechaIncorporacion;
    }

    public function __construct4($nombre, $tipo, $sector, $precio) {
        $this -> __construct7(0, $nombre, $tipo, $sector, $precio, true, "");
    }

    public function CrearProducto($cargarActivo = false) {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::ObtenerInstancia();
        if ($cargarActivo) {
            $query = "INSERT INTO Productos (nombre, tipo, sector, precio, activo) VALUES (:nombre, :tipo, :sector, :precio, :activo)";
        } else {
            $query = "INSERT INTO Productos (nombre, tipo, sector, precio) VALUES (:nombre, :tipo, :sector, :precio)";
        }
        $consulta = $objetoAccesoDatos -> PrepararConsulta($query);
        $consulta -> bindParam(':nombre', $this -> nombre);
        $consulta -> bindParam(':tipo', $this -> tipo);
        $consulta -> bindParam(':sector', $this -> sector);
        if ($cargarActivo) $consulta -> bindParam(':activo', $this -> activo);
        $consulta -> bindParam(':precio', $this -> precio);

        $resultado = $consulta -> execute();
        if ($resultado) {
            $retorno = $objetoAccesoDatos -> ObtenerUltimoId();
        }
        return $retorno;
    }

    public static function ObtenerTodosLosProductos($soloActivos = false) {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::ObtenerInstancia();
        if ($soloActivos) {
            $query = "SELECT * FROM Productos WHERE activo = TRUE";
        } else {
            $query = "SELECT * FROM Productos";
        }
        $consulta = $objetoAccesoDatos -> PrepararConsulta($query);
        $resultado = $consulta->execute();
        if ($resultado) {
            $retorno = $consulta->fetchAll(PDO::FETCH_CLASS, 'Producto');
        }
        return $retorno;
    }

    public static function ObtenerPorID($id, $soloActivos = false) {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::ObtenerInstancia();
        if ($soloActivos) {
            $query = "SELECT * FROM Productos WHERE activo = TRUE AND id = :id";
        } else {
            $query = "SELECT * FROM productos WHERE id = :id";
        }
        $consulta = $objetoAccesoDatos -> PrepararConsulta($query);
        $consulta -> bindParam(':id', $id);
        $resultado = $consulta -> execute();
        if ($resultado) {
            $retorno = $consulta -> fetchObject('Producto');
        }
        return $retorno;
    }

    public static function Borrar($id) {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::ObtenerInstancia();
        $consulta = $objetoAccesoDatos -> PrepararConsulta("UPDATE productos SET activo = FALSE WHERE id = :id");
        $consulta -> bindParam(':id', $id);
        $resultado = $consulta -> execute();
        if ($resultado) {
            $retorno = true;
        }
        return $retorno;
    }

    public function Modificar() {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::ObtenerInstancia();
        $consulta = $objetoAccesoDatos -> PrepararConsulta("UPDATE productos SET tipo = :tipo, sector = :sector, precio = :precio WHERE id = :id");
        $consulta -> bindParam(':id', $this -> id);
        $consulta -> bindParam(':tipo', $this -> tipo);
        $consulta -> bindParam(':sector', $this -> sector);
        $consulta -> bindParam(':precio', $this -> precio);
        $resultado = $consulta -> execute();

        if ($resultado) {
            $retorno = true;
        }

        return $retorno;
    }

    public static function GuardarEnCSV() {
        $retorno = false;

        $objetoAccesoDatos = AccesoDatos::ObtenerInstancia();
        $consulta = $objetoAccesoDatos -> PrepararConsulta("SELECT * FROM productos");
        $resultado = $consulta -> execute();

        if ($resultado) {
            $listaProductos = $consulta -> fetchAll(PDO::FETCH_CLASS, 'Producto');
            $timestamp = time();
            $nombreArchivoTemporal = sys_get_temp_dir() . "\listaProductos_{$timestamp}.csv";
            // echo $nombreArchivoTemporal;
            $archivo = fopen($nombreArchivoTemporal, "w");
            fputcsv($archivo, [ "id", "nombre", "tipo", "sector", "precio", "activo", "fechaIncorporacion" ]);
            foreach ($listaProductos as $producto) {
                fputcsv($archivo, (array)$producto);
            }
            fclose($archivo);
            $retorno = $nombreArchivoTemporal;
        }

        return $retorno;
    }

    public static function CargarDesdeCSV($rutaArchivo) {
        $retorno = false;
        if (file_exists($rutaArchivo)) {          
            $archivo = fopen($rutaArchivo, "r");
            $listaProductos = array();
            $cabecera = fgetcsv($archivo);
            if (count($cabecera) == 7) { // El archivo debe poseer la estructura de la entidad completa, por más que algunos datos los complete la base de datos
                while(!feof($archivo)) {
                    $productoArray = fgetcsv($archivo);
                    if ($productoArray != NULL) {
                        $producto = new Producto($productoArray[0], $productoArray[1], $productoArray[2], $productoArray[3], $productoArray[4], $productoArray[5], $productoArray[6]);
                        if ($productoArray[5] == "") { // Si 'activo' está vacio, entonces queda el valor por default
                            $producto -> CrearProducto();
                        } else {
                            $producto -> CrearProducto(true);
                        }
                    }
                }
                $retorno = true;
            }
            fclose($archivo);

        }
        return $retorno;
    }

}

?>