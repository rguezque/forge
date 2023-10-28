<?php declare(strict_types = 1);
/**
 * @author    Luis Arturo Rodríguez
 * @copyright Copyright (c) 2022 Luis Arturo Rodríguez <rguezque@gmail.com>
 * @link      https://github.com/rguezque
 * @license   https://opensource.org/licenses/MIT    MIT License
 */

namespace rguezque\Forge\Router;

/**
 * Represents a response emitter.
 * 
 * @method void emit(Response $response) Emit a response
 */
class Emitter extends Response {

    /**
     * Emit a response
     * 
     * @param Response $response A Response object
     * @return void
     */
    public static function emit(Response $response): void {
        if (ob_get_length() > 0) {
            ob_end_clean();
        }
            
        if(!headers_sent()) {
            $response->sendHeaders();
        }
        
        $response->sendContent();
    }

}

?>