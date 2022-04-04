<?php declare(strict_types = 1);
/**
 * @author    Luis Arturo Rodríguez
 * @copyright Copyright (c) 2021 Luis Arturo Rodríguez <rguezque@gmail.com>
 * @license   https://opensource.org/licenses/MIT    MIT License
 */

namespace Forge\Route;

/**
 * Represent $GLOBALS parameters
 * 
 * @static void set(string $key, $value) Set or overwrite a global parameter
 * @static void remove(string $key) Remove a global parameter by name
 * @static void clear() Remove all globals parameters
 * @static mixed get(string $key, $default = null) Retrieve a global parameter by name
 * @static array all() Retrieve all globals parameters array
 * @static bool has(string $key) Return true if a global parameter exists
 * @static bool valid(string $key) Return true if a global parameter exists and is not empty or null
 * @static int count() Retrieve the count of globals parameters
 */
class Globals {

    /**
     * Set or overwrite a global parameter
     * 
     * @param string $key Parameter name
     * @param mixed $value Parameter value
     * @return void
     */
    public static function set(string $key, $value): void {
        $GLOBALS[$key] = $value;
    }

    /**
     * Remove a global parameter
     * 
     * @param string $key Parameter name
     * @return void
     */
    public static function remove(string $key): void {
        unset($GLOBALS[$key]);
    }

    /**
     * Remove all global parameters
     * 
     * @return void
     */
    public static function clear(): void {
        $GLOBALS = array();
    }

    /**
     * Retrieve a global parameter by name
     * 
     * If the parameter is array, return into a Bag object
     * 
     * @param string $key Parameter name
     * @param mixed $default Value to return if the parameter isn't found
     * @return mixed
     */
    public static function get(string $key, $default = null) {
        return self::has($key) 
        ? (is_array($GLOBALS[$key]) ? new Bag($GLOBALS[$key]) : $GLOBALS[$key]) 
        : $default;
    }

    /**
     * Retrieve all globals parameters array
     * 
     * @return array
     */
    public static function all(): array {
        return $GLOBALS;
    }

    /**
     * Return true if a global parameter exists
     * 
     * @param string $key Nombre del parámetro a verificar
     * @return bool
     */
    public static function has(string $key): bool {
        return array_key_exists($key, $GLOBALS);
    }

    /**
     * Return true if a global parameter exists and is not empty or null
     * 
     * @param string $key Parameter name
     * @return bool
     */
    public static function valid(string $key): bool {
        return self::has($key) && !empty($GLOBALS[$key]) && !is_null($GLOBALS[$key]);
    }

    /**
	 * Retrieve the count of globals parameters
	 * 
	 * @return int
	 */
    public static function count(): int {
    	return sizeof($GLOBALS);
    }

    /**
     * Return all globals vars as json data
     * 
     * @return string
     */
    public function toJson(): string {
        return json_encode($GLOBALS, JSON_PRETTY_PRINT);
    }
    
}

?>