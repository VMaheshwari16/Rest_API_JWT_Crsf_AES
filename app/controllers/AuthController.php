<?php

class AuthController {

    public static function register($req) {

        if (empty($req['name']) || empty($req['email']) || empty($req['password'])) {
            Response::json("All fields are required", 422);
        }

        Validator::email($req['email']);
        Validator::password($req['password']);

        if (User::findByEmail($req['email'])) {
            Response::json("Email already exists", 409);
        }

        User::create([
            $req['name'],
            $req['email'],
            password_hash($req['password'], PASSWORD_DEFAULT)
        ]);

        Response::json("User registered successfully", 201);
    }

public static function login($req) {

    $user = User::findByEmail($req['email']);

    if (!$user || !password_verify($req['password'], $user['password'])) {
        Response::json("Invalid credentials", 401);
    }

    $accessToken = JWT::generate([
        "id" => $user['id'],
        "email" => $user['email']
    ]);

    $refreshToken = bin2hex(random_bytes(40));

    $hashedToken = password_hash($refreshToken, PASSWORD_DEFAULT);

    User::deleteAllUserTokens($user['id']);
    $refreshExpiry = time() + (int)JWT_REFRESH_EXPIRY;

    User::saveRefreshToken(
        $user['id'],
        $hashedToken,
        date('Y-m-d H:i:s', $refreshExpiry)
    );

    setcookie(
        "refresh_token",
        $refreshToken,
        $refreshExpiry,
        "/",
        "",
        false,
        true
    );

    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

    Response::json([
    "access_token" => $accessToken,
    "csrf_token" => $_SESSION['csrf_token']
    ]);

}

public static function refresh() {

    if (!isset($_COOKIE['refresh_token'])) {
        Response::json("Unauthorized or expired", 401);
    }

    $refreshToken = $_COOKIE['refresh_token'];

    $record = User::getRefreshToken($refreshToken);

    if (!$record) {
        Response::json("refresh token not found please, login again", 401);
    }

    if (strtotime($record['expires_at']) < time()) {
        Response::json("Refresh token expired", 401);
    }

    $userId = $record['user_id'];

    User::deleteAllUserTokens($userId);

    $newAccess = JWT::generate([
        "id" => $userId
    ]);

    $newRefresh = bin2hex(random_bytes(40));

    $hashedToken = password_hash($newRefresh, PASSWORD_DEFAULT);
    $refreshExpiry = time() + (int)JWT_REFRESH_EXPIRY;

    User::saveRefreshToken(
        $userId,
        $hashedToken,
        date('Y-m-d H:i:s', $refreshExpiry)
    );

    setcookie(
        "refresh_token",
        $newRefresh,
        $refreshExpiry,
        "/",
        "",
        false,
        true
    );

    Response::json([
        "access_token" => $newAccess
    ]);
}


}
