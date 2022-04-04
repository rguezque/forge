<?php declare(strict_types = 1);

require __DIR__.'/../vendor/autoload.php';

use Forge\Route\Dependency;
use Forge\Route\Injector;
use Forge\Route\View;
use PHPUnit\Framework\TestCase;

class InjectorTest extends TestCase {

    private $injector;

    protected function setUp(): void {
        $this->injector = new Injector;
    }

    public function testAdd() {
        $view = $this->injector->add('view', View::class)->addParameter(__DIR__.'/templates');
        $this->assertInstanceOf(Dependency::class, $view);
        $params = $view->getParameters();
        $this->assertIsArray($params);
        $this->assertCount(1, $params);
        $dep = $view->getDependency();
        $this->assertIsString($dep);
    }

    public function testGet() {
        $this->injector->add('foo', function() {
            return 'Lorem ipsum';
        });
        $foo = $this->injector->get('foo');
        $this->assertIsString($foo);
        $this->assertEquals('Lorem ipsum', $foo);
        $this->injector->add('view', View::class)->addParameter(__DIR__.'/templates');
        $view = $this->injector->get('view');
        $this->assertInstanceOf(View::class, $view);
    }

    public function testHas() {
        $this->injector->add('view', View::class)->addParameter(__DIR__.'/templates');
        $has = $this->injector->has('view');
        $this->assertTrue($has);
    }
}

?>