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
    $group -> get('/{codigoMesa}', \MesaController::class . ':TraerUno') -> add(new AuthMiddleware(["socio", "bartender", "cervecero", "mozo", "cocinero"]));
    $group -> delete('/{codigoIdentificacion}', \MesaController::class . ':BorrarUno') -> add(new AuthMiddleware(["socio"]));
    $group -> put('[/]', \MesaController::class . ':ModificarUno') -> add(new AuthMiddleware(["socio"]));
});
#endregion
#region Usuarios
$app -> group('/usuarios', function (RouteCollectorProxy $group) {
    $group -> post('[/]', \UsuarioController::class . ':CargarUno') -> add(new AuthMiddleware(["socio"]));
    $group -> post('/login', \UsuarioController::class . ':IniciarSesion');
    $group -> get('[/]', \UsuarioController::class . ':TraerTodos') -> add(new AuthMiddleware(["socio"]));
    $group -> get('/{dni}', \UsuarioController::class . ':TraerUno') -> add(new AuthMiddleware(["socio"]));
    $group -> get('/puesto/{puesto}', \UsuarioController::class . ':TraerPorPuesto') -> add(new AuthMiddleware(["socio"]));
    $group -> delete('/{dni}', \UsuarioController::class . ':BorrarUno') -> add(new AuthMiddleware(["socio"]));
    $group -> put('[/]', \UsuarioController::class . ':ModificarUno') -> add(new AuthMiddleware(["socio"]));
});
#endregion
#region Productos
$app -> group('/productos', function (RouteCollectorProxy $group) {
    $group -> post('[/]', \ProductoController::class . ':CargarUno') -> add(new AuthMiddleware(["socio"]));
    $group -> get('[/]', \ProductoController::class . ':TraerTodos');
    $group -> get('/{id}', \ProductoController::class . ':TraerUno');
    $group -> delete('/{id}', \ProductoController::class . ':BorrarUno') -> add(new AuthMiddleware(["socio"]));
    $group -> put('[/]', \ProductoController::class . ':ModificarUno') -> add(new AuthMiddleware(["socio"]));
});
#endregion
#region Pedidos
$app ->group('/pedidos', function (RouteCollectorProxy $group) {
    $group -> get('[/]', \PedidoController::class . ':TraerTodos') -> add(new AuthMiddleware(["socio"]));
    $group -> get('/pedido/{id}', \PedidoController::class . ':TraerUno') -> add(new AuthMiddleware(["socio","cocinero","cervecero","bartender"]));
    $group -> get('/{codigoIdentificacion}', \PedidoController::class . ':TraerPorCodigoIdentificacion'); // El usuario debería poder ver todos los pedidos de su mesa
    $group -> get('/tiempoRestante/{codigoMesa}/{idPedido}', \PedidoController::class . ':TraerTiempoEstimadoPedido');
    $group -> get('/sector/{sector}', \PedidoController::class . ':TraerPedidosPorSector') -> add(new AuthMiddleware(["socio","cocinero","cervecero","bartender","mozo"]));
    $group -> post('[/]', \PedidoController::class . ':CargarUno') -> add(new AuthMiddleware(["mozo"]));
    $group -> post('/cambioEstado', \PedidoController::class . ':CambiarEstado') -> add(new AuthMiddleware(["cocinero","cervecero","bartender"]));
    $group -> put('[/]', \PedidoController::class . ':ModificarUno') -> add(new AuthMiddleware(["mozo","socio"]));
});
#endregion
#region Encuestas
$app ->group('/encuestas', function (RouteCollectorProxy $group) {
    $group -> get('[/]', \EncuestaController::class . ':TraerTodos');
    $group -> post('[/]', \EncuestaController::class . ':CargarUno');
});
#endregion
#endregion

$app->run();