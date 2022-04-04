<?php declare(strict_types = 1);
/**
 * @author    Luis Arturo Rodríguez
 * @copyright Copyright (c) 2021 Luis Arturo Rodríguez <rguezque@gmail.com>
 * @license   https://opensource.org/licenses/MIT    MIT License
 */

namespace Forge\Route;

use Forge\Exceptions\FileNotFoundException;
use Forge\Exceptions\MissingArgumentException;

use function Forge\functions\add_trailing_slash;

/**
 * Render templates.
 * 
 * @method View setPath(string $path) Set the views path
 * @method View addArgument(string $key, $value) Add a parameter
 * @method View addArguments(array $arguments) Add parameters array
 * @method View template(string $file, array $variables = []) Set a view to render
 * @method View extend(string $file, array $variables = array(), string $extend_name) Add a view to buffer to extend a main view
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

        if('' !== $templates_dir) {
            $this->path = add_trailing_slash($templates_dir);
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
     * @param array $variables View parameters
     * @return View
     * @throws FileNotFoundException
     */
	public function template(string $file, array $variables = []): View {
        $file = $this->path . $file;
        
        if (!file_exists($file)) {
            throw new FileNotFoundException(sprintf('Don\'t exists the template file "%s".', $file));
        }

        if(is_array($variables) && !empty($variables)) {
            foreach($variables as $key => $var) {
                $this->arguments->set($key, $var);
            }
        }
        
        $this->view_file = $file;
        return $this;
    }

    /**
     * Return a fetched main view in buffer to render
     * 
     * @return string
     * @throws MissingArgumentException
     */
    public function render(): string {
        if(!$this->view_file) {
            throw new MissingArgumentException('The view file wasn\'t not declared.');
        }

        extract($this->arguments->all());
        ob_start();
        include $this->view_file;
        $rendered_view = ob_get_clean();

        return $rendered_view;
    }

    /**
     * Add a view to buffer to extend a main view
     * 
     * @param string $file View file name
     * @param array $variables View parameters
     * @param string $extend_name View name
     * @return View
     */
    public function extend(string $file, array $variables = array(), string $extend_name): View {
        $this->addArgument($extend_name, $this->render($file, $variables));

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

}

?>