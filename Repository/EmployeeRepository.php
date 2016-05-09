<?php
/**
 * Created by PhpStorm.
 * User: DHarter
 * Date: 5/9/2016
 * Time: 3:38 PM
 */

namespace mvc;

require_once($_SERVER['DOCUMENT_ROOT'] . "/../Repository/RepositoryInterface.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/../Repository/MySQLConnection.php");

class EmployeeRepository implements RepositoryInterface
{

    use MySQLConnection;

    public static function find($id)
    {
        if (empty($id)) {
            return null;
        }
        $mysqli = self::openMySQLConnection();
        if (!isset($mysqli)) {
            //self::errors("Unable to obtain MySql connection.");
            return null;
        }
        $query = "SELECT Id, 
                         last_name AS \"LastName\", 
                         first_name AS \"FirstName\", 
                         email AS \"Email\" 
                    FROM employee 
                  WHERE id = $id";
        $employees = $mysqli->query($query, MYSQLI_USE_RESULT);
        $result = null;
        if (!empty($employees)) {
            foreach ($employees as $employee) {
                $result = $employee;
            }
        }
        self::closeMySQLConnection();
        return $result;
    }

    public static function findAll()
    {
        $mysqli = self::openMySQLConnection();
        if (!isset($mysqli)) {
            //self::errors("Unable to obtain MySql connection.");
            return null;
        }
        $query = "SELECT Id, last_name AS \"LastName\", first_name AS \"FirstName\", email AS \"Email\" FROM employee";
        $employees = $mysqli->query($query, MYSQLI_USE_RESULT);
        $result = [];
        foreach ($employees as $employee) {
            $result[] = $employee;
        }
        self::closeMySQLConnection();
        return $result;
    }

    public function save($employee)
    {
        $mysqli = self::openMySQLConnection();
        if (!isset($mysqli)) {
            $employee->errors("Unable to obtain MySql connection.");
            return false;
        }
        $id = null;
        $results = null;
        if (empty($employee->getId())) {
            $firstName = !empty($employee->getFirstName()) ? "'" . $employee->getFirstName() . "'" : "null";
            $query = "insert into employee(last_name, first_name, email) 
                         VALUES ('" . $employee->getLastName() . "', $firstName, '" . $employee->getEmail() . "')";
            $results = $mysqli->query($query);
            if (!$results || $mysqli->insert_id === 0) {
                $employee->errors("Error creating Employee:  $mysqli->error");
                $results = false;
            } else {
                $employee->setId($mysqli->insert_id);
            }
        } else {
            $firstName = !empty($employee->getFirstName()) ? "'" . $employee->getFirstName() . "'" : "null";
            $query = "update employee 
                         set last_name = '" . $employee->getLastName() . "', 
                             first_name = $firstName, 
                             email = '" . $employee->getEmail() . "' 
                       where id = " . $employee->getId();
            $results = $mysqli->query($query);
            if (!$results) {
                $employee->errors("Error updating Employee: $mysqli->error");
            }
        }
        self::closeMySQLConnection();
        return $results;
    }

    public function destroy($employee)
    {
        $mysqli = self::openMySQLConnection();
        if (!isset($mysqli)) {
            $employee->errors("Unable to obtain MySql connection.");
            return false;
        }
        $query = "DELETE FROM employee WHERE id = " . $employee->getId();
        $results = $mysqli->query($query);
        if (!$results) {
            $employee->errors("Error deleting Employee: $mysqli->error");
        }
        self::closeMySQLConnection();
        return $results;
    }
}
