<?php

class Producto {
    public $id;
    public $nombre;
    public $tipo;
    public $precio;
    public $tiempoPreparacion;
    public $fechaIncorporacion;

    public function __construct() {
        $parametros = func_get_args();
        $numero_parametros = func_num_args();
        $funcion_constructor = '__construct' . $numero_parametros;
        if (method_exists($this, $funcion_constructor)) {
            call_user_func_array(array($this, $funcion_constructor), $parametros);
        }
    }

    public function __construct6($id, $nombre, $tipo, $precio, $tiempoPreparacion, $fechaIncorporacion) {
        $this -> id = $id;
        $this -> nombre = $nombre;
        $this -> tipo = $tipo;
        $this -> precio = $precio;
        $this -> tiempoPreparacion = $tiempoPreparacion;
        $this -> fechaIncorporacion = $fechaIncorporacion;
    }

    public function __construct4($nombre, $tipo, $precio, $tiempoPreparacion) {
        $this -> __construct6(0, $nombre, $tipo, $precio, $tiempoPreparacion,"");
    }

    public function CrearProducto() {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::ObtenerInstancia();
        $consulta = $objetoAccesoDatos->PrepararConsulta("INSERT INTO Productos (nombre, tipo, precio, tiempoPreparacion) VALUES (:nombre, :tipo, :precio, :tiempoPreparacion)");
        $consulta->bindParam(':nombre', $this -> nombre);
        $consulta->bindParam(':tipo', $this -> tipo);
        $consulta->bindParam(':precio', $this -> precio);
        $consulta->bindParam(':tiempoPreparacion', $this -> tiempoPreparacion);

        $resultado = $consulta -> execute();
        if ($resultado) {
            $retorno = $objetoAccesoDatos -> ObtenerUltimoId();
        }
        return $retorno;
    }

    public static function ObtenerTodosLosProductos() {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::ObtenerInstancia();
        $consulta = $objetoAccesoDatos -> PrepararConsulta("SELECT * FROM Productos");
        $resultado = $consulta->execute();
        if ($resultado) {
            $retorno = $consulta->fetchAll(PDO::FETCH_CLASS, 'Producto');
        }
        return $retorno;
    }

    public static function ObtenerProducto($id) {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::ObtenerInstancia();
        $consulta = $objetoAccesoDatos -> PrepararConsulta("SELECT * FROM productos WHERE id = :id");
        $consulta -> bindParam(':id', $id);
        $resultado = $consulta -> execute();
        if ($resultado) {
            $retorno = $consulta -> fetchObject('Producto');
        }
        return $retorno;
    }
}

?>