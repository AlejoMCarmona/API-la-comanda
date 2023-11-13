<?php

class Encuesta {
    public $id;
    public $codigoPedido;
    public $puntuacionMesa;
    public $puntuacionRestaurante;
    public $puntuacionMozo;
    public $puntuacionCocinero;
    public $descripcionExperiencia;
    public $fecha;

    public function __construct() {
        $parametros = func_get_args();
        $numero_parametros = func_num_args();
        $funcion_constructor = '__construct' . $numero_parametros;
        if (method_exists($this, $funcion_constructor)) {
            call_user_func_array(array($this, $funcion_constructor), $parametros);
        }
    }

    public function __construct8($id, $codigoPedido, $puntuacionMesa, $puntuacionRestaurante, $puntuacionMozo, $puntuacionCocinero, $descripcionExperiencia, $fecha) {
        $this -> id = $id;
        $this -> codigoPedido = $codigoPedido;
        $this -> puntuacionMesa = $puntuacionMesa;
        $this -> puntuacionRestaurante = $puntuacionRestaurante;
        $this -> puntuacionMozo = $puntuacionMozo;
        $this -> puntuacionCocinero = $puntuacionCocinero;
        $this -> descripcionExperiencia = $descripcionExperiencia;
        $this -> fecha = $fecha;
    }

    public function __construct6($codigoPedido, $puntuacionMesa, $puntuacionRestaurante, $puntuacionMozo, $puntuacionCocinero, $descripcionExperiencia) {
        $this -> __construct8(0, $codigoPedido, $puntuacionMesa, $puntuacionRestaurante, $puntuacionMozo, $puntuacionCocinero, $descripcionExperiencia, "");
    }

    public function CrearEncuesta() {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::ObtenerInstancia();
        $consulta = $objetoAccesoDatos -> PrepararConsulta("INSERT INTO encuestas (codigoPedido, puntuacionMesa, puntuacionRestaurante, puntuacionMozo, puntuacionCocinero, descripcionExperiencia) VALUES (:codigoPedido, :puntuacionMesa, :puntuacionRestaurante, :puntuacionMozo, :puntuacionCocinero, :descripcionExperiencia)");
        $consulta->bindParam(":codigoPedido", $this -> codigoPedido);
        $consulta->bindParam(":puntuacionMesa", $this -> puntuacionMesa);
        $consulta->bindParam(":puntuacionRestaurante", $this -> puntuacionRestaurante);
        $consulta->bindParam(":puntuacionMozo", $this -> puntuacionMozo);
        $consulta->bindParam(":puntuacionCocinero", $this -> puntuacionCocinero);
        $consulta->bindParam(":descripcionExperiencia", $this -> descripcionExperiencia);
    
        $resultado = $consulta -> execute();
    
        if ($resultado) {
            $retorno = $objetoAccesoDatos -> ObtenerUltimoId();
        }
    
        return $retorno;
    }
}

?>