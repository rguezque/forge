<?php declare(strict_types = 1);

namespace rguezque\Forge\Route;

use InvalidArgumentException;
use mysqli;
use mysqli_sql_exception;
use PDO;
use PDOException;

/**
 * Represents a MySQL connection with PDO driver or mysqli
 * 
 * @method PDO getConnection(array $params) Return a Singleton PDO connection
 * @method PDO autoConnect() Return a PDO connection from stored params into .env file (using dotenv library)
 * @method array dsnParser(string $url) Parse a database URL
 */
class DbConnection {

    /**
     * PDO connection
     * 
     * @var PDO|mysqli
     */
    private static $connection = null;

    /**
     * Return a Singleton MySQL connection
     * 
     * @param array $params connection params
     * @return PDO|mysqli
     * @throws PDOException
     * @throws mysqli_sql_exception
     * @throws InvalidArgumentException
     */
    public static function getConnection(array $params) {
        if(!self::$connection) {
            $driver = $params['driver'];

            switch($driver) {
                case 'pdomysql':
                    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s;', $params['host'] ?? '127.0.0.1', $params['port'] ?? 3306, $params['dbname'], $params['charset'] ?? 'utf8');
                    $options = $params['options'] ?? [
                        PDO::ATTR_PERSISTENT => true,
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                    ];
        
                    try {
                        self::$connection = new PDO($dsn, $params['user'], $params['pass'] ?? '', $options);
                    } catch(PDOException $e) {
                        throw new PDOException(sprintf('Failed to connect to MySQL with PDO driver: %s', $e->getMessage()));
                    }
                    break;
                case 'mysqli':
                    // You should enable error reporting for mysqli before attempting to make a connection
                    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

                    $mysqli = new mysqli($params['host'] ?? '127.0.0.1', $params['user'], $params['pass'] ?? '', $params['dbname'], intval($params['port']) ?? 3306, $params['socket'] ?? null);

                    if($mysqli->connect_errno) {
                        throw new mysqli_sql_exception(sprintf('Failed to connect to MySQL with mysqli: %s', $mysqli->connect_error));
                    }

                    $mysqli->set_charset($params['charset'] ?? 'utf8');
                    self::$connection = $mysqli;
                    break;
                default:
                    throw new InvalidArgumentException('Invalid value for parameter "driver", mandatory to define as "pdomysql" or "mysqli".');
            }
        }

        return self::$connection;
    }

    /**
     * Return a PDO connection from stored params into .env file (using dotenv library)
     * 
     * @return PDO|mysqli
     */
    public static function autoConnect() {
        if(isset($_ENV['DB_DRIVER']) && !in_array($_ENV['DB_DRIVER'], ['pdomysql', 'mysqli'])) {
            throw new InvalidArgumentException('Invalid value for "DB_DRIVER" variable, mandatory to define as "pdomysql" or "mysqli".');
        }

        $params = [
            'driver' => $_ENV['DB_DRIVER'] ?? 'pdomysql',
            'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
            'port' => $_ENV['DB_PORT'] ?? 3306,
            'dbname' => $_ENV['DB_NAME'],
            'charset' => $_ENV['DB_CHARSET'] ?? 'utf8',
            'user' => $_ENV['DB_USER'],
            'pass' => $_ENV['DB_PASS'] ?? ''
        ];

        return self::getConnection($params);
    }

    /**
     * Parse a database URL
     * 
     * @param string $url Database URL
     * @return array
     * @throws InvalidArgumentException
     */
    public static function dsnParser(string $url): array {
        $dsn = parse_url($url);

        if(isset($dsn['scheme']) && !in_array($dsn['scheme'], ['pdomysql', 'mysqli'])) {
            throw new InvalidArgumentException('Invalid value for "scheme" component of database URL, mandatory to define as "pdomysql" or "mysqli".');
        }

        if(isset($dsn['query'])) {
            parse_str($dsn['query'], $segments);
            $charset = $segments['charset'];
            $socket = $segments['socket'];
        }

        return [
            'driver' => $dsn['scheme'] ?? 'pdomysql',
            'host' => $dsn['host'] ?? '127.0.0.1',
            'port' => $dsn['port'] ?? 3306,
            'dbname' => trim($dsn['path'], '/\\'),
            'charset' => $charset ?? 'utf8',
            'user' => $dsn['user'],
            'pass' => $dsn['pass'] ?? '',
            'socket' => $socket ?? null
        ];
    }
}

?>