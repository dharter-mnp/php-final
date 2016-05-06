<?php
//var_dump($_POST);

/**
 * Created by PhpStorm.
 * User: DHarter
 * Date: 5/4/2016.
 * Time: 2:11 PM
 */
if (isset($_SERVER['PATH_INFO'])){
    $path = explode("/",$_SERVER['PATH_INFO']);
    if (count($path) <= 1){
        $controller = 'Employee';
    } else {
        $controller = ucfirst($path[1]);
    }
    if (count($path) > 2){
        $action= $path[2];
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
            foreach ($_POST as $key => $val){
                $body = $key;
                break;
            };
            if (empty($body)){
                header('Content-Type: application/json');
                http_response_code(400);
                echo json_encode(["message"=>"Request body required", "code"=> 400]);
                exit();
            }
            $body = json_decode($body, true);
            if (empty($body)){
                header('Content-Type: application/json');
                http_response_code(400);
                echo json_encode(["message"=>"Invalid request body", "code"=> 400]);
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
require_once('../routes.php');
