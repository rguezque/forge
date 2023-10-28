<?php declare(strict_types = 1);
/**
 * @author    Luis Arturo Rodríguez
 * @copyright Copyright (c) 2022 Luis Arturo Rodríguez <rguezque@gmail.com>
 * @link      https://github.com/rguezque
 * @license   https://opensource.org/licenses/MIT    MIT License
 */

namespace rguezque\Forge\Router;

use rguezque\Forge\Exceptions\NotFoundException;

use function rguezque\Forge\functions\str_path;

/**
 * Generate URIs from route names and parameters.
 * 
 * @method string generate(string $path_name, array $params = []) Generate a URi from route name
 */
class UrlGenerator {

    /**
     * Contain the route names
     * 
     * @var Bag
     */
    private $route_names;

    /**
     * Receive an array with route names collection and save/merge into a Bag object
     * 
     * @param Bag $route_names Bag object caontaining associative array with route names and paths
     * @return void
     */
    public function __construct(Bag $route_names) {
        $this->route_names = $route_names;
    }

    /**
     * Generate a URI from route name (Reverse routing)
     * 
     * @param string $path_name Route path name
     * @param array $params Parameters to match with the route path
     * @return string
     * @throws NotFoundException
     */
    function reverseRouting(string $path_name, array $parameters = []): string {
        if(!$this->route_names->has($path_name)) {
            throw new NotFoundException(sprintf('Does not exist any route with name "%s".', $path_name));
        }

        $route_path = $this->route_names->get($path_name);

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
