<?php declare(strict_types = 1);
/**
 * @author    Luis Arturo Rodríguez
 * @copyright Copyright (c) 2022 Luis Arturo Rodríguez <rguezque@gmail.com>
 * @link      https://github.com/rguezque
 * @license   https://opensource.org/licenses/MIT    MIT License
 */

namespace rguezque\Forge\Route;

use Closure;
use rguezque\Forge\Exceptions\BadNameException;
use rguezque\Forge\Exceptions\DuplicityException;
use rguezque\Forge\Exceptions\NotFoundException;
use InvalidArgumentException;

/**
 * Services provider.
 * 
 * @method void register(string $alias, Closure $closure) Register services
 * @method void unregister(string ...$alias) Unregister one or multiple services by name
 * @method bool has(string $key) Return true if a service exists
 * @method array all() Return all services array
 * @method array keys() Return a lineal array with the registered names of services
 * @method int count() Return the count of services
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
     * @param string $name Service name
     * @param Closure $closure Service definition
     * @return Services
     * @throws BadNameException
     * @throws InvalidArgumentException
     * @throws DuplicityException
     */
    public function register(string $name, Closure $closure): Services {
        // Avoid whitespaces
        if(strpos($name, ' ')) {
            throw new BadNameException(sprintf('Whitespaces not allowed in name definition for service "%s"', $name));
        }

        // Not allow reserved names
        if(in_array($name, get_class_methods($this))) {
            throw new InvalidArgumentException(sprintf('"%s" is a reserved name for an existent property of %s.', $name, __CLASS__));
        }

        // Check for duplicity 
        if($this->has($name)) {
            throw new DuplicityException(sprintf('Already exists a service with name "%s".', $name));
        }

        $this->services[$name] = $closure;

        return $this;
    }

    /**
     * Unregister one or multiple services by name
     * 
     * @param string ...$names Service names
     * @return void
     */
    public function unregister(string ...$names): void {
        foreach($names as $name) {
            unset($this->services[$name]);
        }
    }

    /**
     * Return true if a service exists
     * 
     * @param string $name Service name
     * @return bool
     */
    public function has(string $name): bool {
        return array_key_exists($name, $this->services);
    }

    /**
     * Return all services array
     * 
     * @return array
     */
    public function all(): array {
        return $this->services;
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
     * Return the count of services
     * 
     * @return int
     */
    public function count(): int {
        return count($this->services);
    }

    /**
     * Allow to access the private services
     * 
     * @param string $name Service name
     * @param array $params Service parameters
     * @return mixed
     * @throws NotFoundException
     */
    public function __call(string $name, array $params) {
        if(!$this->has($name) && !is_callable($this->services[$name])) {
            throw new NotFoundException(sprintf('The request service "%s" wasn\'t found.', $name));
        }

        return call_user_func($this->services[$name], ...$params);
    }

    /**
     * Allow to acces services in object context
     * 
     * @param string $name Sevice name
     * @return mixed
     * @throws NotFoundException
     */
    public function __get(string $name) {
        if(!$this->has($name)) {
            throw new NotFoundException(sprintf('The request service "%s" wasn\'t found.', $name));
        }

        $service = $this->services[$name];

        return $service();
    }
}
?>
