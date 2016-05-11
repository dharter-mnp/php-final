<?php
/**
 * Created by PhpStorm.
 * User: DHarter
 * Date: 5/9/2016
 * Time: 3:38 PM
 */

namespace mvc;

use PDO;
use PDOException;

require_once($_SERVER['DOCUMENT_ROOT'] . "/../Repository/RepositoryInterface.php");

class EmployeeRepository implements RepositoryInterface
{

    private $connection;

    /**
     * EmployeeRepository constructor.
     */
    public function __construct()
    {
        $this->connection = self::instantiateConnection();
    }

    private static function instantiateConnection()
    {
        $conn = new PDO(
            "mysql:host=" . self::DB_HOST . ";dbname=" . self::DB_NAME,
            self::DB_USER,
            self::DB_PASS
        );
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;

    }

    public static function find($id)
    {
        if (empty($id)) {
            return null;
        }
        $connection = self::instantiateConnection();
        $query = "SELECT Id, 
                         last_name AS \"LastName\", 
                         first_name AS \"FirstName\", 
                         email AS \"Email\" 
                    FROM employee 
                  WHERE id = :id";
        $statement = $connection->prepare($query);
        $statement->execute(['id' => $id]);
        $result = [];

        while ($employee = $statement->fetch()) {
            $result = $employee;
            break;
        }

        if (empty($result)) {
            $result = null;
        }

        return $result;
    }

    public static function findAll()
    {
        $connection = self::instantiateConnection();
        $query = "SELECT Id, last_name AS \"LastName\", first_name AS \"FirstName\", email AS \"Email\" FROM employee";
        $statement = $connection->prepare($query);
        $statement->execute();
        $result = $statement->fetchAll();
        return $result;
    }

    public function save(&$employee)
    {
        $id = null;
        $results = null;
        $firstName = !empty($employee->getFirstName()) ?  $employee->getFirstName()  : "null";
        if (empty($employee->getId())) {
            $query = "INSERT INTO employee(last_name, first_name, email) 
                         VALUES (:lastName, :firstName, :email)";
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
            $query = "UPDATE employee 
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
