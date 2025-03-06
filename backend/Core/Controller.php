<?php

require_once "./Core/mkwdb.php";

abstract class Controller
{
    public const ROUTES = [];

    public function __construct(public mkwdb $db)
    {
    }

    public function jsonResponse(array $response, int $status = 200): void
    {
        echo json_encode(['response' => $response, 'status' => $status]);
        die();
    }

    public function validateRoute(string $route): void
    {
        $routeData = static::ROUTES[$route];
        if (
            isset($routeData['user_only'])
            && $routeData['user_only'] == true
            && false == isset($_SESSION['userId'])
        ) {
            $this->jsonResponse(['result' => 'error', 'message' => "Route {$route} requires user to be logged in."]);
        }
    }

    public function validateRequest(string $route, array $request): void
    {
        $routeData = static::ROUTES[$route];
        foreach($routeData['params'] as $param) {
            if (false == isset($request[$param])) {
                $this->jsonResponse(['result' => 'error', 'message' => "Missing param: {$param}"]);
            }
        }
    }
}
