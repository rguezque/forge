<?php declare(strict_types = 1);
/**
 * @author    Luis Arturo Rodríguez
 * @copyright Copyright (c) 2022-2024 Luis Arturo Rodríguez <rguezque@gmail.com>
 * @link      https://github.com/rguezque
 * @license   https://opensource.org/licenses/MIT    MIT License
 */

namespace rguezque\Forge\Router;

/**
 * Represents an HTTP request.
 * 
 * @method Request fromGlobals() Create request with superglobals
 * @method Bag getQueryParams() Return the $_GET params
 * @method Bag getBodyParams() Return the $_POST params
 * @method PhpInputStream getPhpInputStream() Return the 'php://input' read-only stream that allows you to read raw data from the request body
 * @method Bag getServerParams() Return the $_SERVER params
 * @method Bag getCookieParams() Return the $_COOKIE params
 * @method Bag getUploadedFiles() Return the $_FILE params
 * @method Bag getParameters() Return the named parameters of route
 * @method mixed getParameter(string $parameter, $default = null) Return a parameter by name
 * @method Bag getAllHeaders() Fetches all HTTP headers from the current request
 * @method Request withQueryParams(array $query) Add parameters to Request object in the $_GET array
 * @method Request withBodyParams(array $body) Add parameters to Request object in the $_POST array
 * @method Request withServerParams(array $server) Add parameters to Request object in the $_SERVER array
 * @method Request withQueryParams(array $query) Add parameters to Request object in the $_GET array
 * @method Request withCookieParams(array $cookies) Add parameters to Request object in the $_COOKIE array
 * @method Request withUploadedFiles(array $files) Add parameters to Request object in the $_FILEs array
 * @method Request withParameters(array $parameters) Add to Request object named parameter from route
 * @method Request withParameter(string $name, $value) Add to Request object a parameter by name
 * @method Request withoutParameter(string $name) Remove from Request object a parameter by name
 * @method string buildQueryString(string $url, array $params) Generate URL-encoded query string
 */
class Request {

    /**
     * $_GET
     * 
     * @var array
     */
    private $query;

    /**
     * $_POST
     * 
     * @var array
     */
    private $body;

    /**
     * $_SERVER
     * 
     * @var array
     */
    private $server;

    /**
     * $_COOKIE
     * 
     * @var array
     */
    private $cookies;

    /**
     * $_FILES
     * 
     * @var array
     */
    private $files;

    /**
     * Attributes of a route with named params
     * 
     * @var array
     */
    private $parameters;

    /**
     * Store the regex matches from route path
     * 
     * @var array
     */
    private $matches;

    /**
     * Construct the request
     */
    public function __construct(
        array $query, 
        array $body, 
        array $server, 
        array $cookies, 
        array $files, 
        array $parameters
    ) {
        $this->query = $query;
        $this->body = $body;
        $this->server = $server;
        $this->cookies = $cookies;
        $this->files = $files;
        $this->parameters = $parameters;
    }

    /**
     * Create request with superglobals
     * 
     * @return Request
     */
    public static function fromGlobals(): Request {
        return new Request(
            $_GET,
            $_POST,
            $_SERVER,
            $_COOKIE,
            $_FILES,
            []
        );
    }

    /**
     * Return the $_GET params
     * 
     * @return Bag
     */
    public function getQueryParams(): Bag {
        return new Bag($this->query);
    }

    /**
     * Return the $_POST params
     * 
     * @return Bag
     */
    public function getBodyParams(): Bag {
        return new Bag($this->body);
    }

    /**
     * Return the 'php://input' read-only stream that allows you to read raw data from the request body
     * 
     * @return PhpInputStream
     */
    public function getPhpInputStream(): PhpInputStream {
        return new PhpInputStream();
    }

    /**
     * Return the $_SERVER params
     * 
     * @return Bag
     */
    public function getServerParams(): Bag {
        return new Bag($this->server);
    }

    /**
     * Return the $_COOKIE params
     * 
     * @return Bag
     */
    public function getCookieParams(): Bag {
        return new Bag($this->cookies);
    }

    /**
     * Return the $_FILES params
     * 
     * @return Bag
     */
    public function getUploadedFiles(): Bag {
        return new Bag($this->files);
    }

    /**
     * Return the named parameters of route
     * 
     * @return Bag
     */
    public function getParameters(): Bag {
        return new Bag($this->parameters);
    }

    /**
     * Return a parameter by name
     * 
     * @param string $parameter Parameter name
     * @param mixed $default Default value to return
     * @return mixed
     */
    public function getParameter(string $parameter, $default = null) {
        return isset($this->parameters[$parameter]) ? $this->parameters[$parameter] : $default;
    }

    /**
     * Fetches all HTTP headers from the current request
     * 
     * @return Bag
     */
    public function getAllHeaders(): Bag {
        return new Bag(getallheaders());
    }

    /**
     * Add parameters to Request object in the $_GET array
     * 
     * @param array $query $_GET params
     * @return Request
     */
    public function withQueryParams(array $query): Request {
        $this->query = $query;

        return $this;
    }

    /**
     * Add parameters to Request object in the $_POST array
     * 
     * @param array $body $_POST params
     * @return Request
     */
    public function withBodyParams(array $body): Request {
        $this->body = $body;

        return $this;
    }

    /**
     * Add parameters to Request object in the $_SERVER array
     * 
     * @param array $server $_SERVER params
     * @return Request
     */
    public function withServerParams(array $server): Request {
        $this->server = $server;

        return $this;
    }

    /**
     * Add parameters to Request object in the $_COOKIE array
     * 
     * @param array $cookies $_COOKIE params
     * @return Request
     */
    public function withCookieParams(array $cookies): Request {
        $this->cookies = $cookies;

        return $this;
    }

    /**
     * Add parameters to Request object in the $_FILES array
     * 
     * @param array $files $_FILES params
     * @return Request
     */
    public function withUploadedFiles(array $files): Request {
        $this->files = $files;

        return $this;
    }

    /**
     * Add to Request object named parameter from route
     * 
     * @param array $parameters Named parameters
     * @return Request
     */
    public function withParameters(array $parameters): Request {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * Add to Request object a parameter by name
     * 
     * @param string $name Parameter name
     * @param mixed $value Parameter value
     * @return Request
     */
    public function withParameter(string $name, $value): Request {
        $this->parameters[$name] = $value;

        return $this;
    }

    /**
     * Remove from Request object a parameter by name
     * 
     * @param string $name Parameter name
     * @return Request
     */
    public function withoutParameter(string $name): Request {
        unset($this->parameters[$name]);

        return $this;
    }

    /**
     * Generate URL-encoded query string
     * 
     * @param string $url URI to construct query
     * @param array $params Params to construct query
     * @return string
     */
    public static function buildQueryString(string $url, array $params): string {
        if(!strpos($url, 'https://') && !strpos($url, 'http://') && !strpos($url, 'www')) {
            $url = rtrim($url, '/\\').'/';
        }

        return $url.'?'.http_build_query($params);
    }

}
