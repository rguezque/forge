<?php declare(strict_types = 1);
/**
 * @author    Luis Arturo Rodríguez
 * @copyright Copyright (c) 2022 Luis Arturo Rodríguez <rguezque@gmail.com>
 * @link      https://github.com/rguezque
 * @license   https://opensource.org/licenses/MIT    MIT License
 */

namespace Forge\Route;

use Forge\Interfaces\ArgumentsInterface;
use Forge\Interfaces\BagInterface;

/**
 * Represents a PHP session.
 * 
 * @method void start() Starts once a session
 * @method bool started() Return true if already exists an active session, otherwise false
 * @method void set(string $key, $value) Set or overwrite a session var
 * @method void get(string $key, $default = null) If exists, retrieve a session var by name, otherwise returns default
 * @method string getNamespace() Retrieve the current session vars namespace
 * @method array all() Retrieve all session vars in the current namespace
 * @method bool has(string $key) Return true if exists a session var by name
 * @method bool valid(string $key) Return true if a session var is not null and is not empty
 * @method int count() Return the count of session vars
 * @method void remove(string $key) Removes a session var by name
 * @method void clear() Removes all session vars
 * @method bool destroy() Destroy the active session
 */
class Session implements ArgumentsInterface, BagInterface {

    /**
     * Session vars namespace
     * 
     * @var string
     */
    private $namespace;

    /**
     * Initialize a session
     * 
     * @param string $namespace Session vars namespace
     */
    public function __construct(string $namespace) {
        $this->start();
        $this->namespace = trim($namespace);
    }

    /**
     * Starts once a session
     * 
     * @return void
     */
    public function start(): void {
        if(!$this->started()) {
            session_start();
        }
    }

    /**
     * Return true if already exists an active session, otherwise false
     * 
     * @return bool
     */
    public function started(): bool {
        return session_status() === PHP_SESSION_ACTIVE;
    }

    /**
     * Set or overwrite a session var
     * 
     * @param string $key Variable name
     * @param mixed $value Variable value
     * @return void
     */
    public function set(string $key, $value): void {
        $this->start();
        $_SESSION[$this->namespace][$key] = $value;
    }

    /**
     * Set or overwrite a session var in object context
     * 
     * @param string $key Variable name
     * @param mixed $value Variable value
     * @return void
     */
    public function __set(string $key, $value): void {
        $this->set($key, $value);
    }

    /**
     * If exists, retrieve a session var by name, otherwise returns default
     * 
     * @param string $key Variable name
     * @param mixed $default Default value to return
     * @return mixed
     */
    public function get(string $key, $default = null) {
        $this->start();
        return $this->has($key) ? $_SESSION[$this->namespace][$key] : $default;
    }

    /**
     * Retrieve a session var by name in object context
     * 
     * @param string $key Variable name
     * @return mixed
     */
    public function __get(string $key) {
        return $this->get($key);
    }

    /**
     * Retrieve the current session vars namespace
     * 
     * @return string
     */
    public function getNamespace():string {
        return $this->namespace;
    }

    /**
     * Retrieve all session vars in the current namespace
     * 
     * @return array
     */
    public function all(): array {
        $this->start();
        return (array) $_SESSION[$this->namespace];
    }

    /**
     * Return true if exists a session var by name
     * 
     * @param string $key Variable name
     * @return bool
     */
    public function has(string $key): bool {
        $this->start();
        return array_key_exists($this->namespace, $_SESSION) && array_key_exists($key, $_SESSION[$this->namespace]);
    }

    /**
     * Return true if a session var is not null and is not empty
     * 
     * @param string $key Variable name
     * @return bool
     */
    public function valid(string $key): bool {
        $this->start();
        return $this->has($key) && !empty($_SESSION[$this->namespace][$key]) && !is_null($_SESSION[$this->namespace][$key]);
    }

    /**
     * Return the count of session vars
     * 
     * @return int
     */
    public function count(): int {
        $this->start();
    	return sizeof($_SESSION[$this->namespace]);
    }

    /**
     * Remove a session var by name
     * 
     * @param string $key Variable name
     * @return void
     */
    public function remove(string $key): void {
        $this->start();
        unset($_SESSION[$this->namespace][$key]);
    }

    /**
     * Removes all session vars
     * 
     * @return void
     */
    public function clear(): void {
        $_SESSION[$this->namespace] = [];
    }

    /**
     * Destroy the active session
     * 
     * @return bool True on success or false on failure
     */
    public function destroy(): bool {
        $this->start();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), 
                '', 
                time() - 42000,
                $params["path"], 
                $params["domain"],
                $params["secure"], 
                $params["httponly"]
            );
        }
        $this->clear();
        return session_destroy();
    }

    /**
     * Print all session vars in readable format if the class is invoked like a string
     * 
     * @return string
     */
    public function __toString(): string {
        $this->start();
        return sprintf('<pre>%s</pre>', print_r($_SESSION[$this->namespace], true));
    }

}
?>