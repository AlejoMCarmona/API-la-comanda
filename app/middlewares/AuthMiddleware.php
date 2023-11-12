<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class AuthMiddleware {
    private $puestosValidos;
    
    public function __construct($puestosValidos) {
        $this -> puestosValidos = $puestosValidos;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response {
        $parametros = $request -> getQueryParams();

        if (Validadores::ValidarParametros($parametros, [ "puesto" ])) {
            $puesto = $parametros["puesto"];

            $resultado = false;
            foreach ($this -> puestosValidos as $puestoValido) {
                if ($puestoValido === $puesto) {
                    $resultado = true;
                    break;
                }
            }
    
            if ($resultado) {
                $response = $handler -> handle($request);
            } else {
                $payload = json_encode(array('ERROR DE AUTORIZACION' => 'No posees el rol adecuado para realizar esta acción'));
                $response = new Response();
                $response -> getBody() -> write($payload);
            }
        } else {
            $payload = json_encode(array('ERROR' => 'No posees ningun rol para realizar esta acción'));
            $response = new Response();
            $response -> getBody() -> write($payload);          
        }

        return $response -> withHeader('Content-Type', 'application/json');
    }
}

?>