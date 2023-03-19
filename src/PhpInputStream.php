<?php declare(strict_types = 1);
/**
 * @author    Luis Arturo Rodríguez
 * @copyright Copyright (c) 2022 Luis Arturo Rodríguez <rguezque@gmail.com>
 * @link      https://github.com/rguezque
 * @license   https://opensource.org/licenses/MIT    MIT License
 */

namespace Forge\Route;

/**
 * Represent a read-only stream
 * 
 * @method Bag getParsedStr() Parses the raw query string into variables
 * @method Bag getDecodedJson() Decode variables from a json string
 */
class PhpInputStream {

    /**
     * Is a read-only stream that allows reading data from the requested body
     * 
     * @var string
     */
    private $stream;

    function __construct($body = 'php://input') {
        $this->stream = file_get_contents($body);
    }

    /**
     * Parses the raw query string into variables
     * 
     * @return Bag
     */
    function getParsedStr(): Bag {
        parse_str($this->stream, $data);

        return new Bag($data);
    }

    /**
     * Decode variables from a json string
     * 
     * @return Bag
     */
    function getDecodedJson(): Bag {
        $data = json_decode($this->stream, true);

        return new Bag($data);
    }

}

?>