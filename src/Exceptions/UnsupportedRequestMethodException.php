<?php declare(strict_types = 1);
/**
 * @author    Luis Arturo Rodríguez
 * @copyright Copyright (c) 2021 Luis Arturo Rodríguez <rguezque@gmail.com>
 * @license   https://opensource.org/licenses/MIT    MIT License
 */

namespace Forge\Exceptions;

use Exception;

/**
 * Throws a exception when a request method is not supported by the router.
 */
class UnsupportedRequestMethodException extends Exception {

}

?>