<?php

class Producto {
    public $id;
    public $nombre;
    public $tipo;
    public $sector;
    public $precio;
    public $fechaIncorporacion;

    public function __construct() {
        $parametros = func_get_args();
        $numero_parametros = func_num_args();
        $funcion_constructor = '__construct' . $numero_parametros;
        if (method_exists($this, $funcion_constructor)) {
            call_user_func_array(array($this, $funcion_constructor), $parametros);
        }
    }

    public function __construct6($id, $nombre, $tipo, $sector, $precio, $fechaIncorporacion) {
        $this -> id = $id;
        $this -> nombre = $nombre;
        $this -> tipo = $tipo;
        $this -> sector = $sector;
        $this -> precio = $precio;
        $this -> fechaIncorporacion = $fechaIncorporacion;
    }

    public function __construct4($nombre, $tipo, $sector, $precio) {
        $this -> __construct6(0, $nombre, $tipo, $sector, $precio,"");
    }

    public function CrearProducto() {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::ObtenerInstancia();
        $consulta = $objetoAccesoDatos -> PrepararConsulta("INSERT INTO Productos (nombre, tipo, sector, precio) VALUES (:nombre, :tipo, :sector, :precio)");
        $consulta->bindParam(':nombre', $this -> nombre);
        $consulta->bindParam(':tipo', $this -> tipo);
        $consulta->bindParam(':sector', $this -> sector);
        $consulta->bindParam(':precio', $this -> precio);

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
}

?>