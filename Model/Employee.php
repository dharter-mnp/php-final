<?php
namespace mvc;

require_once($_SERVER['DOCUMENT_ROOT'] . "/../Model/Employee.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/../Repository/EmployeeRepository.php");

use mvc\EmployeeRepository as Repository;


/**
 * Created by PhpStorm.
 * User: DHarter
 * Date: 5/4/2016
 * Time: 2:45 PM
 */
class Employee
{
    private $id;
    private $lastName;
    private $firstName;
    private $email;
    private $errorMessages = array();
    private $repository;

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
        $this->repository = new Repository();
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
        if (empty($id)) {
            return null;
        }
        return Repository::find($id);
    }

    public static function findAll()
    {
        return Repository::findAll();
    }

    public function save()
    {
        if (!$this->validate()) {
            return false;
        }

        return $this->repository->save($this);
    }

    public function destroy()
    {
        if (empty($this->id)) {
            $this->errors("No employee id specified.");
            return false;
        }
        return $this->repository->destroy($this);
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
}
