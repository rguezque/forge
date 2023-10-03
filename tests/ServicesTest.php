<?php declare(strict_types = 1);

require __DIR__.'/../vendor/autoload.php';

use rguezque\Forge\Exceptions\DuplicityException;
use rguezque\Forge\Route\Services;
use PHPUnit\Framework\TestCase;

class ServicesTest extends TestCase {

    private $services;

    protected function setUp(): void {
        $this->services = new Services;
        $this->services->register('pi', function() {
            return 3.141592654;
        })
        ->register('suma', function(int $a, int $b) {
            return $a + $b;
        })
        ->register('is_even', function(int $x) {
            return $x % 2 === 0;
        });
    }

    public function testRegister() {
        $this->expectException(DuplicityException::class);
        
        $this->services->register('pi', function() {
            return 3.1416;
        });
    }

    public function testGet() {
        $pi = $this->services->pi();
        $this->assertEquals(3.141592654, $pi);
    }

    public function testHas() {
        $this->assertTrue($this->services->has('pi'));
    }

    public function testUnregister() {
        $this->assertCount(3, $this->services->all());
        $this->services->unregister('suma', 'pi');
        $this->assertCount(1, $this->services->all());
    }
}

?>