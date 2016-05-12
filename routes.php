<?php
namespace mvc;

/**
 * Created by PhpStorm.
 * @author DHarter
 * Date: 5/5/2016
 * Time: 4:56 PM
 */

/**
 * Array of the available controllers and their actions.  First action in the array is the default for the method.
 * 
 * @return string[] array of controllers and their methods.
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

/**
 * Calls the requested controller with the specified action.  Default action is the initial action 
 * in the array for the method.
 * 
 * @param string $controller Name of the requested controller.
 * @param string $action Action of the controller to be performed.
 */
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
