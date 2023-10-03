<?php declare(strict_types = 1);

namespace rguezque\Forge\Interfaces;

/**
 * Contain a parameters array.
 * 
 * @method mixed get(string $key, $default = null) Retrieve a parameter by name
 * @method array all() Retrieve all parameters array
 * @method bool has(string $key) Return true if a parameter exists
 * @method bool valid(string $key) Return true if a parameter exists and is not empty or null
 * @method int count() Return the count of parameters
 */
interface BagInterface {
    /**
     * Retrieve a parameter by name
     * 
     * If the parameter is array, return into a Bag object
     * 
     * @param string $key Parameter name
     * @param mixed $default Value to return if the parameter isn't found
     * @return mixed
     */
    public function get(string $key, $default = null);

    /**
     * Retrieve all parameters array
     * 
     * @return array
     */
    public function all(): array;

    /**
     * Return true if a parameter exists
     * 
     * @param string $key Parameter name
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * Return true if a parameter exists and is not empty or null
     * 
     * @param string $key Parameter name
     * @return bool
     */
    public function valid(string $key): bool;

    /**
	 * Return the count of parameters
	 * 
	 * @return int
	 */
    public function count(): int;
}

?>