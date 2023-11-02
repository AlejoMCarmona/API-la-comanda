<?php

class Validadores {
    /**
     * Valida la presencia de claves requeridas en un array asociativo.
     *
     * @param array $arrayAsociativo El array asociativo que se desea validar.
     * @param array $clavesRequeridas Un array que contiene las claves requeridas que se deben buscar en el array asociativo.
     *
     * @return bool Retorna true si todas las claves requeridas están presentes y no son vacías en el array asociativo, o false en caso contrario.
     */
    public static function ValidarParametros($arrayAsociativo, $clavesRequeridas) {
        if ($arrayAsociativo == NULL || $clavesRequeridas == NULL) return false;

        foreach ($clavesRequeridas as $clave) {
            if (!array_key_exists($clave, $arrayAsociativo) || empty($arrayAsociativo[$clave])) {
                return false;
            }
        }
        return true;
    }
}

?>