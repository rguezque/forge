<?php declare(strict_types = 1);
/**
 * @author    Luis Arturo Rodríguez
 * @copyright Copyright (c) 2022 Luis Arturo Rodríguez <rguezque@gmail.com>
 * @link      https://github.com/rguezque
 * @license   https://opensource.org/licenses/MIT    MIT License
 */

namespace rguezque\Forge\Router\Attributes;

use Attribute;

use function rguezque\Forge\functions\str_path;

/**
 * It represents a route group definition and is used only to create an instance and access the 
 * class arguments.
 * 
 * The class name is used as an attribute when defining a routes group in the form of annotations 
 * directly (and strictly) in beginning of each controller class.
 * 
 * The attribute "Attribute" indicates that this class will be used as an attribute and can only 
 * be used as an attribute in a controller class.
 */
#[Attribute(Attribute::TARGET_CLASS)]
class GroupAttribute {

    public $prefix = '';
    
    public function __construct(string $prefix) {
        $this->prefix = str_path($prefix);
    }
}

?>