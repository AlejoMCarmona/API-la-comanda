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
#endregion
#region Middlewares
require_once './Middlewares/AuthMiddleware.php';
#endregion

// Cargar .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

$app = AppFactory::create();

#region Routes
#region Inicio
$app -> get('/', function (Request $request, Response $response, $args) {
    $response -> getBody() -> write("La comanda!");
    return $response;
});
#endregion
#region Mesas
$app -> group('/mesas', function (RouteCollectorProxy $group) {
    $group -> post('[/]', \MesaController::class . ':CargarUno') -> add(new AuthMiddleware(["socio"], "POST"));
    $group -> get('[/]', \MesaController::class . ':TraerTodos') -> add(new AuthMiddleware(["socio"]), "GET");
    $group -> get('/{codigoMesa}', \MesaController::class . ':TraerUno') -> add(new AuthMiddleware(["socio"]), "GET");
});
#endregion
#region Usuarios
$app -> group('/usuarios', function (RouteCollectorProxy $group) {
    $group -> post('[/]', \UsuarioController::class . ':CargarUno') -> add(new AuthMiddleware(["socio"], "POST"));
    $group -> post('/login', \UsuarioController::class . ':IniciarSesion');
    $group -> get('[/]', \UsuarioController::class . ':TraerTodos') -> add(new AuthMiddleware(["socio"]), "GET");
    $group -> get('/{dni}', \UsuarioController::class . ':TraerUno') -> add(new AuthMiddleware(["socio"]), "GET");
    $group -> get('/puesto/{puesto}', \UsuarioController::class . ':TraerPorPuesto') -> add(new AuthMiddleware(["socio"]), "GET");;
});
#endregion
#region Productos
$app -> group('/productos', function (RouteCollectorProxy $group) {
    $group -> post('[/]', \ProductoController::class . ':CargarUno') -> add(new AuthMiddleware(["socio"], "POST"));
    $group -> get('[/]', \ProductoController::class . ':TraerTodos');
    $group -> get('/{id}', \ProductoController::class . ':TraerUno');
});
#endregion
#region Pedidos
$app ->group('/pedidos', function (RouteCollectorProxy $group) {
    $group -> get('[/]', \PedidoController::class . ':TraerTodos') -> add(new AuthMiddleware(["socio"]), "GET");
    $group -> get('/pedido/{id}', \PedidoController::class . ':TraerUno') -> add(new AuthMiddleware(["socio"]), "GET");
    $group -> get('/{codigoIdentificacion}', \PedidoController::class . ':TraerPorCodigoIdentificacion') -> add(new AuthMiddleware(["socio","cocinero","cervecero","bartender"], "GET"));
    $group -> get('/tiempoRestante/{codigoMesa}/{idPedido}', \PedidoController::class . ':TraerTiempoEstimadoPedido');
    $group -> get('/sector/{sector}', \PedidoController::class . ':TraerPedidosPorSector') -> add(new AuthMiddleware(["socio","cocinero","cervecero","bartender"], "GET"));
    $group -> post('[/]', \PedidoController::class . ':CargarUno') -> add(new AuthMiddleware(["mozo"], "POST"));
    $group -> post('/cambioEstado', \PedidoController::class . ':CambiarEstado') -> add(new AuthMiddleware(["cocinero","cervecero","bartender"], "POST"));
});
#endregion
#endregion

$app->run();