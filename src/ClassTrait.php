<?php declare(strict_types = 1);

namespace rguezque\Forge\Router;

use ReflectionClass;
use rguezque\Forge\Exceptions\DependencyNotFoundException;
use rguezque\Forge\Exceptions\NotFoundException;

trait ClassTrait {

    /**
     * Retrieve the controller class for the route, from a container or reflection class
     * 
     * @param string $controller Controller name
     * @return object
     * @throws DependencyNotFoundException
     * @throws NotFoundException
     */
    private function retrieveControllerClass($controller): object {
        // Check for dependencies container
        if(isset($this->container) && $this->container instanceof Injector) {
            if(!$this->container->has($controller)) {
                throw new DependencyNotFoundException(sprintf('Don\'t exists the dependency "%s" in the container.', $controller));
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

        return $class;
    }
}

?>