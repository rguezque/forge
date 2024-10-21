<?php declare(strict_types = 1);
/**
 * @author    Luis Arturo Rodríguez
 * @copyright Copyright (c) 2022-2024 Luis Arturo Rodríguez <rguezque@gmail.com>
 * @link      https://github.com/rguezque
 * @license   https://opensource.org/licenses/MIT    MIT License
 */

namespace rguezque\Forge\Interfaces;

/**
 * Contain a parameters array.
 * 
 * @method void set(string $key, $value) Set or overwrite a parameter
 * @method void remove(string $key) Remove a parameter by name
 * @method void clear() Remove all parameters
 */
interface ArgumentsInterface {
    /**
     * Set or overwrite a parameter
     * 
     * @param string $key Parameter name
     * @param mixed $value Parameter value
     * @return void
     */
    public function set(string $key, mixed $value): void;

    /**
     * Remove a parameter by name
     * 
     * @param string $key Parameter name
     * @return void
     */
    public function remove(string $key): void;

    /**
     * Remove all parameters
     * 
     * @return void
     */
    public function clear(): void;
}

?>