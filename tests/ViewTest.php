<?php declare(strict_types = 1);

require __DIR__.'/../vendor/autoload.php';

use rguezque\Forge\Router\View;
use PHPUnit\Framework\TestCase;

class ViewTest extends TestCase {

    private $view;

    protected function setUp(): void {
        $this->view = new View(__DIR__.'/templates');
    }

    public function testRender() {
        $this->view->template('index.php');
        echo $this->view->render();
        $this->expectOutputString('Hola mundo!');
    }

    public function testExtends() {
        $this->view->extendWith('header.php', ['message' => 'Hola mundo'], 'message_header');
        $this->view->template('hello.php');
        echo $this->view->render();
        $this->expectOutputString('<h1>Hola mundo</h1>');
    }

}

?>