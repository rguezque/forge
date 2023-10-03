<?php declare(strict_types = 1);
/**
 * @author    Luis Arturo Rodríguez
 * @copyright Copyright (c) 2022 Luis Arturo Rodríguez <rguezque@gmail.com>
 * @link      https://github.com/rguezque
 * @license   https://opensource.org/licenses/MIT    MIT License
 */

namespace rguezque\Forge\Route;

use InvalidArgumentException;

use function rguezque\Forge\functions\add_trailing_slash;
use function rguezque\Forge\functions\remove_trailing_slash;
use function rguezque\Forge\functions\str_path;

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

    private $available_options = [
        'set.basepath', 
        'set.supported.request.methods'
    ];

    /**
     * Receive an array with options
     * 
     * @param array $options Router options
     * @throws InvalidArgumentException
     */
    public function __construct(array $options = []) {
        if([] !== $options) {
            $options = array_change_key_case($options, CASE_LOWER);
            $options = array_map('trim', $options);
            $bad_options = array_diff(array_keys($options), $this->available_options);
    
            if(0 < count($bad_options)) {
                throw new InvalidArgumentException(sprintf('Next options are invalid for router configuration: %s', implode(', ', $bad_options)));
            }
        }

        $this->options = new Arguments($options);
    }

    /**
     * Apply configuration for router
     * 
     * @param Router $router Router object
     * @param array $options Array options
     * @return void
     */
    public static function configure(Router &$router, array $options): void {
        (new self($options))->setBasepath($router)->setSupportedRequestMethods($router)->setViewsPath();
    }

    /**
     * Set the basepath if the router lives in a subdirectory
     * 
     * @return Configurator
     */
    private function setBasepath(Router &$router): Configurator {
        $router->basepath = $this->options->has('set.basepath') ? str_path($this->options->get('set.basepath')) : remove_trailing_slash(str_replace(['\\', ' '], ['/', '%20'], dirname($_SERVER['SCRIPT_NAME'])), '/\\');

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
        } else {
            $router->supported_request_methods = ['GET', 'POST'];
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
     * Normalize the array for http request methods definition
     * 
     * @param array $haystack An array
     * @return void
     */
    private function normalize(array &$haystack): void {
        $haystack = array_map('trim', $haystack);
        $haystack = array_map('strtoupper', $haystack);
    }
 
}

?>