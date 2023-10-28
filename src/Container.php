<?php declare(strict_types = 1);

namespace rguezque\Forge\Router;

/**
 * Facade for Injector (Dependencies container)
 */
class Container {

    /**
     * Store the dependency container
     * 
     * @var Injector
     */
    private static Injector $injector;

    /**
     * Allow call statically the methods of Injector
     * 
     * @param string $name Method name
     * @param array 4params Method parameters
     * @return mixed
     */
    public static function __callStatic(string $name, array $params) {
        $app = self::app();

        return call_user_func_array([$app, $name], array_values($params));
    }

    /**
     * Return a Injector object. Implement Singleton pattern
     * 
     * @return Injector
     */
    public static function app(): Injector {
        static $initialized = false;

        if (!$initialized) {
            self::$injector = new Injector();

            $initialized = true;
        }

        return self::$injector;
    }
}

?>