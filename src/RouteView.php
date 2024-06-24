<?php declare(strict_types = 1);
/**
 * @author    Luis Arturo Rodríguez
 * @copyright Copyright (c) 2022-2024 Luis Arturo Rodríguez <rguezque@gmail.com>
 * @link      https://github.com/rguezque
 * @license   https://opensource.org/licenses/MIT    MIT License
 */

namespace rguezque\Forge\Router;

use rguezque\Forge\Exceptions\FileNotFoundException;

use function rguezque\Forge\functions\remove_leading_slash;
use function rguezque\Forge\functions\str_path;

/**
 * Represent a simple route that render a view, without define a controller
 * 
 * @method Response render() Return a view to render
 * @method void addArguments(array $arguments) Add arguments to already existents
 */
class RouteView extends Route {

    /**
     * PHP template file
     * 
     * @var string
     */
    private $template;

    /**
     * Default path to search for templates
     * 
     * @var string
     */
    private $views_path;

    /**
     * Create the route definition and view to render
     * 
     * @param string $name Route name
     * @param string $path Route path
     * @param string $template Template file path
     * @param array $arguments Arguments to pass to template
     */
    public function __construct(
        string $name, 
        string $path, 
        string $template, 
        array $arguments = []
    ) {
        $this->path = str_path($path);
        $this->template = remove_leading_slash($template);
        $this->arguments = $arguments;
        $this->request_method = 'GET';
        $this->name = $name;
        $this->views_path = Globals::has('router.views.path') 
            ? Globals::get('router.views.path') 
            : '';
    }

    /**
     * Return a view to render
     * 
     * @return Response
     * @throws FileNotFoundException
     */
    public function render(): Response {
        $view = new View;
        $rendered_view = $view->template($this->template, $this->arguments)->render();

        return new Response($rendered_view, Response::HTTP_OK);
    }

    /**
     * Add arguments to already existents
     * 
     * @param array $arguments Arguments to add
     * @return void
     */
    public function addArguments(array $arguments): void {
        $this->arguments = array_merge($this->arguments, $arguments);
    }

}