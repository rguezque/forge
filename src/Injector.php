<?php declare(strict_types = 1);
/**
 * @author    Luis Arturo Rodríguez
 * @copyright Copyright (c) 2021 Luis Arturo Rodríguez <rguezque@gmail.com>
 * @license   https://opensource.org/licenses/MIT    MIT License
 */

namespace Forge\Route;

use Closure;
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
    private $dependencies = array();

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
     */
    public function get(string $name) {
        if(!$this->has($name)) {
            throw new DependencyNotFoundException(sprintf('Don\'t exists a dependency with name "%s".', $name));
        }

        // Retrieve the dependency
        $dependency = $this->dependencies[$name];
        
        if($dependency->getDependency() instanceof Closure) {
            $closure = $dependency->getDependency();

            return $closure();
        } else {
            $ref = new ReflectionClass($dependency->getDependency());

            // If has parameters...
            if(!empty($dependency->getParameters())) {
                $temp = array();

                foreach ($dependency->getParameters() as $param) {
                    // If parameter exists in the container, retrieve recursively
                    if(is_string($param) && $this->has($param)) {
                        $ref_param = $this->get($param);
                        $temp[] = $ref_param;
                    } else { // If parameter don't exists in container
                        $temp[] = $param;
                    }
                }

                return $ref->newInstanceArgs($temp);
            } else {
                return $ref->newInstance();
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