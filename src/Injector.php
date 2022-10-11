<?php declare(strict_types = 1);
/**
 * @author    Luis Arturo Rodríguez
 * @copyright Copyright (c) 2022 Luis Arturo Rodríguez <rguezque@gmail.com>
 * @link      https://github.com/rguezque
 * @license   https://opensource.org/licenses/MIT    MIT License
 */

namespace Forge\Route;

use Closure;
use Forge\Exceptions\ClassNotFoundException;
use Forge\Exceptions\DependencyNotFoundException;
use Forge\Exceptions\DuplicityException;
use ReflectionClass;

/**
 * Dependencies container.
 * 
 * @method void|Dependency add(string $name, $object = null) Add a dependency to container
 * @method object|Closure get(string $name) Retrieves a dependency
 * @method bool has(string $name) Returns true if a dependency exists
 */
class Injector {

    /**
     * Dependencies collection
     * 
     * @var Dependency[]
     */
    private $dependencies = [];

    /**
     * Add a dependency to container
     * 
     * @param string $name Dependendy name
     * @param string|Closure $object Dependency
     * @return Dependency|void
     * @throws DuplicityException
     */
    public function add(string $name, $object = null) {
        if($this->has($name)) {
            throw new DuplicityException(sprintf('Already exists a dependency with name "%s".', $name));
        }

        $object = $object ?? $name;    
            
        $dependency = new Dependency($object);
        $this->dependencies[$name] = $dependency;

        if(!$object instanceof Closure) {
            return $dependency;
        }
    }
    
    /**
     * Retrieves a dependency
     * 
     * @param string $name Dependency name
     * @return object|Closure
     * @throws DependencyNotFoundException
     * @throws ClassNotFoundException
     */
    public function get(string $name, array $arguments = []) {
        if(!$this->has($name)) {
            throw new DependencyNotFoundException(sprintf('Don\'t exists a dependency with name "%s".', $name));
        }

        // Retrieve the dependency
        $dependency = $this->dependencies[$name];
        
        if($dependency->getDependency() instanceof Closure) {
            $closure = $dependency->getDependency();

            return [] !== $arguments ? call_user_func_array($closure, array_values($arguments)) : call_user_func($closure);
        } else {
            $class = $dependency->getDependency();
            if(!class_exists($class)) {
                throw new ClassNotFoundException(sprintf('Don\'t exists the class "%s".', $class));
            }

            $class = new ReflectionClass($class);

            // If has parameters...
            $parameters = $dependency->getParameters();
            if([] !== $parameters) {
                foreach ($parameters as &$param) {
                    // If parameter exists in the container as dependency, retrieve recursively
                    if(is_string($param) && $this->has($param)) {
                        $param = $this->get($param);
                    }
                }
                
                if([] !== $arguments) {
                    $parameters = array_merge($parameters, $arguments);
                }
                return $class->newInstanceArgs($parameters);
            } else {
                return [] !== $arguments ? $class->newInstanceArgs($arguments) : $class->newInstance();
            }
        }
    }
    
    /**
     * Returns true if a dependency exists
     * 
     * @param string $name Dependency name
     * @return bool
     */
    public function has(string $name): bool {
        return array_key_exists($name, $this->dependencies);
    }

}

?>