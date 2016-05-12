<?php
/**
 * Created by PhpStorm.
 * @author DHarter
 * Date: 5/4/2016.
 * Time: 2:11 PM
 */

use function mvc\call;
use function mvc\getControllers;

require_once($_SERVER['DOCUMENT_ROOT'] . "/../routes.php");

if (isset($_SERVER['PATH_INFO'])) {
    $path = explode("/", $_SERVER['PATH_INFO']);
    if (count($path) <= 1) {
        $controller = 'Employee';
    } else {
        $controller = ucfirst($path[1]);
    }
    if (count($path) > 2) {
        $action = $path[2];
    }
}
if (isset($_SERVER['REQUEST_METHOD'])) {
    $method = $_SERVER['REQUEST_METHOD'];
} else {
    $method = 'GET';
}

switch ($method) {
    case 'GET':
        break;
    case 'POST':
        if (isset($_POST)) {
            foreach ($_POST as $key => $val) {
                $body = $key;
                break;
            };
            if (empty($body)) {
                header('Content-Type: application/json');
                http_response_code(400);
                echo json_encode(["message" => "Request body required", "code" => 400]);
                exit();
            }
            $body = json_decode($body, true);
            if (empty($body)) {
                header('Content-Type: application/json');
                http_response_code(400);
                echo json_encode(["message" => "Invalid request body", "code" => 400]);
                exit();
            }
        }
        break;
    case 'DELETE':
        break;
    default:
        http_response_code(500);
        break;
}

// check that the requested controller and action are both allowed
// if someone tries to access something else he will be redirected to the error action of the pages controller
$controllers = getControllers();
if (array_key_exists($controller, $controllers)) {
    if (empty($action)) {
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
