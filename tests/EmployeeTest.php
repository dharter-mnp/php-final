<?php
namespace mvc;

require_once($_SERVER['DOCUMENT_ROOT'] . "/../Model/Employee.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/../Repository/MySQLConnection.php");

use Faker\Factory as Faker;
use PHPUnit_Framework_TestCase;
use Mockery as Mock;

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
    private $mockDBConnection;
    private $mockStatement;

    protected function setUp()
    {
        $this->faker = Faker::create();
        $this->mockDBConnection = Mock::mock('PDO');
        $this->mockStatement = Mock::mock('PDOStatement');
    }

    protected function tearDown()
    {
        self::closeMySQLConnection();
        Mock::close();
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

        $lastName = $this->faker->regexify('[A-Z][a-z]{60,100}');
        $firstName = $this->faker->regexify('[A-Z][a-z]{60,100}');
        $email = $this->faker->regexify('[A-Z0-9._%+-]{120,200}@[A-Z0-9.-]{131,200}\.[A-Z]{2,4}');
        $employee = new Employee(null, $lastName, $firstName, $email);

        /*Verify the validation is indeed failing*/
        $this->assertFalse($employee->validate(), "Employee validation expected to fail.");
        $errors = $employee->errors();

        $this->assertContains("Last Name max length is 60", $errors, "Last Name max length validation missing");
        $this->assertContains("First Name max length is 60", $errors, "Last Name max length validation missing");
        $this->assertContains("Email max length is 255", $errors, "Email max length validation missing");

        /*Check null validation:
        Last Name 
        Email.
        First Name should still fail with max length validation. */

        $employee->setLastName(null);
        $employee->setEmail(null);
        $this->assertFalse($employee->validate(), "Employee validation expected to fail.");
        $errors = $employee->errors();

        $this->assertContains("Last Name is required", $errors, "Last Name required validation missing");
        $this->assertContains("First Name max length is 60", $errors, "Last Name max length validation missing");
        $this->assertContains("Email is required", $errors, "Email required validation missing");

        /*Clear Last Name null validation error.
        Expected First name to fail with max length validation and 
        Email to fail with null validation.*/
        $lastName = $this->faker->regexify('[A-Z][a-z]{1,59}');
        $employee->setLastName($lastName);
        $this->assertFalse($employee->validate(), "Employee validation expected to fail.");
        $errors = $employee->errors();
        $this->assertNotContains("Last Name is required", $errors, "Last Name required validation included");
        $this->assertContains("First Name max length is 60", $errors, "Last Name max length validation missing");
        $this->assertContains("Email is required", $errors, "Email required validation missing");

        /*Clear First name max length validation
        Expected Email to still fail with null validation.*/
        $firstName = $this->faker->regexify('[A-Z][a-z]{1,59}');
        $employee->setFirstName($firstName);
        $this->assertFalse($employee->validate(), "Employee validation expected to fail.");
        $errors = $employee->errors();
        $this->assertNotContains("First Name max length is 60", $errors, "First Name max length validation included");
        $this->assertContains("Email is required", $errors, "Email required validation missing");

        /*Clear Email null validation error.
        Expect validation to succeed.*/
        do {
            $email = $this->faker->email;
        } while (strlen($email) > 255);
        $employee->setEmail($email);
        $this->assertTrue($employee->validate(), "Employee validation expected to succeed.");
        $errors = $employee->errors();
        $this->assertEmpty($errors, "Validation errors haven't cleared.");

    }

    public function testEmployeeSaveDestroy()
    {
        /*Setup Employee Object*/
        $lastName = $this->faker->regexify('[A-Z][a-z]{1,59}');
        $firstName = $this->faker->regexify('[A-Z][a-z]{1,59}');
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

        $lastName = $this->faker->regexify('[A-Z][a-z]{1,59}');
        $firstName = $this->faker->regexify('[A-Z][a-z]{1,59}');
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

    public function testMockDB()
    {
        $id = $this->faker->randomDigit;
        $lastName = $this->faker->regexify('[A-Z][a-z]{1,59}');
        $firstName = $this->faker->regexify('[A-Z][a-z]{1,59}');
        do {
            $email = $this->faker->email;
        } while (strlen($email) > 255);
        $errors = null;

        $employee = new Employee(null, $lastName, $firstName, $email, $this->mockDBConnection);
        $this->mockDBConnection->shouldReceive('prepare')->with(
            "INSERT INTO employee(last_name, first_name, email) VALUES (:lastName, :firstName, :email)"
        )->andReturn($this->mockStatement);
        $this->mockStatement->shouldReceive('execute')->with([
            'lastName' => $employee->getLastName(),
            'firstName' => $firstName,
            'email' => $employee->getEmail()
        ])->andReturn(true);
        $this->mockDBConnection->shouldReceive('lastInsertId')->andReturn($id);
        $this->assertTrue($employee->save(), 'Employee insert failed');
        $errors = $employee->errors();
        $this->assertTrue(empty($errors), 'Errors were returned from Employee insert');
        if (!empty($errors)) {
            var_dump($errors);
        }

        /*Verify the Employee Id was populated and has a valid value*/
        $this->assertNotEmpty($employee->getId(), 'Employee id is empty after save');
        $this->assertEquals($id, $employee->getId(), 'Employee id invalid after save');

        $this->mockDBConnection->shouldReceive('prepare')->once()->with('SELECT Id, 
                         last_name AS "LastName", 
                         first_name AS "FirstName", 
                         email AS "Email" 
                    FROM employee 
                  WHERE id = :id')->andReturn($this->mockStatement);
        $this->mockStatement->shouldReceive('execute')->once()->with(['id' => $id])->andReturn(true);
        $this->mockStatement->shouldReceive('fetch')->once()->andReturn([
            "id" => $id,
            "LastName" => $employee->getLastName(),
            "FirstName" => $employee->getFirstName(),
            "Email" => $employee->getEmail()
        ]);

        $insertEmployee = Employee::find($id, $this->mockDBConnection);
        $this->assertNotEmpty($insertEmployee, 'Employee record was not found after insert');
        $this->assertEquals(
            $employee->getLastName(),
            $insertEmployee["LastName"],
            "Created Employee has invalid Last Name"
        );
        $this->assertEquals(
            $employee->getFirstName(),
            $insertEmployee["FirstName"],
            "Created Employee has invalid First Name"
        );
        $this->assertEquals(
            $employee->getEmail(),
            $insertEmployee["Email"],
            "Created Employee has invalid Email"
        );

        /*Modify the employee properties and update the record*/

        $lastName = $this->faker->regexify('[A-Z][a-z]{1,59}');
        $firstName = $this->faker->regexify('[A-Z][a-z]{1,59}');
        do {
            $email = $this->faker->email;
        } while (strlen($email) > 255);
        $employee->setLastName($lastName);
        $employee->setFirstName($firstName);
        $employee->setEmail($email);

        $this->mockDBConnection->shouldReceive('prepare')->with(
            "UPDATE employee 
                 SET last_name = :lastName, 
                     first_name = :firstName, 
                     email = :email 
                 WHERE id = :id"
        )->andReturn($this->mockStatement);
        $this->mockStatement->shouldReceive('execute')->with([
                'lastName' => $employee->getLastName(),
                'firstName' => $employee->getFirstName(),
                'email' => $employee->getEmail(),
                'id' => $employee->getId()
            ])->andReturn(true);
        $this->assertTrue($employee->save(), 'Employee update failed');
        $errors = $employee->errors();
        $this->assertTrue(empty($errors), 'Errors were returned from Employee update');
        if (!empty($errors)) {
            var_dump($errors);
        }

        $this->mockDBConnection->shouldReceive('prepare')->once()->with('SELECT Id, 
                         last_name AS "LastName", 
                         first_name AS "FirstName", 
                         email AS "Email" 
                    FROM employee 
                  WHERE id = :id')->andReturn($this->mockStatement);
        $this->mockStatement->shouldReceive('execute')->once()->with(['id' => $employee->getId()])->andReturn(true);
        $this->mockStatement->shouldReceive('fetch')->once()->andReturn([
            "id" => $employee->getId(),
            "LastName" => $employee->getLastName(),
            "FirstName" => $employee->getFirstName(),
            "Email" => $employee->getEmail()
        ]);

        $updateEmployee = Employee::find($id, $this->mockDBConnection);
        $this->assertNotEmpty($updateEmployee, 'Employee record was not found after update');
        $this->assertEquals(
            $employee->getLastName(),
            $updateEmployee["LastName"],
            "Updated Employee has invalid Last Name"
        );
        $this->assertEquals(
            $employee->getFirstName(),
            $updateEmployee["FirstName"],
            "Updated Employee has invalid First Name"
        );
        $this->assertEquals(
            $employee->getEmail(),
            $updateEmployee["Email"],
            "Updated Employee has invalid Email"
        );

        /*Delete the employee*/
        $this->mockDBConnection->shouldReceive('prepare')->once()->
        with('DELETE FROM employee WHERE id = :id')->andReturn($this->mockStatement);
        $this->mockStatement->shouldReceive('execute')->once()->with(['id' => $employee->getId()])->andReturn(true);
        $this->assertTrue($employee->destroy(), 'Employee failed to delete');

        /*Verify the record is no longer in the database*/
        $this->mockDBConnection->shouldReceive('prepare')->once()->with('SELECT Id, 
                         last_name AS "LastName", 
                         first_name AS "FirstName", 
                         email AS "Email" 
                    FROM employee 
                  WHERE id = :id')->andReturn($this->mockStatement);
        $this->mockStatement->shouldReceive('execute')->once()->with(['id' => $employee->getId()])->andReturn(false);

        $deleteEmployee = Employee::find($id, $this->mockDBConnection);
        $this->assertEmpty($deleteEmployee, 'Employee record was found after delete.');
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
