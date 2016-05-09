<?php
namespace mvc;

/**
 * Created by PhpStorm.
 * User: DHarter
 * Date: 5/4/2016
 * Time: 8:30 PM
 */
class EmployeeController
{

    public function index()
    {
        $employees = Employee::findAll();
        if (!empty($employees)) {
            self::returnSuccessResponse($employees);
        }
    }

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

    public function notFound()
    {

    }

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

    private static function returnSuccessResponse($response)
    {
        http_response_code(200);
        echo json_encode($response);
    }

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
