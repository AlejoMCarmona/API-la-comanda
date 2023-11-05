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
    $group -> post('[/]', \MesaController::class . ':CargarUno');
    $group -> get('[/]', \MesaController::class . ':TraerTodos');
});
#endregion
#region Usuarios
$app -> group('/usuarios', function (RouteCollectorProxy $group) {
    $group -> post('[/]', \UsuarioController::class . ':CargarUno');
    $group -> post('/login', \UsuarioController::class . ':IniciarSesion');
    $group -> get('[/]', \UsuarioController::class . ':TraerTodos');
});
#endregion
#region Productos
$app -> group('/productos', function (RouteCollectorProxy $group) {
    $group -> post('[/]', \ProductoController::class . ':CargarUno');
    $group -> get('[/]', \ProductoController::class . ':TraerTodos');
});
#endregion
#region Pedidos
$app ->group('/pedidos', function (RouteCollectorProxy $group) {
    $group -> get('[/]', \PedidoController::class . ':TraerTodos');
    $group -> get('/{numeroIdentificacion}', \PedidoController::class . ':TraerPorNumeroIdentificacion');
    $group -> get('/tiempoRestante/{numeroIdentificacion}', \PedidoController::class . ':TraerTiempoEstimadoPedido');
    $group -> post('[/]', \PedidoController::class . ':CargarUno');
});
#endregion
#endregion

$app->run();