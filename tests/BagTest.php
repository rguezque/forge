<?php declare(strict_types = 1);

require __DIR__.'/../vendor/autoload.php';

use rguezque\Forge\Router\Bag;
use PHPUnit\Framework\TestCase;

class BagTest extends TestCase {

    private $bag;

    protected function setUp(): void {
        $data = [
            'name' => 'John',
            'lastname' => 'Doe',
            'age' => 29,
            'birthday' => [
                'year' => 1991,
                'month' => 10,
                'day' => 23
            ],
            'decess-date' => null
        ];
        $this->bag = new Bag($data);
    }

    public function testGet() {
        $lastname = $this->bag->get('lastname');
        $this->assertEquals('Doe', $lastname);
    }

    public function testCount() {
        $all = $this->bag->all();
        $birthday = $this->bag->get('birthday')->all();
        $this->assertCount(5, $all);
        $this->assertCount(3, $birthday);
    }

    public function testInstanceOf() {
        $section = $this->bag->get('birthday');
        $this->assertInstanceOf(Bag::class, $section);
        $this->assertInstanceOf(Bag::class, $this->bag);
    }

    public function testIsValid() {
        $valid = $this->bag->valid('name');
        $this->assertTrue($valid);
    }

    public function testIsNotValid() {
        $valid = $this->bag->valid('decess-date');
        $this->assertNotTrue($valid);
    }

    public function testHasNot() {
        $has = $this->bag->has('address');
        $this->assertFalse($has);
    }

    public function testHas() {
        $has = $this->bag->has('age');
        $this->assertTrue($has);
    }

    public function testIsNum() {
        $age = $this->bag->get('age');
        $this->assertIsInt($age);
    }
}

?>