<?php
namespace mvc;

use mysqli;

/**
 * Created by PhpStorm.
 * User: DHarter
 * Date: 5/4/2016
 * Time: 2:45 PM
 */
class Employee
{

    const DB_HOST = 'localhost'; //Host name<br>
    const DB_USER = 'root'; //Host Username<br>
    const DB_PASS = ''; //Host Password<br>
    const DB_NAME = 'php-final'; //Database name<br><br>

    private $id;
    private $lastName;
    private $firstName;
    private $email;
    private $errorMessages;
    private static $mysqli = null;

    /**
     * Employee constructor.
     * @param $id
     * @param $lastName
     * @param $firstName
     * @param $email
     */
    public function __construct($id = null, $lastName = null, $firstName = null, $email = null)
    {
        $this->id = $id;
        $this->lastName = $lastName;
        $this->firstName = $firstName;
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param mixed $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param mixed $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }


    public static function find($id)
    {
        $mysqli = self::openMySQLConnection();
        if (!isset($mysqli)) {
            self::errors("Unable to obtain MySql connection.");
            return null;
        }
        $query = "select Id, 
                         last_name AS \"LastName\", 
                         first_name AS \"FirstName\", 
                         email AS \"Email\" 
                    from employee 
                  where id = $id";
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
            self::errors("Unable to obtain MySql connection.");
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

    public function save()
    {
        if (!$this->validate()) {
            return false;
        }
        $mysqli = self::openMySQLConnection();
        if (!isset($mysqli)) {
            $this->errors("Unable to obtain MySql connection.");
            return false;
        }
        $id = null;
        $results = null;
        if (empty($this->id)) {
            $firstName = (!empty($this->firstName) ? "'$this->firstName'" : "null");
            $query = "insert into employee(last_name, first_name, email) 
                         VALUES ('$this->lastName', $firstName, '$this->email')";
            $results = $mysqli->query($query);
            if (!$results || $mysqli->insert_id === 0) {
                $this->errors("Error creating Employee:  $mysqli->error");
                $results = false;
            }
        } else {
            $firstName = !empty($this->firstName) ? "'$this->firstName'" : "null";
            $query = "update employee 
                         set last_name = '$this->lastName', 
                             first_name = $firstName, 
                             email = '$this->email' 
                       where id = $this->id";
            $results = $mysqli->query($query);
            if (!$results) {
                $this->errors("Error updating Employee: $mysqli->error");
            }
        }
        self::closeMySQLConnection();
        return $results;
    }

    public function destroy()
    {
        $mysqli = self::openMySQLConnection();
        if (!isset($mysqli)) {
            $this->errors("Unable to obtain MySql connection.");
            return false;
        }
        $query = "delete from employee where id = $this->id";
        $results = $mysqli->query($query);
        if (!$results) {
            $this->errors("Error deleting Employee: $mysqli->error");
        }
        self::closeMySQLConnection();
        return $results;
    }

    public function validate()
    {
        if (empty($this->lastName)) {
            $this->errors("Last Name is required");
        }
        if (strlen($this->lastName) > 60) {
            $this->errors("Last Name max length is 60");
        }
        if (!empty($this->firstName) and strlen($this->firstName) > 60) {
            $this->errors("First Name max length is 60");
        }
        if (empty($this->email)) {
            $this->errors("Email is required");
        }
        if (strlen($this->email) > 255) {
            $this->errors("Email max length is 255");
        }
        return empty($this->errorMessages);
    }

    public function errors($errMsg = null)
    {
        if (empty($errMsg)) {
            $errReturn = $this->errorMessages;
            $this->errorMessages = null;
            return $errReturn;
        }
        $this->errorMessages[] = $errMsg;
        return null;
    }

    private static function openMySQLConnection()
    {
        if (!isset(self::$mysqli)) {
            $tmysqli = new mysqli(self::DB_HOST, self::DB_USER, self::DB_PASS, self::DB_NAME);
            if ($tmysqli->connect_errno) {
                self::errors("Failed to connect to MySQL: (" . $tmysqli->connect_errno . ") " .
                    $tmysqli->connect_error);
                return null;
            }
            self::$mysqli = $tmysqli;
        }
        return self::$mysqli;
    }

    private static function closeMySQLConnection()
    {
        self::$mysqli->close();
        self::$mysqli = null;
    }
}
