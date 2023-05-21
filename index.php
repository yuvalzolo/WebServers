<?php
require_once 'vendor/autoload.php';

use controllers\WebserverController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

$controller = new WebserverController();

$app = AppFactory::create();
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
// Define the routes
$app->get('/', function (Request $request, Response $response) {
    $response->getBody()->write('Welcome to the Webserver Monitor');
    return $response;
});
$app->post('/webservers', function (Request $request, Response $response) use ($controller) {
    return $controller->createWebserver($request, $response);
});
// Update Web Server Endpoint
$app->put('/webservers/{id}', function (Request $request, Response $response, $args) use ($controller) {
    return $controller->editWebserver($request, $response, $args['id']);
});
// Delete Web Server Endpoint
$app->delete('/webservers/{id}', function (Request $request, Response $response, $args) use ($controller) {
    $id = $args['id'];
    return $controller->deleteWebserver($request, $response, $id);
});
// Get all Web Servers Endpoint
$app->get('/webservers', function (Request $request, Response $response) use ($controller) {
    return $controller->getAllWebservers($request, $response);
});
// Get Web Server Requests History Endpoint
$app->get('/webservers/{id}/requests', function (Request $request, Response $response, $args) use ($controller) {
    $id = $args['id'];
    return $controller->getWebserverRequestsHistory($request, $response, $id);
});
// Get Web Server Endpoint
$app->get('/webservers/{id}', function (Request $request, Response $response, $args) use ($controller) {
    $id = $args['id'];
    return $controller->getWebserver($request, $response, $id);
});
// Run the Slim application
$app->run();
