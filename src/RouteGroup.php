<?php declare(strict_types = 1);

namespace Forge\Route;

use Closure;

use function Forge\functions\str_path;

/**
 * Represents a routes group.
 * 
 * @method RouteGroup addRoute(Route $route) Add a route to the route collection
 */
class RouteGroup {

    /**
     * Route group namespace
     * 
     * @var string
     */
    private $namespace;

    /**
     * Routes group closure
     * 
     * @var Closure
     */
    private $closure;

    /**
     * Router instance
     * 
     * @var Router
     */
    private $router;

    /**
     * Routes group constructor
     * 
     * @param string $namespace Routes group namespace
     * @param Closure $closure Routes group definition
     * @param Router $router Router instance
     */
    public function __construct(string $namespace, Closure $closure, Router $router) {
        $this->namespace = str_path($namespace);
        $this->closure = $closure;
        $this->router = $router;
    }

    /**
     * Add a route to the route collection
     * 
     * @param Route $route Route definition
     * @return RouteGroup
     * @throws DuplicityException
     * @throws UnsupportedRequestMethodException
     * @throws BadNameException
     */
    public function addRoute(Route $route): RouteGroup {
        $route->prependStringPath($this->namespace);
        $this->router->addRoute($route);

        return $this;
    }

    /**
     * Exec when the class is invoked as function
     * 
     * @return void
     */
    public function __invoke(): void {
        ($this->closure)($this);
    }

}

?>