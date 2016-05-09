<?php
namespace mvc;

require_once($_SERVER['DOCUMENT_ROOT'] . "/../Model/Employee.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/../Repository/MySQLConnection.php");

use Faker\Factory as Faker;
use PHPUnit_Framework_TestCase;

/**
 * Created by PhpStorm.
 * User: DHarter
 * Date: 5/6/2016
 * Time: 5:03 PM
 */
class EmployeeTest extends PHPUnit_Framework_TestCase
{
    use MySQLConnection;
    private $faker = null;


    protected function setUp()
    {
        $this->faker = Faker::create();
    }

    protected function tearDown()
    {
        self::closeMySQLConnection();
    }


    public function testEmployeeValidation()
    {
        /*Setup Employee Object*/
        $lastName = "";
        $firstName = "";
        $email = null;
        $errors = null;

        /*Check exceed max length validation:
        Last Name length > 60 characters
        First Name length > 60 characters
        Email length > 255 characters*/

        do {
            $lastName = $lastName . $this->faker->lastName;
        } while (strlen($lastName) < 61);
        do {
            $firstName = $firstName . $this->faker->firstName;
        } while (strlen($firstName) < 61);
        $email = $this->faker->regexify('[A-Z0-9._%+-]{120,200}@[A-Z0-9.-]{131,200}\.[A-Z]{2,4}');
        $employee = new Employee(null, $lastName, $firstName, $email);

        /*Verify the validation is indeed failing*/
        $this->assertFalse($employee->validate(), "Employee validation expected to fail.");
        $errors = $employee->errors();

        $this->assertTrue(
            in_array("Last Name max length is 60", $errors, true),
            "Last Name max length validation missing"
        );
        $this->assertTrue(
            in_array("First Name max length is 60", $errors, true),
            "Last Name max length validation missing"
        );
        $this->assertTrue(
            in_array("Email max length is 255", $errors, true),
            "Email max length validation missing"
        );

        /*Check null validation:
        Last Name 
        Email.
        First Name should still fail with max length validation. */

        $employee->setLastName(null);
        $employee->setEmail(null);
        $this->assertFalse($employee->validate(), "Employee validation expected to fail.");
        $errors = $employee->errors();

        $this->assertTrue(
            in_array("Last Name is required", $errors, true),
            "Last Name required validation missing"
        );
        $this->assertTrue(
            in_array("First Name max length is 60", $errors, true),
            "Last Name max length validation missing"
        );
        $this->assertTrue(
            in_array("Email is required", $errors, true),
            "Email required validation missing"
        );

        /*Clear Last Name null validation error.
        Expected First name to fail with max length validation and 
        Email to fail with null validation.*/
        do {
            $lastName = $this->faker->lastName;
        } while (strlen($lastName) > 60);
        $employee->setLastName($lastName);
        $this->assertFalse($employee->validate(), "Employee validation expected to fail.");
        $errors = $employee->errors();
        $this->assertFalse(
            in_array("Last Name is required", $errors, true),
            "Last Name required validation included"
        );
        $this->assertTrue(
            in_array("First Name max length is 60", $errors, true),
            "Last Name max length validation missing"
        );
        $this->assertTrue(
            in_array("Email is required", $errors, true),
            "Email required validation missing"
        );

        /*Clear First name max length validation 
        Expected Email to still fail with null validation.*/
        do {
            $firstName = $this->faker->firstName;
        } while (strlen($firstName) > 60);
        $employee->setFirstName($firstName);
        $this->assertFalse($employee->validate(), "Employee validation expected to fail.");
        $errors = $employee->errors();
        $this->assertFalse(
            in_array("First Name max length is 60", $errors, true),
            "First Name max length validation included"
        );
        $this->assertTrue(
            in_array("Email is required", $errors, true),
            "Email required validation missing"
        );

        /*Clear Email null validation error.
        Expect validation to succeed.*/
        do {
            $email = $this->faker->email;
        } while (strlen($email) > 255);
        $employee->setEmail($email);
        $this->assertTrue($employee->validate(), "Employee validation expected to succeed.");
        $errors = $employee->errors();
        $this->assertTrue(
            empty($errors),
            "Validation errors haven't cleared."
        );

    }

    public function testEmployeeSaveDestroy()
    {
        /*Setup Employee Object*/
        do {
            $lastName = $this->faker->lastName;
        } while (strlen($lastName) > 60);
        do {
            $firstName = $this->faker->firstName;
        } while (strlen($firstName) > 60);
        do {
            $email = $this->faker->email;
        } while (strlen($email) > 255);
        $errors = null;
        $employee = new Employee(null, $lastName, $firstName, $email);

        /*Get Employee count and max employee Id*/
        $originalNumberEmployees = $this->getNumberEmployees();
        $maxId = $this->getMaxEmployeeId();

        /*Insert the employee and verify it was successful*/
        $this->assertTrue($employee->save(), 'Employee insert failed');
        $errors = $employee->errors();
        $this->assertTrue(empty($errors), 'Errors were returned from Employee insert');
        if (!empty($errors)) {
            var_dump($errors);
        }

        /*Verify the Employee record count increased.*/
        $afterInsertNumberEmployees = $this->getNumberEmployees();
        $this->assertEquals(
            $originalNumberEmployees + 1,
            $afterInsertNumberEmployees,
            'Employee count did not increase after insert'
        );

        /*Verify the Employee Id was populated and has a valid value*/
        $this->assertNotEmpty($employee->getId(), 'Employee id is empty after save');
        $this->assertGreaterThan($maxId, $employee->getId(), 'Employee id should be greater than max employee id');

        /*Verify the Employee Last Name, First Name, and Email address are equal to the values entered.*/
        $verifyEmployee = Employee::find($employee->getId());
        $this->assertNotEmpty($verifyEmployee, 'Employee record was not found after insert');
        $this->assertEquals(
            $employee->getLastName(),
            $verifyEmployee["LastName"],
            "Inserted Employee has invalid Last Name"
        );
        $this->assertEquals(
            $employee->getFirstName(),
            $verifyEmployee["FirstName"],
            "Inserted Employee has invalid First Name"
        );
        $this->assertEquals(
            $employee->getEmail(),
            $verifyEmployee["Email"],
            "Inserted Employee has invalid Email"
        );

        /*Modify the employee properties and update the record*/

        do {
            $lastName = $this->faker->lastName;
        } while (strlen($lastName) > 60);
        do {
            $firstName = $this->faker->firstName;
        } while (strlen($firstName) > 60);
        do {
            $email = $this->faker->email;
        } while (strlen($email) > 255);
        $employee->setLastName($lastName);
        $employee->setFirstName($firstName);
        $employee->setEmail($email);

        $this->assertTrue($employee->save(), 'Employee update failed');
        $errors = $employee->errors();
        $this->assertTrue(empty($errors), 'Errors were returned from Employee update');
        if (!empty($errors)) {
            var_dump($errors);
        }

        /*Verify the Employee record count did not increase.*/
        $afterUpdateNumberEmployees = $this->getNumberEmployees();
        $this->assertEquals(
            $afterInsertNumberEmployees,
            $afterUpdateNumberEmployees,
            'Employee count increased after update'
        );

        /*Verify the Employee Last Name, First Name, and Email address are equal to the values entered.*/
        $verifyEmployee = Employee::find($employee->getId());
        $this->assertNotEmpty($verifyEmployee, 'Employee record was not found after update');
        $this->assertEquals(
            $employee->getLastName(),
            $verifyEmployee["LastName"],
            "Updated Employee has invalid Last Name"
        );
        $this->assertEquals(
            $employee->getFirstName(),
            $verifyEmployee["FirstName"],
            "Updated Employee has invalid First Name"
        );
        $this->assertEquals(
            $employee->getEmail(),
            $verifyEmployee["Email"],
            "Updated Employee has invalid Email"
        );

        /*Delete the employee*/
        $this->assertTrue($employee->destroy(), 'Employee failed to delete');

        /*Verify the record count decreased*/
        $afterDeleteNumberEmployees = $this->getNumberEmployees();
        $this->assertEquals(
            $afterUpdateNumberEmployees - 1,
            $afterDeleteNumberEmployees,
            'Employee count did not decrease after delete'
        );

        /*Verify the record is no longer in the database*/
        $verifyEmployee = Employee::find($employee->getId());
        $this->assertEmpty($verifyEmployee, 'Employee record was found after delete.');

    }

    private function getNumberEmployees()
    {
        $mysqli = self::openMySQLConnection();
        $query = "SELECT count(1) AS \"numEmployees\" 
                    FROM employee";
        $employees = $mysqli->query($query, MYSQLI_USE_RESULT);
        $numEmployees = 0;
        if (!empty($employees)) {
            foreach ($employees as $employee) {
                $numEmployees = $employee["numEmployees"];
            }
        }
        return $numEmployees;

    }

    private function getMaxEmployeeId()
    {
        $mysqli = self::openMySQLConnection();
        $query = "SELECT max(id) AS \"maxEmployeeId\" 
                    FROM employee";
        $employees = $mysqli->query($query, MYSQLI_USE_RESULT);
        $maxId = 0;
        if (!empty($employees)) {
            foreach ($employees as $employee) {
                $maxId = $employee["maxEmployeeId"];
            }
        }
        return $maxId;

    }
}
