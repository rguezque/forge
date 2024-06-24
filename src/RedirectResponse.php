<?php declare(strict_types = 1);
/**
 * @author    Luis Arturo Rodríguez
 * @copyright Copyright (c) 2022-2024 Luis Arturo Rodríguez <rguezque@gmail.com>
 * @link      https://github.com/rguezque
 * @license   https://opensource.org/licenses/MIT    MIT License
 */

namespace rguezque\Forge\Router;

/**
 * Represent a redirect response
 */
class RedirectResponse extends Response {

    /**
     * Create the redirect response object
     * 
     * @param string $uri URI to redirect
     */
    public function __construct(string $uri) {
        parent::__construct('', Response::HTTP_SEE_OTHER, ['location' => $uri]);
    }

}

?>