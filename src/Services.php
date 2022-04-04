<?php declare(strict_types = 1);
/**
 * @author    Luis Arturo Rodríguez
 * @copyright Copyright (c) 2021 Luis Arturo Rodríguez <rguezque@gmail.com>
 * @license   https://opensource.org/licenses/MIT    MIT License
 */

namespace Forge\Route;

use Closure;
use Forge\Exceptions\BadNameException;
use Forge\Exceptions\DuplicityException;
use Forge\Exceptions\NotFoundException;
use InvalidArgumentException;

/**
 * Services provider.
 * 
 * @method void register(string $alias, Closure $closure) Register services
 * @method void unregister(string ...$alias) Unregister one or multiple services by name
 * @method bool has(string $key) Return true if a service exists
 * @method array keys() Return a lineal array with the registered names of services
 */
class Services {

    /**
     * Services collection
     * 
     * @var array
     */
    private $services = [];

    /**
     * Register services
     * 
     * @param string $alias Service name
     * @param Closure $closure Service registered
     * @return void
     * @throws BadNameException
     * @throws InvalidArgumentException
     * @throws DuplicityException
     */
    public function register(string $alias, Closure $closure): void {
        // Avoid whitespaces
        if(strpos($alias, ' ')) {
            throw new BadNameException(sprintf('Whitespaces not allowed in name definition for service "%s"', $alias));
        }

        // Not allow reserved names
        if(in_array($alias, get_class_methods($this))) {
            throw new InvalidArgumentException(sprintf('"%s" is a reserved name for an existent property of %s.', $alias, __CLASS__));
        }

        // Check for duplicity 
        if(array_key_exists($alias, $this->services)) {
            throw new DuplicityException(sprintf('Already exists a service with name "%s".', $alias));
        }

        $this->services[$alias] = $closure;
    }

    /**
     * Unregister one or multiple services by name
     * 
     * @param string ...$alias Service names
     * @return void
     */
    public function unregister(string ...$alias): void {
        foreach($alias as $name) {
            unset($this->services[$name]);
        }
    }

    /**
     * Return true if a service exists
     * 
     * @param string $key Service name
     * @return bool
     */
    public function has(string $key): bool {
        return array_key_exists($key, $this->services);
    }

    /**
     * Return a lineal array with the registered names of services
     * 
     * @return array
     */
    public function keys(): array {
        return array_keys($this->services);
    }

    /**
     * Allow to access the private services
     * 
     * @param string $method Method name
     * @param array $params Method parameters
     * @return mixed
     * @throws NotFoundException
     */
    public function __call(string $method, array $params) {
        if(!isset($this->services[$method]) && !is_callable($this->services[$method])) {
            throw new NotFoundException(sprintf('The request service "%s" wasn\'t found.', $method));
        }

        return call_user_func($this->services[$method], ...$params);
    }

    /**
     * Allow to acces services in object context
     * 
     * @param string $method Sevice name
     * @return mixed
     * @throws NotFoundException
     */
    public function __get(string $method) {
        if(!isset($this->services[$method])) {
            throw new NotFoundException(sprintf('The request service "%s" wasn\'t found.', $method));
        }

        $service = $this->services[$method];

        return $service();
    }
}
?>
