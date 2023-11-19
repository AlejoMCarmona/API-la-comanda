<?php

class Validadores {
    public static function ValidarParametros($arrayAsociativo, $clavesRequeridas) {
        if ($arrayAsociativo == NULL || $clavesRequeridas == NULL) return false;

        foreach ($clavesRequeridas as $clave) {
            if (!array_key_exists($clave, $arrayAsociativo) || empty($arrayAsociativo[$clave])) {
                return false;
            }
        }
        return true;
    }

    public static function ValidarEnum($valor, $claseEnum) {
        $reflector = new ReflectionClass($claseEnum);
        $valoresEnum = $reflector -> getConstants();
        return in_array($valor, $valoresEnum);
    }

    public static function GenerarNumeroAlfanumericoIdentificacion($longitud, $clase) {
        $caracteres = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $codigoIdentificacion = '';
        
        do {
            for ($i = 0; $i < $longitud; $i++) {
                $indiceRandom = mt_rand(0, strlen($caracteres) - 1);
                $codigoIdentificacion .= $caracteres[$indiceRandom];
            }
            $codigoExistente = $clase::ObtenerPorCodigoIdentificacion($codigoIdentificacion);
        } while ($codigoExistente != false); // Valido que el número generado ya no exista
        
        return $codigoIdentificacion;
    }
}

?>