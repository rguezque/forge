<?php declare(strict_types = 1);

use App\TestController;
use Forge\Route\Emitter;
use Forge\Route\JsonEngine;
use Forge\Route\Request;
use Forge\Route\Route;
use Forge\Route\Router;

require __DIR__.'/vendor/autoload.php';

$app = new Router();
$app->cors([
    '(http(s)://)?(www\.)?localhost:3000'
]);

$app->addNamespaces([
    'App\\' => __DIR__.'/app'
]);

$app->addRoute(new Route('index', '/', TestController::class, 'indexAction'));
$app->addRoute(new Route('new-article', '/save-new-article', TestController::class, 'saveAction', Router::POST));
$app->addRoute(new Route('get-authors', '/get-authors', TestController::class, 'getAuthorsAction'));

$engine = new JsonEngine;
$app->setEngine($engine);

$response = $app->handleRequest(Request::fromGlobals());
Emitter::emit($response);

?>