<?php declare(strict_types = 1);
/**
 * @author    Luis Arturo Rodríguez
 * @copyright Copyright (c) 2022-2024 Luis Arturo Rodríguez <rguezque@gmail.com>
 * @link      https://github.com/rguezque
 * @license   https://opensource.org/licenses/MIT    MIT License
 */

namespace rguezque\Forge\Router;

use rguezque\Forge\Exceptions\FileNotFoundException;
use rguezque\Forge\Exceptions\MissingArgumentException;

use function rguezque\Forge\functions\add_trailing_slash;

/**
 * Render templates.
 * 
 * @method View setPath(string $path) Set the views path
 * @method View addArgument(string $key, $value) Add a parameter
 * @method View addArguments(array $arguments) Add parameters array
 * @method View template(string $file, array $variables = []) Set a view to render
 * @method View extendWith(string $file, array $variables = array(), string $extend_name) Add a view to buffer to extend a main view
 * @method string render() Returns a fetched view in buffer to render
 */
class View {

    /**
     * Views path
     * 
     * @var string
     */
    private $path;

    /**
     * Views arguments
     * 
     * @var Arguments
     */
    private $arguments;

    /**
     * Template file
     * 
     * @var string
     */
    private $view_file;

    /**
     * Views constructor
     * 
     * @param string $templates_dir Templates files directory
     * @throws MissingArgumentException
     */
    public function __construct(string $templates_dir = '') {
        $this->arguments = new Arguments();

        if('' !== trim($templates_dir)) {
            $this->path = add_trailing_slash(trim($templates_dir));
        } else if(Globals::has('router.views.path')) {
            $this->path = Globals::get('router.views.path');
        } else {
            throw new MissingArgumentException('Missing argument for views path. Expected at least 1 argument.');
        }
    }

    /**
     * Set a view to render
     *
     * @param string $file View name
     * @param array $params View parameters
     * @return View
     * @throws FileNotFoundException
     */
	public function template(string $file, array $params = []): View {
        $file = $this->path . trim($file);
        
        if (!file_exists($file)) {
            throw new FileNotFoundException(sprintf('Don\'t exists the template file "%s".', $file));
        }

        $this->view_file = $file;

        if(is_array($params) && [] !== $params) {
            foreach($params as $key => $var) {
                $this->arguments->set($key, $var);
            }
        }
        
        return $this;
    }

    /**
     * Return as string a fetched main view in buffer to render
     * 
     * @return string
     * @throws MissingArgumentException
     */
    public function render(): string {
        if(!isset($this->view_file)) {
            throw new MissingArgumentException('The view file wasn\'t not declared.');
        }

        return $this->getRender($this->view_file, $this->arguments->all());
    }

    /**
     * Add a view to buffer to extend a main view
     * 
     * @param string $file View file name
     * @param string $extend_name View name
     * @param array $variables View parameters
     * @return View
     */
    public function extendWith(string $file, string $extend_name, array $variables = []): View {
        $file = $this->path . $file;
        $this->addArgument($extend_name, $this->getRender($file, $variables));

        return $this;
    }

    /**
     * Add an argument
     * 
     * @param string $key Argument name
     * @param mixed $value Argument value
     * @return View
     */
    public function addArgument(string $key, $value): View {
        $this->arguments->set($key, $value);

        return $this;
    }
    
    /**
     * Add arguments array
     * 
     * @param array $arguments Arguments array
     * @return View
     */
    public function addArguments(array $arguments): View {
        foreach ($arguments as $key => $value) {
            $this->addArgument($key, $value);
        }

        return $this;
    }

    /**
     * Set the views path
     * 
     * @param string $path Views path
     * @return View
     */
    public function setPath(string $path): View {
        $this->path = add_trailing_slash($path);

        return $this;
    }

    /**
     * Return a rendered template as string
     * 
     * @param string $template Template name
     * @param array $params Template parameters
     * @return string
     * @throws FileNotFoundException
     */
    private function getRender(string $template, array $params = []): string {
        if (!file_exists($template)) {
            throw new FileNotFoundException(sprintf('Don\'t exists the template file "%s".', $template));
        }

        // If there is an invalid variable name (for example an array that is not associative) 
        // it is assigned the prefix 'param_view' followed by the number of its index in the array. 
        // Example: extract([12, 32], EXTR_PREFIX_INVALID, 'param_view') it will generate 
        // $param_view_0 and $param_view_1 with values 12 and 32 respectively
        extract($params, EXTR_PREFIX_INVALID, 'param_view');
        
        ob_start();
        include $template;
        $rendered_view = ob_get_clean();

        return $rendered_view;
    }

}

?>