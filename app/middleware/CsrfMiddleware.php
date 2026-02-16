<?php
class CsrfMiddleware {

    public static function handle() {

        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        if (strpos($uri, '/login') !== false ||
            strpos($uri, '/register') !== false || strpos($uri, '/refresh') !== false) {
            return;
        }

        if (!in_array($method, ['GET','POST', 'PUT', 'DELETE'])) {
            return;
        }

        $headers = getallheaders();
        $csrfHeader = $headers['X-CSRF-Token'] ?? null;

        if (!isset($_SESSION['csrf_token']) || !$csrfHeader) {
            Response::json("CSRF token missing", 403);
        }

        if (!hash_equals($_SESSION['csrf_token'], $csrfHeader)) {
            Response::json("Invalid CSRF token", 403);
        }
    }
}
