<?php declare(strict_types = 1);

namespace Forge\Route;

class Users {


    public function login($username, $password): bool {
        return 'JognFor' == $username && 'Bjornsson' == $password;
    }

    public function getUsername(): string {
        return 'JognFor';
    }

    public function getRole(): string {
        return 'ROLE_ADMIN';
    }
}

?>