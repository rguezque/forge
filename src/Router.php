<?php declare(strict_types = 1);
/**
 * @author    Luis Arturo Rodríguez
 * @copyright Copyright (c) 2022 Luis Arturo Rodríguez <rguezque@gmail.com>
 * @link      https://github.com/rguezque
 * @license   https://opensource.org/licenses/MIT    MIT License
 */

namespace Forge\Route;

use Closure;
use Composer\Autoload\ClassLoader;
use Forge\Exceptions\BadNameException;
use Forge\Exceptions\DuplicityException;
use Forge\Exceptions\MissingArgumentException;
use Forge\Exceptions\RouteNotFoundException;
use Forge\Exceptions\UnsupportedRequestMethodException;
use Forge\Interfaces\EngineInterface;
use ReflectionClass;
use stdClass;

use function Forge\functions\generator;
use function Forge\functions\namespace_format;
use function Forge\functions\remove_trailing_slash;
use function Forge\functions\str_ends_with;
use function Forge\functions\str_path;

/**
 * Router
 * 
 * @method void addNamespaces(array $namespaces, bool $prepend = false) Add namespaces for controllers. The paths for namespaces can be a string or an array of PSR-4 base directories
 * @method Router setEngine(EngineInterface $engine) Set a router engine, tells how to process request and response
 * @method Router addRoute(Route $route) Add a route to the route collection
 * @method Router addRouteGroup(string $namespace, Route ...$routes) Add routes group with a common namespace
 * @method Response handleRequest(Request $request) Handle the request URI and routing
 */
class Router {

    /**
     * Index name in $GLOBAL that contains the route names array
     * 
     * @var string
     */
    const ROUTER_ROUTE_NAMES_ARRAY = 'router_route_names_array';

    /**
     * Const for GET verb
     */
    const GET = 'GET';
    
    /**
     * Const for POST verb
     */
    const POST = 'POST';

    /**
     * Supported request methods for the application
     * 
     * @var string[]
     */
    protected $supported_request_methods = ['GET', 'POST'];

    /**
     * Routes collection
     * 
     * @var Route[]
     */
    private $routes = [];

    /**
     * Routes group collection
     * 
     * @var RouteGroup[]
     */
    private $routes_group = [];

    /**
     * Basepath if the router lives in a subdirectory
     * 
     * @var string
     */
    protected $basepath = '';

    /**
     * Route names
     * 
     * @var string[]
     */
    private $route_names = [];

    /**
     * Router engine
     * 
     * @var EngineInterface
     */
    private $engine;

    /**
     * Class loader for controllers
     * 
     * @var ClassLoader
     */
    private $loader;

    /**
     * @param Configurator $config Object with configs definition
     */
    public function __construct(?Configurator $config = null) {
        if(null !== $config) {
            $config($this);
        }
        $this->loader = new ClassLoader;
    }

    /**
     * It allows to mount a controlling class with the definition of the routes 
     * within the annotations of each method, following a specific pattern.
     * 
     * @param string $controller_classname Full controller class name
     * @param ?string $prefix Optional param to group routes under a common prefix
     * @return Router
     */
    public function addWithAnnotations(string $controller_classname, ?string $prefix = null): Router {
        $reflector = new ReflectionClass($controller_classname);
        $methods_class = $reflector->getMethods();

        foreach($methods_class as $method) {
            // Ignore the methods wich begins with double undescore (magic methods)
            if(strcmp(substr($method->name, 0, 2), '__') === 0) {
                continue;
            }
            
            $closure = $reflector->getMethod($method->name);
            $comment = $closure->getDocComment();
            $result = $this->filterDocComment($comment);

            // Check for errors in route definition annotation
            if($result instanceof stdClass) {
                if(1 === $result->errno) {
                    throw new BadNameException(sprintf('Incorrect annotation route, don\'t match the pattern for method "%s" in class "%s".', $method->name, $controller_classname));
                } else if(2 === $result->errno) {
                    throw new MissingArgumentException(sprintf('Parameters missing (%s) in route definition for method "%s" in class "%s". Check for illegal whitespaces and correct params names.', implode(', ', $result->data),$method->name, $controller_classname));
                }                
            } 

            $path = $result['path'];
            $http_method = strtoupper($result['method']);
            $name = strtolower($result['name']);

            // Check for allowed http request method
            if(!in_array($http_method, $this->supported_request_methods)) {
                throw new UnsupportedRequestMethodException(sprintf('The request http method %s isn\'t allowed in route definition.', $http_method));
            }

            $new_route = new Route($name, $path, $closure->class, $closure->name, $http_method);
            if(null !== $prefix) {
                $new_route->prependStringPath($prefix);
            }

            $this->addRoute($new_route);
        }

        return $this;
    }

    /**
     * Set a router engine, tells how to process request and response
     * 
     * @param EngineInterface $engine Type of functionality
     * @return Router
     */
    public function setEngine(EngineInterface $engine): Router {
        $this->engine = $engine;
        return $this;
    }

    /**
     * Add namespaces for controllers. The paths for namespaces can be a string or 
     * an array of PSR-4 base directories
     *
     * @param array $namespace The namespace => path, namespace must have trailing '\\'
     * @param bool $prepend Whether to prepend the directories
     * @return void
     * @throws InvalidArgumentException
     */
    public function addNamespaces(array $namespaces, bool $prepend = false): void {
        foreach($namespaces as $namespace => $path) {
            $this->loader->addPsr4(namespace_format($namespace), $path, $prepend);
        }
        $this->loader->register();
    }

    /**
     * Add a route to the route collection
     * 
     * @param Route $route Route definition
     * @return Router
     * @throws DuplicityException
     * @throws UnsupportedRequestMethodException
     * @throws BadNameException
     */
    public function addRoute(Route $route): Router {
        // Check for already existent route name and avoid overwrite
        $name = $route->getName();
        if(array_key_exists($name, $this->route_names)) {
            throw new DuplicityException(sprintf('Already exists a route with name "%s".', $name));
        }
        
        // Check for allowed request method
        $http_request_method = $route->getRequestMethod();
        if(!in_array($http_request_method, $this->supported_request_methods)) {
            throw new UnsupportedRequestMethodException(sprintf('HTTP request method %s isn\'t allowed in route definition for {name: "%s", path: "%s"}.', $http_request_method, $name, $route->getPath()));
        }

        // Avoid checking RouteView objects because they don't have a controller
        if(!$route instanceof RouteView) {
            // Verify nomenclature for controller name and action name
            if(!str_ends_with($route->getController(), 'Controller')) {
                throw new BadNameException(sprintf('Controllers name must ends with the suffix "Controller". Error in "%s".', $route->getController()));
            }

            if(!str_ends_with($route->getAction(), 'Action')) {
                throw new BadNameException(sprintf('Action name of the controller must ends with the suffix "Action". Error in "%s::%s"', $route->getController(), $route->getAction()));
            }
        }

        // Add the router basepath to route path
        $route->prependStringPath($this->basepath);
        // Add the route name to the array of route names
        $this->route_names[$name] = $route->getPath();
        // Add the route to collection (GET,POST,PUT or DELETE) according to request method of the route
        $this->routes[$http_request_method][] = $route;

        return $this;
    }

    /**
     * Add routes group with a common prefix
     * 
     * @param string $prefix Group prefix
     * @param Closure $closure Closure with routes definition
     * @return Router
     */
    public function addRouteGroup(string $prefix, Closure $closure): Router {
        $this->routes_group[] = new RouteGroup($prefix, $closure, $this);
        return $this;
    }

    /**
     * Retrieve the array with route names in a Bag object
     * 
     * @return Bag
     */
    private function getRouteNames(): Bag {
        return new Bag($this->route_names);
    }

    /**
     * Save all the route names in a global var
     * 
     * @return void
     */
    private function saveRouteNamesToGlobals(): void {
        Globals::set(Router::ROUTER_ROUTE_NAMES_ARRAY, $this->getRouteNames());
    }

    /**
     * Handle the request URI and routing
     * 
     * @param Request $request Request object
     * @return Response
     * @throws UnsupportedRequestMethodException
     * @throws RouteNotFoundException
     */
    public function handleRequest(Request $request): Response {
        static $invoke_once = false;

        if(!$invoke_once) {
            $this->saveRouteNamesToGlobals();
            $this->resolveRouteGroups();
            $invoke_once = true;

            return $this->resolve($request);
        }
    }

    /**
     * Handle the request URI and routing
     * 
     * @param Request $request Request object
     * @return Response
     * @throws UnsupportedRequestMethodException
     * @throws RouteNotFoundException
     */
    private function resolve(Request $request): Response {
        $server = $request->getServerParams()->all();
        $request_method = $server['REQUEST_METHOD'];

        // Check for valid request method
        if(!in_array($request_method, $this->supported_request_methods)) {
            throw new UnsupportedRequestMethodException(sprintf('The HTTP request method %s isn\'t supported by router.', $request_method));
        }

        // Catch the request uri
        $request_uri = $this->filterRequestUri($server['REQUEST_URI']);

        /**
         * Select the route collection according the request method and implement a generator. 
         * Send an empty array in case of inexistents routes with the request method.
         */
        $routes = generator($this->routes[$request_method] ?? []);

        foreach ($routes as $route) {
            // Match the route pattern against the request
            if (preg_match($route->getPattern(), $request_uri, $arguments)) {
                // Send a flag to Generator object to stop the generator when the route path is found
                $routes->send('stop');

                // $arguments[0] will always be equal to $request, so we just shift it off
                array_shift($arguments);
                
                // Filter the regex matches and push to lineal array into key '_matches'
                $this->filterArguments($arguments);

                // Check for route with only view without controller
                if($route instanceof RouteView) {
                    $route->addArguments($arguments);
                    return $route->render(); // Early return
                }

                // Add arguments from the parsed URI to the request object
                $request->withParameters($arguments);
        
                // Verifies if a specific engine was defined and applies it, otherwise set default engine
                $this->engine = $this->engine ?? new ApplicationEngine();
                $response = $this->engine->resolve($route, $request);
        
                // Finally, we return from the function, because we do not want the request to be handled more than once
                return $response;
            }
        }
        
        // For default throw a exception if the request URI did not match any routh path
        throw new RouteNotFoundException(sprintf('The %s request "%s" did not match any route.', $request_method, $request_uri));
    }

    /**
     * Resolve the routes group
     * 
     * @return void
     */
    private function resolveRouteGroups() {
        if([] !== $this->routes_group) {
            foreach($this->routes_group as $group) {
                $group();
            }
        }
    }

    /**
     * Filter the request URI
     * 
     * If has been sent a query in the request uri(eg. /path/?foo=bar), take the 'path' 
     * component as the request uri and parameters are catched in the $_GET parameters 
     * and are accessible with Request::getQueryParams() 
     * 
     * @param string $uri URI to parse
     * @return string
     */
    private function filterRequestUri(string $uri): string {
        // Clean the uri from url-encoded query string
        $uri = rawurldecode(parse_url($uri, PHP_URL_PATH));
        
        // Trailing slash no matters
        if('/' !== $uri) {
            $uri = remove_trailing_slash($uri);
        }

        return $uri;
    }

    /**
     * Filter an arguments array. Regex matches are pushed into a lineal array with key '_matches'.
     * 
     * @param array $params Array to process
     * @return void
     */
    private function filterArguments(array &$params): void {
        $matches = [];
        foreach($params as $key => $item) {
            if(is_int($key)) {
                unset($params[$key]);
                $matches[] = $item;
            }
        }

        if([] !== $matches) {
            $params['@matches'] = $matches;
        }
    }

    /**
     * Remove slashes and asterisks from comment lines and return the route parts definition
     * 
     * @param string $comment Comment lines to parse
     * @return string[]|stdClass
     */
    private function filterDocComment(string $comment) {
        $pattern = '#(@Route\([a-zA-Z0-9,_].*\))#';

        if(!preg_match($pattern, $comment, $parts)) {
            $result = new stdClass;
            $result->errno = 1;
            return $result;
        }

        $definition = array_pop($parts);
        preg_match_all('#(\w+)=\'(.*?)\'#', $definition, $pairs);
        $data = array_combine( $pairs[1], $pairs[2]);

        $diff = array_diff(['path','method', 'name'], array_keys($data));
        if($diff) {
            $result = new stdClass;
            $result->errno = 2;
            $result->data = $diff;
            return $result;
        }

        return $data;
    }

    /**
     * A copyright message
     * 
     * @return string
     */
    public static function copyright(): string {
        return base64_decode('TWFkZSB3aXRoIDxzcGFuIHN0eWxlPSJjb2xvcjpyZWQ7Ij7inaQ8L3NwYW4+IGJ5IDxhIGhyZWY9Imh0dHBzOi8vd3d3LmdpdGh1Yi5jb20vcmd1ZXpxdWUiIHRhcmdldD0iX2JsYW5rIj5yZ3VlenF1ZTwvYT4');
    }

}
