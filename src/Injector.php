<?php declare(strict_types = 1);
/**
 * @author    Luis Arturo Rodríguez
 * @copyright Copyright (c) 2022-2024 Luis Arturo Rodríguez <rguezque@gmail.com>
 * @link      https://github.com/rguezque
 * @license   https://opensource.org/licenses/MIT    MIT License
 */

namespace rguezque\Forge\Router;

use Closure;
use rguezque\Forge\Exceptions\ClassNotFoundException;
use rguezque\Forge\Exceptions\DependencyNotFoundException;
use rguezque\Forge\Exceptions\DuplicityException;
use ReflectionClass;
use ReflectionMethod;

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
     * @param callable $object Dependency
     * @return Dependency|void
     * @throws DuplicityException
     */
    public function add(string $name, callable $object = null) {
        if($this->has($name)) {
            throw new DuplicityException(sprintf('Already exists a dependency with name "%s".', $name));
        }

        $object = $object ?? $name;    
            
        $dependency = new Dependency($object);
        $this->dependencies[$name] = $dependency;

        if(!$object instanceof Closure || !is_array($object)) {
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
        $dependency_object = $this->dependencies[$name];
        $dependency = $dependency_object->getDependency();
        
        if($dependency instanceof Closure) {
            return [] !== $arguments ? call_user_func_array($dependency, array_values($arguments)) : call_user_func($dependency);
        } else if(is_array($dependency)) {
            list($class, $method) = $dependency;

            if(!class_exists($class)) {
                throw new ClassNotFoundException(sprintf('Don\'t exists the class "%s".', $class));
            }

            $rm = new ReflectionMethod($class, $method);

            if(!$rm->isStatic()) {
                $rc = new ReflectionClass($class);
                $class = $rc->newInstance();
                $dependency = [$class, $method];
            }

            return [] !== $arguments ? call_user_func_array($dependency, array_values($arguments)) : call_user_func($dependency);
        } else {
            if(!class_exists($dependency)) {
                throw new ClassNotFoundException(sprintf('Don\'t exists the class "%s".', $dependency));
            }

            $class = new ReflectionClass($dependency);

            // If has parameters...
            $parameters = $dependency_object->getParameters();
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