<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

#region Controladores
require_once './controllers/MesaController.php';
require_once './controllers/UsuarioController.php';
require_once './controllers/ProductoController.php';
require_once './controllers/PedidoController.php';
require_once './controllers/EncuestaController.php';
require_once './controllers/LoginController.php';
#endregion
#region Middlewares
require_once './Middlewares/AuthMiddleware.php';
#endregion

// Cargar .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

$app = AppFactory::create();
$app -> addBodyParsingMiddleware();

#region Routes
#region Inicio
$app -> get('/', function (Request $request, Response $response, $args) {
    $response -> getBody() -> write("La comanda!");
    return $response;
});
#endregion
#region Mesas
$app -> group('/mesas', function (RouteCollectorProxy $group) {
    $group -> post('[/]', \MesaController::class . ':CargarUno') -> add(new AuthMiddleware(["socio"]));
    $group -> post('/cambioEstado', \MesaController::class . ':CambiarEstado') -> add(new AuthMiddleware(["socio","mozo"]));
    $group -> get('[/]', \MesaController::class . ':TraerTodos') -> add(new AuthMiddleware(["socio", "bartender", "cervecero", "mozo", "cocinero"]));
    $group -> get('/{codigoIdentificacion}', \MesaController::class . ':TraerUno') -> add(new AuthMiddleware(["socio", "bartender", "cervecero", "mozo", "cocinero"]));
    $group -> delete('/{codigoIdentificacion}', \MesaController::class . ':BorrarUno') -> add(new AuthMiddleware(["socio"]));
    $group -> put('[/]', \MesaController::class . ':ModificarUno') -> add(new AuthMiddleware(["socio"]));
});
#endregion
#region Usuarios
$app -> group('/usuarios', function (RouteCollectorProxy $group) { 
    $group -> post('[/]', \UsuarioController::class . ':CargarUno');
    $group -> get('[/]', \UsuarioController::class . ':TraerTodos');
    $group -> get('/{dni}', \UsuarioController::class . ':TraerUno');
    $group -> get('/puesto/{puesto}', \UsuarioController::class . ':TraerPorPuesto');
    $group -> delete('/{dni}', \UsuarioController::class . ':BorrarUno');
    $group -> put('[/]', \UsuarioController::class . ':ModificarUno');
}) -> add(new AuthMiddleware(["socio"])); // Las operaciones que tienen que ver con la información de los empleados y sus correos, solo deben ser vista por los socios, puesto que contiene información del personal
#endregion
#region Productos
$app -> group('/productos', function (RouteCollectorProxy $group) {
    $group -> post('[/]', \ProductoController::class . ':CargarUno') -> add(new AuthMiddleware(["socio"]));
    $group -> get('[/]', \ProductoController::class . ':TraerTodos');
    $group -> get('/csv', \ProductoController::class . ':DescargarCSV');
    $group -> post('/csv', \ProductoController::class . ':CargarCSV') -> add(new AuthMiddleware(["socio"]));
    $group -> get('/{id}', \ProductoController::class . ':TraerUno');
    $group -> delete('/{id}', \ProductoController::class . ':BorrarUno') -> add(new AuthMiddleware(["socio"]));
    $group -> put('[/]', \ProductoController::class . ':ModificarUno') -> add(new AuthMiddleware(["socio"]));
});
#endregion
#region Pedidos
$app -> group('/pedidos', function (RouteCollectorProxy $group) {
    $group -> get('[/]', \PedidoController::class . ':TraerTodos') -> add(new AuthMiddleware(["socio"]));
    $group -> get('/pedido/{id}', \PedidoController::class . ':TraerUno') -> add(new AuthMiddleware(["mozo","socio","cocinero","cervecero","bartender"]));
    $group -> get('/{codigoIdentificacion}', \PedidoController::class . ':TraerPorCodigoIdentificacion'); // El usuario debería poder ver todos los pedidos de su mesa, por eso no hay restricciones. Lo mismo aplica para el tiempo restante.
    $group -> get('/tiempoRestante/{codigoMesa}/{codigoIdentificacion}', \PedidoController::class . ':TraerTiempoRestante');
    $group -> get('/sector/{sector}', \PedidoController::class . ':TraerPedidosPendientesPorSector') -> add(new AuthMiddleware(["mozo","socio","cocinero","cervecero","bartender"]));
    $group -> post('[/]', \PedidoController::class . ':CargarUno') -> add(new AuthMiddleware(["mozo"]));
    $group -> post('/cambioEstado', \PedidoController::class . ':CambiarEstado') -> add(new AuthMiddleware(["cocinero","cervecero","bartender"]));
    $group -> post('/foto', \PedidoController::class . ':SubirFotoMesa') -> add(new AuthMiddleware(["mozo"]));
    $group -> put('[/]', \PedidoController::class . ':ModificarUno') -> add(new AuthMiddleware(["mozo"]));
    $group -> delete('/{id}', \PedidoController::class . ':BorrarUno') -> add(new AuthMiddleware(["mozo"]));
});
#endregion
#region Encuestas
$app -> group('/encuestas', function (RouteCollectorProxy $group) {
    $group -> post('[/]', \EncuestaController::class . ':CargarUno');
});
#endregion
#region Login
$app -> group('/login', function (RouteCollectorProxy $group) {
    $group -> post('[/]', \LoginController::class . ':Login');
});
#endregion
#endregion

$app -> run();