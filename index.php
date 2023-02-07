<?php declare(strict_types = 1);

use App\TestController;
use Forge\Route\ApplicationEngine;
use Forge\Route\Authentication;
use Forge\Route\Emitter;
use Forge\Route\Injector;
use Forge\Route\Request;
use Forge\Route\Route;
use Forge\Route\Router;
use Forge\Route\Users;

require __DIR__.'/vendor/autoload.php';

$app = new Router();

$app->addNamespaces([
    'App\\' => __DIR__.'/app'
]);

$app->addRoute(new Route('index', '/', TestController::class, 'indexAction'));
$app->addRoute(new Route('form', '/form_admin', TestController::class, 'formAction'));
$app->addRoute(new Route('admin', '/admin', TestController::class, 'indexAction'));

$app->security([
    [
        'protect' => '/admin',
        'form' => '/form_admin',
        'roles' => ['ROLE_ADMIN', 'ROLE_SUPER']
    ],
    [
        'protect' => '/super',
        'form' => '/form_super',
        'roles' => ['ROLE_SUPER']
    ]
]);

$cont = new Injector;
$cont->add(TestController::class);
$cont->add(Users::class);
$cont->add(Authentication::class)->addParameter(Users::class);

$engine = new ApplicationEngine;
$engine->setContainer($cont);

$app->setEngine($engine);

$response = $app->handleRequest(Request::fromGlobals());
Emitter::emit($response);

?>