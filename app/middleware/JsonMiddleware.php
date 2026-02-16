<?php

class JsonMiddleware {

    public static function handle(): array {

        header("Content-Type: application/json");

        $method = $_SERVER['REQUEST_METHOD'];

        $bodyRequiredMethods = ['POST', 'PUT'];

        if (!in_array($method, $bodyRequiredMethods)) {
            return [];
        }

        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (stripos($contentType, 'application/json') === false) {
            Response::json("Content-Type must be application/json", 400);
        }

        $raw = file_get_contents("php://input");

        if (trim($raw) === '') {
            Response::json("Request body cannot be empty", 400);
        }

        $data = json_decode($raw, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Response::json("Invalid JSON", 400);
        }

        return $data;
    }
}
