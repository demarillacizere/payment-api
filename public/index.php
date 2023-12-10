<?php

use PaymentApi\Middleware\CustomErrorHandler;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../container/container.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../");
$dotenv->safeLoad();
//APP_ROOT
$app = AppFactory::createFromContainer(container: $container);
$jwt = new Tuupola\Middleware\JwtAuthentication([
    "secret" => $_ENV['JWT_SECRET']
]);

$app->get('/v1', function (Request $request, Response $response, $args) {
    echo "Welcome to the payment app";
});

$app->get('/v1/api-docs', function (Request $request, Response $response) {
    include __DIR__ . '/swagger-ui/dist/index.html';
    return $response;
});

$app->get('/v1/token-generator', function (Request $request, Response $response) {
    exec('php ' . __DIR__ . '/../config/jwt-token.php', $output);
    $response = $response->withHeader('Content-Type', 'application/json');
    $response->getBody()->write(implode("\n", $output));
    return $response;
});

//Methods
$app->group('/v1/methods', function (RouteCollectorProxy $group) {
    $group->get('', '\PaymentApi\Controller\MethodsController:indexAction');
    $group->post('', '\PaymentApi\Controller\MethodsController:createAction');
    $group->delete('/{id:[0-9]+}', '\PaymentApi\Controller\MethodsController:removeAction');
    $group->get('/deactivate/{id:[0-9]+}', '\PaymentApi\Controller\MethodsController:deactivateAction');
    $group->get('/reactivate/{id:[0-9]+}', '\PaymentApi\Controller\MethodsController:reactivateAction');
    $group->put('/{id:[0-9]+}', '\PaymentApi\Controller\MethodsController:updateAction');
});

//Customers
$app->group('/v1/customers', function (RouteCollectorProxy $group) {
    $group->get('', '\PaymentApi\Controller\CustomersController:indexAction');
    $group->post('', '\PaymentApi\Controller\CustomersController:createAction');
    $group->delete('/{id:[0-9]+}', '\PaymentApi\Controller\CustomersController:removeAction');
    $group->put('/{id:[0-9]+}', '\PaymentApi\Controller\CustomersController:updateAction');
    $group->get('/deactivate/{id:[0-9]+}', '\PaymentApi\Controller\CustomersController:deactivateAction');
    $group->get('/reactivate/{id:[0-9]+}', '\PaymentApi\Controller\CustomersController:reactivateAction');
});

//Payments (transactions)
$app->group('/v1/payments', function (RouteCollectorProxy $group) {
    $group->get('', '\PaymentApi\Controller\PaymentsController:indexAction');
    $group->post('', '\PaymentApi\Controller\PaymentsController:createAction');
    $group->delete('/{id:[0-9]+}', '\PaymentApi\Controller\PaymentsController:removeAction');
    $group->put('/{id:[0-9]+}', '\PaymentApi\Controller\PaymentsController:updateAction');
})->add($jwt);


$displayErrors = $_ENV['APP_ENV'] != 'production';
$displayErrors = true;
$customErrorHandler = new CustomErrorHandler($app);

$errorMiddleware = $app->addErrorMiddleware($displayErrors, true, true);
$errorMiddleware->setDefaultErrorHandler($customErrorHandler);

$app->run();
