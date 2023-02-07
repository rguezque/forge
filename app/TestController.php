<?php declare(strict_types = 1);

namespace App;

use Forge\Route\Request;
use Forge\Route\Response;

class TestController {
    public function indexAction(Request $request, Response $response) {
        return $response->withContent('Hola mundo!');
    }

    public function formAction(Request $request, Response $response) {
        $form = '<form action="/form_admin/login" method="post">
        <input type="text" name="_username" autofocus>
        <input type="password" name="_password">
        <input type="hidden" name="_redirect_success" value="/admin">
        <input type="hidden" name="_redirect_fail" value="/login_form">
        <input type="submit" value="Login">
        </form>';
        
        return $response->withContent($form);
    }
}

?>