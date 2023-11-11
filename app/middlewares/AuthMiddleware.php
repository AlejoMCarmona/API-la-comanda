<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class AuthMiddleware {
    private $puestosValidos;
    private $metodo;
    
    public function __construct($puestosValidos, $metodo = "GET") {
        $this -> puestosValidos = $puestosValidos;
        $this -> metodo = $metodo;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response {

        // Tengo que diferenciar entre "GET" y "POST" debido a que los parámetros se obtienen de diferentes maneras
        if ($this -> metodo === "GET") {
            $parametros = $request -> getQueryParams();
        } else {
            $parametros = $request -> getParsedBody();            
        }

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