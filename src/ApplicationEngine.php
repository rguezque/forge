<?php declare(strict_types = 1);
/**
 * @author    Luis Arturo Rodríguez
 * @copyright Copyright (c) 2022 Luis Arturo Rodríguez <rguezque@gmail.com>
 * @link      https://github.com/rguezque
 * @license   https://opensource.org/licenses/MIT    MIT License
 */

namespace rguezque\Forge\Route;

use rguezque\Forge\Exceptions\NotFoundException;
use rguezque\Forge\Interfaces\EngineInterface;
use ReflectionClass;
use UnexpectedValueException;

/**
 * Set the default engine for router.
 */
class ApplicationEngine implements EngineInterface {

    /**
     * Dependencies container or Services provider
     * 
     * @var Injector|Services
     */
    private $container;

    /**
     * {@inheritdoc}
     */
    public function setContainer(Injector $container): void {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function setServices(Services $services): void {
        $this->container = $services;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(Route $route, Request $request): Response {
        // Controller full classname
        $controller = $route->getController();

        // Check for dependencies container
        if(isset($this->container) && $this->container instanceof Injector) {
            if(!$this->container->has($controller)) {
                throw new NotFoundException(sprintf('Don\'t exists the dependency "%s" in the container.', $controller));
            }
            // Get the instance from container
            $class = $this->container->get($controller);
        } else {
            if(!class_exists($controller)) {
                throw new NotFoundException(sprintf('Don\'t exists the class "%s".', $controller));
            }
            // Construct the controller instance
            $class = (new ReflectionClass($controller))->newInstance();
        }
        
        // Method of the controller
        $action = $route->getAction();

        // Check for services and exec the callback for the route
        if(isset($this->container) && $this->container instanceof Services) {
            $result = call_user_func([$class, $action], $request, new Response(), $this->container);
        } else {
            $result = call_user_func([$class, $action], $request, new Response());
        }
        
        if(!$result instanceof Response) {
            ob_end_clean();
            throw new UnexpectedValueException(sprintf('%s::%s() must return a Response object, catched %s', get_class($class), $action, gettype($result)));
        }

        return $result;
    }

}

?>