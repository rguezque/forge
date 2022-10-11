<?php declare(strict_types = 1);
/**
 * @author    Luis Arturo Rodríguez
 * @copyright Copyright (c) 2022 Luis Arturo Rodríguez <rguezque@gmail.com>
 * @link      https://github.com/rguezque
 * @license   https://opensource.org/licenses/MIT    MIT License
 */

namespace Forge\Route;

use ArgumentCountError;
use Forge\Exceptions\NotFoundException;

use function Forge\functions\str_path;

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
    private $route_names;

    /**
     * Receive the Bag object with route names
     */
    public function __construct() {
        $this->route_names = Globals::get(Router::ROUTER_ROUTE_NAMES_ARRAY) ?? new Bag([]);
    }

    /**
     * Generate a URI from route name
     * 
     * @param string $path_name Route path name
     * @param array $params Parameters to match with the route path
     * @return string
     * @throws NotFoundException
     */
    public function generate(string $path_name, array $params = []): string {
        if(!$this->route_names->has($path_name)) {
            throw new NotFoundException(sprintf('Does not exist any route with name "%s".', $path_name));
        }

        $path = $this->route_names->get($path_name);

        if(!empty($params)) {
            //$path = preg_replace_callback('#{(\w+)}#', function($match) use($path, $path_name, $params) {
            $path = preg_replace_callback('#{(.*?)}#', function($match) use($path, $path_name, $params) {
                $key = $match[1];
                if(!array_key_exists($key, $params)) {
                    throw new ArgumentCountError(sprintf('Missing parameters at generate URI for route %s:"%s".', $path_name, $path));
                }
                
                return $params[$key];
            }, $path);
        }

        return str_path($path);
    }

}

?>