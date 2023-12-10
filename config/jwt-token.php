<?php

use Firebase\JWT\JWT;

require_once __DIR__ . '/../container/container.php';

$jwtPayload = [];
$jwtSecretKey = $_ENV['JWT_SECRET'];
$token = JWT::encode($jwtPayload, (string)$jwtSecretKey, 'HS256');

$response = [
    'token' => $token,
];

header('Content-Type: application/json');
echo json_encode($response);