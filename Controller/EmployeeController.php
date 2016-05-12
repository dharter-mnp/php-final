<?php
namespace mvc;

/**
 * Created by PhpStorm.
 * @author DHarter
 * Date: 5/4/2016
 * Time: 8:30 PM
 */

require_once($_SERVER['DOCUMENT_ROOT'] . "/../Model/Employee.php");

/**
 * Class EmployeeController
 *
 * This Controller will handle the functionality for the Employee model operations.
 *
 * @package mvc
 */
class EmployeeController
{

    /**
     * Handles GET request to retrieve all Employee objects.
     * Default method for GET requests.  Outputs json response.
     *
     * @api
     * @example http://localhost/employee
     */
    public function index()
    {
        $employees = Employee::findAll();
        if (!empty($employees)) {
            self::returnSuccessResponse($employees);
        }
    }

    /**
     * Handles GET request to retrieve a single Employee object.
     * Outputs json response.
     *
     * @api
     * @example http://localhost/employee/show?id=1
     */
    public function show()
    {
        if (empty($_GET['id'])) {
            self::returnErrorResponse(400, 'Employee Id required');
            exit();
        }
        $id = $_GET['id'];
        $employee = Employee::find($id);
        if (!empty($employee)) {
            self::returnSuccessResponse($employee);
        }
    }

    /**
     * Handles POST request to create an Employee object.
     * Outputs true or false json response.
     *
     * @api
     * @example http://localhost/employee
     */
    public function create()
    {
        $employee = $this->instantiateEmployeeFromRequest('POST');
        if (!empty($employee->getId())) {
            self::returnErrorResponse(400, 'Unexpected employee Id.');
            exit();
        }

        $created = $employee->save();
        if (!$created) {
            $errors = ["Errors" => $employee->errors()];
            self::returnErrorResponse(400, $errors);
            exit();
        }
        self::returnSuccessResponse(["created" => true]);
    }

    /**
     * Handles PUT request to update an Employee object.
     * Outputs true or false json response.
     *
     * @api
     * @example http://localhost/employee
     */
    public function edit()
    {
        $employee = $this->instantiateEmployeeFromRequest('PUT');
        if (empty($employee->getId())) {
            self::returnErrorResponse(400, 'Employee Id required.');
            exit();
        }
        $empCheck = Employee::find($employee->getId());
        if (empty($empCheck) || !isset($empCheck["Id"]) || $empCheck["Id"] != $employee->getId()) {
            self::returnErrorResponse(400, 'Invalid employee Id');
            exit();
        }

        $updated = $employee->save();
        if (!$updated) {
            $errors = ["Errors" => $employee->errors()];
            self::returnErrorResponse(400, $errors);
            exit();
        }
        self::returnSuccessResponse(["updated" => true]);
    }

    /**
     * Handles DELETE request to delete an Employee object.
     * Outputs true or false json response.
     *
     * @api
     * @example http://localhost/employee?id=1
     */
    public function destroy()
    {
        $employee = $this->instantiateEmployeeFromRequest('DELETE');
        $empCheck = Employee::find($employee->getId());
        if (empty($empCheck) || !isset($empCheck["Id"]) || $empCheck["Id"] != $employee->getId()) {
            self::returnErrorResponse(400, 'Invalid employee Id');
            exit();
        }
        $destroyed = $employee->destroy();
        if (!$destroyed) {
            $errors = ["Errors" => $employee->errors()];
            self::returnErrorResponse(400, $errors);
            exit();
        }
        self::returnSuccessResponse(["deleted" => true]);
    }

    /**
     * Uses the json request body to instantiate an Employee object.
     *
     * @uses \mvc\Employee::__construct()
     * @param string $method 'POST', 'PUT', 'DELETE'
     * @return /mvc/Employee object
     */
    private function instantiateEmployeeFromRequest($method)
    {
        $body = null;
        switch ($method) {
            case 'POST':
                $request = null;
                foreach ($_POST as $key => $val) {
                    $request = $key;
                    break;
                }
                $body = json_decode($request, true);
                break;
            case 'PUT':
                $request = null;
                $request = file_get_contents("php://input");
                $body = json_decode($request, true);
                break;
            case 'DELETE':
                $request = null;
                $queryString = null;
                if (isset($_SERVER['QUERY_STRING'])) {
                    $queryString = $_SERVER['QUERY_STRING'];
                }
                parse_str($queryString, $body);
                break;
            default:
                return null;
        }
        $id = (isset($body["Id"]) ? $body["Id"] : null);
        if (empty($id)) {
            $id = (isset($body["id"]) ? $body["id"] : null);
        }
        $lastName = (isset($body["LastName"]) ? $body["LastName"] : null);
        $firstName = (isset($body["FirstName"]) ? $body["FirstName"] : null);
        $email = (isset($body["Email"]) ? $body["Email"] : null);

        $emp = new Employee($id, $lastName, $firstName, $email);

        return $emp;
    }

    /**
     * Writes the json success response.
     *
     * @param mixed[] $response The array to be returned in the successful response.  Will be converted to json format.
     */
    private static function returnSuccessResponse($response)
    {
        http_response_code(200);
        echo json_encode($response);
    }

    /**
     * Writes the json error response.
     *
     * @param integer $code The error code to be returned in the response
     * @param string $response The error message to be returned in the response
     */
    private static function returnErrorResponse($code, $response)
    {
        http_response_code($code);
        if (!is_array($response)) {
            $response = ['Error' => $response];
        }
        $response["code"] = $code;
        echo json_encode($response);
    }
}
