<?php declare(strict_types = 1);
/**
 * @author    Luis Arturo Rodríguez
 * @copyright Copyright (c) 2022-2024 Luis Arturo Rodríguez <rguezque@gmail.com>
 * @link      https://github.com/rguezque
 * @license   https://opensource.org/licenses/MIT    MIT License
 */

namespace rguezque\Forge\Router;

/**
 * Represent $GLOBALS router parameters
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
     * Key for globals router parameters into $GLOBALS array
     * 
     * @var string
     */
    private const NAMESPACE = 'ROUTER_GLOBALS';

    /**
     * Set or overwrite a global parameter
     * 
     * @param string $key Parameter name
     * @param mixed $value Parameter value
     * @return void
     */
    public static function set(string $key, $value): void {
        $GLOBALS[Globals::NAMESPACE][$key] = $value;
    }

    /**
     * Remove a global parameter
     * 
     * @param string $key Parameter name
     * @return void
     */
    public static function remove(string $key): void {
        unset($GLOBALS[Globals::NAMESPACE][$key]);
    }

    /**
     * Remove all global parameters
     * 
     * @return void
     */
    public static function clear(): void {
        $GLOBALS[Globals::NAMESPACE] = [];
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
        ? (is_array($GLOBALS[Globals::NAMESPACE][$key]) ? new Bag($GLOBALS[Globals::NAMESPACE][$key]) : $GLOBALS[Globals::NAMESPACE][$key]) 
        : $default;
    }

    /**
     * Retrieve all globals parameters array
     * 
     * @return array
     */
    public static function all(): array {
        return $GLOBALS[Globals::NAMESPACE];
    }

    /**
     * Return true if a global parameter exists
     * 
     * @param string $key Nombre del parámetro a verificar
     * @return bool
     */
    public static function has(string $key): bool {
        return array_key_exists($key, $GLOBALS[Globals::NAMESPACE]);
    }

    /**
     * Return true if a global parameter exists and is not empty or null
     * 
     * @param string $key Parameter name
     * @return bool
     */
    public static function valid(string $key): bool {
        return self::has($key) && !empty($GLOBALS[Globals::NAMESPACE][$key]) && !is_null($GLOBALS[Globals::NAMESPACE][$key]);
    }

    /**
	 * Retrieve the count of globals parameters
	 * 
	 * @return int
	 */
    public static function count(): int {
    	return sizeof($GLOBALS[Globals::NAMESPACE]);
    }

    /**
     * Return all globals router parameters as json data
     * 
     * @return string
     */
    public static function jsonSerialize() {
        return json_encode($GLOBALS[Globals::NAMESPACE], JSON_PRETTY_PRINT);
    }
    
}

?>