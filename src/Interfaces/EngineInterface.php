<?php declare(strict_types = 1);

namespace rguezque\Forge\Interfaces;

use rguezque\Forge\Route\Injector;
use rguezque\Forge\Route\Request;
use rguezque\Forge\Route\Response;
use rguezque\Forge\Route\Route;
use rguezque\Forge\Route\Services;

/**
 * Set the type engine for router.
 * 
 * @method void setContainer(Injector $container) Set a container where to search for controllers and dependencies
 * @method void setServices(Services $services) Set a provider where to search for services
 * @method Response resolve(Route $route, Request $request) Process the route controller and return a response
 */
interface EngineInterface {

    /**
     * Set a container where to search for controllers and dependencies
     * 
     * @param Injector $container The dependency container
     * @return void
     */
    public function setContainer(Injector $container): void;

    /**
     * Set a provider where to search for services
     * 
     * @param Services $services The services provider
     * @return void
     */
    public function setServices(Services $services): void;

    /**
     * Process the route controller and return a response
     * 
     * @param Route $route The route object to resolve
     * @param Request $request HTTP request object 
     * @return Response
     * @throws NotFoundException
     * @throws UnexpectedValueException
     */
    public function resolve(Route $route, Request $request): Response;
}

?>