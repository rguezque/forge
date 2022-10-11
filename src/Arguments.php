<?php declare(strict_types = 1);
/**
 * @author    Luis Arturo Rodríguez
 * @copyright Copyright (c) 2022 Luis Arturo Rodríguez <rguezque@gmail.com>
 * @link      https://github.com/rguezque
 * @license   https://opensource.org/licenses/MIT    MIT License
 */

namespace Forge\Route;

use Forge\Interfaces\ArgumentsInterface;

/**
 * Contain a parameters array.
 * 
 * @method void set(string $key, $value) Set or overwrite a parameter
 * @method void remove(string $key) Remove a parameter by name
 * @method void clear() Remove all parameters
 */
class Arguments extends Bag implements ArgumentsInterface {

    /**
     * Receive a parameters array
     * 
     * @param array $arguments Parameters
     */
    public function __construct(array $arguments = []) {
        parent::__construct($arguments);
    }

    /**
     * Set or overwrite a parameter
     * 
     * @param string $key Parameter name
     * @param mixed $value Parameter value
     * @return void
     */
    public function set(string $key, $value): void {
        $this->bunch[$key] = $value;
    }

    /**
     * Set or overwrte an parameter in object context
     * 
     * @param string $key Parameter name
     * @param mixed $value Parameter value
     * @return void
     */
    public function __set(string $key, $value): void {
        $this->set($key, $value);
    }

    /**
     * Remove a parameter by name
     * 
     * @param string $key Parameter name
     * @return void
     */
    public function remove(string $key): void {
        unset($this->bunch[$key]);
    }

    /**
     * Remove all parameters
     * 
     * @return void
     */
    public function clear(): void {
        $this->bunch = array();
    }

}

?>