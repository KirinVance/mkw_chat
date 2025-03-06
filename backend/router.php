<?php

require_once "Core/utils.php";
require_once "Core/mkwdb.php";

### CONTROLLERS ###
require_once "Controllers/UsersController.php";
require_once "Controllers/FriendsController.php";
require_once "Controllers/FriendsRequestsController.php";

header("Content-Type: application/json");

$input = json_decode(file_get_contents("php://input"));

loadEnv();

$route = (string)$input->route;
$request = (array)$input->data;

$db = new mkwdb();

$controllers = [
    UsersController::class,
    FriendsController::class,
    FriendsRequestsController::class,
];

foreach ($controllers as $controllerClass) {
    if (in_array($route, array_keys($controllerClass::ROUTES))) {
        $controller = new $controllerClass($db);

        $controller->validateRoute($route);
        $controller->validateRequest($route, $request);

        $functionName = explode('.', $route)[1];
        $controller->$functionName($request);
    }
}

jsonResponse(['result' => 'error', 'message' => "Incorrect route."], 404);
