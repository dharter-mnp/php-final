<?php
namespace mvc;

/**
 * Created by PhpStorm.
 * User: DHarter
 * Date: 5/5/2016
 * Time: 4:56 PM
 */
function getControllers()
{
    // just a list of the controllers we have and their actions
    // we consider those "allowed" values
    return array(
        'Employee' => [
            'GET' => ['index', 'show'],
            'POST' => ['create'],
            'PUT' => ['edit'],
            'DELETE' => ['destroy']
        ]
    );

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
