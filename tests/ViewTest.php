<?php declare(strict_types = 1);

require __DIR__.'/../vendor/autoload.php';

use Forge\Route\View;
use PHPUnit\Framework\TestCase;

class ViewTest extends TestCase {

    private $view;

    protected function setUp(): void {
        $this->view = new View(__DIR__.'/templates');
    }

    public function testRender() {
        echo $this->view->render('index');
        $this->expectOutputString('Hola mundo!');
    }

    public function testExtends() {
        $this->view->extendWith('header', ['message' => 'Hola mundo'], 'message_header');
        echo $this->view->render('hello');
        $this->expectOutputString('<h1>Hola mundo</h1>');
    }

}

?>