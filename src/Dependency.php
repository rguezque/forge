<?php
/**
 * @author    Luis Arturo Rodríguez
 * @copyright Copyright (c) 2022-2024 Luis Arturo Rodríguez <rguezque@gmail.com>
 * @link      https://github.com/rguezque
 * @license   https://opensource.org/licenses/MIT    MIT License
 */

namespace rguezque\Forge\Router;

/**
 * Represents a dependency.
 * 
 * @method Dependency addParameter($parameter) add a parameter
 * @method Dependency addParameters(array $parameters) Add parameters
 * @method string|Closure getDependency() Retrieves the dependency
 * @method array getParameters() Retrieves the dependency parameters
 */
class Dependency {

    /**
     * Dependency
     * 
     * @var string|Closure
     */
    private $dependency;

    /**
     * Dependency parameters
     * 
     * @var array
     */
    private $arguments = [];

    /**
     * Constructor
     * 
     * @param string|Closure $dependency Retrieve the dependency
     */
    public function __construct($dependency) {
        $this->dependency = $dependency;
    }

    /**
     * Add a parameter
     * 
     * @param mixed $parameter Parameter to inject
     * @return Dependency
     */
    public function addParameter($parameter): Dependency {
        $this->arguments[] = $parameter;

        return $this;
    }

    /**
     * Add parameters
     * 
     * @param array $parameters Parameters to inject
     * @return Dependency
     */
    public function addParameters(array $parameters): Dependency {
        $this->arguments = array_merge($parameters, $this->arguments);

        return $this;
    }

    /**
     * Retrieves the dependency
     * 
     * @return string|Closure La dependencia almacenada
     */
    public function getDependency() {
        return $this->dependency;
    }

    /**
     * Retrieves the dependency parameters
     * 
     * @return array
     */
    public function getParameters() {
        return $this->arguments;
    }

}

?>