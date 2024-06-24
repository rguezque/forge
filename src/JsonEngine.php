<?php declare(strict_types = 1);
/**
 * @author    Luis Arturo Rodríguez
 * @copyright Copyright (c) 2022-2024 Luis Arturo Rodríguez <rguezque@gmail.com>
 * @link      https://github.com/rguezque
 * @license   https://opensource.org/licenses/MIT    MIT License
 */

namespace rguezque\Forge\Router;

use rguezque\Forge\Interfaces\EngineInterface;
use UnexpectedValueException;

use function rguezque\Forge\functions\is_assoc_array;

/**
 * Set the engine for allow router return json responses
 */
class JsonEngine implements EngineInterface {

    use ClassTrait;

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
        $controller = $route->getController();

        $class = $this->retrieveControllerClass($controller);

        // Method of the controller
        $action = $route->getAction();
        
        $arguments = [$request];
        
        // Check for services and exec the callback for the route
        if(isset($this->container) && $this->container instanceof Services) {
            array_push($arguments, $this->container);
        }
        // Exec the callback for the route
        $result = call_user_func_array([$class, $action], array_values($arguments));
        
        if(!is_array($result)) {
            $buffer = ob_get_clean();
            throw new UnexpectedValueException(sprintf('%s::%s() must return an array, catched %s', get_class($class), $action, gettype($buffer)));
        }
        
        return new JsonResponse($result);
    }

}

?>