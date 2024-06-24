<?php declare(strict_types = 1);
/**
 * @author    Luis Arturo Rodríguez
 * @copyright Copyright (c) 2022-2024 Luis Arturo Rodríguez <rguezque@gmail.com>
 * @link      https://github.com/rguezque
 * @license   https://opensource.org/licenses/MIT    MIT License
 */

namespace rguezque\Forge\Router;

/**
 * Represent a json response
 */
class JsonResponse extends Response {

    /**
     * Create the json response object
     * 
     * @param array|string $data Data to response as json
     * @param bool $encode Especify if must convert to json
     */
    public function __construct($data, bool $encode = true) {
        $json_data = $encode ? json_encode($data, JSON_PRETTY_PRINT) : $data;
        parent::__construct(
            $json_data, 
            Response::HTTP_OK, 
            [
                'Content-Type' => 'application/json;charset=UTF-8'
            ]
        );
    }

}

?>