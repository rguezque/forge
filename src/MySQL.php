<?php declare(strict_types = 1);
/**
 * @author    Luis Arturo Rodríguez
 * @copyright Copyright (c) 2021 Luis Arturo Rodríguez <rguezque@gmail.com>
 * @link      https://github.com/rguezque
 * @license   https://opensource.org/licenses/MIT    MIT License
 */

namespace Forge\Route;

use Forge\Exceptions\DuplicityException;
use Forge\Exceptions\NotFoundException;
use PDO;

/**
 * Represent a PDO MySQL connection
 * 
 * @method get(string $name) Return a PDO MySQL connection by name. Without params retrieve default connection (first registered)
 * @method add($name, array $params) Add a PDO MySQL connection to the collection
 * @method has(string $name) Return true if a PDO connection exists
 */
class MySQL {

    /**
     * Registered connections
     * 
     * @var PDO[]
     */
    private $dbs = [];

    /**
     * Create a PDO MySQL connection
     * 
     * @param array $params PDO connection params
     * @param string $name PDO connection name
     */
    public function __construct(array $params, string $name = 'default') {
        $this->add($name, $params);
    }

    /**
     * Return a PDO MySQL connection by name. Without params retrieve default connection (first registered)
     * 
     * @param string $name Connection name
     * @return PDO
     * @throws NotFoundException
     */
    public function get(string $name = 'default'): PDO {
        $name = trim(strtolower($name));

        if(!array_key_exists($name, $this->dbs)) {
            throw new NotFoundException(sprintf('Do not exists a MySQL PDO connection with name "%s"', $name));
        }

        $db = $this->dbs[$name];

        if(!isset($db['connection'])) {
            $params = $db['params'];
            $pdo = new PDO($params['dsn'], $params['username'], $params['password'], [
                PDO::ATTR_PERSISTENT => $params['persistent'],
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);

            $db['connection'] = $pdo;
        }

        return $db['connection'];
    }

    /**
     * Add a PDO MySQL connection params to the collection
     * 
     * @param string $name Connection name
     * @param array $params Connection params
     * @return DuplicityException
     */
    public function add(string $name, array $params) {
        $name = trim(strtolower($name));

        if(array_key_exists($name, $this->dbs)) {
            throw new DuplicityException(sprintf('Already exists a MySQL PDO connection with name "%s"', $name));
        }

        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s;', $params['host'], $params['port'] ?? 3306, $params['dbname'], $params['charset'] ?? 'utf8');
        $username = $params['username'];
        $password = $params['password'];
        $persistent = $params['persistent'] ?? true;
        $this->dbs[$name]['params'] = [
            'dsn' => $dsn,
            'úsername' => $username,
            'password' => $password,
            'persistent' => $persistent
        ];
    }

    /**
     * Return true if a PDO connection exists
     * 
     * @param string $name PDO connection name
     * @return bool
     */
    public function has(string $name): bool {
        return array_key_exists($name, $this->dbs);
    }

    /**
     * Allow to acces connection in object context
     * 
     * @param string $name PDO connection name
     * @return PDO
     * @throws NotFoundException
     */
    public function __get(string $name): PDO {
        return $this->get($name);
    }
}

?>