<?php
/**
 * @author    Luis Arturo Rodríguez
 * @copyright Copyright (c) 2021 Luis Arturo Rodríguez <rguezque@gmail.com>
 * @link      https://github.com/rguezque
 * @license   https://opensource.org/licenses/MIT    MIT License
 */

namespace rguezque\Forge\Route;

use PDO;

/**
 * @method Users setUsersTable(string $tablename) Specifies the table that contain users data
 * @method Users setIdentityField(string $identity_field) Specifies the username field
 * @method Users setCredentialField(string $credential_field) Specifies the password field
 * @method array findUser(string $username, string $password) Find a user
 */
class Users {
	/**
     * PDO connection
     *
     * @var PDO
     */
    private $connection;
    
    /**
     * Users tablename
     * 
     * @var string
     */
    private $tablename;

    /**
     * Username field
     * 
     * @var string
     */
    private $identity_field;

    /**
     * Password field
     * 
     * @var string
     */
    private $credential_field;

	/**
     * Constructor
     *
     * @param PDO $db PDO connection
     * @param array $properties Schema users table
     */
	public function __construct(PDO $db, array $properties = []) {
        $this->connection       = $db;
        $this->tablename        = $properties['tablename'] ?? 'users';
        $this->identity_field   = $properties['identity_field'] ?? 'username';
        $this->credential_field = $properties['credential_field'] ?? 'password';
    }

    /**
     * Specifies the table that contain users data
     * 
     * @param string $tablename Table name
     * @return Users
     */
    public function setUsersTable(string $tablename): Users {
        $this->tablename = $tablename;

        return $this;
    }
    
    /**
     * Specifies the username field
     * 
     * @param string $identity_field User field
     * @return Users
     */
    public function setIdentityField(string $identity_field): Users {
        $this->identity_field = $identity_field;

        return $this;
    }

    /**
     * Specifies the password field
     * 
     * @param string $credential_field Password field
     * @return Users
     */
    public function setCredentialField(string $credential_field): Users {
        $this->credential_field = $credential_field;

        return $this;
    }

	/**
     * Find a user
     *
     * @param string $username Username
     * @param string $password Password
     * @return array
     */ 
	public function findUser(string $username, string $password): array {
        $sql = sprintf('SELECT * FROM %s WHERE %s = :username AND %s = :password', $this->tablename, $this->identity_field, $this->credential_field);
		$query = $this->connection->prepare($sql);
		$query->bindValue(':username', $username, PDO::PARAM_STR);
		$query->bindValue(':password', $password, PDO::PARAM_STR);
		$query->execute();

        return 0 < $query->rowCount() ? $query->fetch() : [];
	}

}
?>