<?php
namespace mvc;

/**
 * Created by PhpStorm.
 * @author DHarter
 * Date: 5/4/2016
 * Time: 2:45 PM
 */

require_once($_SERVER['DOCUMENT_ROOT'] . "/../Repository/EmployeeRepository.php");

use mvc\EmployeeRepository as Repository;

/**
 * Class Employee.  Object model to represent the database employee table and interacts with the database via
 * the UserRepository object.
 *
 * @package mvc
 */
class Employee
{
    /**
     * @type integer $id Id of the employee.
     */
    private $id;

    /**
     * @type string $lastName Last Name of the employee.
     */
    private $lastName;

    /**
     * @type string $firstName First Name of the employee.
     */
    private $firstName;

    /**
     * @type string $email Email address of the employee.
     */
    private $email;

    /**
     * @type string[] $errorMessages Array of error messages.
     */
    private $errorMessages = array();

    /**
     * @type /mvc/EmployeeRepository $repository Instance of the data access object.
     */
    private $repository;

    /**
     * Employee constructor.  Populates the employee object and instantiates the UserRepository object and
     * injects it into the employee object.
     *
     * @param integer $id         Id of the employee.  Default null.
     * @param string  $lastName   Last Name of the employee. Default null.
     * @param string  $firstName  First Name of the employee. Default null.
     * @param string  $email      Email address of the employee. Default null.
     * @param object  $dbConnection PDO object. Connection to the database. Default null.
     */
    public function __construct($id = null, $lastName = null, $firstName = null, $email = null, $dbConnection = null)
    {
        $this->id = $id;
        $this->lastName = $lastName;
        $this->firstName = $firstName;
        $this->email = $email;
        $this->repository = new Repository($dbConnection);
    }

    /**
     * Retrieves the id of the employee.
     *
     * @return integer $id Id of the employee.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the id of the employee.
     *
     * @param integer $id Id of the employee.
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Returns the last name of the employee.
     *
     * @return string $lastName Last name of the employee.
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Sets the last name of the employee.
     *
     * @param string $lastName Last name of the employee.
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * Retrieves the first name of the employee.
     *
     * @return string $firstName First name of the employee.
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param mixed $firstName First name of the employee.
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * Retrieves the email address of the employee.
     *
     * @return string $email Email address of the employee.
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Sets the email address of the employee.
     * @param string $email Email address of the employee.
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * Retrieves a single employee object from the database based on id via the Repository object.
     *
     * @param integer $id Id of the employee to retrieve.
     * @param PDO $dbConnection PDO object. Connection to the database. Default null.
     * @return mixed[]|null Array representation of the employee object in the database.
     */
    public static function find($id, $dbConnection = null)
    {
        if (empty($id)) {
            return null;
        }
        return Repository::find($id, $dbConnection);
    }

    /**
     * Retrieves a collection of all employee objects from the database via the Repository object.
     *
     * @param PDO $dbConnection PDO object. Connection to the database. Default null.
     * @return mixed[][]|null Array representation of the employee object in the database.
     */
    public static function findAll($dbConnection = null)
    {
        return Repository::findAll($dbConnection);
    }

    /**
     * Inserts or updates the employee object in the database via the Repository object.
     * If employee id is empty then inserts; otherwise, updates based on the information in the employee object.
     *
     * @return boolean Indicates if the insert/update was successful.
     */
    public function save()
    {
        if (!$this->validate()) {
            return false;
        }

        return $this->repository->save($this);
    }

    /**
     * Deletes the employee object in the database via the Repository object based on employee id.
     *
     * @return boolean Indicates if the delete was successful.
     */
    public function destroy()
    {
        if (empty($this->id)) {
            $this->errors("No employee id specified.");
            return false;
        }
        return $this->repository->destroy($this);
    }

    /**
     * Prior to insert or update the parameters must be validated to ensure they meet validation criteria.
     *
     * @return boolean Indicates if the employee object parameters meet validation criteria.
     */
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

    /**
     * Stores validation errors in array if error message is passed; otherwise, retrieves the contents of the
     * error message array.  Array of error messages is cleared after the array is retrieved.
     *
     * @param string $errMsg Error message to add to the error message array.  Default null.
     * @return string[]|null $errorMessages If an error message is passed in then returns null; otherwise,
     * returns the array of error messages.
     */
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
}
