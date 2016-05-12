<?php
namespace mvc;

/**
 * Created by PhpStorm.
 * @author DHarter
 * Date: 5/9/2016
 * Time: 3:38 PM
 */

use PDO;
use PDOException;

require_once($_SERVER['DOCUMENT_ROOT'] . "/../Repository/RepositoryInterface.php");

/**
 * Class EmployeeRepository
 * Contains the methods for interacting with the database employee object.
 *
 * @package mvc
 */
class EmployeeRepository implements RepositoryInterface
{

    /**
     * @var PDO $connection Database connection object.
     */
    private $connection;

    /**
     * EmployeeRepository constructor.
     * @param null $dbConnection
     */
    public function __construct($dbConnection = null)
    {
        if ($dbConnection == null) {
            $this->connection = self::instantiateConnection();
        } else {
            $this->connection = $dbConnection;
        }
    }

    /**
     * @param null $dbConnection
     * @return null|PDO
     */
    private static function instantiateConnection($dbConnection = null)
    {
        if ($dbConnection == null) {
            $dbConnection = new PDO(
                "mysql:host=" . self::DB_HOST . ";dbname=" . self::DB_NAME,
                self::DB_USER,
                self::DB_PASS
            );
            $dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        return $dbConnection;

    }

    /**
     * @param int $id
     * @param null $dbConnection
     * @return array|mixed|null
     */
    public static function find($id, $dbConnection = null)
    {
        if (empty($id)) {
            return null;
        }
        $connection = self::instantiateConnection($dbConnection);
        $query = "SELECT Id, 
                         last_name AS \"LastName\", 
                         first_name AS \"FirstName\", 
                         email AS \"Email\" 
                    FROM employee 
                  WHERE id = :id";
        $statement = $connection->prepare($query);
        $success = $statement->execute(['id' => $id]);
        $result = [];
        if ($success) {
            while ($employee = $statement->fetch()) {
                $result = $employee;
                break;
            }
        }

        if (empty($result)) {
            $result = null;
        }

        return $result;
    }

    /**
     * @param null $dbConnection
     * @return array|null
     */
    public static function findAll($dbConnection = null)
    {
        $connection = self::instantiateConnection($dbConnection);
        $query = "SELECT Id, last_name AS \"LastName\", first_name AS \"FirstName\", email AS \"Email\" FROM employee";
        $statement = $connection->prepare($query);
        $success = $statement->execute();
        $result = null;
        if ($success) {
            $result = $statement->fetchAll();
        }
        return $result;
    }

    /**
     * @param Object $employee
     * @return bool|null
     */
    public function save(&$employee)
    {
        $id = null;
        $results = null;
        $firstName = !empty($employee->getFirstName()) ? $employee->getFirstName() : "null";
        if (empty($employee->getId())) {
            $query = "INSERT INTO employee(last_name, first_name, email) VALUES (:lastName, :firstName, :email)";
            try {
                $statement = $this->connection->prepare($query);
                $results = $statement->execute([
                    'lastName' => $employee->getLastName(),
                    'firstName' => $firstName,
                    'email' => $employee->getEmail()
                ]);
                $id = $this->connection->lastInsertId();
                if ($id === 0) {
                    $employee->errors("Error creating Employee:  Invalid Id 0");
                    $results = false;
                } else {
                    $employee->setId($id);
                }
            } catch (PDOException $e) {
                $employee->errors("Error creating Employee: " . $e->getMessage());
                $results = false;
            }
        } else {
            $query =
                "UPDATE employee 
                 SET last_name = :lastName, 
                     first_name = :firstName, 
                     email = :email 
                 WHERE id = :id";
            try {
                $statement = $this->connection->prepare($query);
                $results = $statement->execute([
                    'lastName' => $employee->getLastName(),
                    'firstName' => $firstName,
                    'email' => $employee->getEmail(),
                    'id' => $employee->getId()
                ]);
            } catch (PDOException $e) {
                $employee->errors("Error updating Employee: " . $e->getMessage());
                $results = false;
            }
        }
        return $results;
    }

    /**
     * @param Object $employee
     * @return bool
     */
    public function destroy($employee)
    {
        $query = "DELETE FROM employee WHERE id = :id";
        try {
            $statement = $this->connection->prepare($query);
            $results = $statement->execute(['id' => $employee->getId()]);
        } catch (PDOException $e) {
            $employee->errors("Error deleting Employee: " . $e->getMessage());
            $results = false;
        }
        return $results;
    }
}
