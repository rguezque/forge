<?php declare(strict_types = 1);

require __DIR__.'/../vendor/autoload.php';

use rguezque\Forge\Route\Arguments;
use PHPUnit\Framework\TestCase;

class ArgumentsTest extends TestCase {

    private $args;

    protected function setUp(): void {
        $data = [
            'name' => 'John',
            'lastname' => 'Doe',
            'age' => 29,
            'birthday' => [
                'year' => 1991,
                'month' => 10,
                'day' => 23
            ]
        ];
        $this->args = new Arguments($data);
    }

    public function testSet() {
        $this->args->set('country', 'Mexico');
        $all = $this->args->all();
        $this->assertIsArray($all);
        $this->assertArrayHasKey('country', $all);
        $this->assertContains('Mexico', $all);
    }

    public function testRemove() {
        $this->args->remove('lastname');
        $all = $this->args->all();
        $this->assertIsArray($all);
        $this->assertArrayNotHasKey('lastname', $all);
        $this->assertNotContains('Doe', $all);
    }

    public function testClear() {
        $this->args->clear();
        $all = $this->args->all();
        $this->assertEmpty($all);
    }
}

?>