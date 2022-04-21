<?php declare(strict_types = 1);

require __DIR__.'/../vendor/autoload.php';

use Forge\Exceptions\DuplicityException;
use Forge\Route\Services;
use PHPUnit\Framework\TestCase;

class ServicesTest extends TestCase {

    private $services;

    protected function setUp(): void {
        $this->services = new Services;
        $this->services->register('pi', function() {
            return 3.141592654;
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
}

?>