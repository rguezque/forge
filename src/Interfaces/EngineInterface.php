<?php declare(strict_types = 1);
/**
 * @author    Luis Arturo Rodríguez
 * @copyright Copyright (c) 2022-2024 Luis Arturo Rodríguez <rguezque@gmail.com>
 * @link      https://github.com/rguezque
 * @license   https://opensource.org/licenses/MIT    MIT License
 */

namespace rguezque\Forge\Interfaces;

use rguezque\Forge\Router\Injector;
use rguezque\Forge\Router\Request;
use rguezque\Forge\Router\Response;
use rguezque\Forge\Router\Route;
use rguezque\Forge\Router\Services;

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
     * @throws DependencyNotFoundException
     * @throws NotFoundException
     * @throws UnexpectedValueException
     */
    public function resolve(Route $route, Request $request): Response;
}

?>