<?php declare(strict_types = 1);
/**
 * @author    Luis Arturo Rodríguez
 * @copyright Copyright (c) 2022 Luis Arturo Rodríguez <rguezque@gmail.com>
 * @link      https://github.com/rguezque
 * @license   https://opensource.org/licenses/MIT    MIT License
 */

namespace Forge\Route;

use function Forge\functions\add_trailing_slash;
use function Forge\functions\str_path;

/**
 * Configurator for the router.
 */
class Configurator extends Router {

    /**
     * Contain the router options
     * 
     * @var Arguments
     */
    private $options;

    /**
     * Receive an array with options
     * 
     * @param array $options Router options
     */
    public function __construct(array $options) {
        $this->options = new Arguments(
            array_change_key_case($options, CASE_LOWER)
        );
    }

    /**
     * Apply the configuration to router
     * 
     * @return void
     */
    public function __invoke(Router &$router) {
        foreach($this->options->keys() as $method) {
            $action = $this->generateName($method);
            $this->{$action}($router);
        }
    }

    /**
     * Set the basepath if the router lives in a subdirectory
     * 
     * @return Configurator
     */
    private function setBasepath(Router &$router): Configurator {
        if($this->options->has('set.basepath')) {
            $router->basepath = str_path($this->options->get('set.basepath'));
        }

        return $this;
    }

    /**
     * Set the default directory to seach for templates
     * 
     * @return Configurator
     */
    private function setViewsPath(): Configurator {
        if($this->options->has('set.views.path')) {
            Globals::set('router.views.path', add_trailing_slash($this->options->get('set.views.path')));
        }
        return $this;
    }

    /**
     * Set the supported http request methods
     * 
     * @return Configurator
     */
    private function setSupportedRequestMethods(Router &$router): Configurator {
        if($this->options->has('set.supported.request.methods')) {
            $methods = $this->options->get('set.supported.request.methods');
            $methods = ($methods instanceof Bag) ? $methods->all() : [$methods];
            $this->normalize($methods);
            $router->supported_request_methods = array_unique($methods);
        }

        return $this;
    }

    /**
     * Add supported http request methods
     * 
     * @return Configurator
     */
    private function addSupportedRequestMethods(Router &$router): Configurator {
        if($this->options->has('add.supported.request.methods')) {
            $methods = $this->options->get('add.supported.request.methods');
            $methods = ($methods instanceof Bag) ? $methods->all() : [$methods];
            $this->normalize($methods);
            $merge = array_merge($router->supported_request_methods, $methods);
            $router->supported_request_methods = array_unique($merge);
        }

        return $this;
    }

    /**
     * Normalize the array for http request methods definition
     * 
     * @param array $haystack An array
     * @return void
     */
    private function normalize(array &$haystack): void {
        $haystack = array_map('trim', $haystack);
        $haystack = array_map('strtoupper', $haystack);
    }

    /**
     * Generate the method name from options key name to PascalCase
     * 
     * @param string $var String to process
     * @return string
     */
    private function generateName(string $var): string {
        $parts = array_map('strtolower', explode('.', $var));
        $parts = array_map('ucfirst', $parts);

        return lcfirst(implode('', $parts));
    }
 
}

?>