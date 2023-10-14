<?php declare(strict_types = 1);
/**
 * @author    Luis Arturo Rodríguez
 * @copyright Copyright (c) 2022 Luis Arturo Rodríguez <rguezque@gmail.com>
 * @link      https://github.com/rguezque
 * @license   https://opensource.org/licenses/MIT    MIT License
 */

namespace rguezque\Forge\Route;

use ArgumentCountError;
use rguezque\Forge\Exceptions\NotFoundException;

use function rguezque\Forge\functions\str_path;

/**
 * Generate URIs from route names and parameters.
 * 
 * @method string generate(string $path_name, array $params = []) Generate a URi from route name
 */
class UriGenerator {

    /**
     * Contain the route names
     * 
     * @var Bag
     */
    private static $route_names;

    /**
     * Receive an array with route names collection and save/merge into a Bag object
     * 
     * @param array $route_names Associative array with route names and paths
     * @return void
     */
    public static function setRouteNames(array $route_names = []): void {
        if(self::$route_names instanceof Bag) {
            $route_names = array_merge(self::$route_names->all(), $route_names);
        }

        self::$route_names = new Bag($route_names);
    }

    /**
     * Generate a URI from route name (Reverse routing)
     * 
     * @param string $path_name Route path name
     * @param array $params Parameters to match with the route path
     * @return string
     * @throws NotFoundException
     */
    function reverseRouting(string $path_name, array $parameters = []) {
        if(!self::$route_names->has($path_name)) {
            throw new NotFoundException(sprintf('Does not exist any route with name "%s".', $path_name));
        }

        $route_path = self::$route_names->get($path_name);

        $url = preg_replace_callback('#\{\s*([a-zA-Z0-9_]+)\s*\}#', function ($matches) use ($parameters) {
            if (isset($parameters[$matches[1]])) {
                return $parameters[$matches[1]];
            }

            return $matches[0];
        }, $route_path);

        // Remove any optional parameters that were not provided
        $url = preg_replace('/\{[^}]+\?\}/', '', $url);
    
        return str_path($url);
    }

}
