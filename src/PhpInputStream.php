<?php declare(strict_types = 1);
/**
 * @author    Luis Arturo Rodríguez
 * @copyright Copyright (c) 2022 Luis Arturo Rodríguez <rguezque@gmail.com>
 * @link      https://github.com/rguezque
 * @license   https://opensource.org/licenses/MIT    MIT License
 */

namespace rguezque\Forge\Route;

/**
 * Represent a read-only stream
 * 
 * @method Bag getParsedStr() Parses the raw query string into variables
 * @method Bag getDecodedJson() Decode variables from a json string
 * @method string getRaw() Return the raw stream
 */
class PhpInputStream {

    /**
     * Is a read-only stream that allows reading data from the requested body
     * 
     * @var string
     */
    private $stream;

    public function __construct($body = 'php://input') {
        $this->stream = file_get_contents($body);
    }

    /**
     * Return the parsed raw query string into variables
     * 
     * @return Bag
     */
    public function getParsedStr(): Bag {
        parse_str($this->stream, $data);

        return new Bag($data);
    }

    /**
     * Return decoded variables from a json string
     * 
     * @return Bag
     */
    public function getDecodedJson(): Bag {
        $data = json_decode($this->stream, true);

        return new Bag($data);
    }

    /**
     * Return the raw stream
     * 
     * @return string
     */
    public function getRaw(): string {
        return $this->stream;
    }

}

?>