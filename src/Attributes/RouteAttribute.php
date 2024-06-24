<?php declare(strict_types = 1);
/**
 * @author    Luis Arturo Rodríguez
 * @copyright Copyright (c) 2022-2024 Luis Arturo Rodríguez <rguezque@gmail.com>
 * @link      https://github.com/rguezque
 * @license   https://opensource.org/licenses/MIT    MIT License
 */

namespace rguezque\Forge\Router\Attributes;

use Attribute;

use function rguezque\Forge\functions\str_path;

/**
 * It represents a route definition and is used only to create an instance and access the 
 * class arguments.
 * 
 * The class name is used as an attribute when defining a route in the form of annotations 
 * directly (and strictly) in each method of a controller class.
 * 
 * The attribute "Attribute" indicates that this class will be used as an attribute and can 
 * only be used as an attribute in the methods of a controller class.
 */
#[Attribute(Attribute::TARGET_METHOD)]
class RouteAttribute {
    
    public $method = '';

    public $name = '';

    public $path = '';

    public function __construct(string $method, string $name, string $path) {
        $this->method = strtoupper(trim($method));
        $this->name = trim($name);
        $this->path = str_path($path);
    }
}

?>