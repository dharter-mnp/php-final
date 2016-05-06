<?php
namespace mvc;
/**
 * Created by PhpStorm.
 * User: DHarter
 * Date: 5/5/2016
 * Time: 4:56 PM
 */
function getControllers(){
    // just a list of the controllers we have and their actions
    // we consider those "allowed" values
    return array('Employee' => ['GET' =>['index', 'show'], 'POST' =>['create'], 'PUT' => ['edit'], 'DELETE' => ['destroy']]);

}

function call($controller, $action)
{
    // require the file that matches the controller name
    require_once('Controller/' . $controller . 'Controller.php');
    // create a new instance of the needed controller
    switch ($controller) {
        case 'Employee':
            $controller = new EmployeeController();
            break;
    }
    // call the action
    $controller->{$action}();
}

    // check that the requested controller and action are both allowed
    // if someone tries to access something else he will be redirected to the error action of the pages controller
    $controllers = getControllers();
    if (array_key_exists($controller, $controllers)) {
        if (empty($action)){
            $action = $controllers[$controller][$method][0];
        }
        if (in_array($action, $controllers[$controller][$method])) {
            header('Content-Type: application/json');
            call($controller, $action);
        } else {
            http_response_code(404);
        }
    } else {
        http_response_code(404);
    }
