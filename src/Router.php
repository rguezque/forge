<?php declare(strict_types = 1);
/**
 * @author    Luis Arturo Rodríguez
 * @copyright Copyright (c) 2022 LAllows you to load controllers with attributes that are used to define uis Arturo Rodríguez <rguezque@gmail.com>
 * @link      https://github.com/rguezque
 * @license   https://opensource.org/licenses/MIT    MIT License
 */

namespace rguezque\Forge\Router;

use Closure;
use ReflectionClass;
use rguezque\Forge\Exceptions\BadNameException;
use rguezque\Forge\Exceptions\DuplicityException;
use rguezque\Forge\Exceptions\RouteNotFoundException;
use rguezque\Forge\Exceptions\UnsupportedRequestMethodException;
use rguezque\Forge\Interfaces\EngineInterface;
use rguezque\Forge\Router\Attributes\GroupAttribute;
use rguezque\Forge\Router\Attributes\RouteAttribute;

use function rguezque\Forge\functions\generator;
use function rguezque\Forge\functions\remove_trailing_slash;
use function rguezque\Forge\functions\str_ends_with;

/**
 * Router
 * 
 * @method Router addControllersWithAttributes(array $controllers) Allows you to load controllers with attributes that are used to define routes and routes groups
 * @method Router cors(array $origins) Set the Cross-Origin Resources Sharing
 * @method Router setEngine(EngineInterface $engine) Set a router engine, tells how to process request and response
 * @method Route addRoute(Route $route) Add a route to the route collection
 * @method RouteGroup addRouteGroup(string $namespace, Route ...$routes) Add routes group with a common namespace
 * @method Bag getRouteNames() Return all the route names array into a Bag object
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
     * Supported request methods for the application
     * 
     * @var string[]
     */
    protected $supported_request_methods = [];

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
     * Allowed origins for CORS
     * 
     * @var string[]
     */
    private $allowed_origins = [];

    /**
     * @param array $options Array with configs definition
     */
    public function __construct(array $options = []) {
        Configurator::configure($this, $options);
    }

    /**
     * Allows you to load controllers with attributes that are used to define 
     * routes and routes groups.
     * 
     * Group prefix are defined in the attributes of each class. 
     * The routes are defined in the attributes of each method of the class.
     * 
     * @param array $controllers Controller classes with attributes
     * @return Router
     */
    public function addControllersWithAttributes(array $controllers): Router {
        foreach($controllers as $controller) {
            $reflection_controller = new ReflectionClass($controller);


            $controller_attrs = $reflection_controller->getAttributes(GroupAttribute::class);

            if([] !== $controller_attrs) {
                foreach($controller_attrs as $controller_attr) {
                    $group = $controller_attr->newInstance();
                    $prefix = $group->prefix;
                    
                    $this->addRoutesFromAttributes($controller, $reflection_controller->getMethods(), $prefix);
                }
            } else {
                $this->addRoutesFromAttributes($controller, $reflection_controller->getMethods());
            }
        }

        return $this;
    }

    /**
     * Generate and add routes using methods attributes
     * 
     * @param string $controller Controller name
     * @param ReflectionMethod[] $methods Array of methods of controller
     * @param string $prefix Group prefix
     * @return void
     */
    private function addRoutesFromAttributes(string $controller, array $methods, string $prefix = ''): void {
        foreach($methods as $method) {
            $attributes = $method->getAttributes(RouteAttribute::class);

            foreach($attributes as $attribute) {
                $route = $attribute->newInstance();

                $this->addRoute(new Route($route->method, $route->name, $prefix.$route->path, $controller, $method->getName()));
            }
        }
    }

    /**
     * Set the Cross-Origin Resources Sharing
     * 
     * @param string[] Array with allowed origins (allow regex). Ej: '(http(s)://)?(www\.)?localhost:3000'
     * @return Router
     */
    public function cors(array $allowed_origins): Router {
        $this->allowed_origins = $allowed_origins;
        
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
     * @return RouteGroup
     */
    public function addRouteGroup(string $prefix, Closure $closure): RouteGroup {
        $group = new RouteGroup($prefix, $closure, $this);
        $this->routes_group[] = $group;
        
        return $group;
    }

    /**
     * Return all the route names array into a Bag object
     * 
     * @return Bag
     */
    public function getRouteNames(): Bag {
        return new Bag($this->route_names);
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
            $this->resolveCors($request);
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
        
                // Verify if a specific engine was defined and applies it, otherwise set default engine
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
                call_user_func($group);
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
     * Enable Cross-Origin Resources Sharing
     * 
     * @param Request $request Request object
     * @return void
     */
    private function resolveCors(Request $request): void {
        $server = $request->getServerParams();

        if ($server->has('HTTP_ORIGIN') && $server->get('HTTP_ORIGIN') != '') {
            foreach ($this->allowed_origins as $allowed_origin) {
                if (preg_match('#' . $allowed_origin . '#', $server->get('HTTP_ORIGIN'))) {
                    header('Access-Control-Allow-Origin: ' . $server->get('HTTP_ORIGIN'));
                    header('Access-Control-Allow-Methods: ' . implode(', ', $this->supported_request_methods));
                    header('Access-Control-Max-Age: 1000');
                    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
                    break;
                }
            }
        }
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
