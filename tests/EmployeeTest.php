<?php
namespace mvc;

require_once($_SERVER['DOCUMENT_ROOT'] . "/../Model/Employee.php");
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


    public function testEmployeeValidation()
    {
        /*Setup Employee Object*/
        $faker = Faker::create();
        $lastName = "";
        $firstName = "";
        $email = null;
        $errors = null;
        
        /*Check exceed max length validation:
        Last Name length > 60 characters
        First Name length > 60 characters
        Email length > 255 characters*/
        
        do {
            $lastName = $lastName . $faker->lastName;
        } while (strlen($lastName) < 61);
        do {
            $firstName = $firstName . $faker->firstName;
        } while (strlen($firstName) < 61);
        $email = $faker->regexify('[A-Z0-9._%+-]{120,200}@[A-Z0-9.-]{131,200}\.[A-Z]{2,4}');
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
            $lastName = $faker->lastName;
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
            $firstName = $faker->firstName;
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
            $email = $faker->email;
        } while (strlen($email) > 255);
        $employee->setEmail($email);
        $this->assertTrue($employee->validate(), "Employee validation expected to succeed.");
        $errors = $employee->errors();
        $this->assertTrue(
            empty($errors),
            "Validation errors haven't cleared."
        );

    }
}
