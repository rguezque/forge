<?php declare(strict_types = 1);

use Forge\Route\ClientRequest;
use PHPUnit\Framework\TestCase;

class ClientRequestTest extends TestCase {

    public function testGet() {
        $req = new ClientRequest('https://jsonplaceholder.typicode.com/users');
        $result = $req->send(); // returns json string
        $this->assertJson($result);
    }

    public function testPost() {
        $req = new ClientRequest('https://jsonplaceholder.typicode.com/posts');
        $req->withRequestMethod(ClientRequest::POST);
        $new = [
            'title' => 'Lorem ipsum',
            'body' => 'Integer bibendum mollis tellus',
            'userId' => 127
        ];
        $req->withPostFields($new);
        $result = $req->send(); // returns json string
        $this->assertJson($result);        
    }

    public function testPut() {
        $req = new ClientRequest('https://jsonplaceholder.typicode.com/posts/1');
        $req->withRequestMethod(ClientRequest::PUT);
        $update = [
            'id' => 1,
            'title' => 'foo',
            'body' => 'bar',
            'userId' => 1
        ];
        $req->withPostFields($update);
        $result = $req->send(); // returns json string
        $this->assertJson($result);
        $this->assertJsonStringEqualsJsonString(json_encode($update), $result);
    }

    public function testDelete() {
        $req = new ClientRequest('https://jsonplaceholder.typicode.com/posts/1');
        $req->withRequestMethod(ClientRequest::DELETE);
        $result = $req->send();
        $this->assertJson($result);
        $this->assertJsonStringEqualsJsonString('{}', $result);
    }
}

?>